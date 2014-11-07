<?php

if(file_exists("config.php"))
{	
	include_once('config.php');
}

include_once('classes.php');
	
function callStoredProcedure($proc, $data)
{
	global $mt_dbconn;
	
	$param_mask ="";
	$param_count = count($data);
	
	$sql = "CALL " . $proc . "( ";

	foreach ($data as $key => $value) 
	{
		$sql .= ":" . $key . ",";
	}
	$sql[strlen($sql) - 1] = ")";

	//DEBUG: 
	//echo $sql . '<br>';
	$results = $mt_dbconn->getConn()->prepare($sql);
	
	foreach ($data as $key => $value) 
	{
		$results->bindValue(":" . $key, $value);
	}
	
	$results->execute();
	
	return $results;
}

function callStoredProcedureAndFetchAll($proc, $data)
{
	$list = callStoredProcedure($proc, $data);
	$data = $list->fetchAll();
	
	return $data;
}

function getView($viewName)
{
	global $mt_dbconn;
	
	$sql = "SELECT * FROM " . $viewName;
	$results = $mt_dbconn->getConn()->prepare($sql);
	$results->execute();
	
	return $results;
}

function getViewAndFetchAll($viewName)
{
	$list = getView($viewName);
	$data = $list->fetchAll();
	
	return $data;
}

function getResults($sql)
{
	global $mt_dbconn;
	
	$results = $mt_dbconn->getConn()->prepare($sql);
	$results->execute();
	
	return $results->fetchAll();
}

function executeQuery($query)
{
	global $mt_dbconn;
	
	$results = $mt_dbconn->getConn()->prepare($query);
	$results->execute();
} 

?>