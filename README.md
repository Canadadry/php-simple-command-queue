# PHP Simple Command Queue

## Launch

The aim of this project is to build a small app that listen to a socket and can launch script. 

For now it only launch php script.

This app only dependency is on `react/socket` wich as very few dependency.

Right now you can launch the server on port 8080 with

```
php src/server.php port script_folder
```

for example 

```
php src/server.php 8080 src/
```

## Interaction

To interact with it just connect to `127.0.0.1:8080` and send a json string with the requested action like this 

```
echo '{"action":"talk"}' | netcat 127.0.0.1 8080
```

This not an http server so dont use `curl`

To send data from php you can use this code: 

```php
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

## Action

  * *talk* : give instruction
  * *list* : list all valid action
  * *start* [scriptName]: start scriptName (default script name is `count.php`)
  * *stop* [scriptName]: stop scriptName (default script name is `count.php`)
  * *status* [scriptName]: print status of scriptName (is it running or not) (default script name is `count.php`)
  * *out* [scriptName]: print output of scriptName (default script name is `count.php`)
  * *delete* [scriptName]: delete output of scriptName (default script name is `count.php`)


## Example usage 

```bash
src/server.php 8080 src/ &
echo '{"action":"start", "message":"count.php"}' | netcat 127.0.0.1 8080
echo '{"action":"stop", "message":"count.php"}' | netcat 127.0.0.1 8080
echo '{"action":"out", "message":"count.php"}' | netcat 127.0.0.1 8080
```





