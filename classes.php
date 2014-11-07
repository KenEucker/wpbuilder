<?php

class Page
{
	public $title, $description, $name, $meta, $keywords, $author, $favicon, $breadcrumb;
	public $styles, $js_files, $js, $head, $body, $nav_pages, $admin_pages, $exclude_files;
	public $js_files_order, $css_files_order, $copyright;
	private $page_type, $page_type_files;

	public function __construct($name, $sitename)
	{
		$this->sitename = $sitename;
		$this->name = $name;
		$this->title = $sitename." - ".$name;
		$this->description = "a description of my website";
		$this->meta = $sitename." - ".$name;
		$this->keywords = array(); 
		$this->author ="Epic720";
		$this->favicon = "";
		$this->breadcrumb = array( 
			0=>array( 
				"title"=>"Frontpage", 
				"link"=>"index.php"),
			1=>array( 
				"title"=>$name, 
				"link"=>strtolower($name).".php") 
			);
		$this->styles = "";
		$this->css_files = array("css/bootstrap.min.css","css/style.css");
		$this->js_files = array("js/jquery.js","js/bootstrap.min.js","js/html5shiv.js","js/code.js");
		$this->js = "";
		$this->basepath = ".";	
		$this->nav_pages = array();
		$this->admin_pages = array();
		$this->copyright = "Your Company 2014. All Rights Reserved";
		$this->changePageType("");
	}

