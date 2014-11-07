<?php	

	include_once('functions.php');

	global $mt_dbconn,$pages,$admin_pages,$hidden_admin_pages;


	if(file_exists("config.php"))
	{	
		include_once('config.php');
		if(!isset($sitename))
		{
			setDefaults();
		}
	}
	else
	{
		setDefaults();
	}

	function setDefaults()
	{
		global $mt_dbconn,$pages,$admin_pages,$offline_pages,$hidden_admin_pages,$sitename;

		$sitename = "Site";
		$pages = 
		array("0"=>
		array("name"=>"Frontpage","link"=>"/index.php","icon"=>"fa fa-bullhorn"),"1"=>
		array("name"=>"Secondpage","link"=>"/page2.php","icon"=>"fa fa-desktop"),"2"=>
		array("name"=>"Thirdpage","link"=>"/page3.php","icon"=>"fa fa-th"));
		$admin_pages = 
		array("0"=>
		array("name"=>"Settings","link"=>"/settings.php","icon"=>"fa fa-cog"));
		$hidden_admin_pages = 
		array("0"=>
		array("name"=>"Worpdress Plugin","link"=>"/wp-plugin.php"),"1"=>
		array("name"=>"Deploy A Local Wordpress Site","link"=>"/deploy-wp.php"));
		$offline_pages = 
		array("0"=>"settings.php","1"=>"wp-plugin.php");
		
		$mt_dbconn = new TrackingDatabase('localhost','dbname','dbuser','dbpass','3306');	
	}

	$page_info = new Page("Settings", $sitename);
	$page_info->changePageType("admin");
	$page_info->setPages($pages, $admin_pages);

	$db_button = "Database Connection Success";
	$db_icon = "glyphicon-check";
	$db_button_class = "btn-success";
	$db_connection = false;
	$db_message = "";

	$page_info->js = <<<js
			function fieldChanged()
			{
				if($('#dbButton').attr('class') !==  "btn-info")
				{
					$('#dbButton').html("<img class='spinner' border='0'>Check Database Connection")
					.removeClass("btn-danger")
					.removeClass("btn-success")
					.addClass("btn-info")
					.on('click',checkConnectionClick);
					
					$('#dbButton span')
					.removeClass("glyphicon-remove")
					.removeClass("glyphicon-check")
					.addClass("glyphicon-refresh")
				}
			}

			function connectionCallback(response)
			{
				$('#dbButton .spinner').attr('src', '');
				
				if(response.success)
				{
					$('#dbButton').html("<span class='glyphicon glyphicon-check'></span>Database Connection Success").attr("class","btn btn-success").attr('onlick','');
					$('#dberror p').html("");
				}
				else
				{
					$('#dbButton').html("<span class='glyphicon glyphicon-remove'></span>Database Connection Failed").attr("class","btn btn-danger").attr('onlick','');
					$('#dberror p').html(response.error);
				}
			}

			function connectionError(message)
			{
				$('#dbButton .spinner').attr('src', '');
				$('#dbButton').html("<span class='glyphicon glyphicon-remove'></span>Database Connection Failed").attr("class","btn btn-danger").attr('onlick','');
				$('#dberror p').html(message.responseText);
			}

			function checkConnectionClick()
			{
				$('#dbButton .spinner').attr('src','./img/indicator.gif');;
				var data = {dbhostname:$('input[name=\"dbhostname\"]').val(),
							dbname:$('input[name=\"dbname\"]').val(),
							dbuser:$('input[name=\"dbuser\"]').val(),
							dbpass:$('input[name=\"dbpass\"]').val(),
							dbport:$('input[name=\"dbport\"]').val()};

				makeAsyncPOST("?do=checkdb",data,connectionCallback,connectionError);
			}

			$(document).ready(function(){
				$("#database_info li input").change(fieldChanged);
			});
