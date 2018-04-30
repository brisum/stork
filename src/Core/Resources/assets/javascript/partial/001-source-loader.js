function loadScript(url, async, callback){
    var script = document.createElement("script");

    if (callback) {
        if (script.readyState){  //IE
            script.onreadystatechange = function(){
                if (script.readyState == "loaded" ||
                    script.readyState == "complete"){
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //Others
            script.onload = function(){
                callback();
            };
        }
    }

    script.type = "text/javascript";
    script.async = async;
    script.src = url;
    document.getElementsByTagName("body")[0].appendChild(script);
}

function loadScripts(scripts, callback) {
    var progress = 0;
    scripts.forEach(function(script) {
        loadScript(script, false,  function () {
            if (++progress == scripts.length) {
                callback && callback();
            }
        });
    });
}

function loadStylesheet(url){
    var link  = document.createElement('link');

    link.rel  = 'stylesheet';
    link.type = 'text/css';
    link.media = 'all';
    link.href = url;

    document.getElementsByTagName('head')[0].appendChild(link);
}

function loadStylesheets(styles) {
    var progress = 0;
    styles.forEach(function(style) {
        var link  = document.createElement('link');

        link.rel  = 'stylesheet';
        link.type = 'text/css';
        link.media = 'all';
        link.href = style;

        document.getElementsByTagName('head')[0].appendChild(link);
    });
}