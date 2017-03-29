/*
 * author 9007656
 * 新3g站/Q站在线订购组件
 */
ves(function(){ 
	var orderView=function(){
		var self=this;
		self.pro_id_list="459";//商品ID--3g:269;q:459,1071
		self.pro_list=ko.observableArray([
			{goods_id:"",goods_thumb:"",goods_name:"",shop_price:"0",market_price:"0"}
		]);
		self.provinceList=ko.observableArray([
			{index:"",province:"请选择省"}
		]);
		self.priceList=ko.observableArray([
			{originalPrice:"0",preferentialPrice:"10",shipping_fee:"0",need_pay:"0"}
			]);
		self.canClick=true;
		self.wait_t=60;
		self.disabled=ko.observable(true);
		self.token=ko.observable('');
		self.pay_ind=ko.observable('4');
		self.nameChecked=ko.observable(false);
		self.addrChecked=ko.observable(false);
		self.bonusChecked=ko.observable(false);
		self.optionHtml="";
		self.init=function(){
//			var lang=ves("[packageview=orderCpt]").attr("lang");
//			if(lang!=""){
//				self.pro_id_list=lang;
//			}
			self.getProList();
			self.getProviceList();			
		};
		//获取商品信息列表
		self.getProList=function(){
			ves.ajax({ url: '/Goods/getListForId.json',data:{goods_id:self.pro_id_list},success: function (http) {										
				if(http.status==1){									
					var temp=http.data;
					var arr=[];
					for(var i=0;i<temp.length;i++){
						arr.push({goods_id:temp[i].goods_id,goods_thumb:temp[i].goods_thumb,goods_name:temp[i].goods_name,shop_price:temp[i].shop_price,market_price:temp[i].market_price});
					}
					self.pro_list(arr);
					ves.ajax({url:"/Cart/cleanCart",data:{goods_id:self.pro_id_list},success: function (http) {
						if(http.status==1){
							console.log(http.msg);
							self.calculateTotalAmount();
						}else{
							alert(http.msg);
						}
					}})					
				}else{
					console.log(http.msg);
				}
			}});
		};
		//添加购物车
		self.addShoppingCart=function(e){
			var goods_id=e.current.goods_id;
			if(self.canClick){
				self.canClick=false;
				var input = ves(this).parent(".order-quantity").find("input");
				var num = parseInt(input.val());
				ves.ajax({ url: "/cart/addGoodsToCart",data:{goods_id:goods_id},success: function (http) {										
					if(http.status==1){							
						input.val(num + 1);
						self.canClick=true;
						var temp=http.data;					
						self.calculateTotalAmount();
					}else{
						alert(http.msg);
					}
				}});
			}					

		};
		//删除购物车
		self.reduceShoppingCart=function(e){
			var goods_id=e.current.goods_id;
			if(self.canClick){
				self.canClick=false;
				var input = ves(this).parent(".order-quantity").find("input");
				var num = parseInt(input.val());			
				ves.ajax({ url: "/cart/mineOneGoods",data:{goods_id:goods_id},success: function (http) {										
					if(http.status==1){							
						if(--num <= 0){
							input.val(0);
						}else{
							input.val(num);
						}			
						self.calculateTotalAmount();
						self.canClick=true;
					}else{
						alert(http.msg);
					}
				}});
			}
		};
		//获取省列表
		self.getProviceList=function(){	
			ves.ajax({ url: "/Region/Lists.json",data:{parent_id:1},success: function (http) {										
				if(http.status==1){									
					var temp=http.data;
					var arr=[];
					for(var i=0;i<temp.length;i++){
						arr.push({index:temp[i].region_id,province:temp[i].region_name});
					}		
					self.provinceList(arr);					
				}else{
					alert(http.msg);
				}
			}});
		};
		//获取对应的市列表
		self.provinceChange=function(e){	
			optionHtml = '<option value ="0">请选择市</option>';
			var region_id = ves(this).val();
			ves.ajax({ url: "/Region/Lists.json",data:{parent_id:region_id,region_type:2},success: function (http) {
				if(http.status==1){	
					var temp=http.data;
					for(var i=0;i<temp.length;i++){
						optionHtml += '<option value ="'+temp[i].region_id+'">'+temp[i].region_name+'</option>';
					}
					ves('.select-city').html(optionHtml);
					ves('.select-district').html('<option value ="0">请选择区</option>');
					ves('.select-town').html('<option value ="0">请选择街道/镇</option>');
					self.calculateTotalAmount();
				}else{
					alert(http.msg);
				}
			}})
		};
		self.cityChange=function(){				
			var city_id = ves(this).val();
			optionHtml = '<option value ="0">请选择区</option>';
			ves.ajax({ url: "/Region/Lists.json",data:{parent_id:city_id,region_type:3},success: function (http) {										
				if(http.status==1){									
					var temp=http.data;	
					for(var i=0;i<temp.length;i++){
						optionHtml += '<option value ="'+temp[i].region_id+'">'+temp[i].region_name+'</option>';
					}
					ves('.select-district').html(optionHtml);
					ves('.select-town').html('<option value ="0">请选择街道/镇</option>');
				}else{
					alert(http.msg);
				}
			}});
		};
		self.districtChange=function(){						
			var district_id = ves(this).val();
			ves.ajax({ url: "/Region/Lists.json",data:{parent_id:district_id,region_type:4},success: function (http) {										
				if(http.status==1){									
					var temp=http.data;		
					optionHtml = '<option value="0">请选择街道/镇</option>';
					for(var i=0;i<temp.length;i++){
						optionHtml += '<option value ="'+temp[i].region_id+'">'+temp[i].region_name+'</option>';
					}
					ves('.select-town').html(optionHtml);
					console.log('获取街道列表成功');
				}else{
					console.log(http.msg);
				}
			}});
		};
		//获取商品总金额
		self.calculateTotalAmount=function(){
			var consignee=ves('input[name=username]').val();//收件人
			var mobile=ves('input[name=mobile]').val();//手机号
			var province=parseInt(ves('select[name=province]').val());
			var city=parseInt(ves('select[name=city]').val());
			var district=parseInt(ves('select[name=district]').val());
			var town=parseInt(ves('select[name=town]').val());
			var address=ves('input[name=address]').val();
			var attribute=ves('select[name=attribute]').val();
			var bonus_sn=ves('input[name=bonus_sn]').val();
			var payment_id=self.pay_ind();
			if(consignee=="")consignee="";
			if(mobile=="")mobile="";
			if(address=="")address="";
			if(attribute=="")attribute="";
			if(bonus_sn=="")bonus_sn="";
			if(!province)province=0;
			if(!city)city=0;
			if(!district)district=0;
			if(!town)town=0;
			ves.ajax({url: "/OnlinePayment/quickAggregate.json",data:{consignee:consignee,mobile:mobile,province:province,city:city,district:district,town:town,address:address,attribute:attribute,bonus_sn:bonus_sn,payment_id:payment_id},success: function (http) {										
				if(http.status==1){									
					var temp=http.data;	
					if(temp!=""){
						self.token(temp.token);
						self.priceList([{originalPrice:temp.goods_price,preferentialPrice:temp.pay_fee_discount,shipping_fee:temp.shipping_fee,need_pay:temp.amount}]);
					}
					self.bonusChecked(false);
					if(temp.bonus!=""){
						ves('input[name=bonus_sn]').siblings(".ok").html("正确");
					}else{
						ves('input[name=bonus_sn]').siblings(".ok").html("");
					}					
				}else{
					if(payment_id!='1'){
						self.priceList([{originalPrice:"0",preferentialPrice:"10",shipping_fee:"0",need_pay:"0"}]);
					}else{
						self.priceList([{originalPrice:"0",preferentialPrice:"0",shipping_fee:"0",need_pay:"0"}]);					
					}
					if(http.msg=="此优惠券不存在！"){
						self.bonusChecked(true);
						ves('input[name=bonus_sn]').siblings(".error").html("此优惠券不存在");
		        		document.querySelector('.bonus').focus();
					}
					console.log(http.msg);
				}
			}});
		};
		//获取验证码
		self.getCode=function(){
			if(ves("input[name=mobile]").val() == '' || /^1[3-9]{1}[0-9]{9}$/.test(ves("input[name=mobile]").val()) == false){
	            alert('您输入的手机号有误');
	            return false;
	        }
			var time=function(o) {
		        if (self.wait_t == 0) {
		            self.disabled(true);
		            o.value="获取验证码";
		            self.wait_t = 60;		            
		        } else {
		        	self.disabled(false);
		            o.value= + self.wait_t + "秒后重发";
		            self.wait_t--;
		            setTimeout(function() {
	                    time(o)
	                }, 1000);
		        }
		        ves('.phone_code').addClass('on');
		    };
	        time(this);
	        var mobile = ves("input[name=mobile]").val();        
	        ves.ajax({url:"/User/sendSms.json",dataType:'json',
data:{mobile:mobile},function(http){
	            if(http.status==1){
	                alert("发送成功");
	            }else{
	                alert(http.msg);
	            }
	        }});		    
		};			
		self.getPayIndex=function(){
			var pay_id=ves(this).val();
			self.pay_ind(pay_id);
			ves(this.parentNode).addClass('checked').siblings('label').removeClass('checked');
			self.calculateTotalAmount();
		};
		self.checkName=function(){
			var $username = ves(this).val();
			if($username== '' || $username.length<2){
		        self.nameChecked(true);		        
		        ves(this).siblings(".error").html("不少于两个字符");
		        this.focus();
		    }else{
		    	self.nameChecked(false);	
		    	ves(this).siblings(".ok").html("√");
		    }
		};

		self.checkAddr=function(){
			var addr_str=ves(this).val();
			if(addr_str=="" || /^[\u0391-\uFFE5A-Za-z0-9\s]+$/.test(addr_str)==false||addr_str.length < 5){
				self.addrChecked(true);		        
		        ves(this).siblings(".error").html("不少于五个字符");
		        this.focus();
			}else{
				self.addrChecked(false);
				ves(this).siblings(".ok").html("√");
			}			
		};
		self.checkForm=function(){
			var $goods_num_input = ves("input[name=goods_num]");
		    var $username_input = ves("input[name=username]");
		    var $mobile_input = ves("input[name=mobile]");
		    var $address_input = ves("input[name=address]");
		    var $goods_num = $goods_num_input.val() || 0;
		    var province=ves('select[name=province]').val();
			var city=ves('select[name=city]').val();
			var district=ves('select[name=district]').val();
			var attribute=ves('select[name=attribute]').val();
		    if($goods_num < 1){
		        alert("请至少选择一件商品！");
		        return false;
		    }   	
		    if($username_input.val()==""){
		    	alert("请输入收件人姓名！");
		        return false;
		    }
		    if(ves("input[name=mobile]").val() == ''){
		        alert("请输入手机号码！");
		        return false;
		    }
		    if(ves('#getc_btn').val()!=undefined && ves("input[name=telnum]").val() == ''){
		        alert("请填写短信验证码！");
		        return false;
		    }
		    if(province==""){
		    	alert("请选择省！");
		        return false;
		    }
		    if(city==""){
		    	alert("请选择市！");
		        return false;
		    }
		    if(district==""){
		    	alert("请选择区！");
		        return false;
		    }	
		    if(self.nameChecked() || self.addrChecked()||self.bonusChecked()){
		    	alert("信息填写错误！")
		    	return false;
		    }
		    //提交订单
	        var remark="";
	        var code=ves("input[name=telnum]").val();	
	        ves.ajax({url:"/OnlinePayment/quickOrder.json",
data:{remark:remark,code:code,token:self.token()},function(http){
	            if(http.status==1){
	                alert(http.data);
	            }else if(http.status==0){
	                alert(http.msg);
	            }
	        }});
		};		
	}
	var _orderView=new orderView();
	_orderView.init();
	viewModel.orderCpt(_orderView);
})



	