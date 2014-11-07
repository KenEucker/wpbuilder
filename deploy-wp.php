<?php	

	if(!file_exists('config.php'))
	{
		header('location:settings.php');
	}

	include_once("config.php");
	include_once('classes.php');
	include_once('functions.php');

	global $mt_dbconn;

	$page_info = new Page("deploy-wp",$sitename);
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
				if($('#wp_config').prev().attr('style') == "display: none;")
				{
					$('#'+id).hide();
					$('#'+id).prev().hide();
					$('#'+id).prev().prev().hide();
				}
				else
				{
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
		$download_message = "A copy of wordpress has already been downloaded, checking this box will overwite and download the latest from wordpress servers.";
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
		global $wp_deploy_config, $mt_dbconn ;

		$wp_deploy_config = array(
			"dbhost" => $mt_dbconn->dbhostname,
			"dbname" => $mt_dbconn->dbname,
			"dbuser" => $mt_dbconn->dbuser,
			"dbpass" => $mt_dbconn->dbpass,
			"dbport" => $mt_dbconn->dbport,
			"folder" => "wp/"
			);
	}

	function downloadWordpress()
	{
		$zipfilename = "wp_latest.zip";
		$wordpress_latest = "https://wordpress.org/latest.zip";
		getFileFromUrl($wordpress_latest, $zipfilename);

		return $zipfilename;
	}

	function writeWordpressConfig($wp_deploy_config)
	{
		$replace = array(
				"##DBHOST##" => $wp_deploy_config["dbhost"],
				"##DBNAME##" => $wp_deploy_config["dbname"],
				"##DBUSER##" => $wp_deploy_config["dbuser"],
				"##DBPASS##" => $wp_deploy_config["dbpass"],
				"##DBPORT##" => $wp_deploy_config["dbport"]
			);

		getFileAndReplaceThenWrite("templates/_wp-config.php","wp-config.php",$wp_deploy_config["folder"],$replace);
	}

	function deployWordpress($zipfilename, $wp_deploy_config, $copy_config)
	{
		$success = unzipToFolder($zipfilename,$wp_deploy_config["folder"]);
		if($success)
		{	
			if($copy_config)
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
		<input type="checkbox" onclick="toggleUl('<?php echo $name; ?>');">
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
					//downloadWordpress();
					deployWordpress("wp_latest.zip",$wp_deploy_config, false);
					echo "<a href='".$wp_deploy_config["folder"]."'>visit your new wp site</a><br>";
					break;
	
				default:
					break;
			}
		}
	}

	function doSave()
	{
		if( isset($_POST) && !empty($_POST) )
		{
			$do = getQueryData("do");
	
			switch ($do) {
				case 'save':
					$folder = getPostData("folder");
					$dbhost = getPostData("dbhost");
					$dbname = getPostData("dbname");
					$dbuser = getPostData("dbuser");
					$dbpass = getPostData("dbpass");
					$dbport = getPostData("dbport");
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
								$db_options = array(
									0=>array("name"=>"Host","id"=>"dbhost","var"=>$wp_deploy_config["dbhost"]),
									1=>array("name"=>"Name","id"=>"dbname","var"=>$wp_deploy_config["dbname"]),
									2=>array("name"=>"User","id"=>"dbuser","var"=>$wp_deploy_config["dbuser"]),
									3=>array("name"=>"Pass","id"=>"dbpass","var"=>$wp_deploy_config["dbpass"]),
									4=>array("name"=>"Port","id"=>"dbport","var"=>$wp_deploy_config["dbport"])
									);
								buildConditionalUl("Automatic Database Configuration","db_config",$db_options);
								echo "<br>";
								$wp_options = array(
									0=>array("name"=>"Sitename","id"=>"sitename","var"=>"")
									);
								buildConditionalUl("Automatic Wordpress Configuration","wp_config",$wp_options);
							?>
							<br>
							<span>Download a copy of the latest release of wordpress <input type="checkbox" <?php echo $download_new; ?>></span>
							<br>
							<span class="lbl-info"><?php echo $download_message; ?></span>
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