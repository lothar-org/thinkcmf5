/*头部导航*/
$('.nav_item ').hover(function() {
	$(this).addClass('active1');
	$(this).find('.nav_second_item').show();
}, function() {
	$(this).removeClass('active1');
	$(this).find('.nav_second_item').hide();
});


// 关于我们手机端显示问题
if(screen.width<768){
		$('.about_btn_span').on('click',function(){
		if($('.about_us_list').css('display') == "none"){
			$('.about_us_list').css('display','inline-block')
		}else{
			$('.about_us_list').css('display','none')
		}
	})

	$(document).mouseup(function(e){
		var _con = $(' .about_us_list ');   // 设置目标区域
		if(!_con.is(e.target) && _con.has(e.target).length === 0){ // Mark 1
			$('.about_us_list').css('display','none')
		}
	});
}
/**
 * 查看图片对话框
 * @param img 图片地址
 */
function imagePreviewDialog(img) {
    Wind.css('layer');

    Wind.use("layer", function () {
        layer.photos({
            photos: {
                "title": "", //相册标题
                "id": 'image_preview', //相册id
                "start": 0, //初始显示的图片序号，默认0
                "data": [   //相册包含的图片，数组格式
                    {
                        "alt": "",
                        "pid": 666, //图片id
                        "src": img, //原图地址
                        "thumb": img //缩略图地址
                    }
                ]
            } //格式见API文档手册页
            , anim: 5, //0-6的选择，指定弹出图片动画类型，默认随机
            shadeClose: true,
            // skin: 'layui-layer-nobg',
            shade: [0.5, '#000000'],
            shadeClose: true,
        })
    });
}

/*图片预览*/
$('.demonstrate_img img').click(function(){
	imagePreviewDialog(this.src)
})

/*首页*/
$(document).ready(function() {
	var $div_ul = $('.vehicle_con>ul')
	$('.vehicle_tit li').click(function() {
		var $t = $(this).index();
		$(this).addClass('active').siblings().removeClass('active');
		$div_ul.eq($t).show(600).siblings().hide(600);
	});

	/**预约看车*/
	var $car_li = $('.car_process .car_process_list ');
	$('.car_process_tit_item').click(function() {
		/**其他*/
		$('.car_process_tit_item').each(function() {
			var $d = $(this).index();
			var img_src = $(this).find('img').attr('src').split(".");
			var img_ = $(this).find('img').attr('src').split(".")[0];
			var img__ = img_.split('_01')[0];

			if (img_.indexOf('_01') > -1) {
				$(this).find('img').attr('src', img__ + "." + img_src[1]);
			}
		})

		var $d = $(this).index();
		var $this_siblings = $(this).siblings();
		var img_src = $(this).find('img').attr('src').split(".");
		var img_ = $(this).find('img').attr('src').split(".")[0];
		var img__ = img_.split('_01')[0];

		if (img_.indexOf('_01') > -1) {
			$(this).find('img').attr('src', img__ + "." + img_src[1]);
		} else {
			$(this).find('img').attr('src', img_src[0] + "_01." + img_src[1]);
		}

		$(this).addClass('active').siblings().removeClass('active');
		$car_li.eq($d).show(600).siblings().hide(600);
	})
})


// 首页 移动端车辆买卖
$('.vehicle_ul .tit').on('click',function(){
	$(this).siblings('.vehicle_con_detail').toggle(600)
})


// 重置密码找回密码
function check_pwd(obj){
	var password = $('.password').val();
	var password2= $('.password2').val();
	var pwd = $.trim($('input[name="password"]').val());
	var pwd2 = $.trim($('input[name="password2"]').val());

	if(pwd.length == 0|| isPassword(pwd) == false){
		$('input[name="password"]').parent().siblings('b').show();
		return false;
	}else if(pwd2.length == 0 ){
		$('input[name="password"]').parent().siblings('b').hide();
		$('input[name="password2"]').parent().siblings('b').show();
		return false;
	}else if(password != password2){
		$('input[name="password2"]').parent().siblings('b').show();
		return false;
	}
	obj.prentDefault();

	$('.js-ajax-form').submit();
}