	function changePageType($type)
	{
		switch($type)
		{
			case "admin":
				$this->page_type = "admin";
				$this->page_type_files = array("simple-sidebar.css");
				break;

			case "landing":
				$this->page_type = "landing";
				$this->page_type_files = array("landing-page.css");
				break;

			default:
			case "basic":
				$this->page_type = "basic";
				$this->page_type_files = array();
				break;
		}
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

	public function setPages($nav_pages, $admin_pages)
	{
		if($nav_pages !== null && count($nav_pages)) { $this->nav_pages = $nav_pages; }
		if($admin_pages !== null && count($admin_pages)) { $this->admin_pages = $admin_pages; }
	}

	protected function buildMeta()
	{
		$keywordString = ""; 
		foreach($this->keywords as $keyword)
		{
			$keywordString .= ",".$keyword;
		}
		$keywordString = substr($keywordString, 1);

		$meta = <<<metaHtml

			<meta charset="utf-8">
			<!-- Title here -->
			<title>$this->title</title>
			<!-- Description, Keywords and Author -->
			<meta name="description" content="$this->description">
			<meta name="keywords" content="$keywordString">
			<meta name="author" content="$this->author">
			
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			
			<!-- Favicon -->
			<link rel="shortcut icon" href="$this->favicon">    
			<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		    <!--[if lt IE 9]>
		        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		    <![endif]-->
metaHtml;

		return $meta;
	}

	public function getAndLoadFilesFromFolder($folder, $output, $replace, $order)
	{
		$out = "";
		$files = scandir($folder);

		foreach ($order as $file) 
		{
			if(strcmp($file, "-") === 0)
			{
				break;
			}

			if(in_array($file, $files))
			{
				$replace["##FILE##"] = $file;
				$out .= $this->replaceStringContents($output, $replace); 
			}
		}

		foreach ($files as $file) 
		{
			if(!is_dir($file) && 
				(substr($file, 0, 1) !== ".") &&
				(!in_array($file, $order)))
			{	
				$replace["##FILE##"] = $file;
				$out .= $this->replaceStringContents($output, $replace); 
			}
		}

		foreach (array_slice($order, array_key_exists("-", $order) + 1) as $file)
		{
			if(in_array($file, $files))
			{
				$replace["##FILE##"] = $file;
				$out .= $this->replaceStringContents($output, $replace); 
			}
		}

		return $out;
	}

	public function getCssFiles()
	{
		$css = "";

		foreach ($this->css_files as $file) 
		{
			$css .= "<link rel='stylesheet' href='$file'>";
		}

		return $css;
	}

	public function getJsFiles()
	{
		$js = "";

		foreach ($this->js_files as $file) 
		{
			$js .= "<script src='$file'></script><br>";
		}

		return $js;
	}	

	function buildNavigationLinks($links, &$color_start)
	{
		$navigation_colors = array("nred", "ngreen", "nblue", "nlightblue", "nviolet", "norange");
		$color_count = 0 + $color_start;
		$navigation_html = "";

		foreach($links as $link)
		{
			$navigation_html .= "<li class='".$navigation_colors[$color_count]."''><a href=".$this->basepath.$link['link']."><i class='".$link['icon']."'></i> ".$link['name']."</a></li>";
			if($color_count == count($navigation_colors) - 1)
			{
				$color_count = 0;
			}
			else
			{
				++$color_count;
			}
		}

		return $navigation_html;
	}

	function getPageLinks()
	{
		$pageLinks = "";
		$color_start = 0;
		if((isset($_SESSION["admin_logged_in"]) == true) && ($_SESSION["admin_logged_in"] == true))
		{
			 $pageLinks .= $this->buildNavigationLinks($this->admin_pages, $color_start);
		}
		$pageLinks .= $this->buildNavigationLinks($this->nav_pages, $color_start);

		return $pageLinks;
	}

	public function getNavbar()
	{
		$navigationHtml = <<<navHtml
		<!-- Navigation -->
	    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	        <div class="container">
	            <!-- Brand and toggle get grouped for better mobile display -->
	            <div class="navbar-header">
	                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
	                    <span class="sr-only">Toggle navigation</span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                </button>
	                <a class="navbar-brand" href="#">$this->sitename</a>
	            </div>
	            <!-- Collect the nav links, forms, and other content for toggling -->
	            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	                <ul class="nav navbar-nav">
navHtml;
		
	  	$navigationHtml .= $this->getPageLinks();
		$navigationHtml .= <<<navHtml
	                </ul>
	            </div>
	            <!-- /.navbar-collapse -->
	        </div>
	        <!-- /.container -->
	    </nav>
navHtml;
	
		return $navigationHtml;
	}

	public function getPageBody($include_ends)
	{
		$bodyHtml = "";

		if($include_ends === true)
		{
			$bodyHtml .= $this->getBodyBefore();
		}

		switch($this->page_type)
		{
			case "landing":
			$message = isset($this->sections['message']) ? $this->sections['message'] : "";
			$sectionA = isset($this->sections['sectionA']) ? $this->sections['sectionA'] : "";
			$sectionB = isset($this->sections['sectionB']) ? $this->sections['sectionB'] : "";
			$sectionC = isset($this->sections['sectionC']) ? $this->sections['sectionC'] : "";
			$sectionD = isset($this->sections['sectionD']) ? $this->sections['sectionD'] : "";
				$landingHtml = <<<landing
				<!-- Header -->
		    <div class="intro-header">

		        <div class="container">

		            <div class="row">
		                <div class="col-lg-12">
		                    <div class="intro-message">
		                    $message
		                    </div>
		                </div>
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.intro-header -->

		    <!-- Page Content -->

		    <div class="content-section-a">

		        <div class="container">

		            <div class="row">
		                $sectionA
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.content-section-a -->

		    <div class="content-section-b">

		        <div class="container">

		            <div class="row">
		                $sectionB
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.content-section-b -->

		    <div class="content-section-a">

		        <div class="container">

		            <div class="row">
		               $sectionC
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.content-section-a -->

		    <div class="banner">

		        <div class="container">

		            <div class="row">
		               $sectionD
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.banner -->
landing;
				$bodyHtml .= $landingHtml;
			break;

			case "admin":
				$adminHtml = <<<admin
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        $this->body
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
admin;
				$bodyHtml .= $adminHtml;
			break;

			default:
			case "basic":
				$basicHtml = <<<basic
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        $this->body
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
basic;
				$bodyHtml .= $basicHtml;
			break;
		}

		if($include_ends === true)
		{
			$bodyHtml .= $this->getBodyAfter();
		}

		return $bodyHtml;
	}

	public function getBodyBefore()
	{
		$beforeHtml = "";

		switch($this->page_type)
		{
			case "landing":
			$message = isset($this->sections['message']) ? $this->sections['message'] : "";
				$landingHtml = <<<landing
				<!-- Header -->
		    <div class="intro-header">

		        <div class="container">

		            <div class="row">
		                <div class="col-lg-12">
		                    <div class="intro-message">
		                    $message
		                    </div>
		                </div>
		            </div>

		        </div>
		        <!-- /.container -->

		    </div>
		    <!-- /.intro-header -->

		    <!-- Page Content -->
landing;
				$beforeHtml .= $landingHtml;
			break;

			case "admin":
				$adminHtml = <<<admin
		<div id="wrapper">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                    <a href="#">
                        $this->name
                    </a>
                </li>
admin;

				$adminHtml .= $this->getPageLinks()."</ul></div>";
				$beforeHtml .= $adminHtml;
			break;

			default:
			case "basic":
				$basicHtml = $this->body;
				$beforeHtml .= $basicHtml;
			break;
		}

		return $beforeHtml;
	}

	public function getBodyAfter()
	{
		$afterHtml = "";

		switch($this->page_type)
		{
			case "landing":
				$message = isset($this->sections['message']) ? $this->sections['message'] : "";
				$landingHtml = <<<landing
landing;
				$afterHtml .= $landingHtml;
			break;

			case "admin":
				$adminHtml = <<<admin
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
admin;
				$afterHtml .= $adminHtml;
			break;

			default:
			case "basic":

			break;
		}

		return $afterHtml;
	}

	public function getFooterNavigation()
	{
		$footerNav = <<<footerHTML
	        <div class="container">
	            <div class="row">
	                <div class="col-lg-12">
	                    <ul class="list-inline">
footerHTML;
	    $footerNav .= $this->getPageLinks();        
		$footerNav .= <<<footerHTML
	                    </ul>
	                    <p class="copyright text-muted small">Copyright &copy; </p>
	                </div>
	            </div>
	        </div>
footerHTML;

		return $footerNav;
	}

	public function getHeader($include_begins, $include_navigation)
	{
		$header = "";
		if(currentlyInWordpress() == true)
		{
			$header .= $this->getCssFiles();

			foreach($this->page_type_files as $file)
			{
				$header .= "<!-- Page Stylesheet --><link rel='stylesheet' href='css/$file'>";
			}
		}
		else
		{
			if($include_begins === true)
			{
				$header .= "<!DOCTYPE html><html lang='en'>";
			}

			$header .= "<head>";
			$header .= $this->buildMeta();
			$header .= $this->getCssFiles();

			foreach($this->page_type_files as $file)
			{
				$header .= "<link rel='stylesheet' href='css/$file'>";
			}

			$header .= "</head>";

			if($include_begins === true)
			{
				$header .= "<body>";
			}
			if($include_navigation == true)
			{
				$header .= $this->getNavbar();
			}

		}

		return $header."<!-- End head -->";
	}

	public function getFooter($include_ends)
	{
		$footer = "";
		if(currentlyInWordpress() == true)
		{
			$footer .= $this->getJsFiles();
			$footer .= "<script>".$this->js."</script>";
		}
		else
		{

			if($include_ends == true)
			{
				$footer .= "";
			}

			$footer .= $this->getJsFiles();
			$catchError = <<<errorScript
			function checkForError()
			{
				var after_error = \$('body').html().indexOf("<div class");
				var error_end = \$('body').html().indexOf("<meta charset");
				var warning_text = \$('body').html().indexOf("Warning");
				var error_text = \$('body').html().indexOf("Error");
				var notice_text = \$('body').html().indexOf("Notice");
				var error_exists = ( (error_text != -1 && (error_text < after_error)) ||
									 (warning_text != -1 && (warning_text < after_error)) ||
									 (notice_text != -1 && (notice_text < after_error)) ) ? true : false;

				if(error_exists == true)
				{
					var error = \$('body').html().substring(0,error_end);
					var html = \$('body').html().substring(error_end);
					var head_end = html.indexOf("<!-- Navigation -->");
					var head = html.substring(0,head_end);
					var body = html.substring(head_end);

				    var message = \$("<div>").addClass("alert-message error").html("<a class='close' href='#'>x</a><p>"+error+"</p>");
					message = $("<div />").append($(message).clone()).html();
				    var newContent = "<head>"+head+"</head><body>"+message+body+"</body>";
				    document.open();
					document.write(newContent);
					document.close();
				}

				checked = true;
			}

			$(document).ready(function(){
				$('.close').on('click',function(){\$(this).parent().hide();});

				var checked;

				if(checked != true)
				{
					checkForError();
				}
				
			});
errorScript;
			$footer .= "<script>".$catchError."\n".$this->js."</script>";

			if($include_ends == true)
			{
				//$footer .= "</div>";
				$footer .= "</body>";
				$footer .= "</html>";
			}
		}
		return $footer;
	}

	public function getPageHtml()
	{
		$pageHtml = "";

		if(currentlyInWordpress() == true)
		{
			return $this->getHeader(false,false).$this->body.$this->getFooter(false);
		}
		else
		{
			$pageHtml = $this->getHeader(true,true);

			$pageHtml .= $this->getPageBody(true);
			
			$pageHtml .= $this->getFooter(true);
		}

		return $pageHtml;
	}
}

class WordpressPage extends Page
{
	public function getPageHtml()
	{
		return $this->getHeader(false,false).$this->body.$this->getFooter(false);
	}

