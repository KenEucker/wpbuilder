<?php	

	if(!file_exists('config.php'))
	{
		header('location:settings.php');
	}

	include_once("config.php");
	include_once("classes.php");
	include_once("functions.php");

	global $wp_plugin_config, $license, $sitename;

	if(file_exists("wp-plugin-config.php"))
	{
		include_once("wp-plugin-config.php");

		if(!isset($wp_plugin_config["base_files"]))
		{
			setDefaults();
		}
	}
	else
	{
			setDefaults();
	}

	$page_info = new Page("wp-plugin", $sitename);
	$page_info->title = "Tracker - Get Wordpress Plugin ";
	$page_info->description = "Generate and download a new copy of the plugin";
	$page_info->meta = "tracker wordpress plugin download";
	$page_info->setPages($pages, $admin_pages);
	$page_info->changePageType("admin");
	
	$page_info->js = <<<js
		function buildSuccess(response)
		{
			$('div.results').html(response);
		}

		function buildError(message)
		{
			$('div.results').html(message);
		}

		$('#build').click(function(){
			var id = "#"+$(this)[0].id;
            $(id+' .glyphicon').after('<img class="spinner">');
            $(id+ ' .glyphicon').remove();
            $(id+ ' .spinner').attr('src','img/indicator.gif');

			makeAsyncPOST('?do=build',$("#buildForm").serialize(),buildSuccess,buildError);

            $(id+ ' .spinner').after('<span class="glyphicon"></span>');
            $(id+ ' .spinner').remove();
		});

		$('input[name="license"]').each(function(){
			var name = $(this).attr('name'),
			val = $(this).val(),
    		textbox = $(document.createElement('textarea')).attr('name', name).attr('style','width: 70%;height: 300px;');
    		$(textbox).val(val);
			$(this).replaceWith(textbox);
		});
		$('#delete').hide();

		function toggleLi(selector)
		{
			$(selector).toggle();
		}
