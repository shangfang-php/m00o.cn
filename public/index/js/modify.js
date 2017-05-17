// 这里使用最原始的js语法实现，可对应换成jquery语法进行逻辑控制
var visible1=document.getElementById('psw_visibl1');//text block
var invisible1=document.getElementById('psw_invisibl1');//password block
var inputVisible1 = document.getElementById('input_visibl1');
var inputInVisible1 = document.getElementById('input_invisibl1');
//隐藏text block，显示password block
function showPsw1(){
    var val = inputInVisible1.value;//将password的值传给text
    inputVisible1.value = val;
    invisible1.style.display = "none";
    visible1.style.display = "";
}
//隐藏password，显示text
function hidePsw1(){
    var vasl=inputVisible1.value;//将text的值传给password
    inputInVisible1.value = vasl;
    invisible1.style.display = "";
    visible1.style.display = "none";
}
var visible2=document.getElementById('psw_visibl2');//text block
var invisible2=document.getElementById('psw_invisibl2');//password block
var inputVisible2 = document.getElementById('input_visibl2');
var inputInVisible2 = document.getElementById('input_invisibl2');
//隐藏text block，显示password block
function showPsw2(){
    var val2 = inputInVisible2.value;//将password的值传给text
    inputVisible2.value = val2;
    invisible2.style.display = "none";
    visible2.style.display = "";
}
//隐藏password，显示text
function hidePsw2(){
    var vals2=inputVisible2.value;//将text的值传给password
    inputInVisible2.value = vals2;
    invisible2.style.display = "";
    visible2.style.display = "none";
}
var visible3=document.getElementById('psw_visibl3');//text block
var invisible3=document.getElementById('psw_invisibl3');//password block
var inputVisible3 = document.getElementById('input_visibl3');
var inputInVisible3 = document.getElementById('input_invisibl3');
//隐藏text block，显示password block
function showPsw3(){
    var val3 = inputInVisible3.value;//将password的值传给text
    inputVisible3.value = val3;
    invisible3.style.display = "none";
    visible3.style.display = "";
}
//隐藏password，显示text
function hidePsw3(){
    var vals3=inputVisible3.value;//将text的值传给password
    inputInVisible3.value = vals3;
    invisible3.style.display = "";
    visible3.style.display = "none";
}
var Manage = {
    modify: function () {
        //判断值
        s1 = document.getElementById('psw_invisibl1').getAttribute('style');
        s2 = document.getElementById('psw_invisibl2').getAttribute('style');
        s3 = document.getElementById('psw_invisibl3').getAttribute('style');
        if(s1==null){
            password1  = $("#input_invisibl1").val();
        }else if(s1="display: none;"){
            password1  = $("#input_visibl1").val();
        }
        if(s2==null){
            password2  = $("#input_invisibl2").val();
        }else if(s2="display: none;"){
            password2  = $("#input_visibl2").val();
        }
        if(s3==null){
            password3  = $("#input_invisibl3").val();
        }else if(s3="display: none;"){
            password3  = $("#input_visibl3").val();
        }
        len2 = password2.length;
        if(password1 == ''||password1 ==null||password2 == ''||password2 ==null||password3 == ''||password3 ==null){
            $("body").showbanner({
                title : "网站提示",
                content : "密码不能为空！"
            });
        }else if(len2<6){
            $("body").showbanner({
                title : "网站提示",
                content : "密码不能小于6位数！"
            });
        }else if(password2!=password3){
            $("body").showbanner({
                title : "网站提示",
                content : "两次密码输入不相同！"
            });
        }else{
			var id = $('#hidden').val();
            $.ajax({
                url: "/index/index/ver",
                data: {password1:password1,password2:password2,id:id},
                type: 'Post',
                dataType: "json",
                cache: false,success: function (data, state) {
                    if (state == 'success' && data.code == '0') {
                        $("body").showbanner({
                            title : "网站提示",
                            content : data.msg
                        });
						setTimeout(function(){location.href = "/";},1000);
                    } else {
						$("body").showbanner({
							title : "网站提示",
							content : data.msg
						});
                    }
                }
            });
        }
    }
};
