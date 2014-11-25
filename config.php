<?php
	include_once('classes.php');
	include_once('functions.php');
	session_start();

	global $db_conn;
	$offline = false;

	
	/* Site settings */
	$sitename = "Site";
	$pages = array(
	"0"=>array(
	"name"=>"Frontpage",
	"link"=>"/frontpage.php",
	"icon"=>"fa fa-bullhorn"),
	"1"=>array(
	"name"=>"API",
	"link"=>"/rest.php",
	"icon"=>"fa fa-desktop"),
	"2"=>array(
	"name"=>"Wordpress",
	"link"=>"/wp/",
	"icon"=>"fa fa-th"));
	$admin_pages = array(
	"0"=>array(
	"name"=>"Settings",
	"link"=>"/settings.php",
	"icon"=>"fa fa-cog"));
	$hidden_admin_pages = array(
	"0"=>array(
	"name"=>"Worpdress Plugin",
	"link"=>"/wp-plugin.php"),
	"1"=>array(
	"name"=>"Deploy A Local Wordpress Site",
	"link"=>"/deploy-wp.php"));
	$offline_pages = array(
	"0"=>"settings.php",
	"1"=>"wp-plugin.php");



	if (class_exists("DatabaseConnection") && !$db_conn) {
	    $db_conn = new DatabaseConnection('127.0.0.1','tracker','tracker','tracker','3306');	
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