/**
 * Created by youyaoguai on 2015/10/15.
 */

var incident = {
    ajaxEvent: function ( userValue, pwdValue) {
        var xhr = util.createXHR();
        var data = "user="+userValue+"&pwd="+pwdValue;
        var url = "/common/whereisfish";
        xhr.onreadystatechange = function () {
            if ( xhr.readyState == 4) {
                if ( xhr.status == 200) {
                    console.log("响应成功");
                    console.log(xhr.responseText);
                    var jsonDa = JSON.parse(xhr.responseText);
                    if ( jsonDa.status == 0 ){
                        console.log("获取数据成功");
                        incident.loadPage( jsonDa );
                    } else {
                        alert ( "请求失败" );
                    }
                } else {
                    alert( "未知错误" );
                }
            }
        }

        xhr.open("POST",url,true);
        xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded;charset=UTF-8");
        xhr.send(data);
    },
    submit:function ( button ) {
        button.addEventListener( 'click', function () {
            var userValue = document.getElementById( "user").value;
            var pwdValue = document.getElementById( 'pwd').value;
            if ( userValue.length !== 0 && pwdValue.length !== 0 ) {
                incident.ajaxEvent( userValue, pwdValue );
            } else {
                console.log("请检查你的参数");
                alert("请检查你的参数");
            }
        },false);
    },
    loadPage: function ( data ) {
        window.location = data.info;
    }
};

window.onload = init;

function init () {
    //get the button
    var button = document.getElementById( "loginBtn" );
    incident.submit( button );
}