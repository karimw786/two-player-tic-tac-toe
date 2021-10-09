<?php
/******************************************************************************** 
*   Based on https://github.com/sanwebe/Chat-Using-WebSocket-and-PHP-Socket		*
*																				*
*	Note: websockets_server.php must be running first, otherwise players will	*
*	not be able to join a game.													*
*																				*
*	> php -q websockets_server.php												*
********************************************************************************/
require_once("includes/vars.php");

$host = $WEBSOCKET_ADDRESS;
$port = $WEBSOCKET_PORT;
$null = NULL;
$players = array();
$player_id = 0;

echo "Starting socket server on port $port\n";

// Create TCP/IP stream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Make reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

// Bind socket to specified host
socket_bind($socket, 0, $port);

// Listen on port
socket_listen($socket);

// Create & add listening socket to the list
$clients = array($socket);

// Start endless loop, so that script doesn't stop
while (true) {
	// Manage multiple connections
	$changed = $clients;

	// Returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	
	// Check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); // Accept new socket
		$clients[] = $socket_new; // Add socket to client array
		
		$header = socket_read($socket_new, 1024); // Read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port); // Perform websocket handshake
		
		// Make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
	}
	
	// Loop through all connected sockets
	foreach ($changed as $changed_socket) {	
		
		// Check for any incomming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1) {
            $received_text = unmask($buf); // Unmask data
			$tst_msg = json_decode($received_text, true); // JSON decode

			// Take appropriate action based on received message
			switch ($tst_msg["message_type"]) {
				// Event: user checks if websockets_server.php is running
				case "usersyscheck":
					$response_text = mask(json_encode(array("message_type"=>"syssyscheck","msg"=>"Online")));
                	send_message($response_text);
					break;

				// Event: user registers on registration page
				case "userreg":
					$player_name = $tst_msg["player_name"];
					$player_ip = $tst_msg["player_ip"];
					
					// if player_count = 0, add player
					if (count($players) == 0) {
						$player_id++;
						$player_move = "X";
						$player_point = 1;
						array_push($players, array($player_id, $player_name, $player_move, $player_point, $player_ip));
	
						echo "Player $player_id: $player_name ($player_move) from $player_ip has joined.\n";
					
						$response_text = mask(json_encode(array(
							"message_type"=>"sysreg", 
							"msg"=>array(
								"player_id"=>$player_id, 
								"player_name"=>$player_name, 
								"player_move"=>$player_move,
								"player_point"=>$player_point
							)
						)));
						send_message($response_text);
					}
					// if player count = 1, check if IP address is already in players
					elseif (count($players) == 1) {
						// if it is not, then add
						if ($players[0][4] != $player_ip) {
							$player_id++;
							$player_move = "O";
							$player_point = -1;
							array_push($players, array($player_id, $player_name, $player_move, $player_point, $player_ip));
						
							echo "Player $player_id: $player_name ($player_move) from $player_ip has joined.\n";
	
							$response_text = mask(json_encode(array(
								"message_type"=>"sysreg", 
								"msg"=>array(
									"player_id"=>$player_id, 
									"player_name"=>$player_name, 
									"player_move"=>$player_move,
									"player_point"=>$player_point
								)
							)));
							send_message($response_text);
						}
					}
					
					// if both players are in players, send sysready message
					if (count($players) == 2) {
						$response_text = mask(json_encode(array("message_type"=>"sysready", "msg"=>$players)));
						send_message($response_text);
						$players = array();
						$player_id = 0;
					}
					break;

				// Event: users makes a move (plays X or O)
				case "usermove":
					$response_text = mask(json_encode(array(
						"message_type"=>"sysmove", 
						"msg"=>array(
							"player_id"=>$tst_msg["player_id"], 
							"player_name"=>$tst_msg["player_name"], 
							"move_id"=>$tst_msg["move_id"], 
							"move"=>$tst_msg["move"]
						)
					)));
					send_message($response_text);
					break;


				// Event: For each move made by user, user sends their updated copy of scores[]
				case "userscores":
					$response_text = mask(json_encode(array("message_type"=>"sysscores", "msg"=>$tst_msg["scores"])));
					send_message($response_text);
					break;

				// Event: user presses quit button (user quits the game)
				case "userquit":
					$response_text = mask(json_encode(array(
						"message_type"=>"sysquit", 
						"msg"=>array(
							"player_id"=>$tst_msg["player_id"], 
							"player_name"=>$tst_msg["player_name"]
						)
					)));
					send_message($response_text);		
					break;
			}

			break 2; // Exit this loop
		}
		
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);

		if ($buf === false) { // Check disconnected client
			// Remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			unset($clients[$found_socket]);
		}
	}
}
// Close the listening socket
socket_close($socket);

function send_message($msg) {
	global $clients;
	foreach($clients as $changed_socket) {
		@socket_write($changed_socket,$msg,strlen($msg));
	}

	return true;
}

// Unmask incoming framed message
function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

// Encode message for transfer to client
function mask($text) {
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

// Handshake new client
function perform_handshaking($receved_header,$client_conn, $host, $port) {
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line) {
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	// Hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/demo/shout.php\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}
?>