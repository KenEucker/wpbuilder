<?php
	include_once('classes.php');
	include_once('functions.php');
	include_once('##PLUGIN_NAME##-plugin.php');

	global $db_conn, $##CLASSNAME##;

	if (class_exists("##CLASSNAME##") && class_exists("DatabaseConnection"))
	{
		if(!$##CLASSNAME##)
		{
			echo "Plugin class not initialized.<br>";
		}
		if(!$db_conn) 
		{
			$host = $##CLASSNAME##->settings->get_field_value('##PLUGIN_NAME##_db_host');
			$name = $##CLASSNAME##->settings->get_field_value('##PLUGIN_NAME##_db_name');
			$user = $##CLASSNAME##->settings->get_field_value('##PLUGIN_NAME##_db_user');
			$pass = $##CLASSNAME##->settings->get_field_value('##PLUGIN_NAME##_db_pass');
			$port = $##CLASSNAME##->settings->get_field_value('##PLUGIN_NAME##_db_port');
		    $db_conn = new DatabaseConnection($host,$name,$user,$pass,$port);	
		}
	}	
	else
	{
		echo "Classes missing."; die();
	}

	$offline_pages = 
	array("0"=>"settings.php","1"=>"wp-plugin.php");
	$offline = in_array(basename($_SERVER['SCRIPT_NAME']), $offline_pages);

	if(!$db_conn || (!$db_conn->checkConnection()))
	{
		if(!$offline)
		{
			logError('Cannot connect to database, please contact the Administrator.');
			die();
		}
	}
?>