function isPassword(password) {
  var pattern=/^[a-zA-z]{1}[0-9A-Za-z]{7,19}$/;
  return  pattern.test(password);
}

//结束	 重置密码找回密码


/*个人中心*/
// 个人中心卖家中心查看详情
// 弹窗
$(document).delegate('.detail_see', 'click', function() {
	var url = $(this).attr('data-url'),
		id = $(this).attr('data-id');
	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'json',
		data: {id: id},
		success:function(data){
			msgDialog(data);
		}
	});
	// var data = {'name':'你的名字','mobile':'18356082312','pop':'popup-Dialog','addr':'合肥市蜀山区佛子岭路66号'};
	// msgDialog(data);
});


/**车辆买卖  免费登记信息*/
$('.analogy').delegate('.analogy_tit', 'click', function(e) {
	$('.analogy_con').each(function() {
		$(this).hide();
	})

	$(this).siblings('.analogy_con').show();
	var _this = $(this);
	var _this_siblings = $(this).siblings('.analogy_con');
	var _parrent = $(this).parent().parent();
	var _this_siblings_li = $(this).siblings('.analogy_con').children('li');

	$(document).one('click', function() {
		_this_siblings.hide();
	})

	_this_siblings_li.on('click', function() {
		var txt = $(this).children('input').val();
		var liID=$(this).attr('data-val');
		_this.children('input').val(txt);
		_this.children('input').attr('data-id',liID)
		_this_siblings.hide();

	})
	e.stopPropagation();
})

$('.haSel').delegate('.haSel_tit', 'click', function(e) {
	$('.haSel_con').each(function() {
		$(this).hide();
	})

	$(this).siblings('.haSel_con').show();
	var _this = $(this);
	var _this_siblings = $(this).siblings('.haSel_con');
	var _parrent = $(this).parent().parent();
	var _this_siblings_li = $(this).siblings('.haSel_con').children('li');

	$(document).one('click', function() {
		_this_siblings.hide();
	})

	_this_siblings_li.on('click', function() {
		var txt = $(this).children('span').text();
		var liID=$(this).attr('data-val');
		_this.children('span').text(txt);
		_this.children('span').attr('data-id',liID)
		$('.haSel_con').hide();

	})
	e.stopPropagation();
})

// 筛选页筛选分类
$('.fitter_ul li i').click(function(){
	$(this).parent('li').remove();
})
// 个人中心在线充值
// 支付方式切换
function toDecimal2(money) {
    var f = parseFloat(money);
    if (isNaN(f)) {
        return "100.00";
    }
    var f = Math.round(money*100)/100;
    var s = f.toString();
    var rs = s.indexOf('.');
    if (rs < 0) {
        rs = s.length;
        s += '.';
    }
    while (s.length <= rs + 2) {
        s += '0';
    }
    return s;
}


//结束 个人中心在线充值

// 验证手机号
function isPhoneNo(phone) {
	var pattern = /^1[34578]\d{9}$/;
	return pattern.test(phone);
}



$('.yuyue_guang').hover(function() {
	$(this).children('.yuyueguang').show();
}, function() {
	$(this).children('.yuyueguang').hide();
})

if (screen.width > 768) {
	$('.yuyue_guang:last-child a').click(function() {
		$(this).hide();
		$('#teltouch').show();
	})
}

$(document).delegate('.cycle_icon li', 'click', function() {
	$(this).addClass('active').siblings().removeClass('active');
	var t = $(this).index();
	$(this).parent().parent().siblings().find('ul').animate({
		'margin-left': -t * 100 + "%"
	}, 600);
	
})




// 车辆信息
$(".car_message_nav_list").click(function() {
	$(".car_message_nav").css({
		"position": "fixed",
		"top": "0",
		"z-index": "1"
	});
	$(this).addClass('active').siblings().removeClass('active');
	var id = $(this).children('a').attr('href');

	$("html, body").animate({
		scrollTop: $(id).offset().top - 50
	}, {
		duration: 500,
		easing: "swing"
	});

})

