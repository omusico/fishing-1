function getData(e){
    $.post("/index.php/admin/placeItem/"+e.value,'',fillForm);
}

function fillForm( data ) {
    var fishTpye = ["鲤鱼", "鲫鱼", "草鱼", "青鱼", "石尾子", "细鳞鱼", "鲶鱼", "黄腊丁", "马口", "花白鲢", "白参", "翘壳", "其它"];
    var fishArr = data.fishType.split(",");
    var fishStr = [];
    for(var i = 0,len = fishArr.length;i<len; i++ ){
        fishStr.push(fishTpye[fishArr[i]]);
    }
    var fish = fishStr.join(",");
    $('#dename').val(data.name);
    $('#decost').val(data.cost);
    $('#decontent').val(data.content);
    $('#detel').val(data.tel);
    $('#detime').val(data.time);
    $('#fishType').text(fish);
    $('#delng').val(data.lng);
    $('#delat').val(data.lat);
    $('#debriefAddr').val(data.briefAddr);
    $('#decostType').val(data.costType);
    $('#depoolType').val(data.poolType);
    $('#deserviceType').val(data.peserviceType);
    $('#dearea').val(data.area);
    $('#dedeep').val(data.deep);
    $('#dehole').val(data.hole);
    $('#preView').html(picItem(data.preview,'name="preview"'));
    var res='';
    for(x in data.picture){
        res+=picItem(data.picture[x],'class="pic"');
        $('#pics').html(res);
    }
}
function picItem(url,mid){
    return '<div><input '+mid+' value="'+url+'" /><a href="javascript:void()"  onmouseout="hidePic()" onmouseover="showPic(this)">预览</a><a onclick="del(this)" href="javascript:void()">删除</a></div>';
}
function del(e) {
    $(e).parent().remove();
}
function addPic(url) {
    $('#pics').append(picItem(url,'class="pic"'));
}
//预览
function showPic(e) {
    var url=$(e).prev().val();
}
//隐藏预览图
function hidePic() {
    
}
//需要你来放的
function beforeSubmit(form){
    var a=[];
    $('.pic').each(function(index,e){
        a.push(e.value);
    });
    form.picture=JSON.stringify(a);
}