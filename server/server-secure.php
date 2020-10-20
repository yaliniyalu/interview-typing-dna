<?php
include_once __DIR__ . '../config.php';

include_once __DIR__. '/vendor/autoload.php';
include_once __DIR__ . '/src/Server.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$app = new HttpServer(
    new WsServer(
        new Server()
    )
);

$loop = \React\EventLoop\Factory::create();

$socketServer = new \React\Socket\Server('0.0.0.0:8088', $loop);
$socketServer = new \React\Socket\SecureServer($socketServer, $loop, [
    'local_cert' => SSL_CERT,
    'local_pk' => SSL_PK,
    'verify_peer' => false
]);

$ioServer = new \Ratchet\Server\IoServer($app, $socketServer, $loop);
$ioServer->run();