function calcLi(lix) {
	var x = $('.simila_recommendation_list_con').width() / lix;
	$('.simila_recommendation_list .vehicle_con_detail_items ').css('width', x)
	var liLength = $('.simila_recommendation_list>li').length;
	if ($('.cycle_icon li').length < liLength / lix) {

		var num = Math.ceil(liLength / lix) - $('.cycle_icon li').length;
		for (var i = 0; i < num; i++) {
			$('.cycle_icon').append('<li><a></a></li>')
		}
	}
}

function lunpic() {
	if (screen.width > 767 && screen.width < 1200) {
		calcLi(3)
	}
	if (screen.width > 414 && screen.width < 768) {

		calcLi(2)
	}
	if (screen.width > 319 && screen.width < 415) {
		calcLi(1)
	}
}



/*预约看车*/
function car_mess_btn_submit() {
	if ($.trim($('input[name="tel"]').val()) == "" || $.trim($('input[name="tel"]').val()) == "请输入电话号码" || isPhoneNo($.trim($('input[name="tel"]').val())) == false) {
		alert('请填写正确手机号')
		$('input[name="tel"]').siblings('i').css('display', 'inline-block')
		return false;
	} else if ($.trim($('input[name="yanzhengma"]').val()) == "" || $.trim($('input[name="yanzhengma"]').val()) == "请输入验证码") {
		alert('输入验证码')
		$('input[name="yanzhengma"]').siblings('i').css('display', 'inne-block')
		return false;
	}
}


/*车辆买卖信息*/
/*表单验证*/
function check() {
	if ($('input[name="brand"]').val() == "请选择品牌") {
		
		alert('请选择品牌')
		return false;
	} else if ($('input[name="motorcycle"]').val() == "请选择车系") {
		
		alert('请选择车系')
		return false;
	} else if ($('input[name="tel"]').val() == "") {
		
		alert('请填写手机号')
		return false;
	}
}


// 车辆买卖
$('.vehiTrad_tit_item .more').on('click', function() {
	if ($(this).hasClass('on')) {
		$(this).removeClass('on');
		$(this).parent().parent().css('height', '50px');
	} else {
		$(this).addClass('on');
		$(this).parent().parent().css('height', 'auto');
	}
})

$('.vehiTrad_tit_item_other_list p').on('click', function(e) {
	var _this = $(this);
	var _this_siblings = $(this).siblings('ul');
	var _this_siblings_li = $(this).siblings('ul').children('li');
	var _siblings_siblings=$(this).parent().siblings().children('ul');

	_this_siblings.show();
	_siblings_siblings.hide();
	$(document).one('click', function() {
		_this_siblings.hide();
	})
	e.stopPropagation();

	_this_siblings_li.on('click', function() {
		var txt = $(this).children('a').text();
		_this.html(txt)
		_this_siblings.hide();
	})
})

$('.vehiTrad_tit_item_other_list').hover(function(e){

	$(this).children('ul').show();

},function(){

	$(this).children('ul').hide();

})


// maxlength兼容性问题
$(function () {
    $('textarea[maxlength]').on('keyup blur', function(event) {
        var maxlength = $(this).attr('maxlength');
        var val = $(this).val();

        if (val.length > maxlength) {
            /*这里是为了兼容win10自带输入法在字数到达极限值之后再输入中文会清空输入框的内容*/
            $(this).hide().show();
            
            $(this).val(val.substr(0, maxlength));
        }
        event.stopImmediatePropagation();
    });
})
// 结束maxlength兼容性问题

// 车辆买卖筛选条件跳转之后
$(function(){
	$('.vehiTrad_tit_item_other_list ul li').each(function(){
		if($(this).hasClass('active')){
			var text=$(this).children('a').text();
			$(this).parent().siblings('p').html(text)

		}
	})
})

// 结束车辆买卖


