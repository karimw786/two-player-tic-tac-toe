<?php
// For deployment on Docker container, set $WEBSERVER_ADDRESS manually to IP address
// of machine running the container, instead of gethostbyname(gethostname())
$WEBSERVER_ADDRESS = "192.168.0.105"; //gethostbyname(gethostname());

// Socket created in websockets_server.php will listen on all interfaces on port 8080
$WEBSOCKET_ADDRESS = "0.0.0.0";
$WEBSOCKET_PORT = 8080;
?>