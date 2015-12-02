/**
 * Created by youyaoguai on 2015/10/25.
 */
var pending = {
    fishTpye:["鲤鱼", "鲫鱼", "草鱼", "青鱼", "石尾子", "细鳞鱼", "鲶鱼", "黄腊丁", "马口", "花白鲢", "白参", "翘壳", "其它"],
    serviceType:["夜钓","住宿","餐饮"],
    //判断所加载页面
    getPath:function(){
        var urlArr = window.location.pathname.split('/');
        var currentPage = urlArr[urlArr.length-1];
        var result;
        if( currentPage == "pending" ) {
            result = "pending";
            return result;
        }
        if( currentPage == "audited" ){
            result = "audited";
            return result;
        }
    },
    // 待审核地点加载
     initPend: function () {
         var path = pending.getPath();
         if( path == "pending" ){
             window.type = 0;
         }
         if( path == "audited"){
             window.type = 1;
         }

         window.page = 0;
         $.post("/admin/placeList",{type:window.type,page:window.page},function ( json ) {
             if( json.status == 0){
                 pending.loadList(json.data);
             }
         });

         //翻页功能
         $('#pre').on( 'click' , function () {
             window.page--;
             console.log(window.page);
             if(window.page<0){
                  window.page = 0;
             }
             $.post("/admin/placeList",{type:window.type,page:window.page},function ( json ){
                 if( json.status == 0){
                     $('.nav-list').html(" ");
                     pending.loadList(json.data);
                 }
             });
         });

         $('#next').on('click',function(){
             page++;
             console.log(page);
             $.post("/admin/placeList",{type:window.type,page:window.page},function ( json ){
                 if( json.status == 0){
                     $('.nav-list').html(" ");
                     pending.loadList(json.data);
                 }
             });
         });

     },
    loadList: function ( data ) {
        for( var i =0,len = data.length;i<len;i++){
            $('.nav-list').append(function () {
                var str = "<li>" +
                    "<a href=\"###\" id=\""+data[i].id+"\"><i class=\"icon-chevron-right\"></i>"+data[i].name+"</a>" +
                "</li>";
                return str;
            });
        }

        $(".nav-list>li").on( 'click', function ( e ) {
            if(e.target.tagName = "A" ) {
                $.post("/index.php/admin/placeItem/"+e.target.id,{id:e.target.id},function( json ){

                    if( json.status == 0 ){
                        console.log(json.data.id);
                        pending.subInfo(e.target.id);
                        if( json.data.state == 0 ){
                            pending.fillPendForm( json.data );
                        }
                        if( json.data.state == 1){
                            $('#hased').html(" ");
                             $('#noHased').html(" ");
                            $('#curService').html(" ");
                            $('#reService').html(" ");

                            pending.fillAuForm( json.data );

                        }
                    }

                });
            }
            $('#access').one('click',function( ){
                console.log(e.target.id);
                $.post('/admin/placecheck',{id:e.target.id,state:1},function( json ){
                    if( json.status == 0 ){
                        window.location = "/admin/view/pending";
                    } else {
                        alert("未知错误");
                    }
                });
            });

            $('#subReason').one('click',function(){
                var reason = $('#reason').val();
                var reasonStr = $('#info').val();
                reasonStr = reason;
                if( reasonStr.length !== 0 ){
                    $.post('/admin/placecheck',{id:e.target.id,state:2,info:reasonStr},function ( json ) {
                        if( json.status == 0 ){
                            alert("提交成功");
                            window.location = "/admin/view/pending";
                        }
                    });
                }
            });
        });
    },
    //填充待审核表格
    fillPendForm:function ( data ) {
        this.fillForm(data);
        $('#preView').append(function(){
            $('#preView').html(" ");
            var str = "<img src=\""+data.preview+"\" alt=\"预览图\" />";
            return str;
        });

        for( var j = 0,l=data.picture.length;j<l;j++){
            $('#pics').append(function(){
                $('#pics').html(" ");
                var fstr = "<img src=\""+data.picture[j]+"\" alt=\"实拍图\" />";
                return fstr;
            });
        }
    },
    //填充已审核表格
    fillAuForm:function ( data ) {
        this.fillForm(data);

        $('#preView').html(picItem(data.preview,'name="preview"'));

        var res='';
        for(x in data.picture){
            res+=picItem(data.picture[x],'class="pic"');
        }
        $('#depics').html(res);
    },
    subInfo:function( id ){
        $('#sub').on('click',function(){
            document.getElementById('sub').preventDefault;
            this.dealData();
            $.post("/index.php/place/add",$('#auditedPlace').serialize(),function( json ){
                if( json.status == 0){
                    alert("提交成功");
                    window.location = "/admin/view/audited";
                } else{
                    alert("fialed");
                }
            });
        });
    },
    fillForm:function(data){
        var fishArr = data.fishType.split(",");
        var res='';
        for(var i = 0,len = this.fishTpye.length;i<len; i++ ){
            res+=this.checkbox(this.fishTpye[i],'fishTpye',$.inArray(i.toString(),fishArr)>-1,i);
        }
        $('#curFish').html(res);
        var serviceArr = data.serviceType.split(",");
        res='';
        for(var i = 0; i<this.serviceType.length;i++) {
            res+=this.checkbox(this.serviceType[i],'serviceType',$.inArray(i.toString(),serviceArr)>-1,i);
        }
        $('#curService').html(res);

        $('#dename').val(data.name);
        $('#decost').val(data.cost);
        $('#decontent').val(data.content);
        $('#detel').val(data.tel);
        $('#detime').val(data.time);
        $('#delng').val(data.lng);
        $('#delat').val(data.lat);
        $('#debriefAddr').val(data.briefAddr);
        $('#decostType').val(data.costType);
        $('#depoolType').val(data.poolType);
        $('#dearea').val(data.area);
        $('#dedeep').val(data.deep);
        $('#dehole').val(data.hole);
    },
    dealData:function(){
        var res=[];
        $('.fishTpye').each(function(index,e){
            if (e.checked)
                res.push(index);
        });
        $('#fishTpye').val(res.join(','));
        res=[];
        $('.serviceType').each(function(index,e){
            if (e.checked)
                res.push(index);
        });
        $('#serviceType').val(res.join(','));
        res=[];
        $('.pic').each(function(index,e){
            res.push(e.value);
        });
        $('#picture').val(JSON.stringify(res));
    },
    checkbox:function(value,cla,check,index){
        check=check?'checked':'';
        return '<label for="'+cla+index+'"><input type="checkbox" id="'+cla+index+'" class="'+cla+'" '+check+' />'+value+"</label>";
    }
}

