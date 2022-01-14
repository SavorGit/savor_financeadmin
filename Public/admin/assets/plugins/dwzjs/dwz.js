/*
 * jQuery MiniColors: A tiny color picker built on jQuery
 *
 * Copyright: Cory LaViska for A Beautiful Site, LLC
 *
 * Contributions and bug reports: https://github.com/claviska/jquery-minicolors
 *
 * @license: http://opensource.org/licenses/MIT
 *
 */
jQuery&&function($){function i(i,t){var o=$('<div class="minicolors" />'),n=$.minicolors.defaults;i.data("minicolors-initialized")||(t=$.extend(!0,{},n,t),o.addClass("minicolors-theme-"+t.theme).toggleClass("minicolors-with-opacity",t.opacity).toggleClass("minicolors-no-data-uris",t.dataUris!==!0),void 0!==t.position&&$.each(t.position.split(" "),function(){o.addClass("minicolors-position-"+this)}),i.addClass("minicolors-input").data("minicolors-initialized",!1).data("minicolors-settings",t).prop("size",7).wrap(o).after('<div class="minicolors-panel minicolors-slider-'+t.control+'"><div class="minicolors-slider minicolors-sprite"><div class="minicolors-picker"></div></div><div class="minicolors-opacity-slider minicolors-sprite"><div class="minicolors-picker"></div></div><div class="minicolors-grid minicolors-sprite"><div class="minicolors-grid-inner"></div><div class="minicolors-picker"><div></div></div></div></div>'),t.inline||(i.after('<span class="minicolors-swatch minicolors-sprite"><span class="minicolors-swatch-color"></span></span>'),i.next(".minicolors-swatch").on("click",function(t){t.preventDefault(),i.focus()})),i.parent().find(".minicolors-panel").on("selectstart",function(){return!1}).end(),t.inline&&i.parent().addClass("minicolors-inline"),e(i,!1),i.data("minicolors-initialized",!0))}function t(i){var t=i.parent();i.removeData("minicolors-initialized").removeData("minicolors-settings").removeProp("size").removeClass("minicolors-input"),t.before(i).remove()}function o(i){var t=i.parent(),o=t.find(".minicolors-panel"),s=i.data("minicolors-settings");!i.data("minicolors-initialized")||i.prop("disabled")||t.hasClass("minicolors-inline")||t.hasClass("minicolors-focus")||(n(),t.addClass("minicolors-focus"),o.stop(!0,!0).fadeIn(s.showSpeed,function(){s.show&&s.show.call(i.get(0))}))}function n(){$(".minicolors-focus").each(function(){var i=$(this),t=i.find(".minicolors-input"),o=i.find(".minicolors-panel"),n=t.data("minicolors-settings");o.fadeOut(n.hideSpeed,function(){n.hide&&n.hide.call(t.get(0)),i.removeClass("minicolors-focus")})})}function s(i,t,o){var n=i.parents(".minicolors").find(".minicolors-input"),s=n.data("minicolors-settings"),e=i.find("[class$=-picker]"),r=i.offset().left,c=i.offset().top,l=Math.round(t.pageX-r),h=Math.round(t.pageY-c),d=o?s.animationSpeed:0,u,g,m,p;t.originalEvent.changedTouches&&(l=t.originalEvent.changedTouches[0].pageX-r,h=t.originalEvent.changedTouches[0].pageY-c),0>l&&(l=0),0>h&&(h=0),l>i.width()&&(l=i.width()),h>i.height()&&(h=i.height()),i.parent().is(".minicolors-slider-wheel")&&e.parent().is(".minicolors-grid")&&(u=75-l,g=75-h,m=Math.sqrt(u*u+g*g),p=Math.atan2(g,u),0>p&&(p+=2*Math.PI),m>75&&(m=75,l=75-75*Math.cos(p),h=75-75*Math.sin(p)),l=Math.round(l),h=Math.round(h)),i.is(".minicolors-grid")?e.stop(!0).animate({top:h+"px",left:l+"px"},d,s.animationEasing,function(){a(n,i)}):e.stop(!0).animate({top:h+"px"},d,s.animationEasing,function(){a(n,i)})}function a(i,t){function o(i,t){var o,n;return i.length&&t?(o=i.offset().left,n=i.offset().top,{x:o-t.offset().left+i.outerWidth()/2,y:n-t.offset().top+i.outerHeight()/2}):null}var n,s,a,e,c,l,d,g=i.val(),m=i.attr("data-opacity"),f=i.parent(),v=i.data("minicolors-settings"),b=f.find(".minicolors-swatch"),y=f.find(".minicolors-grid"),M=f.find(".minicolors-slider"),w=f.find(".minicolors-opacity-slider"),x=y.find("[class$=-picker]"),C=M.find("[class$=-picker]"),k=w.find("[class$=-picker]"),S=o(x,y),z=o(C,M),D=o(k,w);if(t.is(".minicolors-grid, .minicolors-slider")){switch(v.control){case"wheel":e=y.width()/2-S.x,c=y.height()/2-S.y,l=Math.sqrt(e*e+c*c),d=Math.atan2(c,e),0>d&&(d+=2*Math.PI),l>75&&(l=75,S.x=69-75*Math.cos(d),S.y=69-75*Math.sin(d)),s=u(l/.75,0,100),n=u(180*d/Math.PI,0,360),a=u(100-Math.floor(z.y*(100/M.height())),0,100),g=p({h:n,s:s,b:a}),M.css("backgroundColor",p({h:n,s:s,b:100}));break;case"saturation":n=u(parseInt(S.x*(360/y.width()),10),0,360),s=u(100-Math.floor(z.y*(100/M.height())),0,100),a=u(100-Math.floor(S.y*(100/y.height())),0,100),g=p({h:n,s:s,b:a}),M.css("backgroundColor",p({h:n,s:100,b:a})),f.find(".minicolors-grid-inner").css("opacity",s/100);break;case"brightness":n=u(parseInt(S.x*(360/y.width()),10),0,360),s=u(100-Math.floor(S.y*(100/y.height())),0,100),a=u(100-Math.floor(z.y*(100/M.height())),0,100),g=p({h:n,s:s,b:a}),M.css("backgroundColor",p({h:n,s:s,b:100})),f.find(".minicolors-grid-inner").css("opacity",1-a/100);break;default:n=u(360-parseInt(z.y*(360/M.height()),10),0,360),s=u(Math.floor(S.x*(100/y.width())),0,100),a=u(100-Math.floor(S.y*(100/y.height())),0,100),g=p({h:n,s:s,b:a}),y.css("backgroundColor",p({h:n,s:100,b:100}))}i.val(h(g,v.letterCase))}t.is(".minicolors-opacity-slider")&&(m=v.opacity?parseFloat(1-D.y/w.height()).toFixed(2):1,v.opacity&&i.attr("data-opacity",m)),b.find("SPAN").css({backgroundColor:g,opacity:m}),r(i,g,m)}function e(i,t){var o,n,s,a,e,c,l,g=i.parent(),m=i.data("minicolors-settings"),v=g.find(".minicolors-swatch"),b=g.find(".minicolors-grid"),y=g.find(".minicolors-slider"),M=g.find(".minicolors-opacity-slider"),w=b.find("[class$=-picker]"),x=y.find("[class$=-picker]"),C=M.find("[class$=-picker]");switch(o=h(d(i.val(),!0),m.letterCase),o||(o=h(d(m.defaultValue,!0),m.letterCase)),n=f(o),t||i.val(o),m.opacity&&(s=""===i.attr("data-opacity")?1:u(parseFloat(i.attr("data-opacity")).toFixed(2),0,1),isNaN(s)&&(s=1),i.attr("data-opacity",s),v.find("SPAN").css("opacity",s),e=u(M.height()-M.height()*s,0,M.height()),C.css("top",e+"px")),v.find("SPAN").css("backgroundColor",o),m.control){case"wheel":c=u(Math.ceil(.75*n.s),0,b.height()/2),l=n.h*Math.PI/180,a=u(75-Math.cos(l)*c,0,b.width()),e=u(75-Math.sin(l)*c,0,b.height()),w.css({top:e+"px",left:a+"px"}),e=150-n.b/(100/b.height()),""===o&&(e=0),x.css("top",e+"px"),y.css("backgroundColor",p({h:n.h,s:n.s,b:100}));break;case"saturation":a=u(5*n.h/12,0,150),e=u(b.height()-Math.ceil(n.b/(100/b.height())),0,b.height()),w.css({top:e+"px",left:a+"px"}),e=u(y.height()-n.s*(y.height()/100),0,y.height()),x.css("top",e+"px"),y.css("backgroundColor",p({h:n.h,s:100,b:n.b})),g.find(".minicolors-grid-inner").css("opacity",n.s/100);break;case"brightness":a=u(5*n.h/12,0,150),e=u(b.height()-Math.ceil(n.s/(100/b.height())),0,b.height()),w.css({top:e+"px",left:a+"px"}),e=u(y.height()-n.b*(y.height()/100),0,y.height()),x.css("top",e+"px"),y.css("backgroundColor",p({h:n.h,s:n.s,b:100})),g.find(".minicolors-grid-inner").css("opacity",1-n.b/100);break;default:a=u(Math.ceil(n.s/(100/b.width())),0,b.width()),e=u(b.height()-Math.ceil(n.b/(100/b.height())),0,b.height()),w.css({top:e+"px",left:a+"px"}),e=u(y.height()-n.h/(360/y.height()),0,y.height()),x.css("top",e+"px"),b.css("backgroundColor",p({h:n.h,s:100,b:100}))}i.data("minicolors-initialized")&&r(i,o,s)}function r(i,t,o){var n=i.data("minicolors-settings"),s=i.data("minicolors-lastChange");s&&s.hex===t&&s.opacity===o||(i.data("minicolors-lastChange",{hex:t,opacity:o}),n.change&&(n.changeDelay?(clearTimeout(i.data("minicolors-changeTimeout")),i.data("minicolors-changeTimeout",setTimeout(function(){n.change.call(i.get(0),t,o)},n.changeDelay))):n.change.call(i.get(0),t,o)),i.trigger("change").trigger("input"))}function c(i){var t=d($(i).val(),!0),o=b(t),n=$(i).attr("data-opacity");return o?(void 0!==n&&$.extend(o,{a:parseFloat(n)}),o):null}function l(i,t){var o=d($(i).val(),!0),n=b(o),s=$(i).attr("data-opacity");return n?(void 0===s&&(s=1),t?"rgba("+n.r+", "+n.g+", "+n.b+", "+parseFloat(s)+")":"rgb("+n.r+", "+n.g+", "+n.b+")"):null}function h(i,t){return"uppercase"===t?i.toUpperCase():i.toLowerCase()}function d(i,t){return i=i.replace(/[^A-F0-9]/gi,""),3!==i.length&&6!==i.length?"":(3===i.length&&t&&(i=i[0]+i[0]+i[1]+i[1]+i[2]+i[2]),"#"+i)}function u(i,t,o){return t>i&&(i=t),i>o&&(i=o),i}function g(i){var t={},o=Math.round(i.h),n=Math.round(255*i.s/100),s=Math.round(255*i.b/100);if(0===n)t.r=t.g=t.b=s;else{var a=s,e=(255-n)*s/255,r=(a-e)*(o%60)/60;360===o&&(o=0),60>o?(t.r=a,t.b=e,t.g=e+r):120>o?(t.g=a,t.b=e,t.r=a-r):180>o?(t.g=a,t.r=e,t.b=e+r):240>o?(t.b=a,t.r=e,t.g=a-r):300>o?(t.b=a,t.g=e,t.r=e+r):360>o?(t.r=a,t.g=e,t.b=a-r):(t.r=0,t.g=0,t.b=0)}return{r:Math.round(t.r),g:Math.round(t.g),b:Math.round(t.b)}}function m(i){var t=[i.r.toString(16),i.g.toString(16),i.b.toString(16)];return $.each(t,function(i,o){1===o.length&&(t[i]="0"+o)}),"#"+t.join("")}function p(i){return m(g(i))}function f(i){var t=v(b(i));return 0===t.s&&(t.h=360),t}function v(i){var t={h:0,s:0,b:0},o=Math.min(i.r,i.g,i.b),n=Math.max(i.r,i.g,i.b),s=n-o;return t.b=n,t.s=0!==n?255*s/n:0,t.h=0!==t.s?i.r===n?(i.g-i.b)/s:i.g===n?2+(i.b-i.r)/s:4+(i.r-i.g)/s:-1,t.h*=60,t.h<0&&(t.h+=360),t.s*=100/255,t.b*=100/255,t}function b(i){return i=parseInt(i.indexOf("#")>-1?i.substring(1):i,16),{r:i>>16,g:(65280&i)>>8,b:255&i}}$.minicolors={defaults:{animationSpeed:50,animationEasing:"swing",change:null,changeDelay:0,control:"hue",dataUris:!0,defaultValue:"",hide:null,hideSpeed:100,inline:!1,letterCase:"lowercase",opacity:!1,position:"bottom left",show:null,showSpeed:100,theme:"default"}},$.extend($.fn,{minicolors:function(s,a){switch(s){case"destroy":return $(this).each(function(){t($(this))}),$(this);case"hide":return n(),$(this);case"opacity":return void 0===a?$(this).attr("data-opacity"):($(this).each(function(){e($(this).attr("data-opacity",a))}),$(this));case"rgbObject":return c($(this),"rgbaObject"===s);case"rgbString":case"rgbaString":return l($(this),"rgbaString"===s);case"settings":return void 0===a?$(this).data("minicolors-settings"):($(this).each(function(){var i=$(this).data("minicolors-settings")||{};t($(this)),$(this).minicolors($.extend(!0,i,a))}),$(this));case"show":return o($(this).eq(0)),$(this);case"value":return void 0===a?$(this).val():($(this).each(function(){e($(this).val(a))}),$(this));default:return"create"!==s&&(a=s),$(this).each(function(){i($(this),a)}),$(this)}}}),$(document).on("mousedown.minicolors touchstart.minicolors",function(i){$(i.target).parents().add(i.target).hasClass("minicolors")||n()}).on("mousedown.minicolors touchstart.minicolors",".minicolors-grid, .minicolors-slider, .minicolors-opacity-slider",function(i){var t=$(this);i.preventDefault(),$(document).data("minicolors-target",t),s(t,i,!0)}).on("mousemove.minicolors touchmove.minicolors",function(i){var t=$(document).data("minicolors-target");t&&s(t,i)}).on("mouseup.minicolors touchend.minicolors",function(){$(this).removeData("minicolors-target")}).on("mousedown.minicolors touchstart.minicolors",".minicolors-swatch",function(i){var t=$(this).parent().find(".minicolors-input");i.preventDefault(),o(t)}).on("focus.minicolors",".minicolors-input",function(){var i=$(this);i.data("minicolors-initialized")&&o(i)}).on("blur.minicolors",".minicolors-input",function(){var i=$(this),t=i.data("minicolors-settings");i.data("minicolors-initialized")&&(i.val(d(i.val(),!0)),""===i.val()&&i.val(d(t.defaultValue,!0)),i.val(h(i.val(),t.letterCase)))}).on("keydown.minicolors",".minicolors-input",function(i){var t=$(this);if(t.data("minicolors-initialized"))switch(i.keyCode){case 9:n();break;case 13:case 27:n(),t.blur()}}).on("keyup.minicolors",".minicolors-input",function(){var i=$(this);i.data("minicolors-initialized")&&e(i,!0)}).on("paste.minicolors",".minicolors-input",function(){var i=$(this);i.data("minicolors-initialized")&&setTimeout(function(){e(i,!0)},1)})}(jQuery);
$.fn.scrollEnd = function(callback, timeout) {          
  $(this).scroll(function(){
    var $this = $(this);
    if ($this.data('scrollTimeout')) {
      clearTimeout($this.data('scrollTimeout'));
    }
    $this.data('scrollTimeout', setTimeout(callback,timeout));
  });
};
/**
 * @author ZhangHuihua@msn.com
 * 
 */
