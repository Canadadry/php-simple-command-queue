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
				$decodedData = json_decode($data);
				if(empty($decodedData)) return;
				if(array_key_exists('message', $decodedData) == false) return;
				if(array_key_exists('message', $decodedData) == false) return;
				echo $decodedData->message;
				if(array_key_exists('actions', $decodedData))
				{
					echo "here what you can do : \n";
					foreach ($decodedData->actions as $value)
					{
						echo "    $value\n";
					}
				}

				$command = readline("\n> ");   

				$connection->write("{\"action\":\"$command\"}");
			}
		);
		$connection->on('close', function () { echo '[CLOSED]' . PHP_EOL; });
	}, 
	'printf'
);
$loop->run();