	public function getHeader($include_begins, $include_navigation)
	{
		$header = "";
		$header .= $this->getCssFiles();

		foreach($this->page_type_files as $file)
		{
			$header .= "<!-- Page Stylesheet --!><br><link rel='stylesheet' href='css/$file'><br>";
		}
		
		return $header;
	}

	public function getFooter($include_ends)
	{
		$footer = "";
		$footer .= $this->getJsFiles();
		$footer .= "<script>".$this->js."</script>";

		return $footer;
	}
}

class TrackingDatabase
{
	public $dbhostname,$dbport,$dbip,$dbuser,$dbpass,$dbname;
	public $error;
	private $dbConn;

	public function __construct($hostname,$name,$user,$pass,$port)
	{
		$this->dbhostname = $hostname;
		$this->dbname = $name;
		$this->dbuser = $user; 
		$this->dbpass = $pass; 
		$this->dbport = $port;
		$this->dbip = null;
		$this->error = "";
	}

	private function resetConn()
	{
		try
		{
			$this->disconnect();
			return  $this->connect();
		}
		catch(Exception $e)
		{
			$this->error = $e;
			return false;
		}
	}

	public function changeresetConn($hostname, $name, $user, $pass, $port)
	{
		$this->dbhostname = $hostname;
		$this->dbuser = $user;
		$this->dbname = $name;
		$this->dbport = $port;
		$this->dbpass = $pass;
		
		return $this->resetConn();
	}

