<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Ratchet\WampServer as MyWampServer;
use App\Logging\Logger;

// An event loop
$loop = React\EventLoop\Loop::get();
// $service_url = "127.0.0.1"; // listen only local activity
$service_url = "0.0.0.0"; // listen local and external activity
$service_port = 9090;


// An object that will handle the WampServer events through its methods
$myWampServer = new MyWampServer();

// Set up a WebSocket server to handle the websocket(for clients wanting real-time updates)
$webSocket = new React\Socket\SocketServer($service_url . ":" . $service_port, [], $loop);

// Set up a Wamp server object to handle subscriptions
$wampServer = new \Ratchet\Wamp\WampServer(
    $myWampServer
);

// Set up an I/O server to handle the low level events (read/write) of a socket
$ioserver = new \Ratchet\Server\IoServer(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            $wampServer
        )
    ),
    $webSocket,
    $loop
);

// it is handy to have the loop in our WAMP server class, note that we pass the loop by ref
$myWampServer->setLoop($loop);

$ioserver->run();  // Equals to $loop->run();