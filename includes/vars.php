<?php
// Set $WEBSERVER_ADDRESS manually to IP address of machine running 
// websockets_server.php (e.g. $WEBSERVER_ADDRESS = "192.168.0.105";) 
// in case gethostbyname(gethostname()) doesn't work
$WEBSERVER_ADDRESS = gethostbyname(gethostname());

// Socket created in websockets_server.php will listen on all interfaces
// on port 8080
$WEBSOCKET_ADDRESS = "0.0.0.0";
$WEBSOCKET_PORT = 8080;
?>