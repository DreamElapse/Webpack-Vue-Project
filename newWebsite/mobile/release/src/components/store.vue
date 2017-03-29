<script>
	export default {
		ready() {
			$('#clic_btn').click(function(){
				var clcik_city=$('#get_local option').not(function(){ return !this.selected }).text();
				$('.address dl').hide();
				if(clcik_city=='韩国瓷肌医学美肤'){
					$('#beat_skin dl').show();
					$('#cou_num').html($('#beat_skin dl').length);
				}else if(clcik_city=='瓷肌肌肤修复中心'){
					$('#restore dl').show();
					$('#cou_num').html($('#restore dl').length);
				}else if(clcik_city=='韩国4ever整形'){
					$('#hostiptal dl').show();
					$('#cou_num ').html($('#hostiptal dl').length);
				}else{
					$('#cou_num ').html(0);
				}
			});
		}
	}
</script>

<template>
	<div class="conta_mar">

		<div class="s_tit">
			<h2>实体门店地址</h2>
			<h3>寻找您就近的门店</h3>
		</div>
		<div class="sear_wrap">
			<div class="s_c">
				<select name="" id="get_local">
					<option value="0">请选择瓷肌项目</option>
					<option value="韩国瓷肌医学美肤">韩国瓷肌医学美肤</option>
					<!--<option value="瓷肌肌肤修复中心">瓷肌肌肤修复中心</option>-->
					<option value="韩国4ever整形">韩国4ever整形</option>
				</select>
			<span id="clic_btn">搜  索</span>
			</div>
			<p><i class="font icon-search"></i>共搜索到<i id="cou_num">0</i>个结果</p>
		</div>
		<div class="address">
			<div id="beat_skin">
				<dl>
					<dt><h3>韩国瓷肌医学美肤（广州）</h3></dt>
					<dd>
						<p>广东省 广州市 天河区 珠江新城黄埔大道西118号<br /><b class="tel_pho">400-8125-800</b></p>
					</dd>
				</dl>
				<dl>
					<dt><h3>馨锐•韩国瓷肌医学美肤（成都）</h3></dt>
					<dd>
						<p>成都锦江区红星路四段265号国光商厦三楼<br /><b class="tel_pho">400-8125-800</b></p>
					</dd>
				</dl>
			</div>
			<div id="restore">
				<!-- 广东省 -->
				<div id="gd_city">
					<!-- 广州市 -->
					<dl>
						<dt><h3>天河龙洞店</h3></dt>
						<dd>
							<p>广东省 广州市 天河区 龙洞步行街翠竹5巷1号2楼（龙洞小食街对面, 名创优品左走30米）<br /><b class="tel_pho">020-4575337</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>天河正佳店</h3></dt>
						<dd>
							<p>广东省 广州市 天河区 体育东路35号天盛大厦北梯507室（正佳广场东南门对面，潮流站北梯5楼）<br /><b class="tel_pho">020-87539446</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>天河车陂店</h3></dt>
						<dd>
							<p>广东省 广州市 天河区 中山大道中85号—03<br /><b class="tel_pho">020-82300199</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>海珠江南西店</h3></dt>
						<dd>
							<p>广东省 广州市 海珠区 江南大道中185号—1四楼（地铁：江南西C出口前走20米）<br /><b class="tel_pho">020-84416116</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>海珠客村店</h3></dt>
						<dd>
							<p>广东省 广州市 海珠区 新港西路350号影城花园D座二楼（麦当劳旁边）（地铁：客村站C出口左走100米 公交：珠影站）<br /><b class="tel_pho">020-34259455</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>番禺市桥店</h3></dt>
						<dd>
							<p>广东省 广州市 番禺区 市桥大北路316号（大北路与富华路交界处，市桥汽车站东门斜对面，地铁3号线市桥站D出口走十分钟左右）<br /><b class="tel_pho">020-34519448</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>番禺大石店</h3></dt>
						<dd>
							<p>广东省 广州市 番禺区 大石牌坊105国道大石线段458号（大石牌坊旁）<br /><b class="tel_pho">020-83838456</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>越秀中华广场店</h3></dt>
						<dd>
							<p>广东省 广州市 越秀区 中山三路东昌大街1号（二楼）中华广场旁（地铁站：烈士陵园A出口）<br /><b class="tel_pho">020-39169885</b></p>
						</dd>
					</dl>
					<!-- 广州市 -->
					<!-- 珠海市 -->
					<dl>
						<dt><h3>珠海南坑旗舰店</h3></dt>
						<dd>
							<p>广东省 珠海市 香洲南坑新一佳右侧<br /><b class="tel_pho">0756-2289069</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>珠海拱北分院</h3></dt>
						<dd>
							<p>广东省 珠海市 珠海市粤海中路艺丰大厦二楼明爱医疗祛痘专科<br /><b class="tel_pho">0756-8889599</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>珠海井岸店</h3></dt>
						<dd>
							<p>广东省 珠海市 井岸镇井湾路363号<br /><b class="tel_pho">0756-8506088</b></p>
						</dd>
					</dl>
					<!-- 珠海市 -->
					<!-- 中山门店 -->
					<dl>
						<dt><h3>中山三乡店</h3></dt>
						<dd>
							<p>广东省 中山市 三乡镇雅居乐新城二期C4幢39号铺<br /><b class="tel_pho">0760~85888559</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中山古镇店</h3></dt>
						<dd>
							<p>广东省 中山市 古镇新兴东路大信新都汇5楼512<br /><b class="tel_pho">0760-87560988</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中山大信店</h3></dt>
						<dd>
							<p>广东省 中山市 石岐区大信商业街第178卡（世宇乐园旁）<br /><b class="tel_pho">0756-85755809</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中山总店</h3></dt>
						<dd>
							<p>广东省 中山市 石岐区安栏路中恳百货旁<br /><b class="tel_pho">0760-85756756</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中山小榄店</h3></dt>
						<dd>
							<p>广东省 中山市 升平中路丽阁酒店旁<br /><b class="tel_pho">0760-22252522</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中山坦洲店</h3></dt>
						<dd>
							<p>广东省 中山市 坦神北路18号<br /><b class="tel_pho">0760-86158118</b></p>
						</dd>
					</dl>
					<!-- 中山门店 -->
					<!-- 佛山店 -->
					<dl>
						<dt><h3>佛山市顺德区容桂店</h3></dt>
						<dd>
							<p>广东省 佛山市 顺德区 容桂桂洲大道中96号铺<br /><b class="tel_pho">0757-29321811</b></p>
						</dd>
					</dl>
					<!-- 佛山店 -->
				</div>
				<div id="js_city">
					<!-- 常州市 -->
					<dl>
						<dt><h3>常州市瓷肌医生怀德店</h3></dt>
						<dd>
							<p>江苏省 常州市 怀德中路116号(大润发对面）<br /><b class="tel_pho">0519-88997333</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>常州市瓷肌医生湖塘店</h3></dt>
						<dd>
							<p>江苏省 常州市 花园街230号（广电路交叉口）<br /><b class="tel_pho">0519-86506333</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>常州市瓷肌医生红梅店</h3></dt>
						<dd>
							<p>江苏省 常州市 竹林南路87号（红梅假日花苑对面)<br /><b class="tel_pho">0519-88990867</b></p>
						</dd>
					</dl>
					<!-- 常州市 -->
					<!-- 常熟市 -->
					<dl>
						<dt><h3>常熟市瓷肌医生泰安店</h3></dt>
						<dd>
							<p>江苏省 常熟市 泰安衔65号<br /><b class="tel_pho">0512-52897886</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>常熟市瓷肌医生珠江店</h3></dt>
						<dd>
							<p>江苏省 常熟市 珠江东路96号（黄金海岸底楼电梯旁)<br /><b class="tel_pho">0512-52886881</b></p>
						</dd>
					</dl>
					<!-- 常熟市 -->
					<!-- 张家港 -->
					<dl>
						<dt><h3>张家港市瓷肌医生暨阳店</h3></dt>
						<dd>
							<p>江苏省 张家港市暨阳中路449号<br /><b class="tel_pho">0512-56323658</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>张家港市瓷肌医生花园店</h3></dt>
						<dd>
							<p>江苏省 张家港市沙州东路13-2号<br /><b class="tel_pho">0512-35060115</b></p>
						</dd>
					</dl>
					<!-- 张家港 -->
				</div>
			</div>
			<!-- 整形医院 -->
			<div id="hostiptal">
				<div id="china">
					<dl>
						<dt><h3>中国青岛店</h3></dt>
						<dd>
							<p>中国山东省青岛市崂山区香港东路137<br /><b class="tel_pho">0532-6778-5190</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中国广州店</h3></dt>
						<dd>
							<p>广东省广州市天河黄埔大道西118号勤建大厦首层西侧1、2、4F<br /><b class="tel_pho">400-8125-800</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>中国成都店</h3></dt>
						<dd>
							<p>四川省成都市锦江区红星路四段265号3F<br /><b class="tel_pho">400-8125-800</b></p>
						</dd>
					</dl>
				</div>
				<div id="korea">	
					<dl>
						<dt><h3>江南店</h3></dt>
						<dd>
							<p>首尔市江南区论岘洞201-12 ES tower 3F-6F<br /><b class="tel_pho">1600-8343</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>仁川富平店</h3></dt>
						<dd>
							<p>仁川富平区富平洞159-15 3F<br /><b class="tel_pho">1644-8343</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>盆塘店</h3></dt>
						<dd>
							<p>京畿道城南盆塘区书岘洞251-1 9F<br /><b class="tel_pho">1577-8348</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>江西店</h3></dt>
						<dd>
							<p>首尔特别市江西区登村洞717 3F<br /><b class="tel_pho">1577-8350</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>麻浦店</h3></dt>
						<dd>
							<p>首尔特别市麻浦区新孔德洞171 2F<br /><b class="tel_pho">1600-8345</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>弘济店</h3></dt>
						<dd>
							<p>首尔特别市西大门区弘济洞172-11 2F<br /><b class="tel_pho">1600-8349</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>釜山海云台店</h3></dt>
						<dd>
							<p>釜山广域市海云台区佑洞1432 2F<br /><b class="tel_pho">1577-6346</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>蚕室店</h3></dt>
						<dd>
							<p>首尔特别市松坡区石村洞158-3JTower 3F<br /><b class="tel_pho">1600-5026</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>首尔站店</h3></dt>
						<dd>
							<p>首尔特别市中区南大门路5街541下1F<br /><b class="tel_pho">1600-8347</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>丽水店</h3></dt>
						<dd>
							<p>首尔特别市瑞草区方背洞1808 Anseok大厦6F<br /><b class="tel_pho">1577-8346</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>安养店</h3></dt>
						<dd>
							<p>京畿道安养市东安区虎溪洞1043-1 3F<br /><b class="tel_pho">1588-0335</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>水原店</h3></dt>
						<dd>
							<p>京畿道水原市劝善区劝善洞1023-1 2F<br /><b class="tel_pho">1670-3575</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>釜山西面店</h3></dt>
						<dd>
							<p>釜山广域市釜山镇区釜田洞257-3 16F<br /><b class="tel_pho">1600-8342</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>大田店</h3></dt>
						<dd>
							<p>大田市西区屯山洞1046 3F<br /><b class="tel_pho">1899-8300</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>昌原店</h3></dt>
						<dd>
							<p>昌原市船山区中央洞75-4 6F<br /><b class="tel_pho">1899-8033</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>清州店</h3></dt>
						<dd>
							<p>清州兴德区江西洞478 3F<br /><b class="tel_pho">1600-8234</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>一山店</h3></dt>
						<dd>
							<p>高阳市一山东区獐项洞855 4F<br /><b class="tel_pho">1800-8352</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>九老店</h3></dt>
						<dd>
							<p>首尔市九老区九老洞222 -31 2F<br /><b class="tel_pho">1644-8341</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>天安店</h3></dt>
						<dd>
							<p>天安市东南区新富洞451 -1 6F<br /><b class="tel_pho">1577-4258</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>狎欧亭店</h3></dt>
						<dd>
							<p>首尔市江南区新沙洞585-9<br /><b class="tel_pho">1577-3983</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>芦原店</h3></dt>
						<dd>
							<p>首尔市芦原区上溪洞712 2F<br /><b class="tel_pho">1644-8351</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>九里店</h3></dt>
						<dd>
							<p>九里市仁昌洞676-1 7F<br /><b class="tel_pho">1833-8352</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>芦原店</h3></dt>
						<dd>
							<p>蔚山市南区三山路231 4F<br /><b class="tel_pho">1800-8375</b></p>
						</dd>
					</dl>
					<dl>
						<dt><h3>济州店</h3></dt>
						<dd>
							<p>济州市连洞251-31 2F<br /><b class="tel_pho">1588-5467</b></p>
						</dd>
					</dl>
				</div>
			</div>
			<!-- 整形医院 -->
		</div>

