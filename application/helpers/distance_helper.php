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
?>