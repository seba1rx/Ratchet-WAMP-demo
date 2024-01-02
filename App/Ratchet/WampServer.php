<?php

namespace App\Ratchet;

use Ratchet\Wamp\WampServerInterface;
use Ratchet\ConnectionInterface;
use App\Logging\Logger;
use App\Models\KnockKnockJokes;

class WampServer implements WampServerInterface {
    protected $clients;

    /**
     * this var is needed in order to access the topics from methods that have no access to the topic object
     *
     * @var $topics
     */
    protected $topics;

    /**
     * the loop object set by ref
     *
     * @var $loop
     */
    protected $loop;

    public function __construct() {
        Logger::debug($this, "Sarting new Chat server using Ratchet");
        $this->clients = new \SplObjectStorage;
        $this->topics = new \SplObjectStorage;
    }

    /**
     * see more in https://github.com/cboden/Ratchet-examples/blob/master/src/Website/ChatRoom.php
     */
    public function onCall(ConnectionInterface $conn, $id, $callable, array $params) {
        // $callable is the same as $topic
        Logger::debug($this, "processing RPC {$callable->getId()} with args: " . json_encode($params));

        switch ($callable) {
            case 'getphpversion':
                /**
                 * example of RPC calling a method defined in this class
                 */
                $response = $this->getphpversion();
                return $conn->callResult($id, array('version' => $response));
            break;

            case 'getAllJokes':
                /**
                 * example of RPC calling a method defined in another class
                 */
                $response = KnockKnockJokes::getAllJokes();
                return $conn->callResult($id, array('jokes' => $response));
                break;

            case 'getJoke':
                /**
                 * example of RPC calling a method defined in another class, using arguments
                 */
                $id = $params[0];
                $joke = KnockKnockJokes::getJokeById($id);
                return $conn->callResult($id, array('joke' => $joke));
                break;

            default:
                // if you see the callError not being recognized in your editor, it is defined here: Ratchet\Wamp\WampConnection (vendor\cboden\ratchet\src\Ratchet\Wamp\WampConnection.php)
                return $conn->callError($id, 'Unknown call');
            break;
        }
    }

    /**
     * Since WampServer adds and removes subscribers to Topics automatically,
     * I just need to keep track of topics in order to acces them if the method
     * does not provide an interface to the topic object
     */
    public function onSubscribe(ConnectionInterface $conn, $topic) {

        if(!$this->topics->contains($topic)){
            $this->topics->attach($topic);
            Logger::debug($this, "New topic " . $topic->getId());
        }

        // Inform the subscribers of this topic about the number of total subscribers
        $messageData = array(
            'topic' => $topic->getId(),
            'action' => 'subscribe',
            'client_id' => $conn->resourceId,
            'subscribers' => $topic->count(),
            'when'     => date('H:i:s')
        );

        $topic->broadcast($messageData);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {

        Logger::debug($this, "Client id ({$conn->resourceId}) unsubscribing from " . $topic->getId());

        // Inform the subscribers of this topic about the number of total subscribers
        $messageData = array(
            'topic' => $topic->getId(),
            'action' => 'unsubscribe',
            'client_id' => $conn->resourceId,
            'subscribers' => $topic->count(),
            'when'     => date('H:i:s')
        );

        // if topic has no subscribers, then unset the topic
        if($this->topics->contains($topic)){
            if($topic->count() == 0){
                $this->topics->detach($topic);
                Logger::debug($this, "No clients subscribed to {$topic->getId()}, removing topic " . $topic->getId());
            }
        }

        $topic->broadcast($messageData);
    }

    public function onPublish(ConnectionInterface $conn, $topic, $msg, array $exclude, array $eligible) {
        Logger::debug($this, "new publish event on topic {$topic->getId()} from IP {$conn->remoteAddress} client_id {$conn->resourceId} msg: {$msg}");

        $data = [
            'topic' => $topic->getId(),
            'action' => 'publish',
            'client_id' => $conn->resourceId,
            'content' => $msg,
            'when' => date('H:i:s'),
        ];

        $this->onMessageToPush($data);
    }

    public function onOpen(ConnectionInterface $conn) {
        Logger::debug($this, "new connection ({$conn->resourceId}) from IP {$conn->remoteAddress}");
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn) {
        Logger::debug($this, "closing connection ({$conn->resourceId})");
        $this->clients->detach($conn);

        // notify about disconnecting clients
        foreach($this->topics as $topic){
            $this->onUnSubscribe($conn, $topic);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        Logger::debug($this, "An error has occurred {$e->getMessage()}");
        $conn->close();
    }

    /**
     * Publish a new message to a topic's subscribers. The topic name is
     * included in the message itself. In this application, we call this method
     * periodically through the periodic timer that we have added to the loop.
     *
     * @param array $data
     */
    public function onMessageToPush(array $data){

        /**
         * The format I'll be using here should be like:
         * [
         *     topic ........ the topic name as a string
         *     client_id .... the $conn->resourceId or "fake" for the periodic timers
         *     content ...... the message as a string, or an array
         *     when ......... the time in H:i:s format
         * ]
         */

        foreach($this->topics as $topic){
            if($topic->getId() == $data["topic"]){
                // re-send the data to all the clients subscribed to that topic
                $topic->broadcast($data);
                break;
            }
        }
    }

    private function getphpversion()
    {
        return phpversion();
    }

    public function setLoop(&$loop)
    {
        $this->loop = $loop;

        $this->setPeriodicTimer();
    }

    /**
     * add all the periodic timers you want here
     */
    private function setPeriodicTimer(){
        // this will simulate activity in Jokes topic
        $this->loop->addPeriodicTimer(20, function() {
            $random_joke = \App\Models\KnockKnockJokes::getRandomJoke();
            $data = array(
                'topic' => 'Jokes',
                'action' => 'publish',
                'client_id' => "faker",
                'content' => $random_joke["joke"],
                'when' => date('H:i:s')
            );
            $this->onMessageToPush($data);
            Logger::debug($this, "Sending new joke to jokes topic, joke id ". $random_joke["joke_id"]);
        });

        // fake messages in topic "mainChat" so we can test our browser client
        $this->loop->addPeriodicTimer(5, function() {
            $random_msg = \App\Models\MockMsg::getRandomMockMsg();
            $data = array(
                'topic' => 'mainChat',
                'action' => 'publish',
                'client_id' => "faker",
                'content' => $random_msg,
                'when' => date('H:i:s')
            );
            $this->onMessageToPush($data);
            Logger::debug($this, "Sending new fake msg to mainChat topic, msg: ". $random_msg);
        });
    }
}
