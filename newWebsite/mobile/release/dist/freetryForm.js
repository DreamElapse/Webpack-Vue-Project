webpackJsonp([17],{295:function(e,a,t){var i,n,p={};t(296),i=t(298),n=t(299),e.exports=i||{},e.exports.__esModule&&(e.exports=e.exports.default);var s="function"==typeof e.exports?e.exports.options||(e.exports.options={}):e.exports;n&&(s.template=n),s.computed||(s.computed={}),Object.keys(p).forEach(function(e){var a=p[e];s.computed[e]=function(){return a}})},296:function(e,a,t){var i=t(297);"string"==typeof i&&(i=[[e.id,i,""]]);t(22)(i,{});i.locals&&(e.exports=i.locals)},297:function(e,a,t){a=e.exports=t(3)(),a.push([e.id,".n_bg[_v-7310b494]{background:#fff}.h1_tit[_v-7310b494]{background:#d0213e;text-align:center;line-height:1.8em;font-size:1em;color:#fff;margin-top:1rem}.cont[_v-7310b494]{padding:.5em}.cont h3[_v-7310b494]{font-size:1em;line-height:1.2em;color:#d0213e;font-weight:400;margin-bottom:.5em;text-align:center}.pro_info[_v-7310b494]{border:1px solid #acacac;border-radius:.5em;padding:1.3em 1em;font-size:.8em;box-shadow:1px 4px 6px #acacac;margin-bottom:.6em}.pro[_v-7310b494]{width:80%;text-align:center;margin:0 auto}.pro img[_v-7310b494]{width:100%;max-width:270px}.name[_v-7310b494]{font-size:1.6em;line-height:2.2em;text-align:center}.but[_v-7310b494]{margin-top:.5em;text-align:center}.but img[_v-7310b494]{width:60%;max-width:214px}.form_info[_v-7310b494]{margin-top:.5em;font-size:1em}.form_info p[_v-7310b494]{line-height:1.6em;margin-top:1em}.form_info p span[_v-7310b494]{font-size:1.6em}.form_info p label[_v-7310b494]{font-size:1.2em;margin-right:.5em}.form_info p input[type=checkbox][_v-7310b494],.form_info p input[type=radio][_v-7310b494]{position:relative;top:.1em;margin-right:.2em;-webkit-appearance:radio;box-sizing:border-box}.form_info p input[type=text][_v-7310b494]{font-size:1.4em;font-family:Microsoft YaHei;padding:.1em 0;-webkit-appearance:none;-webkit-border-radius:0}.form_info p .u_name[_v-7310b494]{width:6em;height:1.2em;line-height:1.2em}.form_info p .tel_name[_v-7310b494]{width:8em;height:1.2em;line-height:1.2em}.form_info p .add_name[_v-7310b494]{width:12em;height:1.2em;line-height:1.2em}.form_info p .ly[_v-7310b494]{width:90%;height:4em;padding:.3em}",""])},298:function(e,a,t){(function(e){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default={ready:function(){var a=this,t=this.$route.params.id;this.$http.post("/FreeTrial/index.json?fid="+t).then(function(e){e=e.json(),1==e.status?a.$set("applyList",e.data):a.$dispatch("popup",e.msg)}),e("#apply_btn").on("click",function(){var a={};return a.activity_id=e("input[name='activity_id']").val(),a.name=e("input[name='name']").val(),a.sex=e("input[name='sex']:checked").val(),a.age=e("input[name='age']:checked").val(),a.phone=e("input[name='phone']").val(),a.country=0,a.province=0,a.city=0,a.district=0,a.town=0,a.address=e("input[name='address']").val(),a.apply_reason=e("textarea[name='apply_reason']").val(),a.skin_disease="",a.fid=t,a.activity_id=t,e("input[name='skin_disease']:checked").each(function(){a.skin_disease+=","+e(this).val()}),a.skin_disease=a.skin_disease.substr(1),a.fid<=0?(alert("服务器请求错误！"),!1):a.name.length<=0?(alert("请填写姓名"),!1):null==a.sex?(alert("请选择性别"),!1):null==a.age?(alert("请选择年龄段"),!1):/^1[34578]\d{9}$/.test(a.phone)?a.address.length>4&&/^[\u4e00-\u9fa5A-Za-z0-9\s\(\)\/（）]+$/.test(a.address)?a.skin_disease.length<=0?(alert("请选择需改善的皮肤问题"),!1):""==a.apply_reason?(alert("请填写申请理由"),!1):a.apply_reason.length<20?(alert("申请理由最少20个字"),!1):(e.ajax({url:"/FreeTrial/trialPost.json",type:"POST",data:a,success:function(e){1==e.status?alert("提交成功！"):alert(e.msg)},error:function(e,a){alert("请求失败，请重试！"+a)}}),!1):(alert("请填写正确的收货地址"),!1):(alert("请填写正确的手机号码"),!1)})},data:function(){return{applyList:{}}}}}).call(a,t(24))},299:function(e,a){e.exports=' <div class="main n_bg" _v-7310b494=""> <div class=h1_tit _v-7310b494="">正品试用</div> <div class=cont _v-7310b494=""> <h3 _v-7310b494="">提交申请，将有机会获得以下试用产品</h3> <div class=pro_info _v-7310b494=""> <p class=pro _v-7310b494=""><img :src=applyList.goods_img alt="" _v-7310b494=""></p> <h4 class=name _v-7310b494="">{{applyList.goods_name}}</h4> <div class=form_info _v-7310b494=""> <p _v-7310b494=""><span _v-7310b494="">姓名：</span><input type=text class=u_name name=name _v-7310b494=""></p> <p _v-7310b494=""><span _v-7310b494="">性别：</span><input type=radio name=sex value=1 id=fomale _v-7310b494=""><label for=fomale _v-7310b494="">女</label><input type=radio name=sex value=2 id=men _v-7310b494=""><label for=men _v-7310b494="">男</label></p> <p _v-7310b494=""><span _v-7310b494="">年龄：</span> <input type=radio name=age id=1 value=18岁以下 _v-7310b494=""><label for=1 _v-7310b494="">18以下</label> <input type=radio name=age id=2 value=18—24岁 _v-7310b494=""><label for=2 _v-7310b494="">18—24岁</label> <input type=radio name=age id=3 value=24—30岁 _v-7310b494=""><label for=3 _v-7310b494="">24—30岁</label> </p> <p style=padding-left:4.2em _v-7310b494=""> <input type=radio name=age value=30—40岁 id=4 _v-7310b494=""><label for=4 _v-7310b494="">30—40岁</label> <input type=radio name=age value=40以上 id=5 _v-7310b494=""><label for=5 _v-7310b494="">40岁以上</label> </p> <p _v-7310b494=""><span _v-7310b494="">手机号码：</span><input type=text name=phone class=tel_name _v-7310b494=""></p> <p _v-7310b494=""><span _v-7310b494="">收货地址：</span><input type=text name=address class=add_name _v-7310b494=""></p> <p _v-7310b494=""><span _v-7310b494="">需改善的肌肤问题：</span></p> <p _v-7310b494=""> <input type=checkbox name=skin_disease value=干燥 id=6 _v-7310b494=""><label for=6 _v-7310b494="">干燥</label> <input type=checkbox name=skin_disease value=过敏 id=7 _v-7310b494=""><label for=7 _v-7310b494="">过敏</label> <input type=checkbox name=skin_disease value=细纹 id=8 _v-7310b494=""><label for=8 _v-7310b494="">细纹</label> <input type=checkbox name=skin_disease value=出油 id=9 _v-7310b494=""><label for=9 _v-7310b494="">出油</label> <input type=checkbox name=skin_disease value=痘痘 id=10 _v-7310b494=""><label for=10 _v-7310b494="">痘痘</label> </p> <p _v-7310b494=""> <input type=checkbox name=skin_disease value=红血丝 id=11 _v-7310b494=""><label for=11 _v-7310b494="">红血丝</label> <input type=checkbox name=skin_disease value=色斑 id=12 _v-7310b494=""><label for=12 _v-7310b494="">色斑</label> <input type=checkbox name=skin_disease value=肤色不均 id=13 _v-7310b494=""><label for=13 _v-7310b494="">肤色不均</label> <input type=checkbox name=skin_disease value=暗黄 id=14 _v-7310b494=""><label for=14 _v-7310b494="">暗黄</label> </p> <p _v-7310b494=""><span _v-7310b494="">申请理由：</span></p> <p _v-7310b494=""><textarea name=apply_reason class=ly _v-7310b494=""></textarea></p> </div> <p class=but _v-7310b494=""><a href=javascript:; id=apply_btn _v-7310b494=""><img src=/public/images/freetry/btn1.png alt="" _v-7310b494=""></a></p> </div> </div> </div>'}});