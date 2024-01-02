<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <span id="myid"></span>
    <br>
    <input type="text" id="message">
    <button id="send">Send</button>
    <div id="clients">
        <p>list of clients connected:</p>
        <ul></ul>
    </div>
    <div id="chat"></div>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script>
        var myid = null;
        var destination_id = null;
        const chat = document.getElementById('chat');
        const messageInput = document.getElementById('message');
        const sendButton = document.getElementById('send');

        const port = 9090;
        const url = "ws://ratchet.localdev:" + port;

        const socket = new WebSocket(url);

        socket.onopen = (event) => {
            socket.send("Hello server");
            socket.send("getConnectedClients");
        };

        socket.onmessage = (event) => {
            // is valid json and not [number|boolean| boolean string, infinity, empty, empty space]
            if(isJsonString(event.data) && isNaN(event.data)){
                processServerMsg(event.data);
            }else{
                showMessage(event.data, "Server");
            }
        };

        socket.onerror = (event) => {
            console.log("an error occurred", event);
        };

        socket.onclose = (event) => {
            console.log("Connection has closed", event);
        };

        function sendMessage(theMessage, origin) {
            showMessage(theMessage, origin);

            if(destination_id == null){
                console.log("please select a client to talk to");
                return;
            }
            let payload = {
                "text": theMessage,
                "destination_id": destination_id
            };
            socket.send(payload);
        }

        function showMessage(theMessage, origin) {
            const newMessage = document.createElement('p');
            var date = new Date();
            options = {
                month: "numeric",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                second: "numeric",
                timeZone: "America/Santiago",
            };
            date = new Intl.DateTimeFormat('en-US', options).format(date);
            newMessage.textContent = "[" + origin + " " + date + "] " + theMessage;
            chat.appendChild(newMessage);
        }

        sendButton.addEventListener('click', () => {
            const message = messageInput.value;
            socket.send(message);
            messageInput.value = '';
        });

        $('#message').keyup(function(e) {
            if (e.keyCode == 13) {
                const message = messageInput.value;
                messageInput.value = '';
                sendMessage(message, "Client");
            }
        });

        function setId(id){
            myid = id;
            $("#myid").text("Client id = " + myid);
        }

        function setDestinationId(id){
            destination_id = id;
            console.log("Messages will be sent to client id " + id);
        }

        function updateClientsList(new_id){
            $("#clients ul").append('<li><span onclick="setDestinationId('+new_id+')">'+new_id+'</span></li>');
        }

        /**
         * validates is the param is a valid json string or not
         * usage: isJson("hello") //false
         * usage: isJson(123) //true
         * usage: isJson([{"foo": 123}]) //true
         */
        function isJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

        /**
         * processes the json string
         *
         * the expected custom format is:
         * {
         *     "date": "the date string",
         *     "msg": {
         *         "text": "the actual message",
         *         "from": "the user name who sent the message",
         *         "topic": "the chatroom name"
         *     }
         *     "cmd": {
         *         "fn": "the callback function to be evaluated",
         *         "args": "string or null"
         *     }
         * }
         */
        function processServerMsg(jsonString){

            console.log(jsonString);

            let json = JSON.parse(jsonString);

            //if cmd is set
            if (typeof json.cmd !== 'undefined') {
                let fn = json.cmd.fn + "(" + json.cmd.args + ")";
                eval(fn);
            }

            // the message
            if (typeof json.msg !== 'undefined') {
                showMessage(json.msg.text, json.msg.from + " topic: " + json.msg.topic);
            }
        }

    </script>
</body>

</html>