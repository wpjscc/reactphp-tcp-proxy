<?php

require './vendor/autoload.php';


$socket = new React\Socket\SocketServer('0.0.0.0:'.getParam('--local-port'));

$socket->on('connection', function (React\Socket\ConnectionInterface $proxyConnection) {
    $buffer = '';
    $proxyConnection->on('data', $fn = function ($data) use (&$buffer) {
        $buffer .= $data;
    });

    (new React\Socket\Connector(array('timeout' => 3.0)))
    ->connect("tcp://".getParam("--dest-host").":".getParam("--dest-port"))
    ->then(function (React\Socket\ConnectionInterface $connection) use ($proxyConnection, $fn, &$buffer)  {

        $proxyConnection->removeListener('data', $fn);
        $fn = null;
        $proxyConnection->pipe($connection);
        $connection->pipe($proxyConnection);

        if ($buffer) {
            $connection->write($buffer);
            $buffer = '';
        }

    }, function (Exception $e) use ($proxyConnection) {
        $proxyConnection->write("HTTP/1.1 502 Bad Gateway\r\n\r\n".$e->getMessage());
        $proxyConnection->end();
    });
});



function getParam($key, $default = null){
    foreach ($GLOBALS['argv'] as $arg) {
        if (strpos($arg, $key) !==false){
            return explode('=', $arg)[1];
        }
    }
    return $default;
}
