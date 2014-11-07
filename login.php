<?php 

include_once("config.php");
$_SESSION["admin_logged_in"] = true;
$admin_logged_in = false;
header("location:index.php");

?>