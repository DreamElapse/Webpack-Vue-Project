<script>
	//var birthdaypicker = require('jquery.bday-picker.js');

	export default {
		ready(){

            var selects = document.getElementsByTagName("select");//通过标签名获取select对象
            var date = new Date();
            var nowYear = date.getFullYear();//获取当前的年
            for(var i=nowYear;i>=nowYear-100;i--){
            var optionYear = document.createElement("option");
            optionYear.innerHTML=i;
            optionYear.value=i;
            selects[0].appendChild(optionYear);
            }
            for(var i=1;i<=12;i++){
            var optionMonth = document.createElement("option");
            optionMonth.innerHTML=i;
            optionMonth.value=i;
            selects[1].appendChild(optionMonth);
            }
            getDays(selects[1].value,selects[0].value,selects);

            // 获取某年某月存在多少天
            function getDaysInMonth(month,year){
            var days;
            	if (month==1 || month==3 || month==5 || month==7 || month==8 || month==10 || month==12) {
            		days=31;
            	}else if (month==4 || month==6 || month==9 || month==11){
            		days=30;
            	}else{
            		if ((year%4 == 0 && year%100 != 0) || (year%400 == 0)) {     // 判断是否为润二月
            		days=29; 
            		}else { 
            		days=28; 
            		}
            	}
            	return days;
            }
            function setDays(){
            	var selects = document.getElementsByTagName("select");
            	var year = selects[0].options[selects[0].selectedIndex].value;
            	var month = selects[1].options[selects[1].selectedIndex].value;
            	getDays(month,year,selects);
            }
            function getDays(month,year,selects){
            	var days = getDaysInMonth(month,year);
            	selects[2].options.length = 0;
            	for(var i=1;i<=days;i++){
            	var optionDay = document.createElement("option");
            	optionDay.innerHTML=i;
            	optionDay.value=i;
            	selects[2].appendChild(optionDay);
            	}
            }

            $("<option value='0'>年</option>").appendTo(selects[0]);
            $("<option value='0'>月</option>").appendTo(selects[1]);
            $("<option value='0'>日</option>").appendTo(selects[2]);


		//提交成功弹窗js
   	$('.sub_btn a').click(function(){
        var arr = new Object();

        arr.feature = $('input[name="radio-btn"]:checked').attr('id');
        if(typeof(arr.feature)=='undefined'||arr.feature==''){
            $('input[name="radio-btn"]').eq(0).focus();
            alert('请选择皮肤特性');
            return false;
        }

        arr.skin_color = $('input[name="radio-bt"]:checked').attr('id');
        if(typeof(arr.skin_color)=='undefined'||arr.skin_color==''){
            $('input[name="radio-bt"]').eq(0).focus();
            alert('请选择你的肤色');
            return false;
        }
		//console.log($('.pr input[name="check-box"]:checked').length);
        if($('.pr input[name="check-box"]:checked').length<=0){
            $('.pr input[name="check-box"]').focus();
            alert('请选择你的皮肤问题');
            return false;
        }

        arr.skin_problem = '';
        $('.pr input[name="check-box"]:checked').each(
                function(index){
                        arr.skin_problem += $(this).attr('id')+',';
                }
        );
        arr.skin_problem = arr.skin_problem.substr(0,arr.skin_problem.length-1);

        arr.username = $('input[name="username"]').val();
        if(typeof(arr.username)=='undefined' || arr.username=='' ){
            $('input[name="username"]').focus();
            alert('请填写您的姓名');
            return false;
        }

        if(!(/^[\u0391-\uFFE5A-Za-z]{2,}$/.test(arr.username))){
            $('input[name="username"]').focus();
            alert('姓名填写错误!');
            return false;
        }

        arr.sex = $('input[name="sex"]:checked').attr('id');
        if(typeof(arr.sex)=='undefined' || arr.sex == ''){
            $('input[name="sex"]').focus();
            alert('请选择你的性别');
            return false;
        }

        arr.data_type = $('input[name="date"]:checked').attr('id');
        if(typeof(arr.data_type)=='undefined' || arr.data_type==''){
            $('input[name="date"]').focus();
            alert('请选择生日类型');
            return false;
        }

        arr.birth_year = $('select[name="birth[year]"]').val();
        if(typeof(arr.birth_year)=='undefined' || arr.birth_year=='' ||arr.birth_year==0 ){
            $('select[name="birth[year]"]').focus();
            alert('请选择年份');
            return false;
        }

        arr.birth_month = $('select[name="birth[month]"]').val();
        if(typeof(arr.birth_month)=='undefined' || arr.birth_month=='' || arr.birth_month==0){
            $('select[name="birth[month]"]').focus();
            alert('请选择月份');
            return false;
        }

        arr.birth_date = $('select[name="birth[day]"]').val();
        if(typeof(arr.birth_date)=='undefined' || arr.birth_date=='' || arr.birth_date==0){
            $('select[name="birth[day]"]').focus();
            alert('请选择日期');
            return false;
        }

        arr.phone  = $('input[name="phone"]').val();
        var mobilereg = /^1[34578][\d]{9}$/;
        if(arr.phone>0 && !mobilereg.test(arr.phone))
        {
            $('input[name="phone"]').focus();
            alert('填写手机号格式不对！');
            return false;
        }

        arr.qq  = $('input[name="qq"]').val();
        if((typeof(arr.qq)=='undefined' || arr.qq=='')&&(typeof(arr.phone)=='undefined' || arr.phone=='')){
            $('input[name="phone"]').focus();
            alert('请填写手机号或QQ号');
            return false;
        }
        if(arr.qq.length>0 && (arr.qq.length<=3 || (/[^0-9]/g.test(arr.qq)) || arr.qq.length>=20)){
            $('input[name="qq"]').focus();
            alert('填写QQ号格式不对！');
            return false;
        }

        $.post("/SkinTest/defendWar.json?params="+JSON.stringify(arr),function(data){
            if(data.status=='1'){
                    $('.b_bg').show(200);
                    $('.succ_pic').show(200);
                    $("body").css({overflow:"hidden"});    //禁用滚动条
            }else{
                alert(data.msg);
            }

        },'json') ;

   	});
   	$('.b_bg,.succ_pic').click(function(){
   		$('.b_bg').hide(200);
   		$('.succ_pic').hide(200);
   		$("body").css({overflow:"auto"});    //启用滚动条
		window.location.reload();
   	})


	$('input[name="radio-btn"],input[name="radio-bt"],input[name="sex"],input[name="date"]').wrap('<div class="radio-btn"><i></i></div>');
	$(".sec_q .radio-btn").on('click', function () {
	    var _this = $(this),
	        block = _this.parent().parent();
	    block.find('input').attr('checked', false);
	    block.find(".radio-btn").removeClass('checkedRadio');
	    _this.addClass('checkedRadio');
	   	_this.siblings().children('span').addClass('checked').parent().parent('li').siblings().children().find('span').removeClass('checked');
	    _this.find('input').attr('checked', true);
	});
	$(".per_info .radio-btn").on('click', function () {
	    var _this = $(this),
	        block = _this.parent().parent();
	    block.find('input').attr('checked', false);
	    block.find(".radio-btn").removeClass('checkedRadio');
	    _this.addClass('checkedRadio');
	   	_this.next().children('span').addClass('checked').parent().siblings().find('span').removeClass('checked');
	    _this.find('input').attr('checked', true);
	});
	$('input[name="check-box"]').wrap('<div class="check-box"><i></i></div>');
	$.fn.toggleCheckbox = function () {
	    this.attr('checked', !this.attr('checked'));
	}
	$('.check-box').on('click', function () {
	    $(this).find('input').toggleCheckbox();
	    $(this).toggleClass('checkedBox');
	    $(this).siblings().children('span').toggleClass('checked');
	});

	}

}
</script>

