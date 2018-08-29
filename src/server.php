#!/usr/bin/env php
<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use React\Socket\ConnectionInterface;

class Server
{

	function __construct($argv)
	{
		$this->scriptPath = 'src/';
		$this->port = 8080;
		$this->defautParameter = "count.php";

		if(count($argv) > 1 && is_numeric($argv[1]) )
		{
			$this->port = $argv[1];
		}

		if(count($argv) > 2 && is_dir($argv[2]) )
		{
			$this->scriptPath = $argv[2];
			if(substr($this->scriptPath, -1)!='/')
			{
				$this->scriptPath = $this->scriptPath . '/';
			}
		}

		$this->startServer();
	}

	function startServer(){
		$loop = React\EventLoop\Factory::create();
		$socket = new React\Socket\Server('127.0.0.1:'.$this->port, $loop);

		$socket->on('connection', function (ConnectionInterface $conn) {
			printf("Hello " . $conn->getRemoteAddress() . "!\n");
		    
			$this->tellPresentation($conn);

		    $conn->on('data', function ($data) use ($conn) {
		    	$this->handleIncommingData($data,$conn);
		    });
		});

		$loop->run();
	}

	function handleIncommingData($data,ConnectionInterface $conn)
	{
		$parameter = $this->defautParameter;
		$decodedData = json_decode($data);
		if(empty($decodedData)) return $this->handleBadInput($conn,'json_decode error');
		if(array_key_exists('action', $decodedData) == false) return $this->handleBadInput($conn,'array_key_exists failed');
		if(array_key_exists('parameter', $decodedData))
		{
			if(is_file($this->scriptPath.$decodedData->parameter))
			{
				$parameter = $decodedData->parameter;
			}
		}

		switch ($decodedData->action) {
			case 'talk':
				$this->tellPresentation($conn);
				break;
			case 'list':
				$this->tellAction($conn);
				break;
			case 'start':
				$this->startMyScript($parameter);
				$this->tellMessage($conn,"Script $parameter is starting!\n");
				break;
			case 'status':
				$this->tellMessage($conn,"status of $parameter\n".$this->getStatusOfMyScript($parameter)."\n");
				break;
			case 'stop':
				$this->stopMyScript($parameter);
				$this->tellMessage($conn,"Stopping $parameter script!\n");
				break;
			case 'out':
				$this->tellMessage($conn,file_get_contents($parameter.'.out'));
				break;
			case 'delete':
				unlink('nohup.out');
				$this->tellMessage($conn,"output removed");
				break;
			default: $this->handleBadInput($conn,'switch failed');
		}
	}

	function tellPresentation(ConnectionInterface $conn)
	{	    
		$this->tellMessage($conn,"Hello " . $conn->getRemoteAddress() . "!\nWhat do you whant to do ? \nIf you dont know start by sending this json data\"{'action':'list'}\"\nWe will allways be exchanging json data");
	}

	function tellAction(ConnectionInterface $conn)
	{
		$actions = json_encode(["start","stop","out","list","talk","delete","status"]);
		$msg = "Here what you can do";
		$conn->write("{\"message\":\"$msg\",\"actions\":$actions,\"status\":200}");
	}

	function tellMessage(ConnectionInterface $conn,$msg,$status = 200)
	{
		$conn->write(json_encode(["message"=>$msg,"status"=>$status]));
	}

	function handleBadInput(ConnectionInterface $conn,$reason)
	{
		echo "Some client say shit... ($reason)\n";
		$this->tellMessage($conn,"Mal formatted message sended : $reason ! Send \"{'action':'talk'}\" for instruction\n",400);
	}

	// thanks to https://stackoverflow.com/questions/19553905/php-how-to-start-a-detached-process
	function startMyScript($scriptName = "count.php") 
	{
	    exec("nohup $this->scriptPath$scriptName > $scriptName.out & > /dev/null");
	}

	function stopMyScript($scriptName = "count.php")
	{
	    $otherProcessInfo = array_filter(explode(' ', $this->getStatusOfMyScript($scriptName)));
	    $otherProcessId = $otherProcessInfo[0];
	    exec("kill $otherProcessId");
	}

	function getStatusOfMyScript($scriptName = "count.php") {
	    exec("ps a | grep $scriptName | grep -v grep", $otherProcessInfo);
	    return $otherProcessInfo[0];
	}


}

$server = new Server($argv);
