$(function(){
	
	$('.member-nav a').on('click', function(){
		var $self = $(this);
		$self.addClass('on').siblings().removeClass('on');
	});
	
	$('.member-order-hd a').on('click', function(){
		var $self = $(this);
		$self.addClass('on').siblings().removeClass('on');
		$('.member-order-list').hide().eq($self.index()).show();
	});
	
	
//	$.post('/Order/Lists.json', function(res){
//		console.log(res);
//		var orderViewModel = {
//			orderList : [
//				{
//					order_id: '123',
//					order_sn: 123456789,
//					add_date: '2016.04.21',
//					pay_status: 0
//				},
//				{
//					order_id: '123',
//					order_sn: 123456789,
//					add_date: '2016.04.21',
//					pay_status: 1
//				},
//				{
//					order_id: '123',
//					order_sn: 123456789,
//					add_date: '2016.04.21',
//					pay_status: 2
//				}
//			],
//			getOrderDetail : function(){
//				var id = this.order_id;
//				$.post('/Order/Info.json', {order_id:id}, function(res){
//					
//				});
//			}
//		}
//		console.log(orderViewModel);
//		ko.applyBindings(orderViewModel, document.getElementById('memberOrder'));
//	});
	
	var vm_Order;
	$.post('/Order/Lists.json', function(res){
		console.log(res);
		
		
		vm_Order = new Vue({
			el: '#vm_Order',
			data: {
				list: res.data.list,
//				list: [
//					{
//						order_id: '123',
//						order_sn: 123456789,
//						add_date: '2016.04.21',
//						pay_status: 0
//					},
//					{
//						order_id: '123',
//						order_sn: 123456789,
//						add_date: '2016.04.21',
//						pay_status: 1
//					},
//					{
//						order_id: '123',
//						order_sn: 123456789,
//						add_date: '2016.04.21',
//						pay_status: 2
//					}
//				]
				classObject: {
					
				}
			},
//			classObject: {
//				not-payment: true
//			},
			methods: {
				getOrderDetail: function(id){
					console.log(this);
				}
			}
		});
		
	});
	
});