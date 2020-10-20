<?php

include_once __DIR__. '/vendor/autoload.php';
include_once __DIR__ . '/src/Server.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Server()
        )
    ),
    8088
);

echo "server started on port 8088";
$server->run();