js;

	function writeSettingsToConfigFile()
	{
		global $mt_dbconn,$pages,$admin_pages,$hidden_admin_pages,$offline_pages;

		$code = writeDatabaseConnectionCode($mt_dbconn);
		$code = str_replace("##SITE SETTINGS##", writeSiteSettings($pages, $admin_pages, $hidden_admin_pages,$offline_pages), $code);

		writeToFile("config.php",$code);
	}

	function openBracket(&$tab)//,&$nl)
	{
		$nl = "\n".$tab."{\n";
		$tab .= "\t";
		return $nl;
	}

	function closeBracket(&$tab)//,&$el)
	{
		$el = "\n".$tab."}\n";
		$tab = substr($tab, 0, -2);
		return $el;
	}

	function writeDatabaseConnectionCode($database_info)
	{
		$config_start = file_get_contents("templates/_config.php");
		$code = str_ireplace("##DBHOST##", $database_info->dbhostname, $config_start);
		$code = str_ireplace("##DBNAME##", $database_info->dbname, $code);
		$code = str_ireplace("##DBUSER##", $database_info->dbuser, $code);
		$code = str_ireplace("##DBPASS##", $database_info->dbpass, $code);
		$code = str_ireplace("##DBPORT##", $database_info->dbport, $code);

		return $code;
	}

	function writeSiteSettings($pages, $admin_pages, $hidden_admin_pages, $offline_pages)
	{
		global $sitename;
		$code = "\n\t/* Site settings */\n";
		$code .= "\t".writeVariable('sitename',$sitename)."\n";
		$code .= "\t".writeVariable('pages',$pages)."\n";
		$code .= "\t".writeVariable('admin_pages',$admin_pages)."\n";
		$code .= "\t".writeVariable('hidden_admin_pages',$hidden_admin_pages)."\n";
		$code .= "\t".writeVariable('offline_pages',$offline_pages)."\n";
		return $code."\n";
	}

	function buildLi1($name, $id, $var)
	{
		return "<li><span class='name'>$name</span><input type='text' name='$id' value='$var'></li>";
	}

	function buildLi3($name1, $id1, $var1, $name2, $id2, $var2, $name3, $id3, $var3)
	{	
		$html = "<li><span class='name'>$name1</span><input type='text' name='$id1' value='$var1'><br>";
		$html .=  "<span class='name'>$name2</span><input type='text' name='$id2' value='$var2'><br>";
		$html .=  "<span class='name'>$name3</span><input type='text' name='$id3' value='$var3'></li><br>";

		return $html;
	}

	function buildPagesUl($heading, $pages, $count)
	{
		$html = "<span>$heading</span><ul>";

		foreach($pages as $page)
		{
			$html .= buildLi3('Name',"name-$count",$page['name'],'Link',"link-$count",$page['link'],'Icon',"icon-$count",$page['icon']);
			$count = $count + 1;
		}

		$html .=  "</ul>";

		return $html;
	}

	function setSettingsFromPostValues()
	{
		global $mt_dbconn, $admin_pages, $pages, $sitename;

		$sitename = getPostData("sitename");
		
		$count = 1;
		while(getPostData("name-".$count))
		{
			$name = getPostData("name-".$count);
			$link = getPostData("link-".$count);
			$icon = getPostData("icon-".$count);

			if($count <= count($admin_pages))
			{
				$index = $count - 1;
				$admin_pages[$index]["name"] = $name;
				$admin_pages[$index]["link"] = $link;
				$admin_pages[$index]["icon"] = $icon;
			}
			else
			{
				$index = $count - count($admin_pages) - 1;
				$pages[$index]["name"] = $name;
				$pages[$index]["link"] = $link;
				$pages[$index]["icon"] = $icon;
			}
			$count = $count + 1;
		}

		return $mt_dbconn->changeResetConn(getPostData('dbhostname'),getPostData('dbname'),getPostData('dbuser'),getPostData('dbpass'),getPostData('dbport'));
	}

	if( isset($_POST) && !empty($_POST) )
	{
		$do = getQueryData("do");

		switch ($do) {
			case 'save':
					$db_connection = setSettingsFromPostValues();
					writeSettingsToConfigFile();
				break;
			case 'checkdb':
					$response = array();
					try
					{
						$response["success"] = setSettingsFromPostValues();
						$response["error"] = $mt_dbconn->error;
					}
					catch(Exception $e)
					{
						$response["success"] = false;
						$response["error"] = $e->message;
					}
					header('Content-Type: application/json');
					echo json_encode($response);
					die();
				break;

			default:
				$db_connection =  $mt_dbconn->checkConnection();
				break;
		}
	}
	else
	{
		if($mt_dbconn)
		{
			$db_connection =  $mt_dbconn->checkConnection();
		}
		else 
		{
			$db_connection =  false;
		}
	}

	if(!$db_connection)
	{
		$db_button = "Database Connection Failed";
		$db_icon = "glyphicon-remove";
		$db_button_class = "btn-danger";
		$db_message = isset($mt_dbconn) ? $mt_dbconn->error : "No Database Connection Defined.";
	}


	$page_info->body = <<<bodyHTML
		<form method="post" action="?do=save">
		<div class="settings">
			Admin Links
			<ul>
bodyHTML;
		foreach ($hidden_admin_pages as $page) 
		{ 
			$page_info->body .= "<a href='.".$page['link']."'>".$page['name']."</a><br>"; 
		}

		$page_info->body .= "</ul><br>Site<br><ul>";
		$page_info->body .= buildLi1("Sitename", "sitename", $sitename);

		$page_info->body .= "</ul><br>Database<br><ul id='database_info'>";
		$page_info->body .= buildLi1("Host", "dbhostname", $mt_dbconn->dbhostname);
		$page_info->body .= buildLi1("Name", "dbname",  $mt_dbconn->dbname);
		$page_info->body .= buildLi1("User", "dbuser",  $mt_dbconn->dbuser);
		$page_info->body .= buildLi1("Pass", "dbpass",  $mt_dbconn->dbpass);
		$page_info->body .= buildLi1("Port", "dbport",  $mt_dbconn->dbport);

				
		$page_info->body .= <<<bodyHTML
					<li><a id='dbButton' class="btn $db_button_class"><span class="glyphicon $db_icon"></span>$db_button</a></li>
					<li id="dberror"><p>$db_message</p></li>
				</ul>
bodyHTML;

		$page_info->body .= buildPagesUl("Admin Pages", $admin_pages, 1);
		$page_info->body .= buildPagesUl("Pages", $pages, count($admin_pages) + 1);

		$page_info->body .= <<<bodyHTML
		</div>
			<div id="buttons">
				<button type="submit" id="save" class="btn btn-success" style="float: right;"><span class="glyphicon "></span> Save</button>
			</div>
	</form>
bodyHTML;

	echo $page_info->getPageHtml();
?>