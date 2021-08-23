<!DOCTYPE html>
<html>

<head>
	<?php
	if (isset($_COOKIE['userHandle'])) {
		header("Location: index.php");
	}
	?>
	<meta charset="utf-8">
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
	<div class="row">
		<div class="col-6">
			<form id="login" method="post">
				<h1>Login</h1>
				<label for="email">Email</label>
				<input type="email" placeholder="Email Address" name="email" required>
				<label for="password">Password</label>
				<input type="password" placeholder="Password" name="password" required>
				<input type="submit" id="loginbtn" class="blueButton" value="Login" name="loginbtn">
				<p>Don't have an account? <a href="signup.php">Sign up</a> now!</p>
			</form>
			<br>
			<div id="systemMessageDiv">
				<p id="systemMessage"></p>
			</div>
		</div>
	</div>
	<script>
		function incorrectPasswordMessage(email) {
			document.querySelectorAll("#systemMessageDiv")[0].replaceChild(document.createElement("p").appendChild(document.createTextNode("The email or password is incorrect!")), document.querySelectorAll("#systemMessage")[0]);
			document.querySelectorAll("input[type=\"email\"]")[0].value = email;
		}
	</script>
	<?php

	$db = "chat";
	$dbuser = "root";
	$host = "localhost";

	try {
		$conn = new PDO("mysql:host=$host;dbname=$db", $dbuser, "");

		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		echo "<script>console.log(\"Connection to DB Successful.\");</script>";
	} catch (PDOException $e) {
		echo "<script>console.log(\"Connection Failed. Message: " . $e->getMessage() . "\");</script>";
	}

	if (isset($_POST['loginbtn'])) {
		$email = $_POST['email'];
		$password = md5($_POST['password']);
		$result = $conn->query("SELECT `password`, `user_handle` FROM `users` WHERE `email` = '$email'")->fetchAll();

		if (count($result)) {
			if ($result[0][0] == $password) {
				setcookie("userHandle", $result[0][1], time() + 60 * 60 * 5); // expires in 5 hours: 60 seconds * 60 mins = 1 hour, 1 hour * 5 = 5 hours
				header("Location: index.php");
			} else {
				echo "<script>incorrectPasswordMessage('$email')</script>";
			}
		} else {
			echo "<script>incorrectPasswordMessage('$email')</script>";
		}
	}
	?>
</body>

</html>