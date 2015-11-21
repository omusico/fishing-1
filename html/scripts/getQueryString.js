/**
 * Created by youyaoguai on 2015/10/24.
 */
var getQueryString = function ( name ) {
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)" );
    var r = window.location.search.substr( 1).match( reg );
    if ( r != null ) {
        return unescape( r[2] );
    } else {
        return null;
    }
}