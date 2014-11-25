<?php

include_once("classes.php");

$debug = array('message'=>'', 'errors'=>array());
$dir = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']); // make a note of the current working directory, relative to root. 

if (isset($_SERVER)) 
{
	$base_dir  = __DIR__; // Absolute path to your installation, ex: /var/www/mywebsite
	$doc_root  = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); # ex: /var/www
	$base_url  = preg_replace("!^{$doc_root}!", '', $base_dir); # ex: '' or '/mywebsite'
	$protocol  = empty($_SERVER['HTTPS']) ? 'http' : 'https';
	$port      = $_SERVER['SERVER_PORT'];
	$disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
	$domain    = $_SERVER['SERVER_NAME'];
	$full_url  = "$protocol://{$domain}{$disp_port}{$base_url}"; # Ex: 'http://example.com', 'https://example.com/mywebsite', etc.
}

function getPathToHere($file)
{
	global $base_url;

	$start = strpos($file, $base_url);
	$end = strrpos($file, "/");
	$count = $end - $start;

	return substr($file, $start, $count);
}

function get_client_ip() 
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function getFileFromUrlAndSave($url, $localfile)
{
	$data = fopen($url, 'r');

	file_put_contents($localfile, $data);
}

function getFileFromUrl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function makeGetRequest($url)
{
	return file_get_contents($url);
}

function writeToFile($filepath, $text)
{
	if(true)//is_writable($filepath))
	{
		file_put_contents($filepath, $text);
	}
	else
	{
		return false;
	}

	return true;
}

function zipUpFolder($folder, $filename, $delete, $keep_structure)
{
	$destination = strpos($filename,'.zip') !== false ? $filename : $filename.'.zip';

    if (extension_loaded('zip') === true)
    {
        if (file_exists($folder) === true)
        {
            $zip = new ZipArchive();

            if ($zip->open($destination, ZIPARCHIVE::CREATE) === true)
            {
                $source = realpath($folder);

                if (is_dir($source) === true)
                {
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::LEAVES_ONLY);

                    foreach ($files as $file)
                    {
                        $file = realpath($file);

                        if(($keep_structure === true) && is_dir($file) === true && (strcmp($file, ".") != 0) && (strcmp($file, "..") != 0) && (strpos($folder,$file) != -1))
                        {
                            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                        }

                        else if (is_file($file) === true && (strpos($folder,$file) != -1))
                        {
                            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                        }
                    }
                }

                else if (is_file($source) === true)
                {
                    $zip->addFromString(basename($source), file_get_contents($source));
                }
            }

            if($zip->close())
            {
            	if($delete === true)
            	{
					echo "Removing folder ".$folder."<br>";
            		rrmdir($folder);
            	}
            }
        }
    }

    return "Could not create archive.";
}

function zipFolder($folder,$filename,$delete,$keep_structure)
{
	umask();
	$zipfilename = strpos($filename,'.zip') !== false ? $filename : $filename.'.zip';
	// Initialize archive object
	$zip = new ZipArchive;
	$zip->open($zipfilename, ZipArchive::OVERWRITE);

	if($zip)
	{
		echo "Writing to ".$zipfilename."<br>";

		// Create recursive directory iterator
		$files = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator($folder),
		    RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file) 
		{
			    // Get real path for current file
			$filePath = $file->getRealPath();
			if(!is_dir($file))
			{	
				$dest = str_replace($folder . '/', '', $file->getPath()."/".$file->getFilename());
			    echo "Adding ".$filePath." to the zip archive at ".$dest."<br>";
	
			    // Add current file to archive
			    $zip->addFile($filePath, $dest);
			}
			else if($keep_structure === true)
			{
				$zip->addEmptyDir(str_replace($folder . '/', '', $file . '/'));
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		echo "Zip archive created successfully.<br>";

		if($delete == true)
		{
			rrmdir($folder);
		}

		return $zipfilename;
	}
	else
	{
		echo "Could not create zip file: ".$zipfilename; 
	}

	return false;
}

function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) 
    {
        if(is_dir($file))
        {
            rrmdir($file);
        }
        else
        {
        	echo "Removing file: ".$file."<br>";
            unlink($file);
        }
    }

    echo "Removing Directory: ".$dir."<br>";
    rmdir($dir);
}

function unzipToFolder($zipfilename,$folder)
{
	return unzipToFolderSpecific($zipfilename,$folder,array());
}

function unzipToFolderSpecific($zipfilename,$folder,$include)
{
	$zip = new ZipArchive;

	if ($zip->open($zipfilename) === TRUE) 
	{
		if ( !file_exists($folder) ) 
		{
		  mkdir ($folder, 0777);
		}

		if(!is_writable($folder))
		{
			echo "Cannot write to folder: ".$folder."<br>";
			return false;
		}

		if(count($include) > 0)
		{
			$zip->extractTo($folder, $include);
		}
		else
		{
			$zip->extractTo($folder);
		}
	    $zip->close();
	    echo $zipfilename." extracted to ".$folder."<br>";
	    return true;
	} 
	else 
	{
	    echo "unable to open: ".$zipfilename."<br>";
	    return false;
	}
}

