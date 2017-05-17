// 这里使用最原始的js语法实现，可对应换成jquery语法进行逻辑控制
var visibles=document.getElementById('psw_visibles');//text block
var invisibles=document.getElementById('psw_invisibles');//password block
var inputVisibles = document.getElementById('input_visibles');
var inputInVisibles = document.getElementById('input_invisibles');
//隐藏text block，显示password block
function showPsws(){
    var val = inputInVisibles.value;//将password的值传给text
    inputVisibles.value = val;
    invisibles.style.display = "none";
    visibles.style.display = "";
}
//隐藏password，显示text
function hidePsws(){
    var val=inputVisibles.value;//将text的值传给password
    inputInVisibles.value = val;
    invisibles.style.display = "";
    visibles.style.display = "none";
}
var Manage = {
    reg: function () {

        name = $("#accs").val();
        core = $("#core").val();
        mac  = $("#mac").val();
        s = document.getElementById('psw_invisibles').getAttribute('style');
        if(s==null)
        {
            password  = $("#input_invisibles").val();

        }else if(s="display: none;")
        {
            password  = $("#input_visibles").val();
        }
        len = password.length;
        if(name == ''|| name == null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "账号不能为空！"
            });
        }else if(password == ''||password ==null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "密码不能为空！"
            });
        }else if(len<6){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "密码不能小于6位数！"
            });
        }else if(mac == ''|| mac == null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "邀请码不能为空！"
            });
        }else if(core == ''|| core == null){
            $("body").showbanner({
                title : "\nm.00o.cn",
                content : "验证码不能为空！"
            });
        }else{
            $.ajax({
                url: "/index.php/index/login/reguser",
                data: {phone:name,pass:password,mac:mac,core:core},
                type: 'Post',
                dataType: "json",
                cache: false,success: function (data, state) {
                    if (state == 'success' && data.code == '0') {
                        $("body").showbanner({
                            title : "\nm.00o.cn",
                            content : data.res,
                        });
                        setTimeout(function(){location.href = "/index.php/index/login";},1000);
                    } else {
                        $("body").showbanner({
                            title : "\nm.00o.cn",
                            content : data.res,
                        });
                    }
                }
            });
        }
    },
    core:function()
    {
        phone = $("#accs").val();
        count = 60; //间隔函数，1秒执行
        $("#SendCode").attr("disabled", "true");
        $("#SendCode").attr("style", "background:#ccc;color:#fff;");
        $("#SendCode").html( + count + "秒再获取");
        InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
        if (curCount == 0) {
            window.clearInterval(InterValObj);//停止计时器
            $("#btnSendCode").removeAttr("disabled");//启用按钮
            $("#btnSendCode").val("重新发送验证码");
            code = ""; //清除验证码。如果不清除，过时间后，输入收到的验证码依然有效
        }
        else {
            curCount--;
            $("#btnSendCode").val( + curCount + "秒再获取");
        }
        $.ajax({
            url: "/index.php/index/login/phonecore",
            data: {phone: phone},
            type: 'Post',
            dataType: "json",
            cache: false,success: function (data, state) {
                if (state == 'success' && data.code == '0') {


                    // InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                    $("body").showbanner({
                        title : "\nm.00o.cn",
                        content : data.res,
                    });
                    location.href = "";//跳转地址
                } else {
                    $("body").showbanner({
                        title : "\nm.00o.cn",
                        content : data.res,
                    });
                }
            }
        });

    }

};

var InterValObj; //timer变量，控制时间
var count = 60; //间隔函数，1秒执行
var curCount;//当前剩余秒数
var code = ""; //验证码
function sendMessage() {
    curCount = count;
    phone = $("#accs").val();
    if(phone == ''||phone==null)
    {
        $("body").showbanner({
            title : "\nm.00o.cn",
            content : "账号不能为空",
        });
    }

    else
    {
//设置button效果，开始计时
    $("#SendCode").attr("disabled", "true");
    $("#SendCode").attr("style", "background:#ccc");
    $("#SendCode").html( + curCount + "秒再获取");
    InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
//向后台发送处理数据
    $.ajax({
        url: "/index.php/index/login/phonecore",
        data: {phone: phone},
        type: 'Post',
        dataType: "json",
        cache: false,success: function (data, state) {
            if (state == 'success' && data.code == '0') {

                $("body").showbanner({
                    title : "\nm.00o.cn",
                    content : data.res,
                });
            } else {
                curCount = 0;
                $("body").showbanner({
                    title : "\nm.00o.cn",
                    content : data.res,
                });
            }
        }
    });
    }
}
//timer处理函数
function SetRemainTime() {
    if (curCount == 0) {
        window.clearInterval(InterValObj);//停止计时器
        $("#SendCode").removeAttr("disabled");//启用按钮
        $("#SendCode").removeAttr("style");//启用按钮
        $("#SendCode").html("重新获取");
    }
    else {
        curCount--;
        $("#SendCode").html( + curCount + "秒再获取");
    }
}
