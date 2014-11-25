<?php	
	include_once('classes.php');
	include_once('functions.php');

	global $pages, $admin_pages,$sitename;

	$page_info = new Page("Index",true);

	$page_info->setPages($pages, $admin_pages);
	$page_info->body = "Welcome to the wpbuilder page!";

	echo $page_info->getPageHtml();

?>