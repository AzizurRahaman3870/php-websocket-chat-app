<!DOCTYPE html>
<html>

<head>
	<?php
	if (isset($_COOKIE['userHandle'])) {
		header("Location: index.php");
	}
	?>
	<meta charset="utf-8">
	<title>Sign Up</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
	<div class="row">
		<div class="col-6">
			<form id="login" method="post">
				<h1>Sign Up</h1>
				<label for="email">Email:</label>
				<input type="email" placeholder="Email Address" name="email" required>
				<label for="userHandle">Set Username:</label>
				<input type="text" placeholder="User Handle" name="userHandle" required>
				<label for="password">Password:</label>
				<input type="password" placeholder="Password" name="password" required>
				<label for="confirmPassword">Confirm Password:</label>
				<input type="password" placeholder="Confirm Password" name="confirmPassword" required>
				<input type="submit" id="signupBtn" class="blueButton" value="Sign up!" name="signupBtn">
				<p>Already have an account? <a href="login.php">Login</a> now!</p>
			</form>
			<br>
			<div id="systemMessageDiv">
				<p id="systemMessage"></p>
			</div>
		</div>
	</div>
	<script>
		function userHandleTakenMessage(email) {
			document.querySelectorAll("#systemMessageDiv")[0].replaceChild(document.createElement("p").appendChild(document.createTextNode("This username has already been taken. Please type another username.")), document.querySelectorAll("#systemMessage")[0]);
			document.querySelectorAll("input[type=\"email\"]")[0].value = email;
		}

		function accountExistsMessage(email) {
			document.querySelectorAll("#systemMessageDiv")[0].replaceChild(document.createElement("p").appendChild(document.createTextNode("An account with that email already exists! Please login or reset your password!")), document.querySelectorAll("#systemMessage")[0]);
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

	if (isset($_POST['signupBtn'])) {
		$email = $_POST['email'];
		$password = md5($_POST['password']);
		$userHandle = $_POST['userHandle'];
		$result = $conn->query("SELECT EXISTS(SELECT * FROM `users` WHERE `email` = '$email')")->fetchAll();

		if (count($result)) {
			if ($result[0][0] >= 1) {
				echo "<script>accountExistsMessage('$email');</script>";
			} else {
				$result = $conn->query("SELECT * FROM `users` WHERE `user_handle` = '$userHandle'")->fetchAll();

				if (count($result)) {
					echo "<script>userHandleTakenMessage('$email');</script>";
				} else {
					$conn->prepare("INSERT INTO `users`(`email`, `password`, `user_handle`, `account_created`) VALUES('$email', '$password', '$userHandle', NOW())")->execute();
					setcookie("userHandle", $userHandle, time() + 60 * 60 * 5); // expires in 5 hours: 60 seconds * 60 mins = 1 hour, 1 hour * 5 = 5 hours
					header("Location: index.php");
				}
			}
		}
	}

	?>
</body>

</html>