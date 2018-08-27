# PHP Simple Command Queue

The aim of this project is to build a small app that listen to a socket and can launch script.

This app only dependency is on `react/socket` wich as very few dependency.

Right now you can launch the server on port 8080 with

```
php src/server.php

```

To interact with it just connect to `127.0.0.1:8080` and send a json string with the requested action like this 

```
echo '{"action":"talk"}' | netcat 127.0.0.1 8080
```

This not an http server so dont use `curl`

To send data from php : 

```
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
```