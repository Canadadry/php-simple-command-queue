<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use React\Socket\ConnectionInterface;


function StartServer(){
	register_shutdown_function('stopMyScript');
	$loop = React\EventLoop\Factory::create();
	$socket = new React\Socket\Server('127.0.0.1:8080', $loop);

	$socket->on('connection', function (ConnectionInterface $conn) {
		printf("Hello " . $conn->getRemoteAddress() . "!\n");
	    
		tellPresentation($conn);

	    $conn->on('data', function ($data) use ($conn) {
	    	handleIncommingData($data,$conn);
	    });
	});

	$loop->run();
}

function handleIncommingData($data,ConnectionInterface $conn)
{
	$decodedData = json_decode($data);
	if(empty($decodedData)) return handleBadInput($conn,'json_decode error');
	if(array_key_exists('action', $decodedData) == false) return handleBadInput($conn,'array_key_exists failed');

	switch ($decodedData->action) {
		case 'talk':
			tellPresentation($conn);
			break;
		case 'list':
			tellAction($conn);
			break;
		case 'start':
			startMyScript();
			tellMessage($conn,"Script is starting!\n");
			break;
		case 'status':
			tellMessage($conn,getStatusOfMyScript()."\n");
			break;
		case 'stop':
			stopMyScript();
			tellMessage($conn,"Stopping script!\n");
			break;
		case 'out':
			tellMessage($conn,file_get_contents('nohup.out'));
			break;
		case 'delete':
			unlink('nohup.out');
			tellMessage($conn,"output removed");
			break;
		default: handleBadInput($conn,'switch failed');
	}
}

function tellPresentation(ConnectionInterface $conn)
{	    
	tellMessage($conn,"Hello " . $conn->getRemoteAddress() . "!\nWhat do you whant to do ? \nIf you dont know start by sending this json data\"{'action':'list'}\"\nWe will allways be exchanging json data");
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
	tellMessage($conn,"Mal formatted message sended : $reason ! Send \"{'action':'talk'}\" for instruction\n",400);
}

// thanks to https://stackoverflow.com/questions/19553905/php-how-to-start-a-detached-process
function startMyScript() {
    exec('nohup php src/count.php > nohup.out & > /dev/null');
}

function stopMyScript() {
    exec('ps a | grep count.php | grep -v grep', $otherProcessInfo);
    $otherProcessInfo = array_filter(explode(' ', $otherProcessInfo[0]));
    $otherProcessId = $otherProcessInfo[0];
    exec("kill $otherProcessId");
}

function getStatusOfMyScript() {
    exec('ps a | grep count.php | grep -v grep', $otherProcessInfo);
    return $otherProcessInfo[0];
}

StartServer();