<?php	
	
	if(!file_exists('config.php'))
	{
		header('location:settings.php');
	}
	
	include_once('config.php');
	include_once('classes.php');

	global $pages, $admin_pages;

	$page_info = new Page("Index",$sitename);
	$page_info->setPages($pages, $admin_pages);
	$page_info->body = "Welcome to the wpbuilder page!";

	echo $page_info->getPageHtml();

?>