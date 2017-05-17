// 这里使用最原始的js语法实现，可对应换成jquery语法进行逻辑控制
var visible=document.getElementById('psw_visible');//text block
var invisible=document.getElementById('psw_invisible');//password block
var inputVisible = document.getElementById('input_visible');
var inputInVisible = document.getElementById('input_invisible');
//隐藏text block，显示password block
function showPsw(){
    var val = inputInVisible.value;//将password的值传给text
    inputVisible.value = val;
    invisible.style.display = "none";
    visible.style.display = "";
}
//隐藏password，显示text
function hidePsw(){
    var val=inputVisible.value;//将text的值传给password
    inputInVisible.value = val;
    invisible.style.display = "";
    visible.style.display = "none";
}

//回车
$(function(){
    $(document).keydown(function(event){
        if(event.keyCode==13){
            $("#mouse").click();
        }
    });
})
var Manage = {
    login: function () {
        name = $("#acc").val();
        pass = $("#input_invisible").val();
        pas  = $("#input_visible").val();
        //判断值
        s = document.getElementById('psw_invisible').getAttribute('style');
        if(s===null)
        {
            password  = $("#input_invisible").val();

        }else if(s="display: none;")
        {
            password  = $("#input_visible").val();
        }
        
        if(name == ''|| name == null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "用户名不能为空！"
            });
        }
        else if(password == ''||password ==null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "密码不能为空！"
            });
        }
        else
        {
            $.ajax({
                url: "/index.php/index/login/login",
                data: {name: name,pass:password},
                type: 'Post',
                dataType: "json",
                cache: false,success: function (data, state) {
                    if (state == 'success' && data.code == '0') {
                        $("body").showbanner({
                            title : "\nm.00o.cn",
                            content : data.msg,
                        });
                        setTimeout(function(){location.href = "/";},1000);
                    } else {
                        $("body").showbanner({
                            title : "\nm.00o.cn",
                            content : data.msg,
                        });
                    }
                }
            });
        }


    }
}