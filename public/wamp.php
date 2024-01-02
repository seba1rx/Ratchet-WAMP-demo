<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>WAMP</title>

    <style>
        ul>li{
            margin: 5px;
        }

        button{
            cursor: pointer;
        }

        div{
            padding: 5px;
            margin: 5px;
        }
    </style>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/autobahn_old.min.js"></script>



</head>

<body>
    <h2>Open the browser console in console tab</h2>
    <br>

    <input type="text" id="message" autocomplete="off">
    <button id="send" type="button">Send</button>

    <ul>
        <li><button type="button" onclick="subscribeToAll()">Subscribe to Jokes and mainChat</button></li>
        <li><button type="button" onclick="subscribe('privateChat')">subscribe to privateChat</button></li>
        <li><button type="button" onclick="unsubscribe('Jokes')">Unsubscribe from Jokes</button></li>
        <li><button type="button" onclick="unsubscribe('mainChat')">Unsubscribe from mainChat</button></li>
        <li><button type="button" onclick="makeRPC('getphpversion')">rpc: getphpversion</button></li>
        <li><button type="button" onclick="makeRPC('getAllJokes')">rpc: getAllJokes</button></li>
        <li><button type="button" onclick="makeRPC('getJoke', 16)">rpc: getJoke id 16</button></li>
        <li><button type="button" onclick="unsubscribeFromAll()">Unsubscribe from all topics</button></li>
        <li><button type="button" onclick="closeConnection()">Close connection</button></li>
    </ul>
    <br>

    <div id="content"></div>
</body>

<script>
        // var wampHostname = 'localhost';
        var wampHostname = 'ratchet.localdev';
        var wampPort = '9090';
        var useSecure = false;

        if (useSecure) {
            var protocolScheme = 'wss://';
        } else {
            var protocolScheme = 'ws://';
        }

        var mySubscriptions = [];

        var conn; // The websocket connection variable

        var onOpen = function(event) {
            console.log("connection established with server, connection id is " + conn.sessionid());
        };

        var onClose = function() {
            console.log('WebSocket connection closed');
        };

        function closeConnection(){
            conn.close();
        }

        function publish(topic, msg){
            conn.publish(topic, msg);
        }

        function subscribeToAll(){
            subscribe("Jokes");
            subscribe("mainChat");
        }

        function subscribe(topicName){
            try{
                conn.subscribe(topicName, function(topic, data) {
                    if(!mySubscriptions.includes(topicName)){
                        mySubscriptions.push(topic); //add topic to mySubscriptions
                    }
                    console.log("Topic: " + topic, data);
                    try{
                        let payload = JSON.stringify(data);
                        appendToContentDiv(payload);
                    }catch(e){
                        console.log(e);
                    }
                });
            }catch(e){
                console.log(e);
            }
        }

        function unsubscribe(topic){
            try{
                conn.unsubscribe(topic);
                mySubscriptions.splice(mySubscriptions.indexOf(topic), 1); //remove the topic from mySubscriptions
                console.log("you unsubscribed from topic " + topic);
            }catch(e){
                console.log(e);
            }
        }

        function unsubscribeFromAll(){
            mySubscriptions.forEach((topic) => function(topic){
                unsubscribe(topic);
            });
        }

        /**
         * makes an RPC (remote procedure call)
         *
         * @param {String} method   The method name published by the wamp server
         * @param {Array} args      The method args in an array
         */
        function makeRPC(method, args = null){
            conn.call(method, args).then(function (result) {
                // result
                console.log("rpc result: ", result);
            }, function(error) {
                // error
                console.log("rpc error: ",error);
            });
        }

        /**
         * form handler
         */
        $("#send").on("click", function(e) {
            var message = $("#message").val();
            publish('mainChat', message);
            $("#message").val('');
        });

        $('#message').keyup(function(e) {
            if (e.keyCode == 13) {
                $("#send").click();
            }
        });


        /**
         * when page has loaded, then connect to wamp server
         */
        $( document ).ready(function() {
            makeConnection();
        });

        function makeConnection(){
            console.log("Connecting to wamp server");
            conn = new ab.Session(protocolScheme + wampHostname + ':' + wampPort,
                onOpen,
                onClose, {
                    'skipSubprotocolCheck': false,
                    'verify_peer': false
                }
            );
        }

        /**
         * add the content received from subscription to topics
         */
        function appendToContentDiv(payload){
            let data = JSON.parse(payload);
            let newContent = "<br>------------------------------------------------";
            newContent += "<br>Topic: " + data.topic;
            newContent += "<br>Time: " + data.when;
            newContent += "<br>From: " + data.client_id;
            if(typeof data.content !== "undefined"){
                newContent += "<br>msg: " + data.content;
            }
            if(typeof data.subscribers !== "undefined"){
                newContent += "<br>subscribers: " + data.subscribers;
            }
            $("#content").prepend(newContent);
        }

    </script>

</html>