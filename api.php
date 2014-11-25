<?php

include_once('database.php');

class SlimShim
{
    protected $api,$base;
    private static $key_routes_mapped = false;

    function __construct(Slim\Slim $slim)
    {
    	$this->base = "/api";
        $this->api = $slim;
        $this->mapRoutes();
        //if($key_routes_mapped !== true)
        //{
	        //$key_routes_mapped = true;
    	//}
    }

    function returnError($message)
    {
        $debug["message"] = $message;
        $debug["mode"] = $this->api->request->getMethod();
        $debug["error"] = true;

        $this->api->response->status(500);
        $this->api->response['Content-Type'] = 'apilication/json';
        $this->api->response->body(json_encode($debug));
        $this->api->stop();
    }

    function CheckForNull($array)
    {
        if(count($array) == 0)
        {
            return 0;
        }

        foreach($array as $var)
        {
            if($var === null)
            {
                return false;
            }
        }

        return true;
    }

    function endKey()
    {
    	$ip = $this->api->request->getIp();
    	
    }

    function getKey()
    {
    	$ip = $this->api->request->getIp();
    	$key = $this->wrapCallInDebug('getApiKeyByIp', array("ip"=>$ip), true);

    }

    public function verifyKey(\Slim\Route $route) 
    {
    	$key = $route->getParam('key');
        $keyVerification = $this->wrapCallInDebug('verifyApiKey',array($key),false);
        if(count($keyVerification) > 0) 
        {
            // nothing to do currently
            // record number of API requests?
        } 
        else 
        {
            // no key found, thus this key is invalid
            $this->returnError("You have passed an invalid API key. Return:".$keyVerification);
        }
    }

    function mapKeyRoutes($slimshim)
    {
        $this->addRoute('get','/key', 'getKey', false, $slimshim);
        $this->addRoute('post','/key/get', 'getKey', false, $slimshim);
        $this->addRoute('post','/key/end', 'endKey', true, $slimshim);
    }

    function missingKeyError()
    {
    	$this->returnError("Missing API key. This server requires a key for authenticaion for this request.");
    }

    public function addRoute($type, $path, $callback, $verify_key, $slimshim)
    {
    	$route = $path = $this->base.$path;
    	$missing = array($slimshim,'missingKeyError');
    	$verify = array($slimshim,'verifyKey');
    	$execute = array($slimshim, $callback);

    	if($verify_key === true)
    	{
    		$route .= "/:key";
    	}

    	switch($type)
    	{
    		case 'get':
    			if($verify_key === true)
    			{
    				$slimshim->api->get($route, $verify, $execute);
    				$slimshim->api->get($path, $missing);
    			}
    			else
    			{
    				$slimshim->api->get($route, $execute);
    			}
    			break;

    		case 'post':
    			if($verify_key === true)
    			{
    				$slimshim->api->post($route, $verify, $execute);
    				$slimshim->api->post($path, $missing);
    			}
    			else
    			{
    				$slimshim->api->post($route, $execute);
    			}
    			break;

    		case 'put':
    			if($verify_key === true)
    			{
    				$slimshim->api->put($route, $verify, $execute);
    				$slimshim->api->put($path, $missing);
    			}
    			else
    			{
    				$slimshim->api->put($route, $execute);
    			}
    			break;

    		case 'patch':
    			if($verify_key === true)
    			{
    				$slimshim->api->patch($route, $verify, $execute);
    				$slimshim->api->patch($path, $missing);
    			}
    			else
    			{
    				$slimshim->api->patch($route, $execute);
    			}
    			break;

    		case 'delete':
    			if($verify_key === true)
    			{
    				$slimshim->api->delete($route, $verify, $execute);
    				$slimshim->api->delete($path, $missing);
    			}
    			else
    			{
    				$slimshim->api->delete($route, $execute);
    			}
    			break;
    	}

    }

    function wrapCallInDebug($call,$data,$and_send)
    {
        $null = $this->CheckForNull($data);
        try
        {
	        $id = callStoredProcedureAndFetchAll($call, $data);
	        $id = $id[0][0];

	        if($and_send === true)
	        {
	            $this->api->response['Content-Type'] = 'apilication/json';
	            $this->api->response->body(json_encode(array("mode"=>$mode,"success"=>true,"id"=>$id)));
	    	}

	    	return $id;
    	}
        catch(PDOException $e) 
        {
            $this->returnError("Exception: ".$e->getMessage());
        }	
    }

