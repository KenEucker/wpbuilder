<?php
	include_once('classes.php');
	include_once('functions.php');
	session_start();

	global $db_conn;
	$offline = false;

	##SITE SETTINGS##

	if (class_exists("DatabaseConnection") && !$db_conn) {
	    $db_conn = new DatabaseConnection('##DBHOST##','##DBNAME##','##DBUSER##','##DBPASS##','##DBPORT##');	
	}	

	$offline = in_array(basename($_SERVER['SCRIPT_NAME']), $offline_pages);

	if(!$db_conn || (!$db_conn->checkConnection()))
	{
		if(!$offline)
		{
			logError('Cannot connect to database, please contact the Administrator.');
			die();
		}
	}
	/// TODO: add admin login
	$admin_logged_in = getSessionData("admin_logged_in");

	if(!$offline && !$admin_logged_in && (matchStringInArray("link",basename($_SERVER['SCRIPT_NAME']), $admin_pages) || matchStringInArray("link",basename($_SERVER['SCRIPT_NAME']), $hidden_admin_pages)))
	{
		logError("You are attempting to access a page you are not logged in for.");
		die();
	}
?>