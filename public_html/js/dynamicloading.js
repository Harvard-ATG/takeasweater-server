var myScripts  = new Array();

function loadJS( url, scriptID ) {
    var head = document.getElementsByTagName('head').item(0);
    var script = document.createElement('script');
    script.src = 'js/mobile.js';
    script.type = 'text/javascript';
    script.defer = true;
    script.id = 'mobilejs'; // This will help us in referencing the object later for removal
    head.appendChild(script);
    myScripts.push( script.id );
}

function  removeAllOldJS() {
    for ( var scriptID in myScripts ) {
        var old = document.getElementById( scriptID );
        if (old) head.removeChild(old);
    }
    myScripts  = new Array();
}


             
removeAllOldJS();

loadJS('js/lib/jquery-ui-1.8.17.custom.min.js', 'jqueryuijs');
loadJS('js/lib/jquery.sidecontent.js', 'sideconentjs');
loadJS('js/desktop.js', 'desktopjs');
