<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;

$loop = Factory::create();

$connector = new Connector($loop);
$connector->connect('127.0.0.1:8080')->then(
	function (ConnectionInterface $connection)
	{
    	$connection->on('data', 
    		function ($data) use ($connection) 
    		{
    			echo $data; 
				$line = readline("Commande : ");   
				$connection->write($line);

    		}
    	);
	    $connection->on('close', function () { echo '[CLOSED]' . PHP_EOL; });
	}, 
	'printf'
);
$loop->run();