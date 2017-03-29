<script>
    export default {
        ready(){
            let id = this.$route.params.id;
            this.$http.post('/FreeTrial/index.json?fid=' + id).then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.$set('applyList', res.data);
                }else{
                    this.$dispatch('popup', res.msg);
                }
            });
            $('#apply_btn').on('click',function(){
                var data = {};
                data.activity_id = $("input[name='activity_id']").val();
                data.name = $("input[name='name']").val();
                data.sex = $("input[name='sex']:checked").val();
                data.age = $("input[name='age']:checked").val();
                data.phone = $("input[name='phone']").val();
                data.country = 0;
                data.province = 0;
                data.city = 0;
                data.district = 0;
                data.town = 0;
                data.address = $("input[name='address']").val();
                data.apply_reason = $("textarea[name='apply_reason']").val();
                data.skin_disease = '';
                data.fid = id;
                data.activity_id = id;
                $("input[name='skin_disease']:checked").each(function(){
                  data.skin_disease += ',' + $(this).val();
                });

                data.skin_disease = data.skin_disease.substr(1);

                if(data.fid <= 0){alert('服务器请求错误！');return false;}
                if(data.name.length <= 0){alert('请填写姓名');return false;}
                if(data.sex == null){alert('请选择性别');return false;}
                if(data.age == null){alert('请选择年龄段');return false;}
                if(!/^1[34578]\d{9}$/.test(data.phone)){alert('请填写正确的手机号码');return false;}
                if(!(data.address.length > 4 && /^[\u4e00-\u9fa5A-Za-z0-9\s\(\)\/（）]+$/.test(data.address))){alert('请填写正确的收货地址');return false;}
                if(data.skin_disease.length <= 0){alert('请选择需改善的皮肤问题');return false;}
                if(data.apply_reason == ''){alert('请填写申请理由');return false;}
                if(data.apply_reason.length < 20){alert('申请理由最少20个字');return false;}

                //let param = 'data=' + data;
                $.ajax({
                    url:'/FreeTrial/trialPost.json',
                    //dataType:'JSON',
                    type:'POST',
                    data:data,
                    success:function(d){ 
                        if(d.status == 1){
                            //location.reload();
                            alert('提交成功！')
                        }else{
                            alert(d.msg);
                        }
                    },
                    error:function(x,s){
                        alert("请求失败，请重试！"+s);
                    }
                });
                return false;
            })
        },
        data(){
            return{
                applyList:{}
            }
        }
    }
</script>
<template>
<div class="main n_bg">
	<div class="h1_tit">正品试用</div>
	<div class="cont">
		<h3>提交申请，将有机会获得以下试用产品</h3>
		<div class="pro_info">
			<p class="pro"><img :src="applyList.goods_img" alt=""></p>
			<h4 class="name">{{applyList.goods_name}}</h4>
			<div class="form_info">
				<p><span>姓名：</span><input type="text" class="u_name" name="name"></p>
				<p><span>性别：</span><input type="radio" name="sex" value="1" id="fomale"><label for="fomale">女</label><input type="radio" name="sex" value="2" id="men"><label for="men">男</label></p>
				<p><span>年龄：</span>
					<input type="radio" name="age" id="1" value="18岁以下"><label for="1">18以下</label>
					<input type="radio" name="age" id="2" value="18—24岁"><label for="2">18—24岁</label>
					<input type="radio" name="age" id="3" value="24—30岁"><label for="3">24—30岁</label>
				</p>	
				<p style="padding-left:4.2em;">	
					<input type="radio" name="age" value="30—40岁" id="4"><label for="4">30—40岁</label>
					<input type="radio" name="age" value="40以上" id="5"><label for="5">40岁以上</label>
				</p>
				<p><span>手机号码：</span><input type="text" name="phone" class="tel_name"></p>
				<p><span>收货地址：</span><input type="text" name="address" class="add_name"></p>
				<p><span>需改善的肌肤问题：</span></p>
				<p>
					<input type="checkbox" name="skin_disease" value="干燥" id="6"><label for="6">干燥</label>
					<input type="checkbox" name="skin_disease" value="过敏" id="7"><label for="7">过敏</label>
					<input type="checkbox" name="skin_disease" value="细纹" id="8"><label for="8">细纹</label>
					<input type="checkbox" name="skin_disease" value="出油" id="9"><label for="9">出油</label>
					<input type="checkbox" name="skin_disease" value="痘痘" id="10"><label for="10">痘痘</label>
				</p>
				<p>
					<input type="checkbox" name="skin_disease" value="红血丝" id="11"><label for="11">红血丝</label>
					<input type="checkbox" name="skin_disease" value="色斑" id="12"><label for="12">色斑</label>
					<input type="checkbox" name="skin_disease" value="肤色不均" id="13"><label for="13">肤色不均</label>
					<input type="checkbox" name="skin_disease" value="暗黄" id="14"><label for="14">暗黄</label>
				</p>
				<p><span>申请理由：</span></p>
				<p><textarea name="apply_reason" class="ly"></textarea></p>
                <!-- <input type="hidden" name="activity_id" value="96"> -->
			</div>
			<p class="but"><a href="javascript:;" id="apply_btn"><img src="/public/images/freetry/btn1.png" alt=""></a></p>
		</div>
	</div>
 </template>
<style scoped>
.n_bg{background: #fff;}
.h1_tit{background: #d0213e;text-align: center;line-height: 1.8em;font-size: 1em;color: #fff;margin-top: 1rem;}
.cont{padding: .5em;}
.cont h3{font-size: 1em;line-height: 1.2em;color:#d0213e;font-weight: normal;margin-bottom: .5em;text-align: center;}
.pro_info {border: 1px solid #acacac;border-radius: .5em;padding: 1.3em 1em;font-size: .8em;box-shadow: 1px 4px 6px #acacac;margin-bottom: .6em;}
.pro{width: 80%;text-align: center;margin: 0 auto;}
.pro img{width: 100%; max-width:270px;}
.name{font-size: 1.6em;line-height: 2.2em;text-align: center;}
.but{margin-top: .5em;text-align: center;}
.but img{width: 60%; max-width:214px;}
.form_info{margin-top: .5em;font-size: 1em;}
.form_info p{line-height: 1.6em;margin-top: 1em;}
.form_info p span{font-size: 1.6em;}
.form_info p label{font-size: 1.2em;margin-right: .5em;}
.form_info p input[type='checkbox'],.form_info p input[type='radio']{position: relative;top: .1em;margin-right: .2em;-webkit-appearance: radio;box-sizing: border-box;}
.form_info p input[type='text']{font-size: 1.4em;font-family: "Microsoft YaHei";padding: .1em 0;-webkit-appearance: none;-webkit-border-radius: 0;}
.form_info p .u_name{width: 6em;height: 1.2em;line-height: 1.2em;}
.form_info p .tel_name{width: 8em;height: 1.2em;line-height: 1.2em;}
.form_info p .add_name{width: 12em;height: 1.2em;line-height: 1.2em;}
.form_info p .ly{width:90%;height: 4em;padding: .3em;}
</style>