js;

	function setDefaults()
	{
		global $wp_plugin_config;

		$wp_plugin_config = array(
			"base_files"=>array(
				"classes.php",
				"error.php"),
			"admin_files"=>array(),
			"css_files"=>array(),
			"js_files"=>array(),
			"name"=>"some_plugin",
			"destination"=>"",
			"domain"=>"sp_",
			"version"=>"0.1",
			"classname"=>"Some_plugin",
			"shortname"=>"plugin",

			"author" => "author",
			"email" => "youremail@gmail.com",
			"year" => "2014",
			"uri" => "http://github.com",
			"description" => "A plugin description."
			);
	

	$wp_plugin_config["license"] = <<<LICENSE
	Permission is hereby granted, free of charge, to any person obtaining a copy of
	this software and associated documentation files (the "Software"), to deal in
	the Software without restriction, including without limitation the rights to
	use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
	of the Software, and to permit persons to whom the Software is furnished to do
	so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
LICENSE;

	}

	function getConfigFilesReplace($wp_plugin_config)
	{
		$wp_files_replace = array(
			"##BIG NAME##" => str_replace("_", " ", $wp_plugin_config["classname"]),
			"##URI##" => $wp_plugin_config["uri"],
			"##DESCRIPTION##" => $wp_plugin_config["description"],
			"##VERSION##" => $wp_plugin_config["version"],
			"##AUTHOR##" => $wp_plugin_config["author"],
			"##EMAIL##" => $wp_plugin_config["email"],
			"##YEAR##" => $wp_plugin_config["year"],
			"##LICENSE##" => $wp_plugin_config["license"],
			"##CAPITALIZED##" => strtoupper($wp_plugin_config["classname"]),
			"##CLASSNAME##" => $wp_plugin_config["classname"],
			"##PLUGIN_NAME##" => $wp_plugin_config["name"],
			"##SHORTNAME##" => $wp_plugin_config["shortname"],
			"##CAPITALIZED_SHORTNAME##" => strtoupper($wp_plugin_config["shortname"]),
			"##BIGSHORTNAME##" => ucfirst($wp_plugin_config["shortname"]),
			"##DOMAIN##" => $wp_plugin_config["domain"],
			"##BIGSHORTNAME_PLURAL##" => ucfirst($wp_plugin_config["shortname"])."s",
			"##SHORTNAME_PLURAL##" => $wp_plugin_config["shortname"]."s",

			"##DBHOST##" => "localhost",
			"##DBNAME##" => "tracker",
			"##DBUSER##" => "tracker",
			"##DBPASS##" => "tracker",
			"##DBPORT##" => "3306"
		);

		$wp_files_replace["##PLUGIN_ADMIN_MENUS##"] = "";

		foreach($wp_plugin_config["admin_files"] as $name => $file)
		{
			$wp_files_replace["##PLUGIN_ADMIN_MENUS##"] .= "add_submenu_page(\$mainfile, '$name', '$name', \$menu_type, \$plugin_dir.'/$file' );\n";
		}

		return $wp_files_replace;
	}

	function buildTextLi1($name, $id, $var)
	{
		return "<li><span class='name'>$name</span><input type='text' name='$id' value='$var'></li>";
	}

	function buildTextareaLi1($name, $id, $var)
	{
		return "<li><span class='name'>$name</span><textarea name='$id' value='$var'></li>";
	}

	function buildCommaDelimitedTextLi1($name, $id, $var)
	{
		return "<li><span class='name'>$name</span><input type='text' name='$id' value='".outputArrayToText($var)."'></li>";
	}

	function create_plugin_files($wp_config,$wp_files_replace)
	{
		global $page_info;

		$destination_folder = $wp_config["destination"].$wp_config["name"]."/";
		if ( !file_exists($destination_folder) ) {
		  mkdir ($destination_folder, 0777);
		  mkdir ($destination_folder."css/", 0777);
		  mkdir ($destination_folder."js/", 0777);
		 }

		 getFileAndReplaceThenWrite("templates/_wp-plugin-config.php","config.php",$destination_folder,$wp_files_replace);
		 getFileAndReplaceThenWrite("templates/_wp-plugin.php",$wp_config["name"]."-plugin.php",$destination_folder,$wp_files_replace);
		 getFileAndReplaceThenWrite("templates/_wp-settings.php",$wp_config["name"]."-settings.php",$destination_folder,$wp_files_replace);

		foreach($wp_config["base_files"] as $filename)
		{
			getFileWriteFile($page_info->basepath."/".$filename,$filename,$destination_folder);
		}
		foreach($wp_config["admin_files"] as $filename)
		{
			getFileWriteFile($page_info->basepath."/".$filename,$filename,$destination_folder);
		}
		foreach($wp_config["css_files"] as $filename)
		{
			getFileWriteFile($page_info->basepath."/css/".$filename,$filename,$destination_folder."css/");
		}
		foreach($wp_config["js_files"] as $filename)
		{
			getFileWriteFile($page_info->basepath."/js/".$filename,$filename,$destination_folder."js/");
		}
	}

	function convertArrayToList($heading, $name, $names, $inputs)
	{
		$ulHtml = "<span>$heading</span><br><ul id='$name'>";

		foreach($inputs as $id => $input)
		{
			if($names[$id] !== null)
			{
				if(is_array($input))
				{
					$ulHtml .= buildCommaDelimitedTextLi1($names[$id],$id,$input);
				}
				else
				{
					$ulHtml .= buildTextLi1($names[$id],$id,$input);
				}
			}
		}

		$ulHtml .= "</ul>";

		return $ulHtml;
	}

	function outputArrayToText($array)
	{
		$text = "";

		foreach($array as $var)
		{
			$text .= $var.",";
		}

		$text = substr($text, 0, strlen($text)-1);
		return $text;
	}

	function inputTextToArray($text)
	{
		$array = array();

		$array = explode(",", $text);

		return $array;
	}

	function savePluginSettings($wp_plugin_config)
	{
		foreach($wp_plugin_config as $key => $value)
		{
			$new_value = getPostData($key);
			if(is_array($value))
			{
				$new_array = explode(',', $new_value);
				$wp_plugin_config[$key] = $new_array;
			}
			else
			{
				$wp_plugin_config[$key] = $new_value;
			}
		}

		return $wp_plugin_config;
	}

	function doSave(&$wp_plugin_config)
	{
		$response = "";
		if( isset($_POST) && !empty($_POST) )
		{
			switch(getQueryData("do"))
			{
				case "save":
					try
					{
						$wp_plugin_config = savePluginSettings($wp_plugin_config);
						$replace = array("##WP-PLUGIN-CONFIG##" => writeVariableAlsoExclude("wp_plugin_config", $wp_plugin_config, array("license"=>"license")),
										 "##LICENSE_TEXT##" => $wp_plugin_config["license"]);

						return getFileAndReplaceThenWrite("templates/_wp-plugin-config.php","wp-plugin-config.php","./",$replace);
					}	
					catch(Exception $e)
					{
						$response .= $e;
					}
					break;
			}
		}

		return $response;
	}

	function doBuild($wp_plugin_config)
	{
		global $page_info;
		$response = "";
		if( isset($_POST) && !empty($_POST) )
		{
			switch(getQueryData("do"))
			{
				case "build":
					try
					{
						$response .= "Building wordpress plugin ".$wp_plugin_config["name"]."<br>";

						$wp_files_replace = getConfigFilesReplace($wp_plugin_config);
						create_plugin_files($wp_plugin_config,$wp_files_replace);

						$zip_folder = getPostData("zip_folder") == true ? true : false;
						$delete_folder = getPostData("delete_folder") == true ? true : false;
						if($zip_folder)
						{
							$file = zipFolder($page_info->basepath."/".$wp_plugin_config["name"],$wp_plugin_config["name"], $delete_folder, true);
							$response .= "<a href='$file'>Download the plugin ".$wp_plugin_config["name"]."</a><br>";
						}

						$response .= "Plugin build finished.<br>";
					}
					catch(Exception $e)
					{
						$response .= $e;
					}

					echo $response;
					die();
					break;
			}
		}

		return $response;
	}

	#echo $page_info->getHeader(true,true);
	doSave($wp_plugin_config);
	doBuild($wp_plugin_config); 

	$page_info->body = <<<html
	<form method="post" action="?do=save" id="buildForm">
		<div class="settings">
