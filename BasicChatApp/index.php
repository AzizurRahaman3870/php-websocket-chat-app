<?php
if (!isset($_COOKIE['userHandle'])) {
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html>

<head>
	<title>Basic Chat App</title>

	<!-- Viewport dimensions. -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Caching prevention -->
	<meta http-equiv="cache-control" content="max-age=0">
	<meta http-equiv="cache-control" content="no-store">
	<meta http-equiv="expires" content="-1">
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 11:00:00 GMT">
	<meta http-equiv="pragma" content="no-cache">

	<!-- Character Set -->
	<meta charset="utf-8">

	<!-- Style Sheet -->
	<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
	<div class="row">
		<div class="col-3"></div>
		<div class="col-1 col-separator-left"></div>
		<div class="col-4">
			<form id="chat" autocomplete="off">
				<div id="chat-box"></div>
				<div id="typingNotificationBox"></div>
				<input type="text" name="user" id="user" placeholder="Handle name" required disabled><br>
				<input type="text" name="message" id="message" placeholder="Message" required disabled><br>
				<input type="submit" id="sendbtn" name="send" value="Send">
			</form>
		</div>
		<div class="col-1 col-separator-right"></div>
		<div class="col-3"></div>
	</div>
	<script src="script.js" type="text/javascript"></script>
</body>

</html>