    function getEditorIndex()
    {
    	$ip = $this->api->request->getIp();
    	$plugin_folder = plugins_url('', __FILE__ );
    	$json_rows = json_encode(array(array("id"=>"1","target"=>"t1","source"=>"s1","type"=>"t1","action"=>"a1","timestamp"=>"now")));
    	$json_rows = json_encode(array(array("name"=>"id","field"=>"id","id"=>"id"),array("name"=>"target","field"=>"target","id"=>"target")));
    	$json_data = json_encode(array("queryRecordCount"=>1,"totalRecordCount"=>1,"records"=>array(0=>array("id"=>"1","target"=>"t1","source"=>"s1","type"=>"t1","action"=>"a1","timestamp"=>"now"))));

         $template = <<<EOT
    <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8"/>
                <title>Slim Framework for PHP 5</title>
                <style>
                    html,body,div,span,object,iframe,
                    h1,h2,h3,h4,h5,h6,p,blockquote,pre,
                    abbr,address,cite,code,
                    del,dfn,em,img,ins,kbd,q,samp,
                    small,strong,sub,sup,var,
                    b,i,
                    dl,dt,dd,ol,ul,li,
                    fieldset,form,label,legend,
                    table,caption,tbody,tfoot,thead,tr,th,td,
                    article,aside,canvas,details,figcaption,figure,
                    footer,header,hgroup,menu,nav,section,summary,
                    time,mark,audio,video{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;}
                    body{line-height:1;}
                    article,aside,details,figcaption,figure,
                    footer,header,hgroup,menu,nav,section{display:block;}
                    nav ul{list-style:none;}
                    blockquote,q{quotes:none;}
                    blockquote:before,blockquote:after,
                    q:before,q:after{content:'';content:none;}
                    a{margin:0;padding:0;font-size:100%;vertical-align:baseline;background:transparent;}
                    ins{background-color:#ff9;color:#000;text-decoration:none;}
                    mark{background-color:#ff9;color:#000;font-style:italic;font-weight:bold;}
                    del{text-decoration:line-through;}
                    abbr[title],dfn[title]{border-bottom:1px dotted;cursor:help;}
                    table{border-collapse:collapse;border-spacing:0;}
                    hr{display:block;height:1px;border:0;border-top:1px solid #cccccc;margin:1em 0;padding:0;}
                    input,select{vertical-align:middle;}
                    html{ background: #EDEDED; height: 100%; }
                    body{background:#FFF;margin:0 auto;min-height:100%;padding:0 30px;width:440px;color:#666;font:14px/23px Arial,Verdana,sans-serif;}
                    h1,h2,h3,p,ul,ol,form,section{margin:0 0 20px 0;}
                    h1{color:#333;font-size:20px;}
                    h2,h3{color:#333;font-size:14px;}
                    h3{margin:0;font-size:12px;font-weight:bold;}
                    ul,ol{list-style-position:inside;color:#999;}
                    ul{list-style-type:square;}
                    code,kbd{background:#EEE;border:1px solid #DDD;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:0 4px;color:#666;font-size:12px;}
                    pre{background:#EEE;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:5px 10px;color:#666;font-size:12px;}
                    pre code{background:transparent;border:none;padding:0;}
                    a{color:#70a23e;}
                    header{padding: 30px 0;text-align:center;}
                </style>
                <script type="text/javascript" language="javascript" src="$plugin_folder/js/jquery.js"></script>
                <script type="text/javascript" language="javascript" src="$plugin_folder/js/jquery.dynatable.js"></script>
            </head>
            <body>
               
                <h1>Track Metrics</h1>
                <p>
                    Tracks usage metrics for a given system.
                </p>
               
                <section>
                    <h2>Metric Table Editor</h2>
                    <p>
                    <div>IP: <input type="text" id="ip" value="$ip"></div>
                    <div>KEY: <input type="text" id="key"></div>
                    <div>ID: <input type="text" id="id"></div><br>
                    <a class="btn" onclick="testGetApiKey(this)">Test GetApiKey</a><span class="output"></span><br>
                    <a class="btn" onclick="testAjax('post');">Test POST</a><br>
                    <a class="btn" onclick="testAjax('put')">Test PUT</a><br>
                    <a class="btn" onclick="testAjax('patch')">Test PATCH</a><br>
                    <a class="btn" onclick="testAjax('delete')">Test DELETE</a><br>
                    <a onclick="testGetById(this)">/ID/KEY</a><span class="output"></span><br>
                    </p>
                </section>
                <section>
                	<div id="editor">
                	</div>
                </section>

                <script>
                $(document).ready(function(){
                	var myRecords = $json_data ;
                	var rows = $json_rows;
                	var columns = $json_columns;
					//var dynatable = $('#editor').dynatable();
					//dynatable.records.updateFromJson(myRecords);
					//dynatable.process();
					var slickgrid = new Slick.Grid("#editor", rows, columns, options);
                });

                function makeAsynCall(mode,url,data,success,error)
                {
                     $.ajax({
                      type: mode,
                      url: document.URL+"/"+url,
                      data: data,
                      success: success,
                      error: error
                    });
                }

                function gotApiKey(response)
                {
                    if(typeof response == "string")
                	{
                		alert('It appeared there was success, but nope: '+response.replace(/(<([^>]+)>)/ig,""));
                	}
                	else
                	{
                		$('#key').val(response.id);
                	}
                }

                function testGetApiKey(target)
                {
                    makeAsynCall("get","../key",[],gotApiKey,error);
                }

                function testGetById(target)
                {
                    var id = $('#id').val();
                    var key = $('#key').val();

                    //makeAsynCall("get",id+"/"+key,success,error);
                }

                function error(response)
                {
                	if(typeof response.responseJSON !== "undefined")
                	{
	                    response = response.responseJSON;
	                    alert(response.message + " mode: " + response.mode);
                	}
                	else
                	{
                		alert('Error within an error: '+response.responseText.replace(/(<([^>]+)>)/ig,""));
                    }
                }

                function success(response)
                {
                	if(typeof response == "string")
                	{
                		alert('It appeared there was success, but nope: '+response.replace(/(<([^>]+)>)/ig,""));
                	}
                	else
                	{
                    	alert('id: '+response.id);
                    }
                }

                function testAjax(mode)
                {
                	var url = mode;
                	var key = $('#key').val();
                	if(key.length > 0)
                	{
                		url += "/"+key;
                	}
                    makeAsynCall(mode,url,[],success,error);
                }
                </script>
                
                
            </body>
        </html>
EOT;
            echo $template;
    }
}

class MetricSlim extends SlimShim
{
	public function getMetric($id) 
    {

        $result = array("status" => "success", "id" => $id);
        echo json_encode($result);
    }

    function deleteMetric()
    {
        $id = $this->api->request->get('id');
        $data = array($id);

        $this->wrapCallInDebug('updateMetric', $data, true);
    }

    function updateMetric()
    {
        $id = $this->api->request->get('id');
        $data = array($id);

        $this->wrapCallInDebug('updateMetric', $data, true);
    }

    function createMetric()
    {
        $data = array();
        $session_id = $this->api->request->get('session_id');
        $action = $this->api->request->get('action');
        $type = $this->api->request->get('type');
        $target = $this->api->request->get('target');
        $source = $this->api->request->get('source');
        $timestamp = $this->api->request->get('timestamp');
        array_push($data, $session_id, $source, $target, $action, $type, $timestamp);
        
        $this->wrapCallInDebug('createMetricByNames', $data, true);
    }

    function mapRoutes()
    {
    	$this->addRoute('get', '/metrics', 'getEditorIndex', false, $this);
    	$this->addRoute('post', '/metrics/post', 'createMetric', true, $this);
    	$this->addRoute('put', '/metrics/put', 'createMetric', true, $this);
    	$this->addRoute('patch', '/metrics/patch', 'updateMetric', true, $this);
    	$this->addRoute('delete', '/metrics/delete', 'deleteMetric', true, $this);
    }
}

class SessionSlim extends SlimShim
{
    function startSession(\Slim\Route $route) 
    {
        $key = $route->getParam('key');
		$start = $route->getParam('start');
		$end = $route->getParam('end');
		$ip = get_client_ip();

		if($end != null)
		{
        	$this->wrapCallInDebug('createSession', array($data, $start, $end, $region, $country, $state, $city, $channel, $key, $ip), true);
		}
		else
		{
        	$this->wrapCallInDebug('startNewSession', array($ip, $key), true);
		}
    }

    function getSession(\Slim\Route $route)
    {
		$ip = get_client_ip();
        $key = $route->getParam('key');
        echo "you got here with ip:".$ip." and attempted with key:".$key;
        //if()
    }

    function endSession(\Slim\Route $route)
    {
        try
        {
            $session_id = $route->getParam('session_id');
            $timestamp = $route->getParam('timestamp');
        	$key = $route->getParam('key');
			$ip = get_client_ip();

            
            $this->wrapCallInDebug('endSession', array($session_id, $timestamp), true);
        }
        catch(Exception $e)
        {
        }
    }

    function mapRoutes()
    {
	    $this->mapKeyRoutes($this);
    	$this->addRoute('post', '/session', 'getSession', true, $this);
    	$this->addRoute('post', '/session/get', 'getSession', true, $this);
    	$this->addRoute('post', '/session/end', 'endSession', true, $this);
    }
}

?>