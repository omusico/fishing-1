/**
 * Created by youyaoguai on 2015/11/1.
 */
var add = {
    type: function ( tag ) {
        var result ;
        var str = [];
        tag.each(function(){
            if ( $(this).is(':checked')) {
                str.push($(this).val());
            }
        });
        result = str.join(",");
        return result;
    },
    upload: function (id,infoId){
        $.getJSON('/admin/qiniuToken','',function(data){
            $('#'+id).children().first().val(data.info);
            $('#'+infoId).html('上传中，请稍候');
            var formData = new FormData($("#"+id)[0]);
            $.ajax({
                url:'http://upload.qiniu.com/',
                type:'post',
                data:formData,
                processData: false,
                contentType:false,
                success:function(e){
                    var pic='http://7xn7nj.com2.z0.glb.qiniucdn.com/'+e.key;
                    $('#'+infoId).html('上传成功,'+pic);
                    if( infoId == "upinfo" ){
                       window.pre = pic;
                    }
                    if( infoId == "upinfop" ){
                        window.pics.push(pic);
                    }
                },
                error:function(json){
                    alert(json.error);
                }
            });
        });
    },
    handleFile: function( files ){
        var d = document.getElementById('fileList');
        if( !files.length ) {
            d.innerHTML = '<p>No files here</p>';
        } else {
            var list = document.createElement('ul');
            d.appendChild(list);

            for(var i = 0; i<files.length; i++ ) {
                var li = document.createElement('li');
                list.appendChild(li);

                var img = document.createElement('img');
                img.src = window.URL.createObjectURL(files[i]);
                img.height = 60;
                img.onload = function () {
                    window.URL.createObjectURL(this.src);
                }
                li.appendChild(img);

                var info = document.createElement('span');
                info.innerHTML = files[i].name + ":" + files[i].size + "bytes";
                li.appendChild(info);
            }
        }
    }
}

$(function(){
    $('#doClick').on('click',function(){
        var el = document.getElementById('fileElem');
        if(el){
            el.click();
        }
    });



    window.pics = [];
    $('#subPre').on('click',function(){
        add.upload('upPreview','upinfo');
    });
    $('#subPic').on('click',function(){
        add.upload('upPics','upinfop');
    });

    $('#subAddPlace').on('click',function(){
        $('#prePic').val(window.pre);
        $('#pic').val(JSON.stringify(window.pics));
        $('#fishTypeVal').val( add.type($('#curFish input')) );
        $('#serviceType').val( add.type($('#curService input')) );
        $.post('data.json',$('#addPlace').serialize(),function( json ){
            if( json.status == 0){
                alert("上传成功，等待审核");
            }
        });
    });

});