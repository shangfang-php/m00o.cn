$(document).ready(function(){
	$(".basicdemo").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent aliquet et purus non mattis. Curabitur bibendum magna at augue convallis, placerat luctus tellus faucibus."
		});
	})
	$(".noico").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			content : "无图标横幅演示"
		});
	})
	$(".myico").click(function(){
		$("body").showbanner({
			title : "Chrome",
			icon : "images/chrome.png",
			content : "自定义图标横幅演示"
		});
	})
	$(".nohandle").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			handle : false,
			content : "No handle"
		});
	})
	$(".soundban").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "带有声音的横幅",
			sound : "sounds/sms-received1.mp3"
		});
	});
	$(".bottomdemo").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "底部横幅演示",
			position : "bottom"
		});
	});
	$(".noanim").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "无动画",
			show_duration : 0,
			hide_duration : 0
		});
	});
	$(".durtest1").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "200毫秒进入 持续时间1000毫秒 700毫秒退出",
			show_duration : 200,
			duration : 1000,
			hide_duration : 700
		});
	});
	$(".easingtest").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "匀速进入和匀速退出横幅",
			show_easing : "linear",
			hide_easing : "linear"
		});
	});
	$(".clickevt").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "点这里",
			onClick : function(){
				alert("横幅点击事件测试");
			}
		});
	});
	$(".hideevt").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "请等待这条横幅消失",
			onHide : function(){
				alert("横幅已消失");
			}
		});
	});
	$(".css1").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "横幅样式演示",
			addclass : "mybannerstyle1"
		});
	});
	$(".css2").click(function(){
		$("body").showbanner({
			title : "jq22.com",
			icon : "images/icon.png",
			content : "横幅样式演示2",
			addclass : "mybannerstyle2"
		});
	});
	$(".htmldemo").click(function(){
		$("body").showbanner({
			title : "还可以在横幅上使用<i>HTML</i>",
			icon : "images/html5logo.png",
			content : "HTML横幅演示<input value='文本框'><select><option>下拉选择框</option></select>\n<input type='button' value='按钮'>\n<font color='#FF0000'>颜色</font>",
			html: true
		});
	});
	$(".nohtmldemo").click(function(){
		$("body").showbanner({
			title : "还可以在横幅上使用<i>HTML</i>",
			icon : "images/html5logo.png",
			content : "HTML横幅演示<input value='文本框'><select><option>下拉选择框</option></select>\n<input type='button' value='按钮'>\n<font color='#FF0000'>颜色</font>"
		});
	});
});