	public function getConn()
	{
		return $this->dbConn;
	}

	public function checkConnection()
	{
		if($this->dbConn)
		{
			return true;
		}

		set_time_limit(20);   
		ini_set('mysql.connect_timeout','20');   
		ini_set('max_execution_time', '20'); 
		$this->connect();
		set_time_limit(0);   
		ini_set('mysql.connect_timeout','0');   
		ini_set('max_execution_time', '0'); 

		if($this->dbConn)
		{
			return true;
		}

		return false;
	}	

	private function connect()
	{
		try
		{
			$servername = $this->dbhostname;
			if(strlen($this->dbport) > 0)
			{
				$servername .= ":".$this->dbport;
			}
			$this->dbConn = @new PDO("mysql:host=".$this->dbhostname.";port=".$this->dbport.";dbname=".$this->dbname.";charset=utf8", $this->dbuser, $this->dbpass);
			$this->dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->checkConnection();
		}
		catch (PDOException $e)
		{
			$this->error = $e->getMessage();
			//die();
			return false;
		}
	}

	private function disconnect()
	{
		$this->dbConn = null;
	}
}	


class Rester
{
	protected $debug,$id,$message,$error,$success,$default,$small_name,$big_name,$mode;

	private function init() 
	{	
		$this->debug = getQueryData('debug');
		$this->id = null;
		$this->message = "";
		$this->error = null;
		$this->success = null;
		$this->default = false;
		$this->includes_type = true;
	}

