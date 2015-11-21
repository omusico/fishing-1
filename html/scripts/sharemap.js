
var $mapEvent = {
    loadMap:function( lng, lat, container) {
        var map = new AMap.Map(container,{
            resizeEnbale:true,
            center:[lng,lat],
            zoom:12,
            dragEnable: false,
        });
        return map;
    },
    //添加标记
    addIcon:function(p_lng,p_lat){
        return (function(){
            new AMap.Marker({
                map:$mapEvent.loadMap(p_lng,p_lat,"container"),
                position:[p_lng, p_lat],
                icon: new AMap.Icon({
                    size: new AMap.Size(64,64),  //图标大小
                    image: "/images/location_point_red.png",
                    imageOffset: new AMap.Pixel(0, 0)
                })
            });
        }())
    }

}

var infoEvent= {
    loadtxt:function(fish,pool,service){
        var serviceArr = ["免费", "斤塘", "天塘"];
        $('.icon-blue').text(serviceArr[service]);
        var poolArr = ["池塘", "水库", "江河", "湖", "海洋"];
        $('.icon-range').text(poolArr[pool]);

        var curFish = "";
        console.log(fish);
        var fishArr = [" 鲤鱼", "鲫鱼", "草鱼", "青鱼", "石尾子", "细鳞鱼", "鲶鱼", "黄腊丁", "马口", "花白鲢", "白参", "翘壳", "其它"];
        var _fishArr = fish.split(",");
        for(var i in _fishArr){
            curFish += fishArr[_fishArr[i]]+",";
        }
        $('#fish').text(curFish);
    }
}


