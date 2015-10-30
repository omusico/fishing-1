/**
 * Created by youyaoguai on 2015/10/18.
 */


//地图事件
var mapIncident = {
    //创建地图对象
    map: function ( lng, lnt, zoomLev ) {
        var mapPo = new AMap.Map( 'allmap', {
            center: [lng, lnt],
            zoom:zoomLev
        });
        return mapPo;
    },
    //展示地图
    drawMap: function () {

        return (function getLocation (  ) {
            AMap.service(["AMap.CitySearch"],function(){
                //实例化城市查询类
                var citysearch = new AMap.CitySearch();
                //自动获取用户ip。返回当前城市
                citysearch.getLocalCity( function ( status, result ) {
                    if ( status === 'complete' && result.info === 'OK' ) {
                        if ( result && result.city && result.bounds ) {
                            var citybounds = result.bounds;
                            mapIncident.map( 116.480983, 39.989628, 13 ).setBounds( citybounds );
                        }
                    }
                } );
            });
        })();
    },
    //添加覆盖物和信息框
    addInfo: function( lng, lat, data ){
        //创建覆盖物
        var marker = new AMap.Marker({
            map:mapIncident.map(lng,lat, 14),
            position:[lng,lat]
        });

        //信息框信息
        var title = data.name;
        var info = [];
        info.push(title);
        info.push(data.time);
        info.push(data.briefAddr);
        if ( data.state == 1 ) {
            info.push("<a href=\"###\" class=\"place-link\" id=" + data.id +" onclick=\"getPlace(this.id)\" data-toggle=\"modal\" data-target=\"#changePlace\">查看详情&修改钓点，都点我</a>");
        }
        if( data.state == 0) {
            info.push("<a href=\"###\" class=\"place-link\" id=" + data.id +" onclick=\"getCheck(this.id)\" data-toggle=\"modal\" data-target=\"#checkPlace\">查看详情审核</a>");
        }
        var infoWindow = new AMap.InfoWindow({
            content:info.join('<br />')
        });
        infoWindow.open(mapIncident.map(lng,lat,14),marker.getPosition());

    }
}

window.onload = init;
function init(){
    mapIncident.drawMap();
}