	public function __construct($name,$includes_type=true) 
	{	
		$this->init();
		$this->small_name = $name;
		$this->big_name = strtoupper(substr($name,0,1)).substr($name,1);
		$this->includes_type = $includes_type;
		//echo "small_name: ".$this->small_name." big_name:".$this->big_name." type?:".($this->includes_type ? 'yes' : 'no')."<br>";
	}

	protected function createFunction()
	{
		$data = array();
		$name = getPostData($this->small_name);
		$type = getPostData('type');
		
		if($name != null)
		{
			array_push($data, $name);
		}
		else
		{
			return null;
		}
		if($this->includes_type)
		{
			array_push($data, $type);	
		}
		$result = callStoredProcedureAndFetchAll('create'.$this->big_name, $data);

		return $result[0]['id'];
	}

	protected function updateFunction()
	{
		$data = array();
		$id = getPostData('id');
		$name = getPostData($this->small_name);
		$type = getPostData('type');

		array_push($data, $id);
		if($name != null)
		{
			array_push($data, $name);
		}
		if($this->includes_type)
		{
			array_push($data, $type);	
		}
		$result = callStoredProcedureAndFetchAll('update'.$this->big_name, $data);

		return $result[0]['id'];
	}

	protected function deleteFunction()
	{
		$data = array();
		$id = getPostData('id');
		array_push($data, $id);
		$result = callStoredProcedureAndFetchAll('delete'.$this->big_name, $data);
		return $result[0]['id'];
	}
	
	function doInternalWork()
	{
		switch($this->mode)
		{
			case 'create':
				$this->id = $this->createFunction();
				$this->success = true;
			break;

			case 'update':
				$this->id = $this->updateFunction();
				$this->success = true;
			break;

			case 'delete':
				$this->id = $this->deleteFunction();
				$this->success = true;
			break;

			default:
				$this->message = "No mode given. ".$this->mode;
				$this->default = true;
			break;
		}
	}

	public function doWorkSon()
	{
		$response = "";
		try
		{
			$this->mode = getPostData('sp_mode');
			$this->doInternalWork();
		}
		catch(Exception $e)
		{
			$this->message = "There was an error while trying to record the entry.";
			$this->error = $e;
			$this->success = false;
		}

		if($this->debug == true)
		{
			$response = "<html><head></head><body>";
			$response .= print_r(array('success' => $this->success, 'message' => $this->message, 'id' => $this->id, 'error' => $this->error, 'mode'=>$this->mode));
			$response .= "</body></html>";
		}
		elseif($this->default)
		{
			$response = "Nothing yet.";
		}
		else
		{
			//header('Content-Type: application/json');
			return json_encode(array('success' => $this->success, 'message' => $this->message, 'id' => $this->id, 'error' => $this->error, 'mode'=>$this->mode));
		}

		return $response;
	}

	public function isSuccess()
	{
		return ($this->success && (!$this->default));
	}
}

?>