function getBaseURL () {
    var baseURL = "";
    var baseDocument = "index.html";

    if (document.getElementsByTagName('base').length > 0) {
        baseURL = document.getElementsByTagName('base')[0].href.replace(baseDocument, "");
    } else {
        baseURL = location.protocol + "//" + location.hostname + (location.port && ":" + location.port) + "/";
    }

    return baseURL;
}

function getFolderPath()
{
    return location.pathname.substr(1,location.pathname.indexOf('/',1));
}

function getSiteRoot()
{
    var rootPath = window.location.protocol + "//" + window.location.host + "/";
    if (window.location.hostname == "localhost")
    {
        var path = window.location.pathname;
        if (path.indexOf("/") == 0)
        {
            path = path.substring(1);
        }
        path = path.split("/", 1);
        if (path != "")
        {
            rootPath = rootPath + path + "/";
        }
    }
    return rootPath;
}
trackingUrl = getBaseURL() + getFolderPath();

function getTimestamp()
{   
    var date = new Date();
    var time = date.toTimeString().substr(0,8);
    var timestamp = date.getFullYear() + "-" + date.getMonth() + "-" + date.getDate() + " " + time;

    return timestamp;
}

function makeAsyncPOST(url,data,success,error)
{
     $.ajax({
      type: "POST",
      url: url,
      data: data,
      success: success,
      error: error
    });
}

function buildAjaxButton(target, id, text, success, url, css, icon, error)
{
    css = css === undefined ? 'btn-success' : css;
    icon = icon === undefined ? '' : icon;
    error = error === undefined ? '' : error;

    button_html = '<a id="'+id+'" class="btn '+css+'"><span class="glyphicon '+icon+'"></span> '+text+'</a>';
    $(target).append(button_html);
    $(id).click(function(){
        makeAsyncPOST(url, $(this).attr('data'), success, error);
    });
}