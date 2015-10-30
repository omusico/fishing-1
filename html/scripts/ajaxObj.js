/**
 * Created by youyaoguai on 2015/10/15.
 */
var util = {
    createXHR: function(){
        if ( typeof XMLHttpRequest != "undefined" ) {
            return new XMLHttpRequest();
        } else if ( typeof ActiveXObject != "undefined" ) {
            if ( typeof arguments.callee.activeXString != "string" ) {
                var versions = [ "MSXML2.XMLHttp.6.0", "MSXML2.XMLHttp.3.0", "MSXML2.XMLHttp" ],
                    i,len;
                for ( i = 0,len = versions.length; i<len; i++) {
                    try{
                        new ActiveXObject( versions[i] );
                        arguments.callee.activeXString = versions[i];
                        break;
                    } catch ( ex ) {
                        console.log("there is a error" + ex );
                    }
                }
            }
            return new ActiveXObject( arguments.callee.activeXString );
        } else {
            throw new Error( "no XHR onject available." );
        }
    }
};