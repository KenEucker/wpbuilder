<?php	

	include_once("config.php");
	include_once('classes.php');
	include_once('functions.php');

	global $db_conn;

	$page_info = new Page("deploy-wp",$sitename);
	$page_info->getConfig();
	
	$page_info->setPages($pages, $admin_pages);
	$page_info->changePageType("admin");
	$page_info->title = "Deploy Wordpress";
	$page_info->description = "Deploy A Local Wordpress Site";

	$page_info->js = <<<js
			$('#deploy').click(function(){

				$('form').attr('action','?do=deploy').submit();
			});

			function hideShowUl(id)
			{
				var checkbox = $("input[name='"+id+"']");

				if(checkbox.prop("disabled") == false)
				{
					checkbox.prop( "disabled", true );
					$('#'+id).hide();
					$('#'+id).prev().hide();
					$('#'+id).prev().prev().hide();
				}
				else
				{
					checkbox.prop("disabled", false);
					$('#'+id).show();
					$('#'+id).prev().show();
					$('#'+id).prev().prev().show();
				}
			}

			function toggleUl(id)
			{
				$('#'+id).slideToggle();
			}

			if($('#wp_config').prev().prop('checked') == false)
			{
				$('#wp_config').hide();
			}
			else if($('#use_existing').prop('checked') == true)
			{
				$('#wp_config').hide();	
			}

			if($('#db_config').prev().prop('checked') == false)
			{
				$('#db_config').hide();
			}

			$('#db_config').prepend("<span>Is Existing Wordpress Database</span><input type='checkbox' onclick='hideShowUl(\"wp_config\");'>");
			$('input[name=\"folder\"]').on("change",function(){
				$('#deploy').last("span").html("Deploy to "+$(this).val());
			});
js;

	$download_message = "";
	$download_new = "checked='checked'";

	if(file_exists("wp_latest.zip"))
	{
		$download_message .= "A copy of wordpress has already been downloaded, checking the 'Download a copy...' box will download the latest from wordpress servers and overwrite the local copy.<br><br>";
		$download_new = "";
	}

	if(file_exists("deploy-wp-config.php"))
	{
		include_once("deploy-wp-config.php");

		if(!isset($wp_deploy_config))
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
		global $wp_deploy_config, $db_conn ;

		$wp_deploy_config = array(
			"dbhost" => $db_conn->dbhostname,
			"dbname" => $db_conn->dbname,
			"dbuser" => $db_conn->dbuser,
			"dbpass" => $db_conn->dbpass,
			"dbport" => $db_conn->dbport,
			"folder" => "wp",
			"sitename" => "Plugin Test",
			"prefix" => "wp_"		
			);
	}

	function downloadWordpress()
	{
		$zipfilename = "wp_latest.zip";
		$wordpress_latest = "https://wordpress.org/latest.zip";
		getFileFromUrlAndSave($wordpress_latest, $zipfilename);

		return $zipfilename;
	}

	function writeDeployConfig($wp_deploy_config)
	{
		$config = "<?php\n";
		
		$config .= "\t".writeVariable('wp_deploy_config',$wp_deploy_config)."\n";

		$config .= "\n?>";

		writeToFile("deploy-wp-config.php", $config);
	}

	function writeWordpressConfig($wp_deploy_config)
	{
		$replace = array(
				"##DBHOST##" => $wp_deploy_config["dbhost"],
				"##DBNAME##" => $wp_deploy_config["dbname"],
				"##DBUSER##" => $wp_deploy_config["dbuser"],
				"##DBPASS##" => $wp_deploy_config["dbpass"],
				"##DBPORT##" => $wp_deploy_config["dbport"],
				"##PREFIX##" => $wp_deploy_config["prefix"],
				"##SALTS##" => makeGetRequest("https://api.wordpress.org/secret-key/1.1/salt/")
			);

		echo "Writing wordpress config file";
		getFileAndReplaceThenWrite("templates/_wp-config.php","wp-config.php",$wp_deploy_config["folder"]."/",$replace);
	}

	function deployWordpress($zipfilename, $wp_deploy_config, $copy_config)
	{
		$success = unzipToFolder($zipfilename,"./");
		if($success)
		{	
			rename("wordpress", $wp_deploy_config["folder"]);
			if($copy_config === true)
			{
				writeWordpressConfig($wp_deploy_config);
			}
		}
		else
		{
			echo "Could not deploy wordpress";
		}

		return $success;
	}

	function buildConditionalUl($heading,$name,$inputs)
	{
		?>
		<span><?php echo $heading." (optional)"; ?></span>
		<input type="checkbox" name="<?php echo $name; ?>" value="<?php echo $name; ?>" onclick="toggleUl('<?php echo $name; ?>');">
		<ul id="<?php echo $name; ?>">

		<?php foreach($inputs as $input)
			{
				buildTextLi1($input["name"],$input["id"],$input["var"]);
			}
		?>

		</ul>
		<?php
	}

	function buildTextLi1($name, $id, $var)
	{
		echo "<li><span class='name'>$name</span><input type='text' name='$id' value='$var'></li>";
	}

	function doDeploy($wp_deploy_config)
	{
		if( isset($_POST) && !empty($_POST) )
		{
			$do = getQueryData("do");
	
			switch ($do) {
				case 'deploy':
					$do_download = isset($_POST["download"]);
					$do_delete = isset($_POST["delete"]);
					$copy_config = isset($_POST["wp_config"]);

					echo "delete: ".$do_delete."  download: ".$do_download."<br>";

					if($do_download === true)
					{
						echo "Downloading wordpress";
						downloadWordpress();
					}
					if($do_delete === true)
					{
						if(file_exists($wp_deploy_config["folder"]) === true)
						{
							rrmdir($wp_deploy_config["folder"]);
						}
					}
					deployWordpress("wp_latest.zip",$wp_deploy_config, $copy_config);
					echo "<a href='".$wp_deploy_config["folder"]."'>visit your new wp site</a><br>";
					break;
	
				default:
					break;
			}
		}
	}

	function doSave()
	{
		global $wp_deploy_config;

		if( isset($_POST) && !empty($_POST) )
		{
			$do = getQueryData("do");
	
			switch ($do) {
				case 'save':
					$wp_deploy_config["folder"] = getPostData("folder");
					$wp_deploy_config["dbhost"] = getPostData("dbhost");
					$wp_deploy_config["dbname"] = getPostData("dbname");
					$wp_deploy_config["dbuser"] = getPostData("dbuser");
					$wp_deploy_config["dbpass"] = getPostData("dbpass");
					$wp_deploy_config["dbport"] = getPostData("dbport");

					$wp_deploy_config["folder"] = getPostData("folder");
					$wp_deploy_config["prefix"] = getPostData("prefix");

					writeDeployConfig($wp_deploy_config);
					break;
	
				default:
					break;
			}
		}
	}

	doSave();

	echo $page_info->getHeader(true,true);
	echo $page_info->getBodyBefore();