function modalWidth(){
	$(".modal-table",document).css("height",$(window).height())
	if($(window).width() < 768){
		$(".modal-table",document).width($(window).width())
	}else{
		$(".modal-table",document).width("100%");
	}
}
;(function($){
	$.fn.datetimepicker.dates['zh-CN'] = {
				days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
			daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
			daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
			months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
			monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
			today: "今日",
		suffix: [],
		meridiem: []
	};
}(jQuery));
var DWZ = {
	regPlugins: [], // [function($parent){} ...] 
	// sbar: show sidebar
	keyCode: {
		ENTER: 13, ESC: 27, END: 35, HOME: 36,
		SHIFT: 16, TAB: 9,
		LEFT: 37, RIGHT: 39, UP: 38, DOWN: 40,
		DELETE: 46, BACKSPACE:8
	},
	eventType: {
		pageClear:"pageClear",  // 用于重新ajaxLoad、关闭nabTab, 关闭dialog时，去除xheditor等需要特殊处理的资源
		resizeGrid:"resizeGrid" // 用于窗口或dialog大小调整
	},
	isOverAxis: function(x, reference, size) {
		//Determines when x coordinate is over "b" element axis
		return (x > reference) && (x < (reference + size));
	},
	isOver: function(y, x, top, left, height, width) {
		//Determines when x, y coordinates is over "b" element
		return this.isOverAxis(y, top, height) && this.isOverAxis(x, left, width);
	},
	
	pageInfo: {pageNum:"pageNum", numPerPage:"numPerPage", orderField:"orderField", orderDirection:"orderDirection"},
	statusCode: {ok:200, error:300, timeout:301},
	keys: {statusCode:"statusCode", message:"message"},
	ui:{
		sbar:true,
		hideMode:'display' //navTab组件切换的隐藏方式，支持的值有’display’，’offsets’负数偏移位置的值，默认值为’display’
	},
	frag:{}, //page fragment
	_msg:{}, //alert message
	_set:{
		loginUrl:"", //session timeout
		loginTitle:"", //if loginTitle open a login dialog
		debug:false
	},
	msg:function(key, args){
		var _format = function(str,args) {
			args = args || [];
			var result = str || "";
			for (var i = 0; i < args.length; i++){
				result = result.replace(new RegExp("\\{" + i + "\\}", "g"), args[i]);
			}
			return result;
		}
		return _format(this._msg[key], args);
	},
	debug:function(msg){
		if (this._set.debug) {
			if (typeof(console) != "undefined") console.log(msg);
			else alert(msg);
		}
	},
	loadLogin:function(){
		if ($.pdialog && DWZ._set.loginTitle) {
			$.pdialog.open(DWZ._set.loginUrl, "login", DWZ._set.loginTitle, {mask:true,width:520,height:260});
		} else {
			window.location = DWZ._set.loginUrl;
		}
	},
	
	/*
	 * json to string
	 */
	obj2str:function(o) {
		var r = [];
		if(typeof o =="string") return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
		if(typeof o == "object"){
			if(!o.sort){
				for(var i in o)
					r.push(i+":"+DWZ.obj2str(o[i]));
				if(!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)){
					r.push("toString:"+o.toString.toString());
				}
				r="{"+r.join()+"}"
			}else{
				for(var i =0;i<o.length;i++) {
					r.push(DWZ.obj2str(o[i]));
				}
				r="["+r.join()+"]"
			}
			return r;
		}
		return o.toString();
	},
	jsonEval:function(data) {
		try{
			if ($.type(data) == 'string')
				return eval('(' + data + ')');
			else return data;
		} catch (e){
			return {};
		}
	},
	ajaxError:function(xhr, ajaxOptions, thrownError){
		if (alertMsg) {
			alertMsg.error("<div>Http status: " + xhr.status + " " + xhr.statusText + "</div>" 
				+ "<div>ajaxOptions: "+ajaxOptions + "</div>"
				+ "<div>thrownError: "+thrownError + "</div>"
				+ "<div>"+xhr.responseText+"</div>");
		} else {
			alert("Http status: " + xhr.status + " " + xhr.statusText + "\najaxOptions: " + ajaxOptions + "\nthrownError:"+thrownError + "\n" +xhr.responseText);
		}
	},
	ajaxDone:function(json){
		var cb = {type:""};

		if(json.callback){
			cb = eval("("+json.callback+")");
		}
		
		if(json[DWZ.keys.statusCode] == DWZ.statusCode.error) {
			if(json[DWZ.keys.message] && alertMsg) alertMsg.error(json[DWZ.keys.message]);
		} else if (json[DWZ.keys.statusCode] == DWZ.statusCode.timeout) {
			if(alertMsg) alertMsg.error(json[DWZ.keys.message] || DWZ.msg("sessionTimout"), {okCall:DWZ.loadLogin});
			else DWZ.loadLogin();
		} else if (json[DWZ.keys.statusCode] == DWZ.statusCode.ok && cb.type != "view"){
			if(json[DWZ.keys.message] && alertMsg) alertMsg.correct(json[DWZ.keys.message]);
		};
	},

	init:function(options){
		var op = $.extend({
				loginUrl:"login.html", loginTitle:null, callback:null, debug:false, 
				statusCode:{}, keys:{}
			}, options);
		this._set.loginUrl = op.loginUrl;
		this._set.loginTitle = op.loginTitle;
		this._set.debug = op.debug;
		$.extend(DWZ.statusCode, op.statusCode);
		$.extend(DWZ.keys, op.keys);
		$.extend(DWZ.pageInfo, op.pageInfo);
		$.extend(DWZ.ui, op.ui);
		
		xml = '<div>'+
			'<main id="dialogFrag">'+
				'<div class="dialog modal-table">'+
					'<div class="modal-cell">'+
						'<div class="modal-dialog modal-lg">'+
							'<div class="modal-content">'+
								'<div class="dialogHeader modal-header">'+
									'<a class="close-m"></a>'+
									'<a class="maximize fa fa-expand hidden-xs"></a>'+
									'<a class="restore fa fa-compress"></a>'+
									'<h1 class="fa-th">弹出窗口</h1>'+
								'</div>'+
								'<div class="dialogContent layoutBox unitBox">'+
								'</div>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</main>'+
			'<main id="dialogProxy">'+
				'<div id="dialogProxy" class="dialog dialogProxy">'+
					'<div class="dialogHeader">'+
						'<div class="dialogHeader_r">'+
							'<div class="dialogHeader_c">'+
								'<h1></h1>'+
							'</div>'+
						'</div>'+
					'</div>'+
					'<div class="dialogContent"></div>'+
					'<div class="dialogFooter">'+
						'<div class="dialogFooter_r">'+
							'<div class="dialogFooter_c">'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</main>'+
			'<main id="dwzFrag">'+
				'<div id="alertBackground" class="alertBackground"></div>'+
				'<div id="background" class="background"></div>'+
				'<div id="progressBar" class="progressBar">数据加载中...</div>'+
			'</main>'+
			'<main id="pagination">'+
				'<ul class="pagination">'+
					'<li class="j-first">'+
						'<a class="first" href="javascript:;"><i class="fa fa-angle-double-left"></i></a>'+
					'</li>'+
					'<li class="j-prev">'+
						'<a class="previous" href="javascript:;"><i class="fa fa-angle-left"></i></a>'+
					'</li>'+
					'#pageNumFrag#'+
					'<li class="j-select">'+
						'<select class="select">'+
							'#pageSelect#'+
						'</select>'+
					'</li>'+
					'<li class="j-next">'+
						'<a class="next" href="javascript:;"><i class="fa fa-angle-right"></i></a>'+
					'</li>'+
					'<li class="j-last">'+
						'<a class="last" href="javascript:;"><i class="fa fa-angle-double-right"></i></a>'+
					'</li>'+
				'</ul>'+
			'</main>'+
			'<main id="alertBoxFrag">'+
				'<div id="alertMsgBox" class="alert">'+
					'<div class="alertContent #type#">'+
						'<div class="alertInner">'+
							'<h1>#title#</h1>'+
							'<div class="msg">#message#</div>'+
						'</div>'+
						'<div class="alertBot">#butFragment#</div>'+
					'</div>'+
				'</div>'+
			'</main>'+
			'<main id="alertButFrag">'+
				'<a class="btn" rel="#callback#" onclick="alertMsg.close()" href="javascript:">#butMsg#</a>'+
			'</main>'+
			'<main id="navTabCM">'+
				'<ul id="navTabCM">'+
					'<li rel="reload">刷新标签页</li>'+
					'<li rel="closeCurrent">关闭标签页</li>'+
					'<li rel="closeOther">关闭其它标签页</li>'+
					'<li rel="closeAll">关闭全部标签页</li>'+
				'</ul>'+
			'</main>'+
			'<main id="dialogCM">'+
				'<ul id="dialogCM">'+
					'<li rel="closeCurrent">关闭弹出窗口</li>'+
					'<li rel="closeOther">关闭其它弹出窗口</li>'+
					'<li rel="closeAll">关闭全部弹出窗口</li>'+
				'</ul>'+
			'</main>'+
			'<main id="externalFrag">'+
				'<iframe src="{url}" style="width:100%;height:{height};" frameborder="no" border="0" marginwidth="0" marginheight="0"></iframe>'+
			'</main>'+
			'<section id="statusCode_503">服务器当前负载过大或者正在维护!</section>'+
			'<section id="validateFormError">提交数据不完整，{0}个字段有错误，请改正后再提交!</section>'+
			'<section id="sessionTimout">会话超时，请重新登录!</section>'+
			'<section id="alertSelectMsg">请选择信息!</section>'+
			'<section id="forwardConfirmMsg">继续下一步!</section>'+
			'<section id="dwzTitle">DWZ富客户端框架</section>'+
			'<section id="mainTabTitle">我的主页</section>'+
		'</div>';					
		$(xml).find("main").each(function(){
			var pageId = $(this).attr("id");
			if (pageId) DWZ.frag[pageId] = $(this).html();
		});
		$(xml).find("section").each(function(){
			var id = $(this).attr("id");
			if (id) DWZ._msg[id] = $(this).html();
		});
		if (jQuery.isFunction(op.callback)) op.callback();
		var _doc = $(document);
		if (!_doc.isBind(DWZ.eventType.pageClear)) {
			_doc.bind(DWZ.eventType.pageClear, function(event){
				var box = event.target;
				if ($.fn.xheditor) {
					$("textarea.editor", box).xheditor(false);
				}
			});
		}
	}
};


(function($){
	// DWZ set regional
	$.setRegional = function(key, value){
		if (!$.regional) $.regional = {};
		$.regional[key] = value;
	};
	
	$.fn.extend({
		/**
		 * @param {Object} op: {type:GET/POST, url:ajax请求地址, data:ajax请求参数列表, callback:回调函数 }
		 */
		ajaxUrl: function(op){
			var $this = $(this);

			$this.trigger(DWZ.eventType.pageClear);
			
			$.ajax({
				type: op.type || 'GET',
				url: op.url,
				data: op.data,
				cache: false,
				success: function(response){
					var json = DWZ.jsonEval(response);
					
					if (json[DWZ.keys.statusCode]==DWZ.statusCode.error){
						if (json[DWZ.keys.message]) alertMsg.error(json[DWZ.keys.message]);
					} else {
						$this.html(response).initUI();
						if ($.isFunction(op.callback)) op.callback(response);
					}
					
					if (json[DWZ.keys.statusCode]==DWZ.statusCode.timeout){
						if ($.pdialog) $.pdialog.checkTimeout();
						if (navTab) navTab.checkTimeout();
	
						alertMsg.error(json[DWZ.keys.message] || DWZ.msg("sessionTimout"), {okCall:function(){
							DWZ.loadLogin();
						}});
					} 
					
				},
				error: DWZ.ajaxError,
				statusCode: {
					503: function(xhr, ajaxOptions, thrownError) {
						alert(DWZ.msg("statusCode_503") || thrownError);
					}
				}
			});
		},
		loadUrl: function(url,data,callback){
			$(this).ajaxUrl({url:url, data:data, callback:callback});
		},
		initUI: function(){
			return this.each(function(){
				if($.isFunction(initUI)) initUI(this);
			});
		},
		hoverClass: function(className, speed){
			var _className = className || "hover";
			return this.each(function(){
				var $this = $(this), mouseOutTimer;
				$this.hover(function(){
					if (mouseOutTimer) clearTimeout(mouseOutTimer);
					//$this.addClass(_className);
				},function(){
					//mouseOutTimer = setTimeout(function(){$this.removeClass(_className);}, speed||10);
				});
			})
		},
		focusClass: function(className){
			var _className = className || "textInputFocus";
			return this.each(function(){
				$(this).focus(function(){
					$(this).addClass(_className);
				}).blur(function(){
					$(this).removeClass(_className);
				});
			});
		},
		inputAlert: function(){
			return this.each(function(){
				
				var $this = $(this);
				
				function getAltBox(){
					return $this.parent().find("label.alt");
				}
				function altBoxCss(opacity){
					var position = $this.position();
					return {
						width:$this.width(),
						top:position.top+'px',
						left:position.left +'px',
						opacity:opacity || 1
					};
				}
				if (getAltBox().size() < 1) {
					if (!$this.attr("id")) $this.attr("id", $this.attr("name") + "_" +Math.round(Math.random()*10000));
					var $label = $('<label class="alt" for="'+$this.attr("id")+'">'+$this.attr("alt")+'</label>').appendTo($this.parent());
					
					$label.css(altBoxCss(1));
					if ($this.val()) $label.hide();
				}
				
				$this.focus(function(){
					getAltBox().css(altBoxCss(0.3));
				}).blur(function(){
					if (!$(this).val()) getAltBox().show().css("opacity",1);
				}).keydown(function(){
					getAltBox().hide();
				});
			});
		},
		isTag:function(tn) {
			if(!tn) return false;
			return $(this)[0].tagName.toLowerCase() == tn?true:false;
		},
		/**
		 * 判断当前元素是否已经绑定某个事件
		 * @param {Object} type
		 */
		isBind:function(type) {
			var _events = $(this).data("events");
			return _events && type && _events[type];
		},
		/**
		 * 输出firebug日志
		 * @param {Object} msg
		 */
		log:function(msg){
			return this.each(function(){
				if (console) console.log("%s: %o", msg, this);
			});
		}
	});
	
	/**
	 * 扩展String方法
	 */
	$.extend(String.prototype, {
		isPositiveInteger:function(){
			return (new RegExp(/^[1-9]\d*$/).test(this));
		},
		isInteger:function(){
			return (new RegExp(/^\d+$/).test(this));
		},
		isNumber: function(value, element) {
			return (new RegExp(/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/).test(this));
		},
		trim:function(){
			return this.replace(/(^\s*)|(\s*$)|\r|\n/g, "");
		},
		startsWith:function (pattern){
			return this.indexOf(pattern) === 0;
		},
		endsWith:function(pattern) {
			var d = this.length - pattern.length;
			return d >= 0 && this.lastIndexOf(pattern) === d;
		},
		replaceSuffix:function(index){
			return this.replace(/\[[0-9]+\]/,'['+index+']').replace('#index#',index);
		},
		trans:function(){
			return this.replace(/&lt;/g, '<').replace(/&gt;/g,'>').replace(/&quot;/g, '"');
		},
		encodeTXT: function(){
			return (this).replaceAll('&', '&amp;').replaceAll("<","&lt;").replaceAll(">", "&gt;").replaceAll(" ", "&nbsp;");
		},
		replaceAll:function(os, ns){
			return this.replace(new RegExp(os,"gm"),ns);
		},
		replaceTm:function($data){
			if (!$data) return this;
			return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
				return $data[$1.replace(/[{}]+/g, "")];
			});
		},
		replaceTmById:function(_box){
			var $parent = _box || $(document);
			return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
				var $input = $parent.find("#"+$1.replace(/[{}]+/g, ""));
				return $input.val() ? $input.val() : $1;
			});
		},
		isFinishedTm:function(){
			return !(new RegExp("{[A-Za-z_]+[A-Za-z0-9_]*}").test(this)); 
		},
		skipChar:function(ch) {
			if (!this || this.length===0) {return '';}
			if (this.charAt(0)===ch) {return this.substring(1).skipChar(ch);}
			return this;
		},
		isValidPwd:function() {
			return (new RegExp(/^([_]|[a-zA-Z0-9]){6,32}$/).test(this)); 
		},
		isValidMail:function(){
			return(new RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(this.trim()));
		},
		isSpaces:function() {
			for(var i=0; i<this.length; i+=1) {
				var ch = this.charAt(i);
				if (ch!=' '&& ch!="\n" && ch!="\t" && ch!="\r") {return false;}
			}
			return true;
		},
		isPhone:function() {
			return (new RegExp(/(^([0-9]{3,4}[-])?\d{3,8}(-\d{1,6})?$)|(^\([0-9]{3,4}\)\d{3,8}(\(\d{1,6}\))?$)|(^\d{3,8}$)/).test(this));
		},
		isUrl:function(){
			return (new RegExp(/^[a-zA-z]+:\/\/([a-zA-Z0-9\-\.]+)([-\w .\/?%&=:]*)$/).test(this));
		},
		isExternalUrl:function(){
			return this.isUrl() && this.indexOf("://"+document.domain) == -1;
		}
	});
})(jQuery);

/** 
 * You can use this map like this:
 * var myMap = new Map();
 * myMap.put("key","value");
 * var key = myMap.get("key");
 * myMap.remove("key");
 */
function Map(){

	this.elements = new Array();
	
	this.size = function(){
		return this.elements.length;
	}
	
	this.isEmpty = function(){
		return (this.elements.length < 1);
	}
	
	this.clear = function(){
		this.elements = new Array();
	}
	
	this.put = function(_key, _value){
		this.remove(_key);
		this.elements.push({key: _key, value: _value});
	}
	
	this.remove = function(_key){
		try {
			for (i = 0; i < this.elements.length; i++) {
				if (this.elements[i].key == _key) {
					this.elements.splice(i, 1);
					return true;
				}
			}
		} catch (e) {
			return false;
		}
		return false;
	}
	
	this.get = function(_key){
		try {
			for (i = 0; i < this.elements.length; i++) {
				if (this.elements[i].key == _key) { return this.elements[i].value; }
			}
		} catch (e) {
			return null;
		}
	}
	
	this.element = function(_index){
		if (_index < 0 || _index >= this.elements.length) { return null; }
		return this.elements[_index];
	}
	
	this.containsKey = function(_key){
		try {
			for (i = 0; i < this.elements.length; i++) {
				if (this.elements[i].key == _key) {
					return true;
				}
			}
		} catch (e) {
			return false;
		}
		return false;
	}
	
	this.values = function(){
		var arr = new Array();
		for (i = 0; i < this.elements.length; i++) {
			arr.push(this.elements[i].value);
		}
		return arr;
	}
	
	this.keys = function(){
		var arr = new Array();
		for (i = 0; i < this.elements.length; i++) {
			arr.push(this.elements[i].key);
		}
		return arr;
	}
}

/**
 * @requires jquery.validate.js
 * @author ZhangHuihua@msn.com
 */
