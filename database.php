<?php

if(file_exists("config.php"))
{	
	include_once('config.php');
}

include_once('classes.php');
	
function callStoredProcedure($proc, $data)
{
	global $db_conn;
	
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
	$results = $db_conn->getConn()->prepare($sql);
	
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
	global $db_conn;
	
	$sql = "SELECT * FROM " . $viewName;
	$results = $db_conn->getConn()->prepare($sql);
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
	global $db_conn;
	
	$results = $db_conn->getConn()->prepare($sql);
	$results->execute();
	
	return $results->fetchAll();
}

function executeQuery($query)
{
	global $db_conn;
	
	$results = $db_conn->getConn()->prepare($query);
	$results->execute();
} 

?>