<?php

include_once("functions.php");
include_once("database.php");

global $options, $data;

function getOptionsArray($string)
{
	$options = array();
	$string_ex = explode("<<>>", $string);

	foreach($string_ex as $option)
	{
	  $split = strpos($option,"=");
	  $name = substr($option, 0, $split);
	  $content = substr(substr($option, $split + 1), 1, -1);
	  $options[$name] = $content;
	}

	return $options;
}

$options = array();
$data = null;

$ui_table = getQueryData("table");
$widget_id = getQueryData("widget");

$widget_id = ($widget_id === null) ? 0 : $widget_id;

if($ui_table === null)
{
	echo "No widget to load?"; die();
}

$ui = getResults("select * from ui_".$ui_table." where id=".$widget_id);
$data = getResults("select * from ".$ui_table);
$options = getOptionsArray($ui[0]["options"]);

include_once($ui[0]["file"]);

?>