<template>
    <link rel="stylesheet" href="/public/css/sear_test.css">

    <div class="b_bg"></div>
    <div class="succ_pic pr">
    	<img src="/public/images/test/succ.png" alt="">
    	<div class="close">
    		<img src="/public/images/test/close.png" alt="">
    	</div>
    </div>
    <img src="/public/images/test/img_01.jpg" alt="">
    <img src="/public/images/test/img_02.jpg" alt="">
    <div class="pr">
    	<img src="/public/images/test/img_03.jpg" alt="">
    	<div class="sec_q">
    	 <ul>
            <li>
                <h3><b></b>请你描述下你的皮肤特征：</h3>
            </li>
            <li>
               <input type="radio" name="radio-btn" id="no01" /><label for="no01"><span>A.不干也不油</span></label>
            </li>
            <li>
               <input type="radio" name="radio-btn" id="no02" /><label for="no02"><span> B.干燥，毛孔细</span></label>
            </li>
            <li>
                <input type="radio" name="radio-btn" id="no03" /><label for="no03"><span>C.T区油，U区干</span></label>
            </li>
            <li>
                <input type="radio" name="radio-btn" id="no04" /><label for="no04"><span>D.全脸泛油，易长痘</span></label>
            </li>
    	</ul>
    	</div>
    </div>
    <div class="pr">
    	<img src="/public/images/test/img_04.jpg" alt="">
    	<div class="sec_q">
    	 <ul>
            <li>
                <h3><b></b>你素颜时的肤色属于哪个？</h3>
            </li>
            <li>
                <input type="radio" name="radio-bt" id="no05" /><label for="no05"><span>A. 白皙</span></label>
            </li>
            <li>
                <input type="radio" name="radio-bt" id="no06" /><label for="no06"><span>B. 粉嫩</span></label>
            </li>
            <li>
                <input type="radio" name="radio-bt" id="no07" /><label for="no07"><span>C. 暗黄</span></label>

            </li>
            <li>
                <input type="radio" name="radio-bt" id="no08" /><label for="no08"><span>D. 大麦色</span></label>

            </li>
    	</ul>
    	</div>
    </div>
    <div class="pr">
    	<img src="/public/images/test/img_05.jpg" alt="">
    	<div class="sec_q">
    	 <ul>
            <li>
                <h3><b></b>你的脸部有什么皮肤问题？<br/>（多选）</h3>
            </li>
            <li>
                <input type="checkbox" name="check-box" id="no09" /><label for="no09"><span>A. 黑头/毛孔</span></label>

            </li>
            <li>
                <input type="checkbox" name="check-box" id="no10" /><label for="no10"><span>B. 黑眼圈/眼袋/脂肪粒/眼纹</span></label>
            </li>
            <li>
                <input type="checkbox" name="check-box" id="no11" /><label for="no11"><span>C. 痘痘/痘印</span></label>

            </li>
            <li>
                <input type="checkbox" name="check-box" id="no12" /><label for="no12"><span>D. 干燥/紧绷</span></label>
            </li>
            <li>
                <input type="checkbox" name="check-box" id="no13" /><label for="no13"><span>E. 暗黄/斑点</span></label>
            </li>
            <li>
                <input type="checkbox" name="check-box" id="no14" /><label for="no14"><span>F. 皱纹</span></label>
            </li>
            <li>
                <input type="checkbox" name="check-box" id="no15" /><label for="no15"><span>G. 出油</span></label>
            </li>
    	</ul>
    	</div>
    </div>
    <img src="/public/images/test/img_06.jpg" alt="">
    <div class="pr">
    	<img src="/public/images/test/img_07.jpg" alt="">	
    	<div class="per_info">
    		<dl>
    			<dt>你的姓名：</dt>
    			<dd><input type="text" name="username" value="" placeholder="请输入您的名字" ></dd>
    		</dl>
    		<dl>
    			<dt>性别：</dt>
    			<dd>
    				<input type="radio" name="sex" id="male"><label for="male"><span>男</span></label>
    				<input type="radio" name="sex" id="female"><label for="female"><span>女</span></label>
    			</dd>
    		</dl>
    		<dl>
    			<dt>生日：</dt>
    			<dd>
    				<input type="radio" name="date" id="new_time"><label for="new_time"><span>公历</span></label>
    				<input type="radio" name="date" id="old_time"><label for="old_time"><span>农历</span></label>
    			</dd>
    		</dl>
    		<dl>
    			<dd><div id="birth"><select class="birth-year" name="birth[year]"></select><select class="birth-month" name="birth[month]"></select><select class="birth-day" name="birth[day]"></select></div><p>（年龄会作为你皮肤状况判断的重要参考值）</p></dd>
    		</dl>
    		<dl>
    			<dt>手机号码：</dt>
    			<dd><input type="text" name="phone" value="" placeholder="请输入您的手机号码" ></dd>
    		</dl>
    		<dl>
    			<dt>QQ号码：</dt>
    			<dd><input type="text" name="qq" value="" placeholder="请输入您的QQ号码" ></dd>
    		</dl>
    		<p class="sub_btn"><a href="javascript:;"><img src="/public/images/test/sub_btn.png" alt=""></a></p>
    	</div>
    </div>
</template>