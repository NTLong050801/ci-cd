<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafka Realtime</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<h1>Kafka Realtime Example</h1>
<div>
    <label for="message">Enter your message:</label>
    <input type="text" id="message">
    <button onclick="sendMessage()">Send</button>
</div>
<hr>
<h2>Received Messages:</h2>
<div id="messages"></div>

<script>
    // Function to send message to producer
    function sendMessage() {
        const message = document.getElementById('message').value;
        axios.post('/produce', { message }).then(response => {
            alert(response.data);
        }).catch(error => {
            console.error('Error:', error);
        });
    }

    // Function to listen for new messages
    // function startConsumer() {
    //     setInterval(() => {
    //         axios.get('/consume').then(response => {
    //             document.getElementById('messages').innerHTML = response.data;
    //         }).catch(error => {
    //             console.error('Error:', error);
    //         });
    //     }, 1000); // Polling every second
    // }
    //
    // // Start consumer on page load
    // startConsumer();
</script>
</body>
</html>