$(function(){
    pending.initPend();
});
function upload(){
    $.getJSON('/admin/qiniuToken','',function(data){
       $('#token').val(data.info);
        $('#upinfo').html('上传中，请稍候');
        var formData = new FormData($("#upPics")[0]);
        $.ajax({
            url:'http://upload.qiniu.com/',
            type:'post',
            data:formData,
            processData: false,
            contentType:false,
            success:function(e){
                var pic='http://7xn7nj.com2.z0.glb.qiniucdn.com/'+e.key;
                addPic(pic);
                $('#upinfo').html('上传成功'+pic);
            },
            error:function(json){
                alert(json.error);
            }
        });
});
}
function picItem(url,mid){
    return '<div><input '+mid+' value="'+url+'" id="picLink" /><a href="javascript:void()"  onmouseout="hidePic()" onmousemove="showPic(this)">预览</a><a onclick="del(this)" href="javascript:void()">删除</a></div>';
}
function del(e) {
    $(e).parent().remove();
}
function addPic(url) {
    $('#pics').append(picItem(url,'class="pic"'));
}
//预览
function showPic(e) {
    $('.displayImg').html(" ");
    e = event || window.event;
    var url=$('#picLink').val();
    $('.displayImg').append(function(){
        var str = "<img src=\""+url+"\" alt=\"预览\" />";
        return str;
    });
    var xOffset = 432;
    var yOffset = 452;

    $('.displayImg').css('top',(e.pageY-xOffset)+"px").css('left',(e.pageX-yOffset)+"px").fadeIn("slow");
}
//隐藏预览图
function hidePic() {
    $('.displayImg').fadeOut('fast');
}