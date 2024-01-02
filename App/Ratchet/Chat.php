<?php

namespace App\Ratchet;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Logging\Logger;

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        Logger::debug($this, "Sarting new Chat server using Ratchet");
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        Logger::debug($this, "new connection ({$conn->resourceId})");
        $this->clients->attach($conn);
        // $conn->send("Hello new client");

        $wellcome = [
            "date" => date("m-d H:i:s"),
            "msg" => [
                "text" => "Hello, your client id is " . $conn->resourceId,
                "from" => "Server",
                "to" => $conn->resourceId,
                "topic" => "Hello Message",
            ],
            "cmd" => [
                "fn" => "setId",
                "args" => $conn->resourceId,
            ]
        ];

        $this->sendMsg($wellcome, $conn);

        $payload = [
            "date" => date("m-d H:i:s"),
            "cmd" => [
                "fn" => "updateClientsList",
                "args" => $conn->resourceId,
            ]
        ];
        $this->broadcast($payload, [$conn->resourceId]);

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        Logger::debug($this, "new message from connection id {$from->resourceId} is saying: {$msg}");

        if($this->isJson($msg)){
            $data = json_decode($msg, true);
            $payload = [
                "date" => date("m-d H:i:s"),
                "msg" => [
                    "text" => $data["text"],
                    "from" => "Server",
                    "to" => $data["destination_id"],
                    "topic" => $data["topic"],
                ],
                // "cmd" => [
                //     "fn" => "log",
                //     "args" => "something",
                // ]
            ];

            foreach ($this->clients as $client) {
                if ($client->resourceId === (int)$data["destination_id"]) {
                    $client->sendMsg($payload, $client);
                }
            }

        }else{
            if($msg == "getConnectedClients"){
                Logger::debug($this, "Client id {$from->resourceId} is asking for the clients list, returning clients list");

                $clientsList = [];
                foreach($this->clients as $client){
                    $clientsList[] = $client->resourceId;
                }

                $payload = [
                    "date" => date("m-d H:i:s"),
                    "cmd" => [
                        "fn" => "updateClientsList",
                        "args" => $clientsList,
                    ]
                ];
                $this->sendMsg($payload, $from);
            }
        }
        // else{
        //     // this will broadcast the msg
        //     foreach ($this->clients as $client) {
        //         if ($from !== $client) {
        //             $client->send($msg);
        //         }
        //     }
        // }
    }

    public function onClose(ConnectionInterface $conn)
    {
        Logger::debug($this, "closing connection ({$conn->resourceId})");
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Logger::debug($this, "An error has occurred {$e->getMessage()}");
        $conn->close();
    }

    private function sendMsg(array $payload, ConnectionInterface &$destination)
    {
        $destination->send(json_encode($payload));
    }

    private function broadcast(array $payload, array $excludedConnections)
    {
        foreach ($this->clients as $client) {
            if(!in_array($client->resourceId,$excludedConnections)){
                $this->sendMsg($payload, $client);
            }
        }
    }

    /**
     * I'm using php 8.2 so I had to implement this custom function
     * if you use php 8.3 you can use the php native method "json_validate"
     * https://www.php.net/manual/en/function.json-validate.php
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