?>

	<div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
					<form method="post" action="?do=save">
						<div class="settings">
							Deployment
							<ul>
								<?php 
									buildTextLi1("Folder","folder",$wp_deploy_config["folder"]);
								?>
							</ul>
							<?php
								$wp_options = array(
									1=>array("name"=>"Table Prefix","id"=>"prefix","var"=>$wp_deploy_config["prefix"]),
									2=>array("name"=>"Host","id"=>"dbhost","var"=>$wp_deploy_config["dbhost"]),
									3=>array("name"=>"Name","id"=>"dbname","var"=>$wp_deploy_config["dbname"]),
									4=>array("name"=>"User","id"=>"dbuser","var"=>$wp_deploy_config["dbuser"]),
									5=>array("name"=>"Pass","id"=>"dbpass","var"=>$wp_deploy_config["dbpass"]),
									6=>array("name"=>"Port","id"=>"dbport","var"=>$wp_deploy_config["dbport"])
									);
								buildConditionalUl("Genereate Wordpress Configuration","wp_config",$wp_options);
								#buildConditionalUl("Automatic Database Configuration","db_config",$db_options);
							?>
							<br>
							<?php if(file_exists($wp_deploy_config["folder"]) === true)
								{
									echo "<span id='delete'>Delete Existing Folder <input type='checkbox' name='delete' value='delete'></span><br>";
									$download_message .= "A folder with the same name already exists, by checking the 'Delete Folder' box this installation will overwrite the old. Leaving the box unchecked will add a number to the new installation path folder.<br><br>";
								}?>
							<span>Download a copy of the latest release of wordpress <input type="checkbox" name="download"  value='download'<?php echo $download_new; ?>></span>
							<p>
							<span class="label label-info"><?php echo $download_message; ?></span>
							</p>
						</div>
						<div id="buttons">
							<a id="deploy" class="btn btn-info" style="float: right;display:inline-block;"><span class="glyphicon "></span><span>Deploy to <?php echo $dir.$wp_deploy_config["folder"]; ?></span></a>
							<button type="submit" id="save" class="btn btn-success" style="float: right;display:inline-block;"><span class="glyphicon "></span> Save</button>
						</div>
						<div class="results">
							<?php doDeploy($wp_deploy_config); ?>
						</div>
					</form>
                </div>
            </div>
        </div>
    </div>
<?php
	echo $page_info->getBodyAfter();
	echo $page_info->getFooter(true);?>