</div>
</template>

<style scoped>
	.s_tit{padding-top:1.5em;}
	.s_tit h2,.s_tit h3{text-align:center;font-size:0.8em;color:#a37e2c;}
	.s_tit h3{font-size:1.5em;color:#000;margin-bottom:0.5em;}
	.sear_wrap{overflow: hidden;text-align:center;}
	.sear_wrap .s_c{display:inline-block;width:100%;padding:0 1em 0.4em;}
	.sear_wrap p{font-size:1.1em;position: relative;color:#6b6b6b;}
	.sear_wrap p:before{position: absolute;top:50%;left:17%;margin-top:0.5px;content:"";width:12%;height:1px;background:#e1e1e1;}
	.sear_wrap p:after{position: absolute;top:50%;right:17%;margin-top:0.5px;content:"";width:12%;height:1px;background:#e1e1e1;}
	.sear_wrap p .icon-search{margin-right:0.2em;vertical-align:middle;color:#b6b6b6;}
	.sear_wrap select{height:2.5em;width:65%;font-size:1.2em;outline:none;}
	.sear_wrap select option{display:block;border:1px solid #ccc;outline:none;}
	.sear_wrap span{display:inline-block;background:#f9bc47;color:#fff;vertical-align:middle;border:1px solid #f9bc47;margin-left:0.5em;padding:0 1em;font-size:1.2em;height:2.5em;line-height:2.5em;}
	.address{padding:0.8em;}
	.address dl{display:none;}
	#cou_num{color:#a37e2c;font-style:normal;margin:0 0.2em;}
	.address dl{color:#7d7d7d;margin-bottom:1.5em;}
	.address dl h3{font-size:1.4em;color:#212121;margin-bottom:0.2em;font-weight:normal;}
	.address dl p{font-size:1em;line-height:1.6em;}
	b.tel_pho{color:#757575;font-size:1.2em;margin-top:0.25em;font-weight:normal;}
</style>