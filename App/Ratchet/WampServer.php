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
     * Process RPC calls
     * see more in https://github.com/cboden/Ratchet-examples/blob/master/src/Website/ChatRoom.php
     *
     * be careful not to override the onCall method, specially $id
     *
     * @param ConnectionInterface $conn     The connection object loaded with the Ratchet\Wamp\WampConnection methods
     * @param $id                           The unique id gigen to the client to respond to
     * @param $callable                     The topic object (yes, RPC method is trated as a topic)
     * @param array $params                 The array params given to the RPC method, if any
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
                $joke_id = $params[0];
                $joke = KnockKnockJokes::getJokeById($joke_id);
                return $conn->callResult($id, array('joke' => $joke));
                break;

            default:
                // if you see the callError not being recognized in your editor, it is defined here: Ratchet\Wamp\WampConnection (vendor\cboden\ratchet\src\Ratchet\Wamp\WampConnection.php)
                return $conn->callError($id, 'Unknown call');
            break;
        }
    }

    /**
     * The method to handle subscription events from clients
     *
     * Since WampServer adds and removes subscribers to Topics automatically,
     * I just need to keep track of topics in order to access them if other methods
     * don't provide an interface to the topic object (like onMessageToPush)
     *
     * @param ConnectionInterface $conn     The connection object
     * @param $topic                        The topic object
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

    /**
     * The method to handle the onUnsubscribe events
     *
     * @param ConnectionInterface $conn     The connection object
     * @param $topic                        The topic object
     */
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

    /**
     * The method to handle publish events
     *
     * @param ConnectionInterface $conn     The connection object
     * @param $topic                        The topic object
     * @param $msg                          The msg string
     * @param array $exclude                The array of excluded subscribers to the topic being published to
     * @param array $eligible               The array of eligible subscribers to the topic being published to
     */
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

    /**
     * The method defining the onOpen event of each client making connection with the server
     *
     * useful properties or methods:
     * $conn->resourceId
     * $conn->remoteAddress
     * $conn->getRemoteAddress()  see more in vendor\react\socket\src\ConnectionInterface.php
     * $conn->getLocalAddress()  see more in vendor\react\socket\src\ConnectionInterface.php
     *
     * @param ConnectionInterface $conn     The connection object
     */
    public function onOpen(ConnectionInterface $conn) {
        Logger::debug($this, "new connection ({$conn->resourceId}) from IP {$conn->remoteAddress}");
        $this->clients->attach($conn);
    }

    /**
     * The method defining the onClose event of each client closing connection
     *
     * @param ConnectionInterface $conn     The connection object
     */
    public function onClose(ConnectionInterface $conn) {
        Logger::debug($this, "closing connection ({$conn->resourceId})");
        $this->clients->detach($conn);

        // notify about disconnecting clients
        foreach($this->topics as $topic){
            $this->onUnSubscribe($conn, $topic);
        }
    }

    /**
     * The error handler method
     *
     * from the Exception object you can use $e->getMessage() $e->getFile() $e->getLine() $e->getTrace() to get more exception info
     *
     * @param ConnectionInterface $conn     The connection object
     * @param \Exception $e                 The Exception object
     */
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

    /**
     * this is just a method that is being defined here so clients can make a demo RPC
     */
    private function getphpversion()
    {
        return phpversion();
    }

    /**
     * It is handy to have the $loop object, note that it is being defined by ref
     *
     * @param &$loop
     */
    public function setLoop(&$loop)
    {
        $this->loop = $loop;

        $this->setPeriodicTimer();
    }

    /**
     * A method to define loop works
     *
     * add all the periodic timers you want here
     *
     * I am using this method to define periodic timers to add some fake traffic to the topics used in the demo
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