/**车险服务 --投保流程*/
$('.claim_guidance_guide li .circle').click(function() {
	var _parent = $(this).parent();
	var _t = _parent.index();
	var _div = $('.claim_guidance_guide_con_list');
	// console.log(_t)
	_parent.addClass('active').siblings().removeClass('active');
	_div.eq(_t).show().siblings().hide(600);
})

// 电话验证
function isPhoneNo(phone) {
	var pattern = /(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/;
	return pattern.test(phone)
}

// 车辆号牌
function car_num(num) {
	var pattern = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z_0-9]{5}$/;
	return pattern.test(num)
}

/*strat**险种选择，资料填写*/
function is_submit(value) {
	// console.log($('input[name="sfz"]'))

	var form1 = $("#data_filling");
	// 在线投保
	if ($.trim($('input[name="user_name"]').val()).length == 0) {
		alert('请填写正确用户名')
		$('input[name="user_name"]').siblings('b').css('display', 'block')
		return false;
	} else if ($.trim($('input[name="tel"]').val()).length == 0 || isPhoneNo($.trim($('input[name="tel"]').val())) == false) {
		alert('请填写正确手机号')
		$('input[name="tel"]').siblings('b').css('display', 'block')
		return false;
	} else if ($.trim($('input[name="plateNo"]').val()).length == 0 || car_num($.trim($('input[name="plateNo"]').val())) == false) {
		alert('请填写车牌号')
		$('input[name="plateNo"]').siblings('b').css('display', 'block')
		return false;
	} else if ($.trim($('input[name="xcz"]').val()).length == 0) {
		alert('请上传行车本照片')
		$('input[name="xcz"]').parent().parent().siblings('b').css('display', 'block')
		return false;
	} else if ($('input[name="sfz"]') && $.trim($('input[name="sfz"]').val()) == "") {
		alert('请上传身份证正面照片')
		$('input[name="sfz"]').parent().parent().siblings('b').css('display', 'block')
		return false;
	} else {
		if (value == 1) {
			var id = $("#id").val();
			form1.action = "/Test/querrybyid?id=" + id;
			$("#form1").attr("action", form1.action);
			form1.submit();
		}
		// 线上投保
		if (value == 2) {
			var title = $("#title").val();
			form1.action = "/Test/querrybyname?title=" + title;
			$("#form1").attr("action", form1.action);
			form1.submit();
		}
	}
}


/*end**险种选择，资料填写*/

/*车险选择签合同 */
$(".contract_p .check_span input").click(function() {
	if ($(this).prop('checked')) {
		$(this).siblings('label').attr('tid', '1')
	} else {
		$(this).siblings('label').attr('tid', '0')
	}
})

function insurance_contract(e) {
	var data = $('#contract_form').serialize();
	$('#contract_form').submit();
}

/*同意服务*/
$('.auto_login input').click(function() {
	if ($(this).prop('checked')) {
		$(this).parent('label').attr('tid', '1')
	} else {
		$(this).parent('label').attr('tid', '0')
	}
})

/****登录按钮**/
function loginLayoutValidate() {
	if ($.trim($('input[name="tel"]').val()).length == 0 || isPhoneNo($.trim($('input[name="tel"]').val())) == false) {
		alert('请填写正确手机号')
		$('input[name="tel"]').siblings('b').css('display', 'block')
		return false;
	} else if ($.trim($('input[name="password"]').val()).length == 0) {
		alert('请填写密码')
		$('input[name="password"]').siblings('b').css('display', 'block');
		return false;
	} else if ($.trim($('input[name="yanzheng"]').val()).length == 0) {
		alert('请填写验证码')
		$('input[name="yanzheng"]').siblings('b').css('display', 'block');
		return false;
	} else {
		if ($(".register_form .auto_login label").attr('tid') != 1) {
			alert("您未同意服务条款");
			return false;
		}
	}
}