function copyFolderToFolder($from,$to,$destination_folder)
{
	$full_destination = $destination_folder."/".$to;	

	echo "Creating folder ".$full_destination."<br>";
	mkdir ($full_destination, 0777);

	// Create recursive directory iterator
	$files = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($from),
	    RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $name => $file) 
	{
		    // Get real path for current file
		$filePath = $file->getRealPath();
		if(!is_dir($file))
		{	
			if(!(substr($file->getFilename(), 0, 1) == '.'))
			{
				$source = $file->getPath()."/".$file->getFilename();
				$dest = $destination_folder."/".$to."/".str_replace($from . '/', '', $source);

				echo "Copying from $source to $dest"."<br>";
			    copy($source, $dest);
			}
		}
		else
		{

			$new_dir = $destination_folder."/".$file->getPath();

			if(!file_exists($new_dir))
			{
				echo "Creating new folder ".$new_dir." from $file <br>";
				mkdir($new_dir);
			}
		}
	}
}

function matchStringInArray($key, $value, $array)
{
	#echo "looking for key: ".$key."<br>in:".var_export($array,true);
	if(array_key_exists($key, $array))
	{
		#echo "checking: ".$key." for ".$value."<br>";
		if(strpos($array[$key], $value) !== false)
		{
			#echo "found!";
			return true;
		}
	}
	else
	{
		$ret = false;
		foreach ($array as $subkey => $subarray) {
			if(is_array($subarray))
			{
				#echo "recursing with ".$subkey." as subarray<br>";
				$ret = matchStringInArray($key, $value, $subarray) || $ret;
			}
		}
		return $ret;
	}

	return false;
}

function getPostData($key)
{
	return isset($_POST[$key]) ? $_POST[$key] : null;
}

function getQueryData($key)
{
	return isset($_GET[$key]) ? $_GET[$key] : null;
}

function getSessionData($key)
{
	return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
}

function getPostDataArray($keys)
{
	return array_intersect($keys, $_POST);
}

function getIdFromValue($value, $table, $check)
{
	$query = "select id from ".$table." where ".$check."=".$value;
	if(is_int($value))
	{
		$query = "select id from ".$table." where id=".$value;
	}
	$res = getResults($query);

	if(count($res))
	{
		$id = $row['id'];
	}

	return $id;
}

function getCommaSeparatedList($array)
{
	$arrayList = "";
	$prefix = '';
	foreach ($array as $item)
	{
	    $arrayList .= $prefix.$item;
	    $prefix = ', ';
	}

	return $arrayList;
}

function getQueryFromTableInfo($table, $table_info)
{
	$query = "select ";

	$prefix = '';
	foreach($table_info['columns'] as $column)
	{
		if(in_array($column, array_keys($table_info['aliases'])))
		{
			$query .= $prefix."(".$table_info['aliases'][$column]['select'].") as ".$table_info['aliases'][$column]['column'];
		}
		else
		{
			$query .= $prefix.$column;
		}
		$prefix = ',';
	}

	$query .=" from ".$table;

	return $query;
}

function writeVariableAlsoExclude($name,$value,$exclude)
{
	$val = $value;
	if(is_array($value))
	{
		$val = writeArrayCreationCode($value, $exclude, true);
	}

	$code = "$".$name." = ".$val.";";

	return $code;
}

function writeVariable($name,$value)
{
	$val = $value;
	if(is_array($value))
	{
		$val = writeArrayCreationCode($value, array(), true);
	}
	else if(is_string($val))
	{
		$val = "\"".$val."\"";
	}

	$code = "$".$name." = ".$val.";";

	return $code;
}

function currentlyInWordpress()
{
	if (function_exists('in_the_loop') == true)
	{
		return true;
	}

	return false;
}

function writeArrayCreationCode($array, $exclude, $keys_too)
{
	$code = "array(\n\t";		
			
	foreach ($array as $key => $value) 
	{
		if(!((count($exclude) > 0) && (array_key_exists($key, $exclude) === true)))
		{
			if($keys_too)
			{
				$code .= "\"".$key."\"=>";
			}

			if(is_array($value))
			{
				$code .= writeArrayCreationCode($value, $exclude, $keys_too);
			}
			else 
			{
				$code .= "\"".$value."\"";
			}
			
			$code .= ",\n\t";
		}
	}

	if(strlen($code) > strlen("array(\n\t"))
	{
		$code = substr($code,0,-3);
	}

	return $code.")";
}

function logError($message)
{
	header('location:error.php?message='.$message);
}

function replaceStringContents($needles,$haystack)
{
	$subject = $haystack;
	foreach($needles as $search => $replace)
	{
		$subject = str_ireplace($search, $replace, $subject);
	}

	return $subject;
}

function getFileAndReplaceThenWrite($from,$to,$destination_folder,$replace)
{
	umask();
	$contents = @file_get_contents($from);
	$destination = $destination_folder.$to;

	if(is_writable($destination_folder))
	{
		echo "Creating file ".$destination."<br>";
		file_put_contents($destination, replaceStringContents($replace,$contents));
	}
	else
	{
		echo "Cannot write to destination: ".$destination."<br>";
	}
}

function getFileWriteFile($from,$to,$destination_folder)
{
	umask();
	$contents = @file_get_contents($from);
	$destination = $destination_folder.$to;

	if(!file_exists($destination_folder))
	{
		mkdir($destination_folder, 0777);
	}

	if(is_writable($destination_folder))
	{
		echo "Creating file ".$destination."<br>";
		file_put_contents($destination, $contents);
	}
	else
	{
		echo "Cannot write to destination: ".$destination."<br>";
	}
}

?>