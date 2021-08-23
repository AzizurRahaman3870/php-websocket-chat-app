<?php

define("HOST_NAME", "localhost");
define("PORT", "8000");

require_once("chatHandler.php");
$chatHandler = new ChatHandler();

function log_error($message) {
	$error = socket_strerror(socket_last_error());
	die("$message: [" . socket_last_error() . "] $error");
}

$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp")) or log_error("Error during socket creation!");
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1) or log_error("Couldn't set socket to reuse port!");
socket_bind($socket, 0, PORT) or log_error("Couldn't bind socket to port!");
socket_listen($socket) or log_error("Couldn't listen on socket!");

$clients = array($socket);

while(true) {
	$readSocks = $clients;

	if(socket_select($readSocks, $write, $except, NULL) < 1) {
		continue;
	}

	if(in_array($socket, $readSocks)) {
		$clients[] = $newSock = socket_accept($socket);

		$headers = socket_read($newSock, 1024);

		/*
			Three Options:
			1) Create an object to store client data(IP, socket, userHandle)
			2) -> Accepted Create a login page to enter the chatroom.
			3) -> Accepted but failed 3) Query the client side for activeConnections every time someone connects/disconnects.
		*/

		$chatHandler->doHandshake($headers, $newSock, HOST_NAME, PORT);

		// socket_getpeername($newSock, $ip);
		// $jsonObj = json_encode(array('username' => 'System', 'message' => 'New client '. $ip. ' Connected!', 'messageType' => 'newConnectMessage'));
		// $chatHandler->send($jsonObj, $newSock, "text");

		unset($readSocks[array_search($socket, $readSocks)]);

		var_dump(array_slice($clients, 1));

		$jsonObj = json_encode(array("username" => "System", "message" => json_encode(array_slice($clients, 1)), 'messageType' => 'activeConnections'));
		$chatHandler->send($jsonObj, $socket, "text");
	}

	foreach($readSocks as $readSock) {
		while(socket_recv($readSock, $data, 1024, 0) >= 1) {
			
			$messageObj = json_decode($chatHandler->unmask($data));

			$chatHandler->send(json_encode($messageObj), $readSock, "text");

			break 2;
		}

		$sockData = @socket_read($readSock, 1024, PHP_NORMAL_READ);
		if($sockData === false) {
			socket_getpeername($readSock, $ip);
			$jsonObj = json_encode(array('username' => 'System', 'message' => ('Client ' . $ip . ' has Disconnected!'), 'messageType' => 'disconnectMessage'));
			$chatHandler->send($jsonObj, $readSock, "text");

			socket_close($readSock);
			unset($clients[array_search($readSock, $clients)]);
		}
	}
}

socket_close($socket);