// $('.user_login_input input').blur(function(){
// 	if($.trim($(this).val())==""){
// 		$(this).siblings('b').show();
// 	}else{
// 		if($(this).attr('name')=="tel" && isPhoneNo($.trim($('input[name="tel"]').val())) == false){
// 			$(this).siblings('b').show();
// 		}else{
// 			$(this).siblings('b').hide();
// 		}
// 	}
// })



// 预加载
$(function () {

	// 图片预览
	$('input[type="file"]').change(function() {
		var x = $(this).attr('id');
		var y = $(this).parent().parent().siblings().find('.show_img');
		$(this).parent().parent().siblings('.img_div').show();
		$(this).parent().parent().siblings('b').hide();
		var f = document.getElementById(x).files[0];
		var src = window.URL.createObjectURL(f);

		$(this).parent().parent().siblings().find('.show_img').attr('src', src);
	});
});


/*方法 function*/

// 地区 省份获取城市
function select_province(o,href) {
	var oo = '#input-province';
	if (o) { var ooo = oo+o; } else { var ooo = oo; }
	if (!href) { var href = '/usual/ajax/getcitys.html'; }

    // $(ooo).change(function() {
        var Id = $(ooo).val();
        $.ajax({
            url: href,
            type: 'POST',
            // dataType: 'json',
            data: {parentId: Id},
        })
        .done(function(data) {
            // console.log("success");
            if (data) { $('#input-city'+o).html(data); }
        })
        .fail(function() {
            // console.log("error");
        })
        .always(function() {
            // console.log("complete");
        });

		// $.ajax({
		//     url: href,
		//     type: 'POST',
		//     // dataType: 'json',
		//     data: {parentId: Id},
		//     success:function(data){
		//         console.log(data);
		//         if (data) { $('#input-city').html(data); }
		//     }
		// });
    // });
}



/* 没有form表单 \public\themes\simpleboot3\user\profile\avatar.html
<style type="text/css">
    .uploaded_photo_area {
        margin-top: 20px;
    }
    .uploaded_photo_btns {
        margin-top: 20px;
    }
    .uploaded_photo_area .uploaded_photo_btns {
        display: none;
    }
</style>
<div class="tab-pane active" id="one">
    <br>
    <if condition="empty($photo)">
        <img src="__TMPL__/public/assets/images/headicon_128.png" class="headicon" width="128"/>
    <else/>
        <img src="{:cmf_get_user_avatar_url($photo)}?t={:time()}" class="headicon" width="128"/>
    </if>
    <input type="file" onchange="photo_upload(this,"{:url('Profile/avatarUpload')}")" id="photo_uploder" name="file"/>
    <div class="uploaded_photo_area">
        <div class="uploaded_photo_btns">
            <a class="btn btn-primary confirm_photo_btn" onclick="update_photo(.uploaded_photo_area img,"{:url('Profile/avatarUpdate')}")">确定</a>
            <a class="btn" onclick="reloadPage()">取消</a>
        </div>
    </div>
    <p class="help-block">头像支持jpg,png,jpeg格式,文件大小最大不能超过1M</p>
</div>
 */
/*点击上传按钮
var obj = this;
var url = '{:url('Profile/avatarUpload')}';
*/
function photo_upload(obj,url) {
    var $fileinput = $(obj);
    /* $(obj)
     .show()
     .ajaxComplete(function(){
     $(this).hide();
     }); */
    Wind.css("jcrop");
    Wind.use("ajaxfileupload", "jcrop", "noty", function () {
        $.ajaxFileUpload({
            url: "",
            secureuri: false,
            fileElementId: "photo_uploder",
            dataType: 'json',
            data: {},
            success: function (data, status) {
                if (data.code == 1) {
                    $("#photo_uploder").hide();
                    var $uploaded_area = $(".uploaded_photo_area");
                    $uploaded_area.find("img").remove();
                    var src  = "__ROOT__/upload/" + data.data.file;
                    var $img = $("<img/>").attr("src", src);
                    $img.prependTo($uploaded_area);
                    $(".uploaded_photo_btns").show();
                    var img = new Image();
                    img.src = src;

                    var callback = function () {
                        // console.log(img.width);
                        $img.Jcrop({
                            aspectRatio: 1,
                            trueSize: [img.width, img.height],
                            setSelect: [0, 0, 100, 100],
                            onSelect: function (c) {
                                $img.data("area", c);
                            }
                        });
                    }
                    if (img.complete) {
                        callback();
                    } else {
                        img.onload = callback;
                    }
                } else {
                    noty({
                        text: data.msg,
                        type: 'error',
                        layout: 'center',
                        callback: {
                            afterClose: function () {
                                reloadPage(window);
                            }
                        }
                    });
                }
            },
            error: function (data, status, e) {
            }
        });
    });

    return false;
}

