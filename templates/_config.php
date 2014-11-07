<?php
	include_once('classes.php');
	include_once('functions.php');
	session_start();

	global $mt_dbconn;
	$offline = false;

	##SITE SETTINGS##

	if (class_exists("TrackingDatabase") && !$mt_dbconn) {
	    $mt_dbconn = new TrackingDatabase('##DBHOST##','##DBNAME##','##DBUSER##','##DBPASS##','##DBPORT##');	
	}	

	$offline = in_array(basename($_SERVER['SCRIPT_NAME']), $offline_pages);

	if(!$mt_dbconn || (!$mt_dbconn->checkConnection()))
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