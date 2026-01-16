<?php

include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

$email = $_POST['email'];
$pass = $_POST['pass'];

$sql = 'INSERT INTO `People` (`Username`, `PassHash`) VALUES (?, ?)';

$pass = password_hash($pass, PASSWORD_DEFAULT);

$query = $conn->prepare($sql);
$query->bind_param('ss', $email, $pass);
$query->execute();
$query->close();
?>