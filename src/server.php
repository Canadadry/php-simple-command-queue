<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use React\Socket\ConnectionInterface;


function StartServer(){
	register_shutdown_function('stopMyScript');
	$loop = React\EventLoop\Factory::create();
	$socket = new React\Socket\Server('127.0.0.1:8080', $loop);

	$socket->on('connection', function (ConnectionInterface $conn) {
		printf("Hello " . $conn->getRemoteAddress() . "!\n");
	    
	    $conn->write("Hello " . $conn->getRemoteAddress() . "!\n");
	    $conn->write("Say \"{'action':'talk'}\" for instruction\n");

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
			$conn->write("What do you whant to do ? \nStart a couting script or stop an existing one ?\nSo what's gonna be ? start or stop\n");
			break;
		case 'start':
			startMyScript();
			$conn->write("Script is starting!\n");
			break;
		case 'stop':
			stopMyScript();
			$conn->write("Stopping script!\n");
			break;
		case 'out':
			$conn->write(file_get_contents('nohup.out'));
			break;
		default: handleBadInput($conn,'switch failed');
	}

}

function handleBadInput(ConnectionInterface $conn,$reason)
{
	echo "Some client say shit... ($reason)\n";
    $conn->write("Mal formatted message sended : $reason ! Send \"{'action':'talk'}\" for instruction\n");
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

StartServer();