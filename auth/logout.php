<?php
$domain = 'alexandria.it';
setcookie("email", "", time() - 1, '/', $domain); // delete the cookie 
setcookie("password", "", time() - 1, '/', $domain); // delete the cookie 
session_start();
session_destroy(); // delete the session 
header("Location: ../index.php");
exit();
?>