<?php 
//获取2点之间的距离
function GetDistance($lat1, $lng1, $lat2, $lng2){
    $pi=Pi();
    $radLat1 = $lat1 * ($pi/ 180);
    $radLat2 = $lat2 * ($pi / 180);

    $a = $radLat1 - $radLat2; 
    $b = ($lng1 * ($pi / 180)) - ($lng2 * ($pi / 180)); 

    $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
    $s = $s * 6371.004;
    $s = round($s*1000);//unit is m
    return $s; 
}

function GetRange($point,$raidus){
    //计算纬度
    $degree = (24901 * 1609) / 360.0;
    $radiusLat =$raidus / $degree;
    $res['minlat'] = $point['latitude'] - $radiusLat; //得到最小纬度
    $res['maxlat'] = $point['latitude'] + $radiusLat; //得到最大纬度     
    //计算经度
    $mpdLng = $degree * cos($point['latitude'] * (Pi() / 180));
    $radiusLng = $raidus/ $mpdLng;
    $res['minlon'] = $point['longitude'] - $radiusLng;  //得到最小经度
    $res['maxlon'] = $point['longitude'] + $radiusLng;  //得到最大经度
    return $res;
}

function sortDistance(&$data,$add){
    for($a=0,$lim=count($data);$a<$lim;$a++){
        $data[$a]['distance']=GetDistance($add['latitude'],$add['longitude'],$data[$a]['latitude'],$data[$a]['longitude']);
    }
    usort($data,function($a1,$a2){
        if ($a1['distance']==$a2['distance']) return 0;
        return ($a1['distance']>$a2['distance'])?1:-1;
    });
}
?>