html;
	$names = array(
		"base_files" => null,
		"admin_files"=> null,
		"css_files"=> null,
		"js_files"=> null,
		"name"=>null,
		"destination"=>null,
		"domain"=>null,
		"version"=>null,
		"classname"=>null,
		"shortname"=>null,
		"author" => "Author",
		"email" => "Email",
		"year" => "Year",
		"uri" => "Uri",
		"description" => "Description",
		"license" => "License"
		);
	$page_info->body .= convertArrayToList("Development Information", "wp_development_info", $names, $wp_plugin_config);
				
	$names = array(
		"base_files" => "Base Files",
		"admin_files"=> "Admin Files",
		"css_files"=> "Css Files",
		"js_files"=> "Js Files",
		"name"=>"Name",
		"destination"=>null,
		"domain"=>"Domain",
		"version"=>"Version",
		"classname"=>"Class Name",
		"shortname"=>"Shortname",
		"author" => null,
		"email" => null,
		"year" => null,
		"uri" => null,
		"description" => null,
		"license" => null
		);
	$page_info->body .= convertArrayToList("Plugin Information", "wp_plugin_info", $names, $wp_plugin_config);
				
	$page_info->body .= <<<html
		<ul>
			<li><label for="zip_folder">Package folder after creation</label><input type='checkbox' name='zip_folder' value='true' onclick='toggleLi("#delete");'></li>
			<li id="delete"><label for="delete_folder">Delete folder after packaging</label><input type='checkbox' name='delete_folder' value='true'></li>
		</ul>
		</div>
			<div id="buttons">
				<a id="build" class="btn btn-info" style="float: right;display:inline-block;"><span class="glyphicon "></span><span>Build plugin from site code</span></a>
				<button type="submit" id="save" class="btn btn-success" style="float: right;display:inline-block;"><span class="glyphicon "></span> Save</button>
			</div>
			<div class="results">
html;
	$page_info->body .= <<<html
			</div>
	</form>
html;

	echo $page_info->getPageHtml();
	#echo $page_info->getFooter(true);
?>