(function($){
	if ($.validator) {
		$.validator.addMethod("alphanumeric", function(value, element) {
			return this.optional(element) || /^\w+$/i.test(value);
		}, "Letters, numbers or underscores only please");
		$.validator.addMethod("bdpoint", function(value, element) {
			return this.optional(element) || /^[0-9]{1,3}[.][0-9]{1,6}[,][0-9]{1,3}[.][0-9]{1,6}?$/i.test(value);
		}, "坐标格式不对");
		$.validator.addMethod("lettersonly", function(value, element) {
			return this.optional(element) || /^[a-z]+$/i.test(value);
		}, "Letters only please"); 
		
		$.validator.addMethod("QQ", function(value, element) {
			return this.optional(element) || /^[1-9]\d{4,11}$/i.test(value);
		}, "QQ号格式不对"); 

		$.validator.addMethod("phone", function(value, element) {
			return this.optional(element) || /^[0-9 \(\)]{7,30}$/.test(value);
		}, "Please specify a valid phone number");
		
		$.validator.addMethod("date", function(value, element) {
			return this.optional(element) || /[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]/.test(value) || /[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/.test(value);
		}, "Please enter a valid date.");

		$.validator.addMethod("tel", function(value, element) {
			value = value.replace("-", "").replace(" ", "");
			return this.optional(element) || /^0?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/.test(value) || /^([0-9]{3,4})?-?[0-9]{7,8}$/.test(value) ||  /^(400)[0-9]{7}$/.test(value);
		}, "电话格式不对");
		
		/*自定义js函数验证
		 * <input type="text" name="xxx" customvalid="xxxFn(element)" title="xxx" />
		 */
		$.validator.addMethod("customvalid", function(value, element, params) {
			try{
				return eval('(' + params + ')');
			}catch(e){
				return false;
			}
		}, "Please fix this field.");
		
		$.validator.addClassRules({
			date: {date: true},
			alphanumeric: { alphanumeric: true },
			bdpoint: { bdpoint: true },
			lettersonly: { lettersonly: true },
			phone: { phone: true },
			tel: { tel: true },
		});
		$.validator.setDefaults({errorElement:"span"});
		$.validator.autoCreateRanges = true;
		
	}

})(jQuery);

(function($){
	$.fn.jDrag = function(options){
		if (typeof options == 'string') {
			if (options == 'destroy') 
				return this.each(function(){
					$(this).unbind('mousedown', $.rwdrag.start);
					$.data(this, 'pp-rwdrag', null);
				});
		}
		return this.each(function(){
			var el = $(this);
			$.data($.rwdrag, 'pp-rwdrag', {
				options: $.extend({
					el: el,
					obj: el
				}, options)
			});
			if (options.event) 
				$.rwdrag.start(options.event);
			else {
				var select = options.selector;
				$(select, obj).bind('mousedown', $.rwdrag.start);
			}
		});
	};
	$.rwdrag = {
		start: function(e){
			document.onselectstart=function(e){return false};//禁止选择

			var data = $.data(this, 'pp-rwdrag');
			var el = data.options.el[0];
			$.data(el, 'pp-rwdrag', {
				options: data.options
			});
			if (!$.rwdrag.current) {
				$.rwdrag.current = {
					el: el,
					oleft: parseInt(el.style.left) || 0,
					otop: parseInt(el.style.top) || 0,
					ox: e.pageX || e.screenX,
					oy: e.pageY || e.screenY
				};
				$(document).bind("mouseup", $.rwdrag.stop).bind("mousemove", $.rwdrag.drag);
			}
		},
		drag: function(e){
			if (!e)  var e = window.event;
			var current = $.rwdrag.current;
			var data = $.data(current.el, 'pp-rwdrag');
			var left = (current.oleft + (e.pageX || e.clientX) - current.ox);
			//console.log(current.oleft+"-"+current.ox);
			var top = (current.otop + (e.pageY || e.clientY) - current.oy);
			if (top < 1) top = 0;
			if (data.options.move == 'horizontal') {
				if ((data.options.minW && left >= $(data.options.obj).css("left") + data.options.minW) && (data.options.maxW && left <= $(data.options.obj).css("left") + data.options.maxW)) 
					current.el.style.left = left + 'px';
				else if (data.options.scop) {
					if (data.options.relObj) {
						if ((left - parseInt(data.options.relObj.style.left)) > data.options.cellMinW) {
							current.el.style.left = left + 'px';
						}
					} else 
						current.el.style.left = left + 'px';
				}
			} else if (data.options.move == 'vertical') {
					current.el.style.top = top + 'px';
			} else {
				var selector = data.options.selector ? $(data.options.selector, data.options.obj) : $(data.options.obj);
				if (left >= (selector.outerWidth() - $(window).width()) / 2  && (left < ($(window).width() - selector.outerWidth()) / 2)) {
					current.el.style.left = left + 'px';
					
				}
				if (top >= 0 && (top < $(window).height() - selector.parent().outerHeight() - parseInt($(".dialog").css("margin-top")) - parseInt($(".dialog").css("margin-bottom")))){
					current.el.style.top = top + 'px';
				}
			}
			
			if (data.options.drag) {
				data.options.drag.apply(current.el, [current.el, e]);
			}
			
			return $.rwdrag.preventEvent(e);
		},
		stop: function(e){
			var current = $.rwdrag.current;
			var data = $.data(current.el, 'pp-rwdrag');
			$(document).unbind('mousemove', $.rwdrag.drag).unbind('mouseup', $.rwdrag.stop);
			if (data.options.stop) {
				data.options.stop.apply(current.el, [current.el, e]);
			}
			$.rwdrag.current = null;

			document.onselectstart=function(e){return true};//启用选择
			return $.rwdrag.preventEvent(e);
		},
		preventEvent:function(e){
			if (e.stopPropagation) e.stopPropagation();
			if (e.preventDefault) e.preventDefault();
			return false;     
		}
	};
})(jQuery);
/**/
(function($) {
var jmenus = new Map();
// If the DWZ scope is not available, add it
$.dwz = $.dwz || {};

$(window).resize(function(){
	setTimeout(function(){
		for (var i=0; i<jmenus.size();i++){
			fillSpace(jmenus.element(i).key);
		}
	}, 100);
});

})(jQuery);
function initEnv() {
	$("body").append(DWZ.frag["dwzFrag"]);

	$(window).resize(function(){
		$(this).trigger(DWZ.eventType.resizeGrid);
	});

	var ajaxbg = $("#background,#progressBar");
	ajaxbg.hide();
	$(document).ajaxStart(function(){
		ajaxbg.show();
	}).ajaxStop(function(e){
		//setTimeout(function(){
			var tg = $(e.target.activeElement)
			if(tg.attr("target") == "dialog"){
				setTimeout(function(){ajaxbg.hide();},100);
			}else{
				ajaxbg.hide();
			}
			
		//},300)
		
	});
	navTab.init();
	if ($.fn.switchEnv) $("#switchEnvBox").switchEnv();
	if ($.fn.navMenu) $("#navMenu").navMenu();
		
	setTimeout(function(){
		initUI();
		
		// navTab styles
		var jTabsPH = $("div.tabsPageHeader");
	
	}, 10);

}
var icons = [];
$.getJSON("/tools/icons",function(data){
  $.each( data.icons, function( key, val ) {
    icons.push( "<li data-icon-click='add'><i class='fa " + val + "'></i></li>" );
  });
})
function initUI(_box){
	var $p = $(_box || document);
	$("[data-open-icon]",$p).on("click",function(){
		var pr = $(this).parents(".icon-select-container");
		pr.find("[data-icon-list]").html("");
		pr.find(".icon-list-container").removeClass("hidden")
		pr.find("[data-icon-list]").append("<li data-icon-click='remove'><span class='clear-icon'>清空</span></li>")
		$.each(icons, function(k, v){
			pr.find("[data-icon-list]").append(v);
		})
	})
	$(document).on("click", "[data-icon-click]" ,function(){
		var pr = $(this).parents(".icon-select-container");
		var st = $(this).data("icon-click");
		pr.find("[data-icon-click]").removeClass("active");
		$(this).addClass("active");
		if(st=="add"){
			var vl = $(this).html()
			pr.find("[data-icon-content]").html(vl);
			pr.find("[data-icon-value]").val(vl);
		}else{
			pr.find("[data-icon-content]").html("");
			pr.find("[data-icon-value]").val("");
		}
		pr.find(".icon-list-container").addClass("hidden")
	})
	if($(".tools-group",$p).length > 0){
		$(".tools-group",$p).each(function(){
			var cls = $(this).attr("class");
			$(this).addClass("hidden-xs");
			var html = $(this).html();
			$("#w_list_print",$p).after("<div class='"+cls+" visible-xs-block'>"+html+"</div>");
		})
	}
	$("[data-nav-select]",$p).on("change",function(){
		var t = $(this).find("option:selected").data("custom-link");
		//console.log(t)
		if(t){
			$("[data-custom]",$p).val(t);
			$("[data-link-value]",$p).removeClass("hidden").focus();
			//console.log($("[data-custom]",$p).attr("value"))
		}else{
			$("[data-custom]",$p).val("0");
			$("[data-link-value]",$p).addClass("hidden").blur();
		}
	})
	$("[data-link-value]",$p).on("keyup",function(){
		var vl = $(this).val();
		$("[data-nav-select]",$p).find("option:selected").attr("value",vl);
		$("[data-nav-select]",$p).find("option:selected").text("自定义链接："+vl);
		$("[data-nav-select]",$p).selectpicker('refresh');
	})
	$('.micolor',$p).each(function() {
      $(this).minicolors({
          control: $(this).attr('data-control') || 'hue',
          defaultValue: $(this).attr('data-defaultValue') || '',
          inline: $(this).attr('data-inline') === 'true',
          letterCase: $(this).attr('data-letterCase') || 'lowercase',
          opacity: $(this).attr('data-opacity'),
          position: $(this).attr('data-position') || 'bottom left',
          change: function(hex, opacity) {
              if (!hex) return;
              if (opacity) hex += ', ' + opacity;
              if (typeof console === 'object') {
                  //console.log(hex);
              }
          },
          theme: 'bootstrap'
      });
  });
	$(".form_datetime", $p).each(function(){
		var pos;
		var dt = $(this).data("ymd");
		if(dt == true){
			fm = "yyyy-mm-dd";
			mv = 2
		}else{
			fm = "yyyy-mm-dd hh:ii";
			mv = 0
		}
		$(this).mouseenter(function(e){
			//console.log(e.pageY);
			$(this).datetimepicker('remove');
			if (e.pageY > $(window).height()/2){

				$(this).datetimepicker({
					format: fm,
					autoclose: true,
					todayBtn: true,
					pickerPosition: 'top-left',
					language: "zh-CN",
					minView: mv
				});
			}else{
				$(this).datetimepicker({
					format: fm,
					autoclose: true,
					todayBtn: true,
					pickerPosition: 'bottom-left',
					language: "zh-CN",
					minView: mv
				});
			}
		})
		
	})
	$(".bs-select", $p).selectpicker();
	$('.tags', $p).tagsInput({
      width: 'auto',
      'onAddTag': function () {
          //alert(1);
      },
  });
  $('[data-toggle="tooltip"]', $p).tooltip({
		placement: "top",
	});
	if($(window).width() < 992){
		$('[data-tooltip="mobile"]', $p).tooltip({
			placement: "top",
			title: function(){
				var t = $(this).find("span").text();
				return t;
			}
		})
	}
	$('[data-toggle="editable"]', $p).each(function(){
		var action = $(this).closest("tbody").data("url");
		var id = $(this).data("id");
		var name = $(this).data("name");
		var type = $(this).data("type") || "text";
		var value = $(this).text();
		var width = $(this).data("width");
		var btnSubmit = '<button type="button" class="btn btn-primary btn-xs submit-editable"><i class="fa fa-check"></i></button>';
		var btnClose = '<button type="button" class="btn btn-danger btn-xs close-editable"><i class="fa fa-times"></i></button>';
		var form = $('<div class="editable-box hidden"><div class="input-group"><input class="form-control input-xs" type="'+type+'" style="width:'+width+'"><span class="input-group-btn">'+btnSubmit+btnClose+'</span></div></div>');
		$input = form.find(".form-control");
		$btnSubmit = form.find(".submit-editable");
		$btnClose = form.find(".close-editable");
		form.insertAfter(this);
		$(this).click(function(){
			$(this).closest("tbody").find(".editable-box:not(.hidden)").addClass("hidden");
			$(this).closest("tbody").find('.hidden[data-toggle="editable"]').removeClass("hidden");
			form.removeClass("hidden");
			$(this).addClass("hidden");
			form.find(".form-control").focus().val(value);
		})
		$btnClose.click(function(){
			$(this).closest(".editable-box").addClass("hidden");
			$(this).closest("td").find('[data-toggle="editable"]').removeClass("hidden");
		})
		$btnSubmit.click(function(){
			var val = $(this).closest(".editable-box").find("input").val();
			var title = $(this).closest("td").data("title");
			if(val){
				ajaxTodo(action+'?id='+id+'&name='+name+'&val='+val, "navTabAjaxDone");
			}else{
				alertMsg.error(title+"不能为空！");
			}
			
		})
			
	});
  $(".ueditor-init", $p).each(function(){
  	var closebtn = $(this).closest(".modal").find(".close-m");
  	closebtn.addClass("disabled");
  	var uid = $(this).attr("id");
  	var uecon = $(this).parent();
  	uecon.addClass("ue-loading");
  	//console.log(id);
  	var html = $(this).data("html-content");
  	var ue = "";
  	if($(this).hasClass("ueditor-sm")){
  		var ue = UE.getEditor(uid,{
        toolbars: [[
          'source', '|', 
          'bold', 'italic', 'underline', 'strikethrough', '|', 'removeformat', 'formatmatch', 'autotypeset', '|', 'forecolor', 'backcolor','selectall', 'cleardoc', '|','justifyleft', 'justifycenter', 'justifyright', 
          'fontfamily', 'fontsize', '|',
          'link', 'unlink', '|',
          'help'
        ]],
        removeFormatTags: 'big,code,del,dfn,font,ins,kbd,q,samp,small,strike,sub,sup,tt,u,var',
        allowDivTransToP: false
      });
  	}else{
  		if($(window).width() > 768){
        ue = UE.getEditor(uid,{
          toolbars: [['source', '|', 'undo', 'redo', '|','bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript','|', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|','justifyleft', 'justifycenter', 'justifyright', 'indent', '|', 'paragraph', 'fontfamily', 'fontsize', '|','link', 'unlink', '|', 'emotion',  'insertvideo', 'music', 'map',  'horizontal', 'snapscreen', 'spechars', '|','inserttable', 'deletetable', '|','print', 'preview', 'searchreplace', 'help','template']],
        	allowDivTransToP: false,
        	removeFormatTags: 'big,code,del,dfn,font,ins,kbd,q,samp,small,strike,sub,sup,tt,u,var',
        });
      }else{
        ue = UE.getEditor(uid,{
          toolbars: [['source', '|', 'undo', 'redo', '|','bold', 'italic', 'underline', 'strikethrough', '|', 'removeformat', 'blockquote', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|','justifyleft', 'justifycenter', 'justifyright', 'indent', '|', 'paragraph', 'fontfamily', 'fontsize', '|','link', 'unlink', '|', 'emotion',  'insertvideo', 'map',  '|', 'inserttable', 'deletetable', 'searchreplace', 'help']],
        	allowDivTransToP: false,
        	removeFormatTags: 'big,code,del,dfn,font,ins,kbd,q,samp,small,strike,sub,sup,tt,u,var',
        });
      }
  	}
  	ue.ready(function() {
  		//console.log(html)
  		closebtn.removeClass("disabled");
  		if(html != undefined)
      	ue.setContent(html);
      var ifr = $(ue.container).find(".edui-editor-iframeholder iframe").contents();
				//var a = $(".view", ifr).length;
				// $(".view",ifr).on("keyup",function(){chkform();})
				// $(".ueditor-init pre,.ueditor-init code").on("keyup",function(){chkform();})
				//console.log($(ue.container).find(".edui-for-template").length);
				function initUE(){
					$(ue.container).find(".edui-bs").remove();
					var CodeMirror = $(ue.container).find(".CodeMirror").length
					$(".view [data-bs='carousel']", ifr).each(function(){
						var id = $(this).attr("id");
						$(this).carousel();
						$(this).carousel('pause');
						$(ue.container).append("<div id='edui-"+id+"-wrap' class='edui-bs-wrap edui-editor-carousel-wrap'></div>"+
							"<div id='edui-"+id+"' class='edui-bs edui-editor-carousel' style='display:none'>"+
							"<a href='#' data-carousel-dir='prev'><i class='fa fa-arrow-left'></i></a>"+
							"<a href='#' data-carousel-add='add'><i class='fa fa-plus'></i></a>"+
							"<a href='#' data-carousel-select><i class='fa fa-image'></i></a>"+
							"<a href='#' data-carousel-add='minus'><i class='fa fa-minus'></i></a>"+
							"<a href='#' data-carousel-dir='next'><i class='fa fa-arrow-right'></i></a> "+
							"| <a href='#' data-carousel-remove><i class='fa fa-trash'></i></a>"+
							"</div>");
						var st = $(ue.container).find("#edui-"+id);
						var wr = $(ue.container).find("#edui-"+id+"-wrap");
						var $cr = $(this);
						function initMouse(e){
							if($(ue.container).length > 0){
								var ueY = e.pageY - $(ue.container).offset().top;
								//console.log($(this).offset().top+ "-" +$(this).parents(".modal").scrollTop())
								var ueX = e.pageX - $(ue.container).offset().left;
								//console.log(ueX+"-"+ueY)
								//console.log((e.pageX-ueX)+" - "+(e.pageY-ueY));
								var th = $(ue.container).find(".edui-editor-toolbarbox").height();
								var hh = $cr.height();
								var ww = $cr.width();
								var top = $cr.offset().top;
								var left = $cr.offset().left;
								wr.css({width: ww+2,height:hh+2, top:(top+th)-1,left: left-1});
								st.css({top:top+th+hh-39,left:left+1});
								//wr.show();
								if(ueY > (top+th) && ueY < (top+th+hh) && ueX > 5 && ueX < ww){
									st.show();						
								}else{
									st.hide();
								}
							}
						}
						$(document).on("mousemove",function(e){
							if(!CodeMirror){
								wr.show();
								initMouse(e); 
							}else{
								wr.hide();
							}
						})
						$(".view",ifr).on("mousemove",function(e){
							if(!CodeMirror){
								wr.show();
							 initMouse(e);
							}else{
								wr.hide();
							}
						})
						st.find("[data-carousel-dir]").click(function(){
							var dir = $(this).data("carousel-dir");
							$cr.carousel(dir);
						})
						st.find("[data-carousel-remove]").click(function(){
							$cr.remove();
						})

						st.find("[data-carousel-add]").click(function(){
							var a = $(this).data("carousel-add");
							index = $cr.find(".carousel-indicators i:last-child").data("slide-to")
							
								if(a=="add"){						
									$cr.find(".carousel-indicators").append('<i class="rounded-x" data-target="#'+id+'" data-slide-to="'+index+1+'"></i>')
									$cr.find(".carousel-inner").append('<div class="item"><img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt=""></div>')
								}else{
									if(index > 1){
										var last = $cr.find(".carousel-indicators i:last-child");
										if(last.hasClass("active")){
											$cr.carousel("prev");
											setTimeout(function(){
												$cr.find(".carousel-indicators i:last-child").remove();
												$cr.find(".carousel-inner .item:last-child").remove();
											},1000)
										}else{
											$cr.find(".carousel-indicators i:last-child").remove();
											$cr.find(".carousel-inner .item:last-child").remove();
										}
									}
									

							}
							
						})

						st.find("[data-carousel-select]").click(function(){
							$cr.find(".item.active img").trigger("click");
							//console.log(id);
							ue.execCommand("image",uid);
						})
					})
					$(".view [data-bs='tab']", ifr).each(function(){
						var id = $(this).attr("id");
						$(ue.container).append("<div id='edui-"+id+"-wrap' class='edui-bs-wrap edui-editor-carousel-wrap'></div>"+
							"<div id='edui-"+id+"' class='edui-bs edui-editor-carousel' style='display:none'>"+
							"<a href='#' data-tab-add='add'><i class='fa fa-plus'></i></a>"+
							"<a href='#' data-tab-add='minus'><i class='fa fa-minus'></i></a>"+
							" | <a href='#' data-tab-remove><i class='fa fa-trash'></i></a>"+
							"</div>");
						var st = $(ue.container).find("#edui-"+id);
						var $tb = $(this);
						function getInfo(){
							var m = {th:"",hh:"",ww:"",top:"",left:""};
							m.th = $(ue.container).find(".edui-editor-toolbarbox").height();
							m.hh = $tb.height();
							m.ww = $tb.width();
							m.top = $tb.offset().top;
							m.left = $tb.offset().left;

							//m.right = $tb.offset().right;
							//console.log(m.right)
							st.css({top:m.top+m.th,left:m.ww+1-st.width()});
							return m;
						}
						function initMouse(e){
							if($(ue.container).length > 0){


							var ueY = e.pageY - $(ue.container).offset().top;
							//console.log($(this).offset().top+ "-" +$(this).parents(".modal").scrollTop())
							var ueX = e.pageX - $(ue.container).offset().left;
							
							//console.log((e.pageX-ueX)+" - "+(e.pageY-ueY));
							var m = getInfo();
							//wr.show();
							
							if(ueY > (m.top+m.th) && ueY < (m.top+m.th+m.hh) && ueX > 5 && ueX < m.ww+5){
								st.show();		
								//console.log(ueX+"-"+ueY)				
							}else{
								st.hide();
							}
							}
						}
						st.find("[data-tab-remove]").click(function(){
							$tb.remove();
						})
						st.find("[data-tab-add]").click(function(){
							var add = $(this).data("tab-add");
							var l = $tb.find(".tab-content .tab-pane").length;
							if(add=="add"){
								var a = $tb.find(".tab-content .tab-pane:last-child").attr("id").split("-");
								$tb.find(".nav-tabs").append("<li><a href='#"+id+"-"+(parseInt(a[1])+1)+"' data-toggle='tab'>标签</a></li>");
								$tb.find(".tab-content").append('<div class="tab-pane fade" id="'+id+"-"+(parseInt(a[1])+1)+'">'+
                    '<h4>标题示例 '+(parseInt(a[1])+1)+'</h4>'+
                    '<p>決毎由覧腹善汗集最将供済週問被有殺件所。遺必連今登裏用透臣損芸仙当全有画音。本仲経揃対商町女易識調殺金鹿続荒立員文推。保絶象法工自約高教年録座戒辺。作早材度暇選戦開九成安聞頻領陽止定愛。着制坂表図算滞新部不周建当発組序移宝行阜。討果物勇活感忘毎校暮導子的見暮格。蔵局美置毎認会陽画第経属頑連付写輪供知通。</p>'+
                '</div>');
							}else{
								if(l>2){
									$tb.find(".tab-content .tab-pane:last-child").remove();
									$tb.find(".nav-tabs li:last-child").remove();
								}
								
							}
						})
						$(document).on("mousemove",function(e){
							if(!CodeMirror){
								//wr.show();
								initMouse(e);
							}else{
								//wr.hide();
							}
						})
						$(this).on("mouseover",function(e){
							var m = getInfo();
							st.show();
							//console.log("aa")
						})
					})
					$(".view [data-bs='collapse']", ifr).each(function(){
						var pr = $(this).attr("id");
						var id = pr.split("-")[0];
						$(ue.container).append("<div id='edui-"+id+"-wrap' class='edui-bs-wrap edui-editor-carousel-wrap'></div>"+
							"<div id='edui-"+id+"' class='edui-bs edui-editor-carousel' style='display:none'>"+
							"<a href='#' data-collapse-add='add'><i class='fa fa-plus'></i></a>"+
							"<a href='#' data-collapse-add='minus'><i class='fa fa-minus'></i></a>"+
							" | <a href='#' data-collapse-remove><i class='fa fa-trash'></i></a>"+
							"</div>");
						var st = $(ue.container).find("#edui-"+id);
						var $tb = $(this);
						function getInfo(){
							var m = {th:"",hh:"",ww:"",top:"",left:""};
							m.th = $(ue.container).find(".edui-editor-toolbarbox").height();
							m.hh = $tb.height();
							m.ww = $tb.width();
							m.top = $tb.offset().top;
							m.left = $tb.offset().left;
							st.css({top:m.top+m.th,left:m.ww+1-st.width()});
							return m;
						}
						function initMouse(e){
							if($(ue.container).length > 0){
								var ueY = e.pageY - $(ue.container).offset().top;
								//console.log($(this).offset().top+ "-" +$(this).parents(".modal").scrollTop())
								var ueX = e.pageX - $(ue.container).offset().left;
								
								//console.log((e.pageX-ueX)+" - "+(e.pageY-ueY));
								var m = getInfo();
								//wr.show();
								
								if(ueY > (m.top+m.th) && ueY < (m.top+m.th+m.hh) && ueX > 5 && ueX < m.ww+5){
									st.show();		
									//console.log(ueX+"-"+ueY)				
								}else{
									st.hide();
								}
							}
						}
						st.find("[data-collapse-remove]").click(function(){
							$tb.remove();
						})
						st.find("[data-collapse-add]").click(function(){
							var add = $(this).data("collapse-add");
							var l = $tb.find(".panel").length;
							if(add=="add"){
								var a = $tb.find(".panel:last-child .panel-collapse").attr("id").split("-");
								$tb.append('<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#'+pr+'" href="#'+id+"-"+(parseInt(a[1])+1)+'" aria-expanded="true">'+
                            '手风琴＃'+(parseInt(a[1])+1)+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="'+id+"-"+(parseInt(a[1])+1)+'" class="panel-collapse collapse" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>');

									// '<div class="tab-pane fade" id="'+id+"-"+(parseInt(a[1])+1)+'">'+
         //            '<h4>标题示例 '+(parseInt(a[1])+1)+'</h4>'+
         //            '<p>決毎由覧腹善汗集最将供済週問被有殺件所。遺必連今登裏用透臣損芸仙当全有画音。本仲経揃対商町女易識調殺金鹿続荒立員文推。保絶象法工自約高教年録座戒辺。作早材度暇選戦開九成安聞頻領陽止定愛。着制坂表図算滞新部不周建当発組序移宝行阜。討果物勇活感忘毎校暮導子的見暮格。蔵局美置毎認会陽画第経属頑連付写輪供知通。</p>'+
         //        '</div>'
							}else{
								if(l>2){
									$tb.find(".panel:last-child").remove();
								}
								
							}
						})
						$(document).on("mousemove",function(e){
							if(!CodeMirror){
								//wr.show();
								initMouse(e);
							}else{
								//wr.hide();
							}
						})
						$(this).on("mouseover",function(e){
							var m = getInfo();
							st.show();
							//console.log("aa")
						})
					})
					$(ifr).on("click",".view [data-toggle=tab]",function(){
						var tg = $(this).attr("href");
						var id = $(this).attr("id");
						var pr = $(tg, ifr);
						//console.log(pr+"-"+tg)
						pr.siblings().removeClass("in").removeClass("active");
						pr.addClass("in").addClass("active");
						//console.log($(this).tab('show'))
					})

					$(ifr).on("click",".view [data-toggle=collapse]",function(){
						var tg = $(this).attr("href");
						var pr = $($(this).data("parent"), ifr);
						var pr2 = $(this).closest(".panel");
						pr2.find(".collapse").addClass("in");
						pr2.siblings().find(".collapse").removeClass("in");
					})
					var l = $(".view img").length;
					var t = $(".view img",ifr).each(function(){
						$(this).load(function(){
							$(this).addClass("loaded");
							var mlt = $(".view img.loaded",ifr).length;
							if(l == mlt){
								$(".view img.loaded",ifr).removeClass("loaded")
								var vh = $(".view",ifr).height();
								$(ue.container).find(".edui-editor-iframeholder").height(vh);
							}
						})
					});
						//var t = $(".view [data-ride=carousel]", ifr).length;				
				}
				setTimeout(function(){
					initUE();
				},100)
				
				
				$(ue.container).find(".edui-for-source").click(function(){
					initUE();
				})
				$(ue.container).find(".edui-for-template").click(function(){
					$("#edui_fixedlayer .edui-for-template .edui-okbutton").click(function(){
						setTimeout(function(){
							initUE();
						},100)
					})
				})
			uecon.removeClass("ue-loading");
    });
    //console.log(ue);
  })
  $(".make-switch", $p).bootstrapSwitch();
	//tables
	//$("table.table", $p).jTable();
	
	// css tablesa
	function ellipsis(){
		$(".ellipsis",$p).each(function(){
			console.log($(this)[0].offsetHeight);
			if($(this)[0].offsetHeight < $(this)[0].scrollHeight){
				$(this).addClass("ellipsis-overflow")
			}else{
				$(this).removeClass("ellipsis-overflow")
			}
		})
		$(".ellipsis-hover.ellipsis-overflow",$p).each(function(){
			var text = $(this).text();
			var $this = $(this);
			if($this.next().length == 0){
				$this.after("<span class='ellipsis-title' data-footable-toggle>"+text+"</span>");
			}
			$this.parent().mouseover(function(){
				console.log("hover")
				var trbg = $this.closest("tr").css("background-color");
				$this.next().addClass("show");
				$this.next().css("background-color",trbg);
			})
			$this.parent().mouseout(function(){
				$this.next().removeClass("show");
			})
		})
	}
	setTimeout(function(){
		ellipsis();
	},50)
	
	$(window).resize(function(){
		ellipsis();
	})
	
	$('table.table', $p).cssTable();
	var $footable = $('.foo-tables .table', $p).footable();
	var footable = $('.foo-tables .table', $p).data('footable');
	$("[data-footable-toggle]",$p).on('click',function(){
		var $row = $(this).closest('tr');	
		footable.toggleDetail($row);
	})
	$footable.bind({
		footable_row_collapsed:function(e){
			$(e.row).removeClass("new");
			$(e.row).find("[data-footable-toggle=message] i").removeClass("fa-times").addClass("fa-eye");
		},footable_row_expanded:function(e){
			$('.foo-tables tbody tr.footable-detail-show').not(e.row).each(function() {
				footable.toggleDetail(this);
			});

			var top = $("#w_list_print",$p).offset().top
			$(e.row).find("[data-footable-toggle=message] i").addClass("fa-times").removeClass("fa-eye");
			var url = $(e.row).find("[data-footable-toggle=message]").data("post");
			//console.log($(e.row)[0].offsetTop);
			var h1 = $("#w_list_print",$p).height();
			var h2 = $(e.row)[0].offsetTop;
			var h3 = $('.foo-tables thead').height()
			if(h2 > h1 / 4 * 3 || ($(window).width()<768 && (h2 > h1 / 4 * 2 || h1 / 4*3 <= $(e.row).height() + $(e.row).next().height()))){
				$("#w_list_print",$p).animate({scrollTop:$(e.row)[0].offsetTop - h3});
			}
			
			if(url && $(e.row).hasClass("new")){
				$.ajax({
					type:'POST',
					url:url,
					dataType:"json",
					cache: false,
					global: false,
					success: function(){
						console.log("写入状态成功");
					},
					error: function(a,b,c){
						//console.log(a);console.log(b);console.log(c);
						console.log("写入状态失败");
					}
				});
			}
		}
	})

	$(".table",$p).each(function(){
		//if($(this).find(".fixed-head").length == 0){
			var container = $(this).closest(".no-more-tables,.foo-tables").parent();
			var th = $(this).find("thead th");
			th.each(function(){
				var ct = $(this).html();
				$(this).append("<div class='fixed-head stop'><div>"+ct+"</div></div>")
			})
			container.scroll(function(){
				if(sto){
					clearTimeout(sto);
				}
				var t = $(this).scrollTop();
				var $head = $(this).find(".fixed-head");
				$head.css("top",t-1)
				$head.removeClass("stop");
				var sto = setTimeout(function(){
					$head.addClass("stop");
				},200)
			})
				
	})
	$(".fixed-head-table",$p).each(function(){
		//if($(this).find(".fixed-head").length == 0){
			if($(window).width() < 768){
				var container = $(this).closest(".pageContent.autoflow");
				var top = $(this).closest(".static-details-table")[0].offsetTop;
			}else{		
				var container = $(this).closest(".static-details-table");
			}
			var th = $(this).find("thead th");
			th.each(function(){
				var ct = $(this).html();
				$(this).append("<div class='fixed-head'><div><span>"+ct+"</span></div></div>")
			})
			container.scroll(function(){
				var t = $(this).scrollTop();
				$(this).find(".fixed-head").removeClass("show");
				if($(window).width() < 768){
					if(t > top){
						$(this).find(".fixed-head").css("top",t-top)
						container.find(".fixed-head").removeClass("top");
					}else{
						$(this).find(".fixed-head").css("top",0)
						container.find(".fixed-head").addClass("top");
					}
				}else{
					$(this).find(".fixed-head").css("top",t);
					if(t == 0){
						container.find(".fixed-head").addClass("top");
					}else{
						container.find(".fixed-head").removeClass("top");
					}
				}				
			})
			container.scrollEnd(function(){
				container.find(".fixed-head").addClass("show");
			},150)
				
	})
	/*$("ul.tree", $p).jTree();
	$('div.accordion', $p).each(function(){
		var $this = $(this);
		$this.accordion({fillSpace:$this.attr("fillSpace"),alwaysOpen:true,active:0});
	});*/

	//$(":button.checkboxCtrl, :checkbox.checkboxCtrl", $p).checkboxCtrl($p);
	$("[data-check='all']", $p).on("change",function(){
		var tg = $(this).data("parent");
		if ($(this).prop("checked")){
			$(this).parents(tg).find("[data-check=list] input[type=checkbox]").prop("checked",true)
		}else{
			$(this).parents(tg).find("[data-check=list] input[type=checkbox]").prop("checked",false)
		}	
	})
	$("[data-check='invert']", $p).on("click",function(){
		var tg = $(this).data("parent");
		var ck = $(this).parents(tg).find("[data-check=list] input[type=checkbox]");
		ck.each(function(){
			if($(this).prop("checked")){
				$(this).prop("checked",false)
			}else{
				$(this).prop("checked",true)
			}
		})
	})
	$("[data-check=list]", $p).find("input[type=checkbox]").on("change",function(){
		var tg = $(this).parents("[data-check=list]");
		if (tg.find("input[type=checkbox]").length > tg.find("input[type=checkbox]:checked").length){
			$(tg.data("parent")).find("[data-check=all]").prop("checked",false)
		}else{
			$(tg.data("parent")).find("[data-check=all]").prop("checked",true)
		}
	})
	// init styles
	$("input[type=text], input[type=password], textarea", $p).addClass("textInput").focusClass("focus");

	$("input[readonly], textarea[readonly]", $p).addClass("readonly");
	$("input[disabled=true], textarea[disabled=true]", $p).addClass("disabled");

	//Grid ToolBar
	$("div.panelBar li, div.panelBar", $p).hoverClass("hover");

	//Button
	$("div.button", $p).hoverClass("buttonHover");
	$("div.buttonActive", $p).hoverClass("buttonActiveHover");
	
	//tabsPageHeader

	//validate form
	$("form.required-validate", $p).each(function(){
		var $form = $(this);
		$form.validate({
			onsubmit: false,
			errorElement: 'span',
			errorClass: 'help-block help-block-error', //default input error message container
			focusCleanup: true, // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: ".ignore",  // validate all fields including form hidden input
			invalidHandler: function (event, validator) { //display error alert on form submit              
				// var errors = validator.numberOfInvalids();
				// if (errors) {
				// 	var message = DWZ.msg("validateFormError",[errors]);
				// 	alertMsg.error(message);
				// } 
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				if (element.closest(".input-group").size() > 0) {
					error.insertAfter(element.closest(".input-group"));
				} else if (element.attr("data-error-container")) { 
					error.appendTo(element.attr("data-error-container"));
				} else if (element.closest('.bootstrap-select').size() > 0) {
					error.insertAfter(element.closest('.bootstrap-select'));
				} else if (element.parents('.radio-list').size() > 0) { 
					error.appendTo(element.parents('.radio-list').attr("data-error-container"));
				} else if (element.parents('.radio-inline').size() > 0) { 
					error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
				} else if (element.parents('.checkbox-list').size() > 0) {
					error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
				} else if (element.parents('.checkbox-inline').size() > 0) { 
					error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
				} else {
					error.insertAfter(element); // for other inputs, just perform default behavior
				}
			},
			highlight: function (element) { // hightlight error inputs
			  $(element)
					.closest('.form-group').addClass('has-error').removeClass('has-success'); // set error class to the control group
			},

			unhighlight: function (element) { // revert the change done by hightlight
				$(element)
					.closest('.form-group').removeClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label
					.closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
			},
		});
		function chkform(){
			//console.log("check");
			if($form.valid()){
				$form.find("[type=submit]").removeClass("disabled").prop("disabled",false);
			}else{
				$form.find("[type=submit]").addClass("disabled").prop("disabled",true);
			}
		}
		$(".form_datetime .form-control", $form).change(function(){
			$form.validate().element($(this));
		})
		$form.find('input[customvalid]').each(function(){
			var $input = $(this);
			$input.rules("add", {
				customvalid: $input.attr("customvalid")
			})
		});

		var r = $form.find(".required, [required]").length;
		if (r > 0){
			$form.find("[type=submit]").addClass("disabled").prop("disabled",true);
		}
			
		$(window).resize(function(){
			modalWidth();
		})
		$(".form_datetime .form-control,input[type=checkbox],input[type=radio],select").change(function(){
			//alert("change")
			chkform();
		});
		$form.find(".fileinput a, [data-open-icon]").click(function(){
		 	chkform();
		})
		$form.find(".make-switch").on('switchChange.bootstrapSwitch',function(){
			chkform();
		})
		var ueditor = $form.find(".ueditor-init")
		ueditor.each(function(){
			var $this = $(this);
			var uid = $(this).attr("id");
			var ue = UE.getEditor(uid);		
			ue.ready(function(){
				var ifr = $(ue.container).find(".edui-editor-iframeholder iframe").contents();
				//var a = $(".view", ifr).length;
				$(".view",ifr).on("keyup",function(){chkform();})
				$(ue.container).find("pre,code").on("keyup",function(){chkform();})
				$(ue.container).find(".edui-button").on("click",function(){
					chkform();
				})
			})
		})
		$form.find("input[type=text],input[type=email],input[type=number],input[type=password],textarea").on("keyup blur",function(){
			chkform();
		})
	});
	//initTable2();
	// navTab
	$("a[target=navTab]", $p).each(function(){
		$(this).on("click",function(event){
			var $this = $(this);
			var title = $this.attr("title") || $this.text();
			var tabid = $this.attr("rel") || "_blank";
			var fresh = eval($this.attr("fresh") || "true");
			var external = eval($this.attr("external") || "false");
			var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
			DWZ.debug(url);
			if (!url.isFinishedTm()) {
				alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
				return false;
			}
			location.hash = tabid;
			navTab.openTab(tabid, url,{title:title, fresh:fresh, external:external});

			event.preventDefault();
		});
		
	});
	var hash = location.hash;
	if(hash){
			$(".main-menu a[target=navTab][rel='"+hash.replace('#','')+"']", $p).click();
	}
	$(".click-able-title", $p).on("click",function(){
	  var pr = $(this).closest('tr');
	  //console.log("click");
	  pr.find(".tools-edit .btn-success").click();
	})
	//dialogs
	$(".tabsSideNav", $p).on("click", function(){
		$("#container").toggleClass("active");
		$("#header .logo2").toggleClass("active");
		$("#sidebar").toggleClass("active");
	})
	$(".main-menu a[target=navTab]", $p).click(function(){
		if ($(window).width() < 768) {
		setTimeout(function(){
			$(".tabsSideNav", $p).trigger("click");
			$("#layout").removeClass("active");
		},100)	
		}	
	})
	//if ($(window).width() < 768) {
	//	$(".tabsSideNav", $p).trigger("click");
	//	$("#layout").removeClass("active");
	//}

	$(".get-table-top",$p).each(function(){
		//if($(this).find(".fixed-head").length == 0){
			var $this = $(this);
			var top = $this.offset().top;
			var w = $this.width();
			var logoH = $('.logo2').height();
			var $next = $this.next();
			$next.css({top:top - logoH});
			$(window).resize(function(){
				var top = $this.offset().top;
				var w = $this.width();
				var logoH = $('.logo2').height();
				$next.css({top:top - logoH});
			})		
	})

	if($("#w_list_print",$p).length > 0){
		var $wlp = $("#w_list_print",$p);
		var h1 = $wlp.height();
		var th = $("#w_list_print > div",$p).height();
		if(th > h1+200){
			if($('.pageContent .goTop', $p).length == 0){
					$('.pageContent',$p).append('<a class="btn btn-info goTop"><i class="fa fa-arrow-up"></i></a>')
			}
			$wlp.scroll(function(){
				if($(window).width >= 768){
					var x = 0.5;
				}else{
					var x = 2;
				}
				if($wlp.scrollTop() > $(window).height() * x){
					$('.pageContent .goTop', $p).addClass("active");
				}else{
					$('.pageContent .goTop', $p).removeClass("active");
				}
			})
			$('.pageContent .goTop', $p).click(function(){
				$wlp.animate({scrollTop:0});
			})
		}
	}


	if($(".pageContent.autoflow",$p).length > 0) {
		$(".pageContent.autoflow",$p).each(function(){
			var $pa = $(this);
			if($pa.parent().find('.goTop.s2').length == 0){

				if($pa.next().hasClass("bottomBar")){
					$pa.next().after('<a class="btn btn-info goTop s2"><i class="fa fa-arrow-up"></i></a>');
				}else{
					$pa.after('<a class="btn btn-info goTop s2"><i class="fa fa-arrow-up"></i></a>');
				}

			}

			$pa.scroll(function(){
				if($(window).width >= 768){
					var x = 0.5;
				}else{
					var x = 2;
				}
				if($pa.scrollTop() > $(window).height() * x){
					$pa.parent().find('.goTop.s2').addClass("active");
				}else{
					$pa.parent().find('.goTop.s2').removeClass("active");
				}
			})
			$pa.parent().find('.goTop.s2').click(function(){
				$pa.animate({scrollTop:0});
			})
		});
		
	}
	$("[target=ajaxTrash]",$p).on("click",function(e){
		e.preventDefault();
		var $this = $(this);
		var act = $this.data("action");
		var tr = $this.closest("tr");
		var body = $this.closest("tbody");
		var form = $this.closest("form");
		var checked = body.find(":checked")
		var count = checked.length;
		if(count==1){
			title = checked.closest("tr").find("[data-title-for-msg]").text();
		}else{
			title = tr.find("[data-title-for-msg]").text();
		}
		if(act != "restore"){
			var msgOne = body.data("msg-"+act);
			console.log(msgOne);
			console.log(act);
			var msgGroup =  body.data("msg-group-"+act);
			var msgOne = msgOne.replace("%title%",title);
			var msgGroup = msgGroup.replace("%count%",count);
			if(count > 0){
				if(count == 1){
					var msgGroup = msgOne;				
				}
				form.find("[name=act]").val(act);
				alertMsg.confirm(msgGroup,{okCall: function(){
					form.submit()
				}})
			}else{
				alertMsg.confirm(msgOne,{okCall: function(){
					ajaxTodo($this.attr("href"),"navTabAjaxDone");
				}})			
			}
		}else{
			if(count > 0){
				form.find("[name=act]").val(act);
				form.submit();
			}else{
				ajaxTodo($this.attr("href"),"navTabAjaxDone");			
			}
		}

	})

	$("[data-toggle=trash]",$p).on("click",function(){
		var msg = $(this).data("msg");
		var error = $(this).data("error-msg");
		var form = $(this).data("form");
		var act = $(this).data("action");
		$(form,$p).find("[name=act]").val(act);
		if($(form,$p).find("input[type=checkbox]:checked").length > 0){
			if(msg){
				alertMsg.confirm(msg,{okCall: function(){$(form,$p).submit()}})
			}else{
				$(form,$p).submit();
			}
		}else{
			alertMsg.error(error);
		}
	})
	$("a[target=dialog]", $p).each(function(){
		$(this).click(function(event){
			var $this = $(this);
			var title = $this.attr("title") || $this.text();
			var rel = $this.attr("rel") || "_blank";
			var options = {};
			var w = $this.attr("width");
			var h = $this.attr("height");
			if (w) options.width = w;
			if (h) options.height = h;
			options.max = eval($this.attr("max") || "false");
			options.maxable = eval($this.attr("maxable") || "true");
			options.fresh = eval($this.attr("fresh") || "true");
			options.drawable = eval($this.attr("drawable") || "false");
			options.close = eval($this.attr("close") || "");
			options.param = $this.attr("param") || "";

			var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
			DWZ.debug(url);
			if (!url.isFinishedTm()) {
				alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
				return false;
			}
			$.pdialog.open(url, rel, title, options);
			
			return false;
		});
	});
	$("a[target=ajax]", $p).each(function(){
		$(this).click(function(event){
			var $this = $(this);
			var rel = $this.attr("rel");
			if (rel) {
				var $rel = $("#"+rel);
				$rel.loadUrl($this.attr("href"), {});
			}

			event.preventDefault();
		});
	});
	
	$("div.pages-container", $p).each(function(){
		var $this = $(this);
		pageNumShown = 0;
		if($(window).width()>600){
			pageNumShown = $this.attr("pageNumShown");
		}
		$this.pagination({
			targetType:$this.attr("targetType"),
			rel:$this.attr("rel"),
			totalCount:$this.attr("totalCount"),
			numPerPage:$this.attr("numPerPage"),
			pageNumShown:pageNumShown,
			currentPage:$this.attr("currentPage")
		});
	});

	if ($.fn.sortDrag) $("div.sortDrag", $p).sortDrag();

	// dwz.ajax.js
	if ($.fn.ajaxTodo) $("a[target=ajaxTodo]", $p).ajaxTodo();
	if ($.fn.dwzExport) $("a[target=dwzExport]", $p).dwzExport();

	if ($.fn.lookup) $("a[lookupGroup]", $p).lookup();
	if ($.fn.multLookup) $("[multLookup]:button", $p).multLookup();
	if ($.fn.suggest) $("input[suggestFields]", $p).suggest();
	if ($.fn.itemDetail) $("table.itemDetail", $p).itemDetail();
	if ($.fn.selectedTodo) $("a[target=selectedTodo]", $p).selectedTodo();
	if ($.fn.pagerForm) $("form[rel=pagerForm]", $p).pagerForm({parentBox:$p});
	//console.log('inited');
	if($(".preloading-container").length > 0){
		if($.support.transition){
			//console.log('loaded');
			setTimeout(function(){
				if(location.hash){
					preload = setInterval(function(){
						if($("#progressBar").css('display') == 'none'){
							$(".preloading-container").addClass('loaded');
							setTimeout(function(){
								$(".preloading-container").remove();
							},400)
							
							clearInterval(preload);
						}
					},10)
				}else{
					$(".preloading-container").addClass('loaded');
					//console.log('loaded-2');
					setTimeout(function(){
								$(".preloading-container").remove();
					},400)
				}
			},550)
			
		}else{
			$(".preloading-container").remove();
		}
	}
	// 执行第三方jQuery插件【 第三方jQuery插件注册：DWZ.regPlugins.push(function($p){}); 】
	$.each(DWZ.regPlugins, function(index, fn){
		fn($p);
	});
}
function destroyUE(){
	if($(".modal .ueditor-init").length > 0){
		$(".modal .ueditor-init").each(function(){
			var id = $(this).attr("id");
			UE.getEditor(id).destroy();
		})	
	}
}

/**
 * Theme Plugins
 * @author ZhangHuihua@msn.com
 */
(function($){
	$.fn.extend({
		theme: function(options){
			var op = $.extend({themeBase:"themes"}, options);
			var _themeHref = op.themeBase + "/#theme#/style.css";
			return this.each(function(){
				var jThemeLi = $(this).find(">li[theme]");
				var setTheme = function(themeName){
					$("head").find("link[href$='style.css']").attr("href", _themeHref.replace("#theme#", themeName));
					jThemeLi.find(">div").removeClass("selected");
					jThemeLi.filter("[theme="+themeName+"]").find(">div").addClass("selected");
					
					if ($.isFunction($.cookie)) $.cookie("dwz_theme", themeName);
				}
				
				jThemeLi.each(function(index){
					var $this = $(this);
					var themeName = $this.attr("theme");
					$this.addClass(themeName).click(function(){
						setTheme(themeName);
					});
				});
					
				if ($.isFunction($.cookie)){
					var themeName = $.cookie("dwz_theme");
					if (themeName) {
						setTheme(themeName);
					}
				}
				
			});
		}
	});
})(jQuery);

/**
 * @author zhanghuihua@msn.com
 */
(function($){
	$.fn.navMenu = function(){
		return this.each(function(){
			var $box = $(this);
			$box.find("li>a").click(function(){
				var $a = $(this);
				$.post($a.attr("href"), {}, function(html){
					$("#sidebar").find(".accordion").remove().end().append(html).initUI();
					$box.find("li").removeClass("selected");
					$a.parent().addClass("selected");
					navTab.closeAllTab();
				});
				return false;
			});
		});
	}
	
	$.fn.switchEnv = function(){
		var op = {cities$:">ul>li", boxTitle$:">a>span"};
		return this.each(function(){
			var $this = $(this);
			$this.click(function(){
				if ($this.hasClass("selected")){
					_hide($this);
				} else {
					_show($this);
				}
				return false;
			});
			
			$this.find(op.cities$).click(function(){
				var $li = $(this);

				$.post($li.find(">a").attr("href"), {}, function(html){
					_hide($this);
					$this.find(op.boxTitle$).html($li.find(">a").html());
					navTab.closeAllTab();
					$("#sidebar").find(".accordion").remove().end().append(html).initUI();
				});
				return false;
			});
		});
	}
	
	function _show($box){
		$box.addClass("selected");
		$(document).bind("click",{box:$box}, _handler);
	}
	function _hide($box){
		$box.removeClass("selected");
		$(document).unbind("click", _handler);
	}
	
	function _handler(event){
		_hide(event.data.box);
	}
})(jQuery);


/**
 * @author ZhangHuihua@msn.com
 */
$.setRegional("alertMsg", {
	title:{error:"Error", info:"Information", warn:"Warning", correct:"Successful", confirm:"Confirmation"},
	butMsg:{ok:"OK", yes:"Yes", no:"No", cancel:"Cancel"}
});
var alertMsg = {
	_boxId: "#alertMsgBox",
	_bgId: "#alertBackground",
	_closeTimer: null,

	_types: {error:"error", info:"info", warn:"warn", correct:"correct", confirm:"confirm"},

	_getTitle: function(key){
		return $.regional.alertMsg.title[key];
	},

	_keydownOk: function(event){
		if (event.keyCode == DWZ.keyCode.ENTER) event.data.target.trigger("click");
		return false;
	},
	_keydownEsc: function(event){
		if (event.keyCode == DWZ.keyCode.ESC) event.data.target.trigger("click");
	},
	/**
	 * 
	 * @param {Object} type
	 * @param {Object} msg
	 * @param {Object} buttons [button1, button2]
	 */
	_open: function(type, msg, buttons){
		$(this._boxId).remove();
		var butsHtml = "";
		if (buttons) {
			for (var i = 0; i < buttons.length; i++) {
				var sRel = buttons[i].call ? "callback" : "";
				butsHtml += DWZ.frag["alertButFrag"].replace("#butMsg#", buttons[i].name).replace("#callback#", sRel);
			}
		}
		var boxHtml = DWZ.frag["alertBoxFrag"].replace("#type#", type).replace("#title#", this._getTitle(type)).replace("#message#", msg).replace("#butFragment#", butsHtml);
		var albox = $(boxHtml).appendTo("body");
		setTimeout(function(){
			albox.addClass("open")
		})
				
		if (this._closeTimer) {
			clearTimeout(this._closeTimer);
			this._closeTimer = null;
		}
		if (this._types.info == type || this._types.correct == type){
			this._closeTimer = setTimeout(function(){alertMsg.close()}, 3500);
		} else {
			$(this._bgId).show();
		}
		
		var jButs = $(this._boxId).find("a.btn");
		var jCallButs = jButs.filter("[rel=callback]");
		var jDoc = $(document);
		
		for (var i = 0; i < buttons.length; i++) {
			if (buttons[i].call) jCallButs.eq(i).click(buttons[i].call);
			if (buttons[i].keyCode == DWZ.keyCode.ENTER) {
				jDoc.bind("keydown",{target:jButs.eq(i)}, this._keydownOk);
			}
			if (buttons[i].keyCode == DWZ.keyCode.ESC) {
				jDoc.bind("keydown",{target:jButs.eq(i)}, this._keydownEsc);
			}
		}
	},
	close: function(){
		$(document).unbind("keydown", this._keydownOk).unbind("keydown", this._keydownEsc);
		if($.support.transition){
			$(this._boxId).removeClass("open");
			$(this._boxId).one($.support.transition.end, function(){
				$(this).remove();
			})
		}else{
			$(this._boxId).removeClass("open");
			$(this).remove();
		}
		/*$(this._boxId).animate({top:-$(this._boxId).height()}, 500, function(){
			$(this).remove();
		});*/
		$(this._bgId).hide();
	},
	error: function(msg, options) {
		this._alert(this._types.error, msg, options);
	},
	info: function(msg, options) {
		this._alert(this._types.info, msg, options);
	},
	warn: function(msg, options) {
		this._alert(this._types.warn, msg, options);
	},
	correct: function(msg, options) {
		this._alert(this._types.correct, msg, options);
	},
	_alert: function(type, msg, options) {
		var op = {okName:$.regional.alertMsg.butMsg.ok, okCall:null};
		$.extend(op, options);
		var buttons = [
			{name:op.okName, call: op.okCall, keyCode:DWZ.keyCode.ENTER}
		];
		this._open(type, msg, buttons);
	},
	/**
	 * 
	 * @param {Object} msg
	 * @param {Object} options {okName, okCal, cancelName, cancelCall}
	 */
	confirm: function(msg, options) {
		var op = {okName:$.regional.alertMsg.butMsg.ok, okCall:null, cancelName:$.regional.alertMsg.butMsg.cancel, cancelCall:null};
		$.extend(op, options);
		var buttons = [
			{name:op.okName, call: op.okCall, keyCode:''},
			{name:op.cancelName, call: op.cancelCall, keyCode:DWZ.keyCode.ENTER}
		];
		this._open(this._types.confirm, msg, buttons);
	}
};

/**
 * @author zhanghuihua@msn.com
 */

(function($){
	var menu, shadow, hash;
	$.fn.extend({
		contextMenu: function(id, options){
			var op = $.extend({
						shadow : true,
						bindings:{},
					ctrSub:null
				}, options
			);
			
			if (!menu) {
				menu = $('<div id="contextmenu"></div>').appendTo('body').hide();
			}
			if (!shadow) {
				shadow = $('<div id="contextmenuShadow"></div>').appendTo('body').hide();
			}
			
			hash = hash || [];
			hash.push({
				id : id,
				shadow: op.shadow,
				bindings: op.bindings || {},
				ctrSub: op.ctrSub
			});
			
			var index = hash.length - 1;
			$(this).bind('contextmenu', function(e) {
				display(index, this, e, op);
				return false;
			});
			return this;
		}
	});
	
	function display(index, trigger, e, options) {
		var cur = hash[index];

		var content = $(DWZ.frag[cur.id]);
		content.find('li').hoverClass();
	
		// Send the content to the menu
		menu.html(content);
	
		$.each(cur.bindings, function(id, func) {
			$("[rel='"+id+"']", menu).bind('click', function(e) {
				hide();
				func($(trigger), $("#"+cur.id));
			});
		});
		
		var posX = e.pageX;
		var posY = e.pageY;
		if ($(window).width() < posX + menu.width()) posX -= menu.width();
		if ($(window).height() < posY + menu.height()) posY -= menu.height();

		menu.css({'left':posX,'top':posY}).show();
		if (cur.shadow) shadow.css({width:menu.width(),height:menu.height(),left:posX+3,top:posY+3}).show();
		$(document).one('click', hide);
		
		if ($.isFunction(cur.ctrSub)) {cur.ctrSub($(trigger), $("#"+cur.id));}
	}
	
	function hide() {
		menu.hide();
		shadow.hide();
	}
})(jQuery);

/**
 * @author ZhangHuihua@msn.com
 * 
 */
var navTab = {
	componentBox: null, // tab component. contain tabBox, prevBut, nextBut, panelBox
	_tabBox: null,
	_prevBut: null,
	_nextBut: null,
	_panelBox: null,
	_moreBut:null,
	_moreBox:null,
	_currentIndex: 0,
	
	_op: {id:"navTab", stTabBox:".navTab-tab", stPanelBox:".navTab-panel", mainTabId:"main", close$:"a.close-m", prevClass:"tabsLeft", nextClass:"tabsRight", stMore:".tabsMore", stMoreLi:"ul.tabsMoreList"},
	
	init: function(options){
		if ($.History) $.History.init("#container");
		var $this = this;
		$.extend(this._op, options);

		this.componentBox = $("#"+this._op.id);
		this._tabBox = this.componentBox.find(this._op.stTabBox);
		this._panelBox = this.componentBox.find(this._op.stPanelBox);
		this._prevBut = this.componentBox.find("."+this._op.prevClass);
		this._nextBut = this.componentBox.find("."+this._op.nextClass);
		this._moreBut = this.componentBox.find(this._op.stMore);
		this._moreBox = this.componentBox.find(this._op.stMoreLi);

		this._prevBut.click(function(event) {$this._scrollPrev()});
		this._nextBut.click(function(event) {$this._scrollNext()});
		this._moreBut.click(function(){
			$this._moreBox.show();
			return false;
		});
		$(document).click(function(){$this._moreBox.hide()});
		
		this._contextmenu(this._tabBox);
		this._contextmenu(this._getTabs());
		
		this._init();
		this._ctrlScrollBut();
	},
	_init: function(){

		if($("#navTab div.ueditor-init").length > 0){
			$("#navTab .ueditor-init").each(function(){
				var id = $(this).attr("id");
				UE.getEditor(id).destroy();
			})
		}
		var $this = this;
		this._getTabs().each(function(iTabIndex){
			$(this).unbind("click").click(function(event){
				$this._switchTab(iTabIndex);
				var hash = $(this).find("a").attr("class");
				if(hash == undefined){
					location.hash = "home"
				}else{
					location.hash = hash
				}
			});
			$(this).find(navTab._op.close$).unbind("click").click(function(){
				$this._closeTab(iTabIndex);
			});
		});
		this._getMoreLi().each(function(iTabIndex){
			$(this).find(">a").unbind("click").click(function(event){
				$this._switchTab(iTabIndex);
			});
		});

		this._switchTab(this._currentIndex);
	},
	_contextmenu:function($obj){ // navTab右键菜单
		var $this = this;
		$obj.contextMenu('navTabCM', {
			bindings:{
				reload:function(t,m){
					$this._reload(t, true);
				},
				closeCurrent:function(t,m){
					var tabId = t.attr("tabid");
					if (tabId) $this.closeTab(tabId);
					else $this.closeCurrentTab();
				},
				closeOther:function(t,m){
					var index = $this._indexTabId(t.attr("tabid"));
					$this._closeOtherTab(index > 0 ? index : $this._currentIndex);
				},
				closeAll:function(t,m){
					$this.closeAllTab();
				}
			},
			ctrSub:function(t,m){
				var mReload = m.find("[rel='reload']");
				var mCur = m.find("[rel='closeCurrent']");
				var mOther = m.find("[rel='closeOther']");
				var mAll = m.find("[rel='closeAll']");
				var $tabLi = $this._getTabs();
				if ($tabLi.size() < 2) {
					mCur.addClass("disabled");
					mOther.addClass("disabled");
					mAll.addClass("disabled");
				}
				if ($this._currentIndex == 0 || t.attr("tabid") == $this._op.mainTabId) {
					mCur.addClass("disabled");
					mReload.addClass("disabled");
				} else if ($tabLi.size() == 2) {
					mOther.addClass("disabled");
				}
				
			}
		});
	},
	
	_getTabs: function(){
		return this._tabBox.find("> li");
	},
	_getPanels: function(){
		return this._panelBox.find("> div");
	},
	_getMoreLi: function(){
		return this._moreBox.find("> li");
	},
	_getTab: function(tabid){
		var index = this._indexTabId(tabid);
		if (index >= 0) return this._getTabs().eq(index);
	},
	getPanel: function(tabid){
		var index = this._indexTabId(tabid);
		if (index >= 0) return this._getPanels().eq(index);
	},
	_getTabsW: function(iStart, iEnd){
		//console.log(this._tabsW(this._getTabs().slice(iStart, iEnd)))
		return this._tabsW(this._getTabs().slice(iStart, iEnd));
	},
	_tabsW:function($tabs){
		var iW = 0;
		$tabs.each(function(){
			iW += $(this).outerWidth(true);
		});
		//console.log(iW);
		return iW;
	},
	_indexTabId: function(tabid){
		if (!tabid) return -1;
		var iOpenIndex = -1;
		this._getTabs().each(function(index){
			if ($(this).attr("tabid") == tabid){iOpenIndex = index; return;}
		});
		return iOpenIndex;
	},
	_getLeft: function(){
		return this._tabBox.position().left;
	},
	_getScrollBarW: function(){
		return this.componentBox.width()-102;
	},
	
	_visibleStart: function(){
		var iLeft = this._getLeft(), iW = 0;
		var $tabs = this._getTabs();
		for (var i=0; i<$tabs.size(); i++){
			if (iW + iLeft >= 0) return i;
			iW += $tabs.eq(i).outerWidth(true);
		}
		return 0;
	},
	_visibleEnd: function(){
		var iLeft = this._getLeft(), iW = 0;
		//console.log(iLeft);
		var $tabs = this._getTabs();
		for (var i=0; i<$tabs.size(); i++){
			iW += $tabs.eq(i).outerWidth(true);
			if (iW + iLeft > this._getScrollBarW()) return i;
		}
		return $tabs.size();
	},
	_scrollPrev: function(){
		var iStart = this._visibleStart();
		if (iStart > 0){
			this._scrollTab(-this._getTabsW(0, iStart-1));
		}
	},
	_scrollNext: function(){
		var iEnd = this._visibleEnd();
		if (iEnd < this._getTabs().size()){
			this._scrollTab(-this._getTabsW(0, iEnd+1) + this._getScrollBarW());
		} 
	},
	_scrollTab: function(iLeft, isNext){
		var $this = this;
		var t = this._tabBox.find("li").length;
		if(t==1){
			this._tabBox.animate({ left: 0 }, 0, function(){$this._ctrlScrollBut();});
		}else{
			this._tabBox.animate({ left: iLeft+'px' }, 0, function(){$this._ctrlScrollBut();});
		}
	},
	_scrollCurrent: function(){ // auto scroll current tab
		var iW = this._tabsW(this._getTabs());
		if (iW <= this._getScrollBarW()){
			this._scrollTab(0);
		} else if (this._getLeft() < this._getScrollBarW() - iW - 2){
			this._scrollTab(this._getScrollBarW()-iW-2);
		} else if (this._currentIndex < this._visibleStart()) {
			this._scrollTab(-this._getTabsW(0, this._currentIndex));
		} else if (this._currentIndex >= this._visibleEnd()) {
			this._scrollTab(this._getScrollBarW() - this._getTabs().eq(this._currentIndex).outerWidth(true) - this._getTabsW(0, this._currentIndex) - 2);
		}
	},
	_ctrlScrollBut: function(){
		//var $this = this;
		var $this = this;
		var iW = this._tabsW(this._getTabs());
		if (this._getScrollBarW() > iW){
			this._prevBut.hide();
			this._nextBut.hide();
			this._tabBox.parent().removeClass("tabsPageHeaderMargin");
		} else {
			this._prevBut.show().removeClass("tabsLeftDisabled");
			this._nextBut.show().removeClass("tabsRightDisabled");
			this._tabBox.parent().addClass("tabsPageHeaderMargin");
			setTimeout(function(){
				if ($this._getLeft() >= 0){
					$this._prevBut.addClass("tabsLeftDisabled");
				}else if ($this._getLeft() <= $this._getScrollBarW() - iW) {
					$this._nextBut.addClass("tabsRightDisabled");
				}
			},360)
		}
		
		
	},
	
	_switchTab: function(iTabIndex){
		var $tab = this._getTabs().removeClass("selected").eq(iTabIndex).addClass("selected");

		if (DWZ.ui.hideMode == 'offsets') {
			this._getPanels().css({position: 'absolute', top:'-100000px', left:'-100000px'}).eq(iTabIndex).css({position: '', top:'', left:''});
		} else {
			this._getPanels().hide().eq(iTabIndex).show();
		}

		this._getMoreLi().removeClass("selected").eq(iTabIndex).addClass("selected");
		this._currentIndex = iTabIndex;
		
		this._scrollCurrent();
		this._reload($tab);
	},
			
	_closeTab: function(index, openTabid){
		if($("#navTab .ueditor-init").length > 0){
			$("#navTab .ueditor-init").each(function(){
				var id = $(this).attr("id");
				UE.getEditor(id).destroy();
			})
		}
		this._getTabs().eq(index).remove();
		this._getPanels().eq(index).trigger(DWZ.eventType.pageClear).remove();
		this._getMoreLi().eq(index).remove();
		if (this._currentIndex >= index) this._currentIndex--;
		if (openTabid) {
			var openIndex = this._indexTabId(openTabid);
			if (openIndex > 0) this._currentIndex = openIndex;
		}
		
		this._init();
		this._scrollCurrent();
		this._reload(this._getTabs().eq(this._currentIndex));
		//console.log(this._indexTabId(openTabid));
		
	},
	closeTab: function(tabid){
		var index = this._indexTabId(tabid);
		if (index > 0) { this._closeTab(index); }
	},
	closeCurrentTab: function(openTabid){ //openTabid 可以为空，默认关闭当前tab后，打开最后一个tab
		if (this._currentIndex > 0) {this._closeTab(this._currentIndex, openTabid);}
	},
	closeAllTab: function(){
		this._getTabs().filter(":gt(0)").remove();
		this._getPanels().filter(":gt(0)").trigger(DWZ.eventType.pageClear).remove();
		this._getMoreLi().filter(":gt(0)").remove();
		this._currentIndex = 0;
		this._init();
		this._scrollCurrent();
	},
	_closeOtherTab: function(index){
		index = index || this._currentIndex;
		if (index > 0) {
			var str$ = ":eq("+index+")";
			this._getTabs().not(str$).filter(":gt(0)").remove();
			this._getPanels().not(str$).filter(":gt(0)").trigger(DWZ.eventType.pageClear).remove();
			this._getMoreLi().not(str$).filter(":gt(0)").remove();
			this._currentIndex = 1;
			this._init();
			this._scrollCurrent();
		} else {
			this.closeAllTab();
		}
	},

	_loadUrlCallback: function($panel){
		//$panel.find("[layoutH]").layoutH();
		$panel.find(":button.close-m").click(function(){
			navTab.closeCurrentTab();
		});
	},
	_reload: function($tab, flag){
		flag = flag || $tab.data("reloadFlag");
		var url = $tab.attr("url");
		if (flag && url) {
			$tab.data("reloadFlag", null);
			var $panel = this.getPanel($tab.attr("tabid"));
			
			if ($tab.hasClass("external")){
				navTab.openExternal(url, $panel);
			}else {
				//获取pagerForm参数
				var $pagerForm = $("#pagerForm", $panel);
				var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
				
				$panel.loadUrl(url, args, function(){navTab._loadUrlCallback($panel);});
			}
		}
	},
	reloadFlag: function(tabid){
		var $tab = this._getTab(tabid);
		if ($tab){
			if (this._indexTabId(tabid) == this._currentIndex) this._reload($tab, true);
			else $tab.data("reloadFlag", 1);
		}
	},
	reload: function(url, options){
		var op = $.extend({data:{}, navTabId:"", callback:null}, options);
		var $tab = op.navTabId ? this._getTab(op.navTabId) : this._getTabs().eq(this._currentIndex);
		var $panel =  op.navTabId ? this.getPanel(op.navTabId) : this._getPanels().eq(this._currentIndex);
		
		if ($panel){
			if (!url) {
				url = $tab.attr("url");
			}
			if (url) {
				if ($tab.hasClass("external")) {
					navTab.openExternal(url, $panel);
				} else {
					if ($.isEmptyObject(op.data)) { //获取pagerForm参数
						var $pagerForm = $("#pagerForm", $panel);
						op.data = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
					}
					
					$panel.ajaxUrl({
						type:"POST", url:url, data:op.data, callback:function(response){
							navTab._loadUrlCallback($panel);
							if ($.isFunction(op.callback)) op.callback(response);
						}
					});
				}
			}
		}
	},
	getCurrentPanel: function() {
		return this._getPanels().eq(this._currentIndex);
	},
	checkTimeout:function(){
		var json = DWZ.jsonEval(this.getCurrentPanel().html());
		if (json && json[DWZ.keys.statusCode] == DWZ.statusCode.timeout) this.closeCurrentTab();
	},
	openExternal:function(url, $panel){
		var ih = navTab._panelBox.height();
		$panel.html(DWZ.frag["externalFrag"].replaceAll("{url}", url).replaceAll("{height}", ih+"px"));
	},
	/**
	 * 
	 * @param {Object} tabid
	 * @param {Object} url
	 * @param {Object} params: title, data, fresh
	 */
	openTab: function(tabid, url, options){ //if found tabid replace tab, else create a new tab.
		var op = $.extend({title:"New Tab", type:"GET", data:{}, fresh:true, external:false}, options);

		var iOpenIndex = this._indexTabId(tabid);

		if (iOpenIndex >= 0){
			var $tab = this._getTabs().eq(iOpenIndex);
			var span$ = $tab.attr("tabid") == this._op.mainTabId ? "> span > span" : "> span";
			$tab.find(">a").attr("title", op.title).find(span$).html(op.title);
			var $panel = this._getPanels().eq(iOpenIndex);
			if(op.fresh || $tab.attr("url") != url) {
				$tab.attr("url", url);
				if (op.external || url.isExternalUrl()) {
					$tab.addClass("external");
					navTab.openExternal(url, $panel);
				} else {
					$tab.removeClass("external");
					$panel.ajaxUrl({
						type:op.type, url:url, data:op.data, callback:function(){
							navTab._loadUrlCallback($panel);
						}
					});
				}
			}
			this._currentIndex = iOpenIndex;
		} else {
			var tabFrag = '<li tabid="#tabid#"><a href="javascript:" title="#title#" class="#tabid#"><span>#title#</span></a><a href="javascript:;" class="close-m"></a></li>';
			this._tabBox.append(tabFrag.replaceAll("#tabid#", tabid).replaceAll("#title#", op.title));
			this._panelBox.append('<div class="page unitBox"></div>');
			this._moreBox.append('<li><a href="javascript:" title="#title#">#title#</a></li>'.replaceAll("#title#", op.title));
			
			var $tabs = this._getTabs();
			var $tab = $tabs.filter(":last");
			var $panel = this._getPanels().filter(":last");
			
			if (op.external || url.isExternalUrl()) {
				$tab.addClass("external");
				navTab.openExternal(url, $panel);
			} else {
				$tab.removeClass("external");
				$panel.ajaxUrl({
					type:op.type, url:url, data:op.data, callback:function(){
						navTab._loadUrlCallback($panel);
					}
				});
			}
			
			if ($.History) {
				setTimeout(function(){
					$.History.addHistory(tabid, function(tabid){
						var i = navTab._indexTabId(tabid);
						if (i >= 0) navTab._switchTab(i);
					}, tabid);
				}, 10);
			}
				
			this._currentIndex = $tabs.size() - 1;
			this._contextmenu($tabs.filter(":last").hoverClass("hover"));
		}
		
		this._init();
		this._scrollCurrent();
		
		this._getTabs().eq(this._currentIndex).attr("url", url);
	}
};
/**
 * @author Roger Wu
 * @version 1.0
 */
(function($){
	$.pdialog = {
		_op:{minH:40, minW:50, total:20, max:false, drawable:true, maxable:true,fresh:true},
		_current:null,
		getCurrent:function(){
			return this._current;
		},
		reload:function(url, options){
			var op = $.extend({data:{}, dialogId:"", callback:null}, options);
			var dialog = (op.dialogId && $("body").data(op.dialogId)) || this._current;
			if (dialog){
				var jDContent = dialog.find(".dialogContent");
				jDContent.ajaxUrl({
					type:"POST", url:url, data:op.data, callback:function(response){
						$(":button.close-m", dialog).click(function(){
							$.pdialog.close(dialog);
							return false;
						});
						if ($.isFunction(op.callback)) op.callback(response);
					}
				});
			}
		},
		//打开一个层
		open:function(url, dlgid, title, options) {
			var op = $.extend({},$.pdialog._op, options);
			var dialog = $("body").data(dlgid);
			$("body").addClass("modal-open")
			//重复打开一个层
			if(dialog) {
				if(dialog.is(":hidden")) {
					dialog.show();
				}
				if(op.fresh || url != $(dialog).data("url")){
					dialog.data("url",url);
					dialog.find(".dialogHeader").find("h1").html(title);
					this.switchDialog(dialog);
					var jDContent = dialog.find(".dialogContent");
					jDContent.loadUrl(url, {}, function(){
						$("button.close-m").click(function(){
							$.pdialog.close(dialog);
							return false;
						});
					});
				}
			
			} else { //打开一个全新的层
			
				$("body").append(DWZ.frag["dialogFrag"]);
				dialog = $(">.dialog:last-child", "body");
				dialog.wrap("<div class='modal fade' style='display: block'></div>");
				
				
				dialog.data("id",dlgid);
				dialog.data("url",url);
				if(options.close) dialog.data("close",options.close);
				if(options.param) dialog.data("param",options.param);
				($.fn.bgiframe && dialog.bgiframe());
				
				dialog.find(".dialogHeader").find("h1").html(title);
				if(op.drawable)
					dialog.dialogDrag();
				$("a.close-m", dialog).click(function(event){ 
					$.pdialog.close(dialog);
					return false;
				});
				if (op.maxable) {
					$("a.maximize", dialog).show().click(function(event){
						$.pdialog.maxsize(dialog);
						dialog.dialogDrag("destroy");
						return false;
					});
				} else {
					$("a.maximize", dialog).hide();
				}
				$("a.restore", dialog).click(function(event){
					$.pdialog.restore(dialog);
					dialog.dialogDrag();
					return false;
				});
				$("div.dialogHeader a", dialog).mousedown(function(){
					return false;
				});
				$("div.dialogHeader", dialog).dblclick(function(){
					if($("a.restore",dialog).is(":hidden"))
						$("a.maximize",dialog).trigger("click");
					else
						$("a.restore",dialog).trigger("click");
				});
				if(op.max) { 
					$.pdialog.maxsize(dialog);
					dialog.dialogDrag("destroy");
				}
				$("body").data(dlgid, dialog);
				$.pdialog._current = dialog;
				//load data
				var jDContent = $(".dialogContent",dialog);
				
				jDContent.loadUrl(url, {}, function(){
					modalWidth();
					setTimeout(function(){
						dialog.parent().addClass("in");
						if($.support.transition){
							$(dialog).parent().one($.support.transition.end, function(){
								dialog.parent().addClass("no-transform");
							});
						}
					},100);
					$("button.close-m").click(function(){
						$.pdialog.close(dialog);
						return false;
					});
				});
			}
		},

		close:function(dialog) {
			if(typeof dialog == 'string') dialog = $("body").data(dialog);
			var close = dialog.data("close");
			var go = true;
			if(close && $.isFunction(close)) {
				var param = dialog.data("param");
				if(param && param != ""){
					param = DWZ.jsonEval(param);
					go = close(param);
				} else {
					go = close();
				}
				if(!go) return;
			}		

			$(".datetimepicker").remove();
			if($.support.transition) {
				$(dialog).parent().removeClass("no-transform");
				setTimeout(function(){
					$(dialog).parent().removeClass("in");
				})
				
				$(dialog).parent().one($.support.transition.end, function(){
					destroyUE();
					$(dialog).hide();
					$(dialog).parent().remove();
					$("body").removeData($(dialog).data("id"));
					$(dialog).trigger(DWZ.eventType.pageClear).remove();
					$("body").removeClass("modal-open")
				})
			}else{
				destroyUE();
				$(dialog).hide();
				$(dialog).parent().remove();
				$("body").removeData($(dialog).data("id"));
				$(dialog).trigger(DWZ.eventType.pageClear).remove();
			}
		},
		closeCurrent:function(){
			this.close($.pdialog._current);
		},
		checkTimeout:function(){
			var $conetnt = $(".dialogContent", $.pdialog._current);
			var json = DWZ.jsonEval($conetnt.html());
			if (json && json[DWZ.keys.statusCode] == DWZ.statusCode.timeout) this.closeCurrent();
		},
		maxsize:function(dialog) {
			$(dialog).data("original",{
				top:$(dialog).css("top"),
				left:$(dialog).css("left"),
				width:$(dialog).css("width"),
				height:$(dialog).css("height")
			});
			$("a.maximize",dialog).hide();
			$("a.restore",dialog).show();
			//var iContentW = $(window).width();
			//var iContentH = $(window).height() - 34;
			$(dialog).addClass("fullscreen");
		},
		restore:function(dialog) {
			var original = $(dialog).data("original");
			var dwidth = parseInt(original.width);
			var dheight = parseInt(original.height);
			$(dialog).removeClass("fullscreen");
			$("a.maximize",dialog).show();
			$("a.restore",dialog).hide();
		},
	};
})(jQuery);/**
 * @author Roger Wu
 */
(function($){
	$.fn.dialogDrag = function(options){
				if (typeof options == 'string') {
					if (options == 'destroy') 
					return this.each(function() {
							var dialog = this;    
							$("div.dialogHeader", dialog).unbind("mousedown");
					});
				}
		return this.each(function(){
			var dialog = $(this);
			$("div.dialogHeader", dialog).mousedown(function(e){
				dialog.data("task",true);
				setTimeout(function(){
					if(dialog.data("task"))$.dialogDrag.start(dialog,e);
				},100);
				return false;
			}).mouseup(function(e){
				dialog.data("task",false);
				return false;
			});
		});
	};
	$.dialogDrag = {
		currId:null,
		_init:function(dialog) {
			this.currId = new Date().getTime();
			var shadow = $("#dialogProxy");
			if (!shadow.size()) {
				shadow = $(DWZ.frag["dialogProxy"]);
				$("body").append(shadow);
			}
		},
		start:function(dialog,event){
				this._init(dialog);
				var sh = $("#dialogProxy");
				sh.css({
					left: dialog.css("left"),
					top: dialog.css("top"),
					height: dialog.css("height"),
					width: dialog.css("width"),
					zIndex:parseInt(dialog.css("zIndex")) + 1
				}).show();
				$("div.dialogContent",sh).css("height",$("div.dialogContent",dialog).css("height"));
				sh.data("dialog",dialog);
				dialog.css({left:"-10000px",top:"-10000px"});
				$(".shadow").hide();        
				$(sh).jDrag({
					selector:".dialogHeader",
					stop: this.stop,
					event:event
				});
				return false;
		},
		stop:function(){
			var sh = $(arguments[0]);
			var dialog = sh.data("dialog");
			$(dialog).css({left:$(sh).css("left"),top:$(sh).css("top")});
			$(sh).hide();
		}
	}
})(jQuery);/**
 * @author ZhangHuihua@msn.com
 */
(function($){
	var _op = {
		cursor: 'move', // selector 的鼠标手势
		sortBoxs: 'div.sortDrag', //拖动排序项父容器
		replace: false, //2个sortBox之间拖动替换
		items: '> *', //拖动排序项选择器
		selector: '', //拖动排序项用于拖动的子元素的选择器，为空时等于item
		zIndex: 1000
	};
	var sortDrag = {
		start:function($sortBox, $item, event, op){
			var $placeholder = this._createPlaceholder($item);
			var $helper = $item.clone();
			var position = $item.position();
			$helper.data('$sortBox', $sortBox).data('op', op).data('$item', $item).data('$placeholder', $placeholder);
			$helper.addClass('sortDragHelper').css({position:'absolute',top:position.top+$sortBox.scrollTop(),left:position.left,zIndex:op.zIndex,width:$item.width()+'px',height:$item.height()+'px'}).jDrag({
				selector:op.selector,
				drag:this.drag,
				stop:this.stop,
				event:event
			});

			$item.before($placeholder).before($helper).hide();
			return false;
		},
		drag:function(el, event){
			var $helper = $(arguments[0]), $sortBox = $helper.data('$sortBox'), $placeholder = $helper.data('$placeholder');
			var $items = $sortBox.find($helper.data('op')['items']).filter(':visible').filter(':not(.sortDragPlaceholder, .sortDragHelper)');
			var helperPos = $helper.position(), firstPos = $items.eq(0).position();

			var $overBox = sortDrag._getOverSortBox($helper, event);
			if ($overBox.length > 0 && $overBox[0] != $sortBox[0]){ //移动到其他容器
				$placeholder.appendTo($overBox);
				$helper.data('$sortBox', $overBox);
			} else {
				for (var i=0; i<$items.length; i++) {
					var $this = $items.eq(i), position = $this.position();
		
					if (helperPos.top > position.top + 10) {
						$this.after($placeholder);
					} else if (helperPos.top <= position.top) {
						$this.before($placeholder);
						break;
					}
				}
			}
		},
		stop:function(){
			var $helper = $(arguments[0]), $sortBox = $helper.data('$sortBox'), $item = $helper.data('$item'), $placeholder = $helper.data('$placeholder');

			var position = $placeholder.position();
			$helper.animate({
					top: (position.top+$sortBox.scrollTop()) + "px",
					left: position.left + "px"
				}, 
				{
				complete: function(){
					if ($helper.data('op')['replace']){ //2个sortBox之间替换处理
						$srcBox = $item.parents(_op.sortBoxs+":first");
						$destBox = $placeholder.parents(_op.sortBoxs+":first");
						if ($srcBox[0] != $destBox[0]) { //判断是否移动到其他容器中
							$replaceItem = $placeholder.next();
							if ($replaceItem.size() > 0) {
								$replaceItem.insertAfter($item);
							}
						}
					}
					$item.insertAfter($placeholder).show();
					$placeholder.remove();
					$helper.remove();
				},
				duration: 300
			});
		},
		_createPlaceholder:function($item){
			return $('<'+$item[0].nodeName+' class="sortDragPlaceholder"/>').css({
				width:$item.outerWidth()+'px',
				height:$item.outerHeight()+'px',
				marginTop:$item.css('marginTop'),
				marginRight:$item.css('marginRight'),
				marginBottom:$item.css('marginBottom'),
				marginLeft:$item.css('marginLeft')
			});
		},
		_getOverSortBox:function($item, e){
			var itemPos = $item.position();
			var y = itemPos.top+($item.height()/2), x = itemPos.left+($item.width()/2);
			return $(_op.sortBoxs).filter(':visible').filter(function(){
				var $sortBox = $(this), sortBoxPos = $sortBox.position(),
					sortBoxH = $sortBox.height(), sortBoxW = $sortBox.width();
				return DWZ.isOver(y, x, sortBoxPos.top, sortBoxPos.left, sortBoxH, sortBoxW);
			});
		}
	};
	
	$.fn.sortDrag = function(options){
				
		return this.each(function(){
			var op = $.extend({}, _op, options);
			var $sortBox = $(this);
			
			if ($sortBox.attr('selector')) op.selector = $sortBox.attr('selector');
			$sortBox.find(op.items).each(function(i){
				var $item = $(this), $selector = $item;
				if (op.selector) {
					$selector = $item.find(op.selector).css({cursor:op.cursor});
				}

				$selector.mousedown(function(event){
					sortDrag.start($sortBox, $item, event, op);
	
					event.preventDefault();
				});
			});
			
		});
	}
})(jQuery);

/**
 * Theme Plugins
 * @author ZhangHuihua@msn.com
 */
(function($){
	$.fn.extend({
		cssTable: function(options){

			return this.each(function(){
				var $this = $(this);
				$this.find("thead [orderField]").orderBy({
					targetType: $this.attr("targetType"),
					rel:$this.attr("rel"),
					asc: $this.attr("asc") || "asc",
					desc:  $this.attr("desc") || "desc"
				});
			});
		}
	});
})(jQuery);
/**
 * 普通ajax表单提交
 * @param {Object} form
 * @param {Object} callback
 * @param {String} confirmMsg 提示确认信息
 */
function validateCallback(form, callback, confirmMsg) {
	var $form = $(form);

	if (!$form.valid()) {
		return false;
	}
	
	var _submitFn = function(){
		$.ajax({
			type: form.method || 'POST',
			url:$form.attr("action"),
			data:$form.serializeArray(),
			dataType:"json",
			cache: false,
			success: callback || DWZ.ajaxDone,
			error: DWZ.ajaxError
		});
	}
	
	if (confirmMsg) {
		alertMsg.confirm(confirmMsg, {okCall: _submitFn});
	} else {
		_submitFn();
	}
	
	return false;
}
/**
 * 带文件上传的ajax表单提交
 * @param {Object} form
 * @param {Object} callback
 */
function iframeCallback(form, callback){
	var $form = $(form), $iframe = $("#callbackframe");
	if(!$form.valid()) {return false;}

	if ($iframe.size() == 0) {
		$iframe = $("<iframe id='callbackframe' name='callbackframe' src='about:blank' style='display:none'></iframe>").appendTo("body");
	}
	if(!form.ajax) {
		$form.append('<input type="hidden" name="ajax" value="1" />');
	}
	form.target = "callbackframe";
	
	_iframeResponse($iframe[0], callback || DWZ.ajaxDone);
}
function _iframeResponse(iframe, callback){
	var $iframe = $(iframe), $document = $(document);
	
	$document.trigger("ajaxStart");
	
	$iframe.bind("load", function(event){
		$iframe.unbind("load");
		$document.trigger("ajaxStop");
		
		if (iframe.src == "javascript:'%3Chtml%3E%3C/html%3E';" || // For Safari
			iframe.src == "javascript:'<html></html>';") { // For FF, IE
			return;
		}

		var doc = iframe.contentDocument || iframe.document;

		// fixing Opera 9.26,10.00
		if (doc.readyState && doc.readyState != 'complete') return; 
		// fixing Opera 9.64
		if (doc.body && doc.body.innerHTML == "false") return;
		 
		var response;
		
		if (doc.XMLDocument) {
			// response is a xml document Internet Explorer property
			response = doc.XMLDocument;
		} else if (doc.body){
			try{
				response = $iframe.contents().find("body").text();
				response = jQuery.parseJSON(response);
			} catch (e){ // response is html document or plain text
				response = doc.body.innerHTML;
			}
		} else {
			// response is a xml document
			response = doc;
		}
		
		callback(response);
	});
}

/**
 * navTabAjaxDone是DWZ框架中预定义的表单提交回调函数．
 * 服务器转回navTabId可以把那个navTab标记为reloadFlag=1, 下次切换到那个navTab时会重新载入内容. 
 * callbackType如果是closeCurrent就会关闭当前tab
 * 只有callbackType="forward"时需要forwardUrl值
 * navTabAjaxDone这个回调函数基本可以通用了，如果还有特殊需要也可以自定义回调函数.
 * 如果表单提交只提示操作是否成功, 就可以不指定回调函数. 框架会默认调用DWZ.ajaxDone()
 * <form action="/user.do?method=save" onsubmit="return validateCallback(this, navTabAjaxDone)">
 * 
 * form提交后返回json数据结构statusCode=DWZ.statusCode.ok表示操作成功, 做页面跳转等操作. statusCode=DWZ.statusCode.error表示操作失败, 提示错误原因. 
 * statusCode=DWZ.statusCode.timeout表示session超时，下次点击时跳转到DWZ.loginUrl
 * {"statusCode":"200", "message":"操作成功", "navTabId":"navNewsLi", "forwardUrl":"", "callbackType":"closeCurrent", "rel"."xxxId"}
 * {"statusCode":"300", "message":"操作失败"}
 * {"statusCode":"301", "message":"会话超时"}
 * 
 */
function navTabAjaxDone(json){
	DWZ.ajaxDone(json);
	if (json[DWZ.keys.statusCode] == DWZ.statusCode.ok){
		if (json.navTabId){ //把指定navTab页面标记为需要“重新载入”。注意navTabId不能是当前navTab页面的
			if(!json.del){
				navTab.reloadFlag(json.navTabId);
			}
			
		} else { //重新载入当前navTab页面
			var $pagerForm = $("#pagerForm", navTab.getCurrentPanel());
			var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
			navTabPageBreak(args, json.rel);
		}
		
		if ("closeCurrent" == json.callbackType) {
			setTimeout(function(){navTab.closeCurrentTab(json.navTabId);}, 100);
		} else if ("forward" == json.callbackType) {
			if(!json.del){
				navTab.reload(json.forwardUrl);
			}else{
				$('[data-post-id="'+json.del+'"]').remove();
			}
		} else if ("forwardConfirm" == json.callbackType) {
			alertMsg.confirm(json.confirmMsg || DWZ.msg("forwardConfirmMsg"), {
				okCall: function(){
					navTab.reload(json.forwardUrl);
				},
				cancelCall: function(){
					navTab.closeCurrentTab(json.navTabId);
				}
			});
		} else {
			navTab.getCurrentPanel().find(":input[initValue]").each(function(){
				var initVal = $(this).attr("initValue");
				$(this).val(initVal);
			});
		}
	}
	if(json.callback){
		var cb = eval("("+json.callback+")");
		if(cb["type"] == "image"){
			//console.log("image");
			reloadFile();
		}
	}
}

/**
 * dialog上的表单提交回调函数
 * 当前navTab页面有pagerForm就重新加载
 * 服务器转回navTabId，可以重新载入指定的navTab. statusCode=DWZ.statusCode.ok表示操作成功, 自动关闭当前dialog
 * 
 * form提交后返回json数据结构,json格式和navTabAjaxDone一致
 */
function dialogAjaxDone(json){
	DWZ.ajaxDone(json);
	if (json[DWZ.keys.statusCode] == DWZ.statusCode.ok){
		if (json.navTabId){
			navTab.reload(json.forwardUrl, {navTabId: json.navTabId});
		} else {
			var $pagerForm = $("#pagerForm", navTab.getCurrentPanel());
			var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
			navTabPageBreak(args, json.rel);
		}
		if ("closeCurrent" == json.callbackType) {
			$.pdialog.closeCurrent();
		}
	}
	if(json.callback){
		callback = eval("("+json.callback+")");
		if(callback.type == "view"){
			$("#modal-view").find("iframe").attr("src",callback.url)
			setTimeout(function(){
				$("#modal-view").modal("show");		
			},300)
		}
		if(callback.type == "reload"){
			//setTimeout(function(){
				redirect();
			//},100)
		}	
		// if(json.callback == "module"){
		// 	module();
		// }
	}
	
}

/**
 * 处理navTab上的查询, 会重新载入当前navTab
 * @param {Object} form
 */
function navTabSearch(form, navTabId){
	var $form = $(form);
	if (form[DWZ.pageInfo.pageNum]) form[DWZ.pageInfo.pageNum].value = 1;
	navTab.reload($form.attr('action'), {data: $form.serializeArray(), navTabId:navTabId});
	return false;
}
/**
 * 处理dialog弹出层上的查询, 会重新载入当前dialog
 * @param {Object} form
 */
function dialogSearch(form){
	var $form = $(form);
	if (form[DWZ.pageInfo.pageNum]) form[DWZ.pageInfo.pageNum].value = 1;
	$.pdialog.reload($form.attr('action'), {data: $form.serializeArray()});
	return false;
}
function dwzSearch(form, targetType){
	if (targetType == "dialog") dialogSearch(form);
	else navTabSearch(form);
	return false;
}
/**
 * 处理div上的局部查询, 会重新载入指定div
 * @param {Object} form
 */
function divSearch(form, rel){
	var $form = $(form);
	if (form[DWZ.pageInfo.pageNum]) form[DWZ.pageInfo.pageNum].value = 1;
	if (rel) {
		var $box = $("#" + rel);
		$box.ajaxUrl({
			type:"POST", url:$form.attr("action"), data: $form.serializeArray()
		});
	}
	return false;
}
/**
 * 
 * @param {Object} args {pageNum:"",numPerPage:"",orderField:"",orderDirection:""}
 * @param String formId 分页表单选择器，非必填项默认值是 "pagerForm"
 */
function _getPagerForm($parent, args) {
	var form = $("#pagerForm", $parent).get(0);

	if (form) {
		if (args["pageNum"]) form[DWZ.pageInfo.pageNum].value = args["pageNum"];
		if (args["numPerPage"]) form[DWZ.pageInfo.numPerPage].value = args["numPerPage"];
		if (args["orderField"]) form[DWZ.pageInfo.orderField].value = args["orderField"];
		if (args["orderDirection"] && form[DWZ.pageInfo.orderDirection]) form[DWZ.pageInfo.orderDirection].value = args["orderDirection"];
	}
	
	return form;
}


/**
 * 处理navTab中的分页和排序
 * targetType: navTab 或 dialog
 * rel: 可选 用于局部刷新div id号
 * data: pagerForm参数 {pageNum:"n", numPerPage:"n", orderField:"xxx", orderDirection:""}
 * callback: 加载完成回调函数
 */
function dwzPageBreak(options){
	var op = $.extend({ targetType:"navTab", rel:"", data:{pageNum:"", numPerPage:"", orderField:"", orderDirection:""}, callback:null}, options);
	var $parent = op.targetType == "dialog" ? $.pdialog.getCurrent() : navTab.getCurrentPanel();

	if (op.rel) {
		var $box = $parent.find("#" + op.rel);
		var form = _getPagerForm($box, op.data);
		if (form) {
			$box.ajaxUrl({
				type:"POST", url:$(form).attr("action"), data: $(form).serializeArray()
			});
		}
	} else {
		var form = _getPagerForm($parent, op.data);
		var params = $(form).serializeArray();
		
		if (op.targetType == "dialog") {
			if (form) $.pdialog.reload($(form).attr("action"), {data: params, callback: op.callback});
		} else {
			if (form) navTab.reload($(form).attr("action"), {data: params, callback: op.callback});
		}
	}
}
/**
 * 处理navTab中的分页和排序
 * @param args {pageNum:"n", numPerPage:"n", orderField:"xxx", orderDirection:""}
 * @param rel： 可选 用于局部刷新div id号
 */
function navTabPageBreak(args, rel){
	dwzPageBreak({targetType:"navTab", rel:rel, data:args});
}
/**
 * 处理dialog中的分页和排序
 * 参数同 navTabPageBreak 
 */
function dialogPageBreak(args, rel){
	dwzPageBreak({targetType:"dialog", rel:rel, data:args});
}


function ajaxTodo(url, callback){
	var $callback = callback || navTabAjaxDone;
	if (! $.isFunction($callback)) $callback = eval('(' + callback + ')');
	$.ajax({
		type:'POST',
		url:url,
		dataType:"json",
		cache: false,
		success: $callback,
		error: DWZ.ajaxError
	});
}

/**
 * http://www.uploadify.com/documentation/uploadify/onqueuecomplete/  
 */
function uploadifyQueueComplete(queueData){

	var msg = "The total number of files uploaded: "+queueData.uploadsSuccessful+"<br/>"
		+ "The total number of errors while uploading: "+queueData.uploadsErrored+"<br/>"
		+ "The total number of bytes uploaded: "+queueData.queueBytesUploaded+"<br/>"
		+ "The average speed of all uploaded files: "+queueData.averageSpeed;
	
	if (queueData.uploadsErrored) {
		alertMsg.error(msg);
	} else {
		alertMsg.correct(msg);
	}
}
/**
 * http://www.uploadify.com/documentation/uploadify/onuploadsuccess/
 */
function uploadifySuccess(file, data, response){
	alert(data)
}

/**
 * http://www.uploadify.com/documentation/uploadify/onuploaderror/
 */
function uploadifyError(file, errorCode, errorMsg) {
	alertMsg.error(errorCode+": "+errorMsg);
}


/**
 * http://www.uploadify.com/documentation/
 * @param {Object} event
 * @param {Object} queueID
 * @param {Object} fileObj
 * @param {Object} errorObj
 */
function uploadifyError(event, queueId, fileObj, errorObj){
	alert("event:" + event + "\nqueueId:" + queueId + "\nfileObj.name:" 
		+ fileObj.name + "\nerrorObj.type:" + errorObj.type + "\nerrorObj.info:" + errorObj.info);
}


$.fn.extend({
	ajaxTodo:function(){
		return this.each(function(){
			var $this = $(this);
			$this.click(function(event){
				if ($this.hasClass('disabled')) {
					return false;
				}
				
				var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
				DWZ.debug(url);
				if (!url.isFinishedTm()) {
					alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
					return false;
				}
				var title = $this.attr("title");
				if (title) {
					alertMsg.confirm(title, {
						okCall: function(){
							ajaxTodo(url, $this.attr("callback"));
						}
					});
				} else {
					ajaxTodo(url, $this.attr("callback"));
				}
				event.preventDefault();
			});
		});
	},
	dwzExport: function(){
		function _doExport($this) {
			var $p = $this.attr("targetType") == "dialog" ? $.pdialog.getCurrent() : navTab.getCurrentPanel();
			var $form = $("#pagerForm", $p);
			var url = $this.attr("href");
			//window.location = url+(url.indexOf('?') == -1 ? "?" : "&")+$form.serialize();
			var $iframe = $("#callbackframe");
			if ($iframe.size() == 0) {
				$iframe = $("<iframe id='callbackframe' name='callbackframe' src='about:blank' style='display:none'></iframe>").appendTo("body");
			}

			form.target = "callbackframe";
			
		}
		
		return this.each(function(){
			var $this = $(this);
			$this.click(function(event){
				var title = $this.attr("title");
				if (title) {
					alertMsg.confirm(title, {
						okCall: function(){_doExport($this);}
					});
				} else {_doExport($this);}
			
				event.preventDefault();
			});
		});
	}
});

/**
 * 
 * @author ZhangHuihua@msn.com
 * @param {Object} opts Several options
 */
(function($){
	$.fn.extend({
		pagination: function(opts){
			var setting = {
				first$:"li.j-first", firstDot$:"li.j-first-dot", prev$:"li.j-prev", next$:"li.j-next", last$:"li.j-last", nums$:"li.j-num>a", select$:"li.j-select",
				pageNumFrag:'<li class="#liClass#"><a href="javascript:;">#pageNum#</a></li>',
				pageSelect:'<option value="#pageNum#"#selected#>#pageNum#/#pageNumAll#</options>'
			};
			return this.each(function(){
				var $this = $(this);
				var pc = new Pagination(opts);
				var interval = pc.getInterval();
				var interval2 = pc.getIntervalAll();
				var pageNumFrag = '';
				for (var i=interval.start; i<interval.end;i++){
					pageNumFrag += setting.pageNumFrag.replaceAll("#pageNum#", i).replaceAll("#liClass#", i==pc.getCurrentPage() ? 'active j-num' : 'j-num');
				}
				//$this.html(DWZ.frag["pagination"]
				var pageSelect = '';
				for (var a=interval2.start; a<interval2.end;a++){
					pageSelect += setting.pageSelect.replaceAll("#pageNum#", a).replaceAll("#pageNumAll#", pc.numPages()).replaceAll("#selected#", a==pc.getCurrentPage() ? ' selected' : '');
				}
				$this.html(DWZ.frag["pagination"].replaceAll("#pageSelect#", pageSelect).replaceAll("#pageNumFrag#", pageNumFrag).replaceAll("#currentPage#", pc.getCurrentPage()));

				var $first = $this.find(setting.first$);
				var $prev = $this.find(setting.prev$);
				var $next = $this.find(setting.next$);
				var $last = $this.find(setting.last$);
				
				if (pc.hasPrev()){
					$first.add($prev);
					_bindEvent($prev, pc.getCurrentPage()-1, pc.targetType(), pc.rel());
					_bindEvent($first, 1, pc.targetType(), pc.rel());
				}else{
					$prev.add($first).addClass("disabled");
				}
			
				if (pc.hasNext()) {
					$next.add($last);
					_bindEvent($next, pc.getCurrentPage()+1, pc.targetType(), pc.rel());
					_bindEvent($last, pc.numPages(), pc.targetType(), pc.rel());
				} else {
					$next.add($last).addClass("disabled");
				}
	
				$this.find(setting.nums$).each(function(i){
					_bindEvent($(this), i+interval.start, pc.targetType(), pc.rel());
				});
				$this.find(setting.select$).each(function(){
					var $this = $(this);
					var $select = $this.find("select");
					$select.addClass("bs-select");
					$select.selectpicker({width:"5em"});
					$select.on("change",function(event){
						var pageNum = $select.val();
						if (pageNum && pageNum.isPositiveInteger()) {
							dwzPageBreak({targetType:pc.targetType(), rel:pc.rel(), data: {pageNum:pageNum}});
						}
					});
				});
			});
			
			function _bindEvent($target, pageNum, targetType, rel){
				$target.bind("click", {pageNum:pageNum}, function(event){
					dwzPageBreak({targetType:targetType, rel:rel, data:{pageNum:event.data.pageNum}});
					event.preventDefault();
				});
			}
		},
		
		orderBy: function(options){
			var op = $.extend({ targetType:"navTab", rel:"", asc:"asc", desc:"desc"}, options);
			return this.each(function(){
				var $this = $(this).css({cursor:"pointer"}).click(function(){
					var orderField = $this.attr("orderField");
					var orderDirection = $this.hasClass(op.asc) ? op.desc : op.asc;
					dwzPageBreak({targetType:op.targetType, rel:op.rel, data:{orderField: orderField, orderDirection: orderDirection}});
				});
				
			});
		},
		pagerForm: function(options){
			var op = $.extend({pagerForm$:"#pagerForm", parentBox:document}, options);
			var frag = '<input type="hidden" name="#name#" value="#value#" />';
			return this.each(function(){
				var $searchForm = $(this), $pagerForm = $(op.pagerForm$, op.parentBox);
				var actionUrl = $pagerForm.attr("action").replaceAll("#rel#", $searchForm.attr("action"));
				$pagerForm.attr("action", actionUrl);
				$searchForm.find(":input").each(function(){
					var $input = $(this), name = $input.attr("name");
					if (name && (!$input.is(":checkbox,:radio") || $input.is(":checked"))){
						if ($pagerForm.find(":input[name='"+name+"']").length == 0) {
							var inputFrag = frag.replaceAll("#name#", name).replaceAll("#value#", $input.val());
							$pagerForm.append(inputFrag);
						}
					}
				});
			});
		}
	});
	
	var Pagination = function(opts) {
		this.opts = $.extend({
			targetType:"navTab",  // navTab, dialog
			rel:"", //用于局部刷新div id号
			totalCount:0,
			numPerPage:10,
			pageNumShown:10,
			currentPage:1,
			callback:function(){return false;}
		}, opts);
	}
	
	$.extend(Pagination.prototype, {
		targetType:function(){return this.opts.targetType},
		rel:function(){return this.opts.rel},
		numPages:function() {
			return Math.ceil(this.opts.totalCount/this.opts.numPerPage);
		},
		getInterval:function(){
			var ne_half = Math.ceil(this.opts.pageNumShown/2);
			var np = this.numPages();
			var upper_limit = np - this.opts.pageNumShown;
			var start = this.getCurrentPage() > ne_half ? Math.max( Math.min(this.getCurrentPage() - ne_half, upper_limit), 0 ) : 0;
			var end = this.getCurrentPage() > ne_half ? Math.min(this.getCurrentPage()+ne_half, np) : Math.min(this.opts.pageNumShown, np);
			return {start:start+1, end:end+1};
		},
		getIntervalAll:function(){
			var start = 0
			var end = this.numPages();
			return {start:start+1, end:end+1};
		},
		getCurrentPage:function(){
			var currentPage = parseInt(this.opts.currentPage);
			if (isNaN(currentPage)) return 1;
			return currentPage;
		},
		hasPrev:function(){
			return this.getCurrentPage() > 1;
		},
		hasNext:function(){
			return this.getCurrentPage() < this.numPages();
		}
	});
})(jQuery);
/**
 * @author ZhangHuihua@msn.com
 */
(function($){
	var _lookup = {currentGroup:"", suffix:"", $target:null, pk:"id"};
	var _util = {
		_lookupPrefix: function(key){
			var strDot = _lookup.currentGroup ? "." : "";
			return _lookup.currentGroup + strDot + key + _lookup.suffix;
		},
		lookupPk: function(key){
			return this._lookupPrefix(key);
		},
		lookupField: function(key){
			return this.lookupPk(key);
		}
	};
	
	$.extend({
		bringBackSuggest: function(args){
			var $box = _lookup['$target'].parents(".unitBox:first");
			$box.find(":input").each(function(){
				var $input = $(this), inputName = $input.attr("name");
				
				for (var key in args) {
					var name = (_lookup.pk == key) ? _util.lookupPk(key) : _util.lookupField(key);

					if (name == inputName) {
						$input.val(args[key]);
						break;
					}
				}
			});
		},
		bringBack: function(args){
			$.bringBackSuggest(args);
			$.pdialog.closeCurrent();
		}
	});
	
	$.fn.extend({
		lookup: function(){
			return this.each(function(){
				var $this = $(this), options = {mask:true, 
					width:$this.attr('width')||820, height:$this.attr('height')||400,
					maxable:eval($this.attr("maxable") || "true"),
					resizable:eval($this.attr("resizable") || "true")
				};
				$this.click(function(event){
					_lookup = $.extend(_lookup, {
						currentGroup: $this.attr("lookupGroup") || "",
						suffix: $this.attr("suffix") || "",
						$target: $this,
						pk: $this.attr("lookupPk") || "id"
					});
					
					var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
					if (!url.isFinishedTm()) {
						alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
						return false;
					}
					
					$.pdialog.open(url, "_blank", $this.attr("title") || $this.text(), options);
					return false;
				});
			});
		},
		multLookup: function(){
			return this.each(function(){
				var $this = $(this), args={};
				$this.click(function(event){
					var $unitBox = $this.parents(".unitBox:first");
					$unitBox.find("[name='"+$this.attr("multLookup")+"']").filter(":checked").each(function(){
						var _args = DWZ.jsonEval($(this).val());
						for (var key in _args) {
							var value = args[key] ? args[key]+"," : "";
							args[key] = value + _args[key];
						}
					});

					if ($.isEmptyObject(args)) {
						alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
						return false;
					}
					$.bringBack(args);
				});
			});
		},
		suggest: function(){
			var op = {suggest$:"#suggest", suggestShadow$: "#suggestShadow"};
			var selectedIndex = -1;
			return this.each(function(){
				var $input = $(this).attr('autocomplete', 'off').keydown(function(event){
					if (event.keyCode == DWZ.keyCode.ENTER && $(op.suggest$).is(':visible')) return false; //屏蔽回车提交
				});
				
				var suggestFields=$input.attr('suggestFields').split(",");
				
				function _show(event){
					var offset = $input.offset();
					var iTop = offset.top+this.offsetHeight;
					var $suggest = $(op.suggest$);
					if ($suggest.size() == 0) $suggest = $('<div id="suggest"></div>').appendTo($('body'));

					$suggest.css({
						left:offset.left+'px',
						top:iTop+'px'
					}).show();
					
					_lookup = $.extend(_lookup, {
						currentGroup: $input.attr("lookupGroup") || "",
						suffix: $input.attr("suffix") || "",
						$target: $input,
						pk: $input.attr("lookupPk") || "id"
					});

					var url = unescape($input.attr("suggestUrl")).replaceTmById($(event.target).parents(".unitBox:first"));
					if (!url.isFinishedTm()) {
						alertMsg.error($input.attr("warn") || DWZ.msg("alertSelectMsg"));
						return false;
					}
					
					var postData = {};
					postData[$input.attr("postField")||"inputValue"] = $input.val();

					$.ajax({
						global:false,
						type:'POST', dataType:"json", url:url, cache: false,
						data: postData,
						success: function(response){
							if (!response) return;
							var html = '';

							$.each(response, function(i){
								var liAttr = '', liLabel = '';
								
								for (var i=0; i<suggestFields.length; i++){
									var str = this[suggestFields[i]];
									if (str) {
										if (liLabel) liLabel += '-';
										liLabel += str;
									}
								}
								for (var key in this) {
									if (liAttr) liAttr += ',';
									liAttr += key+":'"+this[key]+"'";
								}
								html += '<li lookupAttrs="'+liAttr+'">' + liLabel + '</li>';
							});
							
							var $lis = $suggest.html('<ul>'+html+'</ul>').find("li");
							$lis.hoverClass("selected").click(function(){
								_select($(this));
							});
							if ($lis.size() == 1 && event.keyCode != DWZ.keyCode.BACKSPACE) {
								_select($lis.eq(0));
							} else if ($lis.size() == 0){
								var jsonStr = "";
								for (var i=0; i<suggestFields.length; i++){
									if (_util.lookupField(suggestFields[i]) == event.target.name) {
										break;
									}
									if (jsonStr) jsonStr += ',';
									jsonStr += suggestFields[i]+":''";
								}
								jsonStr = "{"+_lookup.pk+":''," + jsonStr +"}";
								$.bringBackSuggest(DWZ.jsonEval(jsonStr));
							}
						},
						error: function(){
							$suggest.html('');
						}
					});

					$(document).bind("click", _close);
					return false;
				}
				function _select($item){
					var jsonStr = "{"+ $item.attr('lookupAttrs') +"}";
					
					$.bringBackSuggest(DWZ.jsonEval(jsonStr));
				}
				function _close(){
					$(op.suggest$).html('').hide();
					selectedIndex = -1;
					$(document).unbind("click", _close);
				}
				
				$input.focus(_show).click(false).keyup(function(event){
					var $items = $(op.suggest$).find("li");
					switch(event.keyCode){
						case DWZ.keyCode.ESC:
						case DWZ.keyCode.TAB:
						case DWZ.keyCode.SHIFT:
						case DWZ.keyCode.HOME:
						case DWZ.keyCode.END:
						case DWZ.keyCode.LEFT:
						case DWZ.keyCode.RIGHT:
							break;
						case DWZ.keyCode.ENTER:
							_close();
							break;
						case DWZ.keyCode.DOWN:
							if (selectedIndex >= $items.size()-1) selectedIndex = -1;
							else selectedIndex++;
							break;
						case DWZ.keyCode.UP:
							if (selectedIndex < 0) selectedIndex = $items.size()-1;
							else selectedIndex--;
							break;
						default:
							_show(event);
					}
					$items.removeClass("selected");
					if (selectedIndex>=0) {
						var $item = $items.eq(selectedIndex).addClass("selected");
						_select($item);
					}
				});
			});
		},
		
		itemDetail: function(){
			return this.each(function(){
				var $table = $(this).css("clear","both"), $tbody = $table.find("tbody");
				var fields=[];

				$table.find("tr:first th[type]").each(function(i){
					var $th = $(this);
					var field = {
						type: $th.attr("type") || "text",
						patternDate: $th.attr("dateFmt") || "yyyy-MM-dd",
						name: $th.attr("name") || "",
						defaultVal: $th.attr("defaultVal") || "",
						size: $th.attr("size") || "12",
						enumUrl: $th.attr("enumUrl") || "",
						lookupGroup: $th.attr("lookupGroup") || "",
						lookupUrl: $th.attr("lookupUrl") || "",
						lookupPk: $th.attr("lookupPk") || "id",
						suggestUrl: $th.attr("suggestUrl"),
						suggestFields: $th.attr("suggestFields"),
						postField: $th.attr("postField") || "",
						fieldClass: $th.attr("fieldClass") || "",
						fieldAttrs: $th.attr("fieldAttrs") || ""
					};
					fields.push(field);
				});
				
				$tbody.find("a.btnDel").click(function(){
					var $btnDel = $(this);
					
					if ($btnDel.is("[href^=javascript:]")){
						$btnDel.parents("tr:first").remove();
						initSuffix($tbody);
						return false;
					}
					
					function delDbData(){
						$.ajax({
							type:'POST', dataType:"json", url:$btnDel.attr('href'), cache: false,
							success: function(){
								$btnDel.parents("tr:first").remove();
								initSuffix($tbody);
							},
							error: DWZ.ajaxError
						});
					}
					
					if ($btnDel.attr("title")){
						alertMsg.confirm($btnDel.attr("title"), {okCall: delDbData});
					} else {
						delDbData();
					}
					
					return false;
				});

				var addButTxt = $table.attr('addButton') || "Add New";
				if (addButTxt) {
					var $addBut = $('<div class="button"><div class="buttonContent"><button type="button">'+addButTxt+'</button></div></div>').insertBefore($table).find("button");
					var $rowNum = $('<input type="text" name="dwz_rowNum" class="textInput" style="margin:2px;" value="1" size="2"/>').insertBefore($table);
					
					var trTm = "";
					$addBut.click(function(){
						if (! trTm) trTm = trHtml(fields);
						var rowNum = 1;
						try{rowNum = parseInt($rowNum.val())} catch(e){}

						for (var i=0; i<rowNum; i++){
							var $tr = $(trTm);
							$tr.appendTo($tbody).initUI().find("a.btnDel").click(function(){
								$(this).parents("tr:first").remove();
								initSuffix($tbody);
								return false;
							});
						}
						initSuffix($tbody);
					});
				}
			});
			
			/**
			 * 删除时重新初始化下标
			 */
			function initSuffix($tbody) {
				$tbody.find('>tr').each(function(i){
					$(':input, a.btnLook, a.btnAttach', this).each(function(){
						var $this = $(this), name = $this.attr('name'), val = $this.val();

						if (name) $this.attr('name', name.replaceSuffix(i));
						
						var lookupGroup = $this.attr('lookupGroup');
						if (lookupGroup) {$this.attr('lookupGroup', lookupGroup.replaceSuffix(i));}
						
						var suffix = $this.attr("suffix");
						if (suffix) {$this.attr('suffix', suffix.replaceSuffix(i));}
						
						if (val && val.indexOf("#index#") >= 0) $this.val(val.replace('#index#',i+1));
					});
				});
			}
			
			function tdHtml(field){
				var html = '', suffix = '';
				
				if (field.name.endsWith("[#index#]")) suffix = "[#index#]";
				else if (field.name.endsWith("[]")) suffix = "[]";
				
				var suffixFrag = suffix ? ' suffix="' + suffix + '" ' : '';
				
				var attrFrag = '';
				if (field.fieldAttrs){
					var attrs = DWZ.jsonEval(field.fieldAttrs);
					for (var key in attrs) {
						attrFrag += key+'="'+attrs[key]+'"';
					}
				}
				switch(field.type){
					case 'del':
						html = '<a href="javascript:void(0)" class="btnDel '+ field.fieldClass + '">删除</a>';
						break;
					case 'lookup':
						var suggestFrag = '';
						if (field.suggestFields) {
							suggestFrag = 'autocomplete="off" lookupGroup="'+field.lookupGroup+'"'+suffixFrag+' suggestUrl="'+field.suggestUrl+'" suggestFields="'+field.suggestFields+'"' + ' postField="'+field.postField+'"';
						}

						html = '<input type="hidden" name="'+field.lookupGroup+'.'+field.lookupPk+suffix+'"/>'
							+ '<input type="text" name="'+field.name+'"'+suggestFrag+' lookupPk="'+field.lookupPk+'" size="'+field.size+'" class="'+field.fieldClass+'"/>'
							+ '<a class="btnLook" href="'+field.lookupUrl+'" lookupGroup="'+field.lookupGroup+'" '+suggestFrag+' lookupPk="'+field.lookupPk+'" title="查找带回">查找带回</a>';
						break;
					case 'attach':
						html = '<input type="hidden" name="'+field.lookupGroup+'.'+field.lookupPk+suffix+'"/>'
							+ '<input type="text" name="'+field.name+'" size="'+field.size+'" readonly="readonly" class="'+field.fieldClass+'"/>'
							+ '<a class="btnAttach" href="'+field.lookupUrl+'" lookupGroup="'+field.lookupGroup+'" '+suffixFrag+' lookupPk="'+field.lookupPk+'" width="560" height="300" title="查找带回">查找带回</a>';
						break;
					case 'enum':
						$.ajax({
							type:"POST", dataType:"html", async: false,
							url:field.enumUrl, 
							data:{inputName:field.name}, 
							success:function(response){
								html = response;
							}
						});
						break;
					case 'date':
						html = '<input type="text" name="'+field.name+'" value="'+field.defaultVal+'" class="date '+field.fieldClass+'" dateFmt="'+field.patternDate+'" size="'+field.size+'"/>'
							+'<a class="inputDateButton" href="javascript:void(0)">选择</a>';
						break;
					default:
						html = '<input type="'+field.type+'" name="'+field.name+'" value="'+field.defaultVal+'" size="'+field.size+'" class="'+field.fieldClass+'" '+attrFrag+'/>';
						break;
				}
				return '<td>'+html+'</td>';
			}
			function trHtml(fields){
				var html = '';
				$(fields).each(function(){
					html += tdHtml(this);
				});
				return '<tr class="unitBox">'+html+'</tr>';
			}
		},
		
		selectedTodo: function(){
			
			function _getIds(selectedIds, targetType){
				var ids = "";
				var $box = targetType == "dialog" ? $.pdialog.getCurrent() : navTab.getCurrentPanel();
				$box.find("input:checked").filter("[name='"+selectedIds+"']").each(function(i){
					var val = $(this).val();
					ids += i==0 ? val : ","+val;
				});
				return ids;
			}
			return this.each(function(){
				var $this = $(this);
				var selectedIds = $this.attr("rel") || "ids";
				var postType = $this.attr("postType") || "map";

				$this.click(function(){
					var targetType = $this.attr("targetType");
					var ids = _getIds(selectedIds, targetType);
					if (!ids) {
						alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
						return false;
					}
					
					var _callback = $this.attr("callback") || (targetType == "dialog" ? dialogAjaxDone : navTabAjaxDone);
					if (! $.isFunction(_callback)) _callback = eval('(' + _callback + ')');
					
					function _doPost(){
						$.ajax({
							type:'POST', url:$this.attr('href'), dataType:'json', cache: false,
							data: function(){
								if (postType == 'map'){
									return $.map(ids.split(','), function(val, i) {
										return {name: selectedIds, value: val};
									})
								} else {
									var _data = {};
									_data[selectedIds] = ids;
									return _data;
								}
							}(),
							success: _callback,
							error: DWZ.ajaxError
						});
					}
					var title = $this.attr("title");
					if (title) {
						alertMsg.confirm(title, {okCall: _doPost});
					} else {
						_doPost();
					}
					return false;
				});
				
			});
		}
	});
})(jQuery);
 
(function($){
	$.extend($.fn, {
		jBlindUp: function(options){
			var op = $.extend({duration: 500, easing: "swing", call: function(){}}, options);
			return this.each(function(){
				var $this = $(this);
				$(this).animate({height: 0}, {
					step: function(){},
					duration: op.duration,
					easing: op.easing,
					complete: function(){ 
						$this.css({display: "none"});
						op.call();
					}
				});
			});
		},
		jBlindDown: function(options){
			var op = $.extend({to:0, duration: 500,easing: "swing",call: function(){}}, options);
			return this.each(function(){
				var $this = $(this);
				var fixedPanelHeight = (op.to > 0)?op.to:$.effect.getDimensions($this[0]).height;
				$this.animate({height: fixedPanelHeight}, {
					step: function(){},
					duration: op.duration,
					easing: op.easing,
					complete: function(){ 
					$this.css({display: ""});
					op.call(); }
				});
			});
		},
		jSlideUp:function(options) {
			var op = $.extend({to:0, duration: 500,easing: "swing",call: function(){}}, options);
			return this.each(function(){
				var $this = $(this);
				$this.wrapInner("<div></div>");
				var fixedHeight = (op.to > 0)?op.to:$.effect.getDimensions($(">div",$this)[0]).height;
				$this.css({overflow:"visible",position:"relative"});
				$(">div",$this).css({position:"relative"}).animate({top: -fixedHeight}, {
					easing: op.easing,
					duration: op.duration,
					complete:function(){$this.html($(this).html());}
				});
				
			});
		},
		jSlideDown:function(options) {
			var op = $.extend({to:0, duration: 500,easing: "swing",call: function(){}}, options);
			return this.each(function(){
				var $this = $(this);
				var fixedHeight = (op.to > 0)?op.to:$.effect.getDimensions($this[0]).height;
				$this.wrapInner("<div style=\"top:-" + fixedHeight + "px;\"></div>");
				$this.css({overflow:"visible",position:"relative", height:"0px"})
				.animate({height: fixedHeight}, {
					duration: op.duration,
					easing: op.easing,
					complete: function(){  $this.css({display: "", overflow:""}); op.call(); }
				});
				$(">div",$this).css({position:"relative"}).animate({top: 0}, {
					easing: op.easing,
					duration: op.duration,
					complete:function(){$this.html($(this).html());}
				});
			});
		}
	});
	$.effect = {
		getDimensions: function(element, displayElement){
			var dimensions = new $.effect.Rectangle;
			var displayOrig = $(element).css('display');
			var visibilityOrig = $(element).css('visibility');
			var isZero = $(element).height()==0?true:false;
			if ($(element).is(":hidden")) {
				$(element).css({visibility: 'hidden', display: 'block'});
				if(isZero)$(element).css("height","");
				if ($.browser.opera)
					refElement.focus();
			}
			dimensions.height = $(element).outerHeight();
			dimensions.width = $(element).outerWidth();
			if (displayOrig == 'none'){
				$(element).css({visibility: visibilityOrig, display: 'none'});
				if(isZero) if(isZero)$(element).css("height","0px");
			}
			return dimensions;
		}
	}
	$.effect.Rectangle = function(){
		this.width = 0;
		this.height = 0;
		this.unit = "px";
	}
})(jQuery);
/**
 * @author Roger Wu
 * @version 1.0
 */
(function($){
	$.extend($.fn, {
		jPanel:function(options){
			var op = $.extend({header:"panelHeader", headerC:"panelHeaderContent", content:"panelContent", coll:"collapsable", exp:"expandable", footer:"panelFooter", footerC:"panelFooterContent"}, options);
			return this.each(function(){
				var $panel = $(this);
				var close = $panel.hasClass("close-m");
				var collapse = $panel.hasClass("collapse");
				
				var $content = $(">div", $panel).addClass(op.content);        
				var title = $(">h1",$panel).wrap('<div class="'+op.header+'"><div class="'+op.headerC+'"></div></div>');
				if(collapse)$("<a href=\"\"></a>").addClass(close?op.exp:op.coll).insertAfter(title);

				var header = $(">div:first", $panel);
				var footer = $('<div class="'+op.footer+'"><div class="'+op.footerC+'"></div></div>').appendTo($panel);
				
				var defaultH = $panel.attr("defH")?$panel.attr("defH"):0;
				var minH = $panel.attr("minH")?$panel.attr("minH"):0;
				if (close) 
					$content.css({
						height: "0px",
						display: "none"
					});
				else {
					if (defaultH > 0) 
						$content.height(defaultH + "px");
					else if(minH > 0){
						$content.css("minHeight", minH+"px");
					}
				}
				if(!collapse) return;
				var $pucker = $("a", header);
				var inH = $content.innerHeight() - 6;
				if(minH > 0 && minH >= inH) defaultH = minH;
				else defaultH = inH;
				$pucker.click(function(){
					if($pucker.hasClass(op.exp)){
						$content.jBlindDown({to:defaultH,call:function(){
							$pucker.removeClass(op.exp).addClass(op.coll);
							if(minH > 0) $content.css("minHeight",minH+"px");
						}});
					} else { 
						if(minH > 0) $content.css("minHeight","");
						if(minH >= inH) $content.css("height", minH+"px");
						$content.jBlindUp({call:function(){
							$pucker.removeClass(op.coll).addClass(op.exp);
						}});
					}
					return false;
				});
			});
		}
	});
})(jQuery);     

var IEversion = detectIE();
function detectIE() {
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf('MSIE');
	if (msie > 0) {
		return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
	}

	var trident = ua.indexOf('Trident/');
	if (trident > 0) {
		var rv = ua.indexOf('rv:');
		return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
	}

	var edge = ua.indexOf('Edge/');
	if (edge > 0) {
		return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
	}

	return false;
}
(function($){

	$.extend({
		
		History: {
			_hash: new Array(),
			_cont: undefined,
			_currentHash: "",
			_callback: undefined,
			init: function(cont, callback){
				$.History._cont = cont;
				$.History._callback = callback;
				var current_hash = location.hash.replace(/\?.*$/, '');
				$.History._currentHash = current_hash;
				
				
				if (IEversion) {
					if ($.History._currentHash == '') {
						$.History._currentHash = '#';
					}
					$("body").append('<iframe id="jQuery_history" style="display: none;" src="about:blank"></iframe>');
					var ihistory = $("#jQuery_history")[0];
					var iframe = ihistory.contentDocument || ihistory.contentWindow.document;
					iframe.open();
					iframe.close();
					iframe.location.hash = current_hash;
				}
				if ($.isFunction(this._callback)) 
					$.History._callback(current_hash.skipChar("#"));
				setInterval($.History._historyCheck, 100);
			},
			_historyCheck: function(){
				var current_hash = "";
				if (IEversion) {
					var ihistory = $("#jQuery_history")[0];
					var iframe = ihistory.contentWindow;
					current_hash = iframe.location.hash.skipChar("#").replace(/\?.*$/, '');
				} else {
					current_hash = location.hash.skipChar('#').replace(/\?.*$/, '');
				}
			
				if (current_hash != $.History._currentHash) {
					$.History._currentHash = current_hash;
					$.History.loadHistory(current_hash);
				}
				
			},
			addHistory: function(hash, fun, args){
				$.History._currentHash = hash;
				var history = [hash, fun, args];
				$.History._hash.push(history);
				if (IEversion) {
					var ihistory = $("#jQuery_history")[0];
					var iframe = ihistory.contentDocument || ihistory.contentWindow.document;
					iframe.open();
					iframe.close();
					iframe.location.hash = hash.replace(/\?.*$/, '');
					location.hash = hash.replace(/\?.*$/, '');
				} else {
					location.hash = hash.replace(/\?.*$/, '');
				}
			},
			loadHistory: function(hash){
				if (IEversion) {
					location.hash = hash;
				}
				for (var i = 0; i < $.History._hash.length; i += 1) {
					if ($.History._hash[i][0] == hash) {
						$.History._hash[i][1]($.History._hash[i][2]);
						return;
					}
				}
			}
		}
	});
})(jQuery);
/**
 * @author ZhangHuihua@msn.com
 * 
 */
(function($){
	$.printBox = function(rel){
		var _printBoxId = 'printBox';
		var $contentBox = rel ? $('#'+rel) : $("body"),
			$printBox = $('#'+_printBoxId);
			
		if ($printBox.size()==0){
			$printBox = $('<div id="'+_printBoxId+'"></div>').appendTo("body");
		}
		window.print();

	}

})(jQuery);
$(function(){
	var top = {};
	$("#sidebar").find("a[href^=#menu]").each(function(e,k){
			top[e] = $(this).parent().offset().top;
	});
	//console.log(top);
	/*$("#sidebar .menu-container").scroll(function(){
		var t = $(this).scrollTop();
		$(this).find("a[href^=#menu]").each(function(e,k){
				if(t > top[e]-107-(32*e)){
						$(this).css({top:t-(top[e]-107)+(32*e)})
				}else{
						$(this).css({top:0})
				}

		})
	})*/
	$("#sidebar a[href^=#menu]").on("click",function(){
		var sr = $("#sidebar .menu-container").scrollTop();
		var i = $(this).data("index");
		if(sr == top[i]-107-(32*i)){
			i = i+1;
			$("#sidebar .menu-container").animate({scrollTop:top[i]-107-(32*i)},350)
		}else{			
			$("#sidebar .menu-container").animate({scrollTop:top[i]-107-(32*i)},350)
		}
	});
	//左侧导航加选中状态
	$('.main-menu li>ul>li>a').click(function() {
		$('.main-menu li>ul>li>a').removeClass('active');
		$(this).addClass('active');
	})
	$(document).on('click','[tabid]',function() {
		var id = $(this).attr('tabid');
		$('.main-menu li>ul>li>a').removeClass('active');
		$('.main-menu').find('[rel="'+id+'"]').addClass('active');
	})
})
countObj = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key) && obj[key] != null) size++;
    }
    return size;
  };
  
function fileSize(b){
  $bytes = b;
  if ($bytes >= 1073741824) {
      $bytes = parseFloat($bytes / 1073741824).toFixed(1) + 'GB';
  }else if ($bytes >= 100000){
      $bytes = parseFloat($bytes / 1048576).toFixed(1) + 'MB';
  }else if ($bytes >= 1024){
      $bytes = parseFloat($bytes / 1024).toFixed(1) + 'KB';
  }else if ($bytes > 1){
      $bytes = $bytes + 'B';
  }else if ($bytes == 1){
      $bytes = $bytes + 'B';
  }else{
      $bytes = '0B';
  }
  return $bytes;
}

