<?php

class ChatHandler
{
	function send($message, $sendingSock, $messageType)
	{
		global $clients;
		foreach ($clients as $sendSock) {
			if ($sendSock != $sendingSock) {
				@socket_write($sendSock, $this->encode($message, $messageType));
			}
		}
	}

	function unmask($message)
	{
		$length = ord($message[1]) & 127;

		if ($length == 126) {
			$mask = substr($message, 4, 4);
			$data = substr($message, 8);
		} else if ($length == 127) {
			$mask = substr($message, 10, 4);
			$data = substr($message, 14);
		} else {
			$mask = substr($message, 2, 4);
			$data = substr($message, 6);
		}

		$message = "";
		for ($i = 0; $i < strlen($data); $i++) {
			$message .= $data[$i] ^ $mask[$i % 4];
		}
		return $message;
	}

	function encode($message, $messageType)
	{
		switch ($messageType) {
			case 'text':
				$byte1 = 0x80 | 0x01;
				break;
			case 'binary':
				$byte1 = 0x80 | 0x02;
				break;
			case 'closing':
				$byte1 = 0x80 | 0x08;
				break;
			case 'ping':
				$byte1 = 0x80 | 0x09;
				break;
			case 'pong':
				$byte1 = 0x80 | 0x0A;
				break;
			default:
				die("Gotta specify a valid message type m8. (text, binary, closing, ping, pong)");
		}

		if ($messageType == "text") {
			if (strlen($message) < 126) {
				$header = pack("CC", $byte1, strlen($message));
			} else if (strlen($message) >= 126 && strlen($message) < 65536) {
				$header = pack("CCn", $byte1, 126, strlen($message));
			} else if (strlen($message) >= 65536) {
				$header = pack("CCNN", $byte1, 127, strlen($message));
			}
		}

		return $header . $message;
	}

	function doHandshake($receivedHeaders, $socket, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $receivedHeaders);
		foreach ($lines as $line) {
			if (preg_match("/\A(\S+): (.*)\z/", $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}

		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack("H*", sha1($secKey . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11")));
		$buffer = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Websocket-Origin: $host" .
			"Websocket-Location: ws://$host:$port\r\n" .
			"Sec-Websocket-Accept: $secAccept\r\n\r\n";
		socket_write($socket, $buffer);
	}
}