/*确认上传
var o = '.uploaded_photo_area img';
var url = '{:url('Profile/avatarUpdate')}';
*/
function update_photo(o,url) {
    var area = $(o).data("area");
    $.post(url, area, function (data) {
        if (data.code == 1) {
            reloadPage(window);
        }
    }, "json");
}


// 弹窗 引用个人中心的 不灵活需改造
function msgDialog(data) {
	// for (var i = 0; i < Things.length; i++) {
	// 	Things[i]
	// }
	// var data = {'name':'你的名字','mobile':'18356082312','pop':'付过定金，已认证','addr':'合肥市蜀山区佛子岭路66号'};
	var opts = {
		"imgshow": 1,
		"list1_txt": data.name,
		"list2_txt": data.mobile,
		"list3_txt": data.pop,
		"list4_txt": data.addr,
		"buttons": {
			"确定": function() {
				$(this).parent().parent().hide();
			}
		}
	};
	simpleAlert(opts);

}

/*引入 douphp 的*/
/**
 +----------------------------------------------------------
 * 刷新验证码
 +----------------------------------------------------------
 */
function refreshimage() {
    var cap = document.getElementById("vcode");
    cap.src = cap.src + '?';
}

/**
 +----------------------------------------------------------
 * 搜索框的鼠标交互事件
 +----------------------------------------------------------
 */
function formClick(name, text) {
    var obj = name;
    if (typeof(name) == "string") obj = document.getElementById(id);
    if (obj.value == text) {
        obj.value = "";
    }
    obj.onblur = function() {
        if (obj.value == '') {
            obj.value = text;
        }
    }
}

/**
 +----------------------------------------------------------
 * 表单提交
 +----------------------------------------------------------
 */
function douSubmit(form_id) {
    var formParam = $("#"+form_id).serialize(); //序列化表格内容为字符串

    $.ajax({
        type: "POST",
        url: $("#"+form_id).attr("action")+'&do=callback',
        data: formParam,
        dataType: "json",
        success: function(form) {
            if (!form) {
                $("#"+form_id).submit();
            } else {
                for(var key in form) {
                    $("#"+key).html(form[key]);
                }
            }
        }
    });
}

/**
 +----------------------------------------------------------
 * 弹出窗口
 +----------------------------------------------------------
 */
function douBox(page) {
    $.ajax({
        type: "GET",
        url: page,
        data: "if_check=1",
        dataType: "html",
        success: function(html) {
            $(document.body).append(html);
        }
    });
}

/**
 +----------------------------------------------------------
 * 清空对象内HTML
 +----------------------------------------------------------
 */
function douRemove(target) {
    var obj = document.getElementById(target);
    obj.parentNode.removeChild(obj);
}

/**
 +----------------------------------------------------------
 * 收藏本站
 +----------------------------------------------------------
 */
function AddFavorite(url, title) {
    try {
        window.external.addFavorite(url, title)
    } catch(e) {
        try {
            window.sidebar.addPanel(title, url, "")
        } catch(e) {
            alert("加入收藏失败，请使用Ctrl+D进行添加")
        }
    }
}

/**
 +----------------------------------------------------------
 * 在线客服
 +----------------------------------------------------------
*/

/**
 +----------------------------------------------------------
 * 返回顶部
 +----------------------------------------------------------*/
