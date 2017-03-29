(function() {
	var $=ves;
	var alerts=[];
	$.alert=function(param,complete,type,closeTime) {
		if(param==undefined||param==null)param+='';
		var _alert={};
		alerts.push(_alert);
		if(!param.content) param={ content: param };
		if(alerts.length==1) $.html.addClass('ves-alert');
		var alert=document.createElement('div');
		alert.id='alert';
		alert.className='ves';
		alert.style.zIndex='1000'+alerts.length;
		var body=document.createElement('div');
		body.className="body";
		alert.appendChild(body);
		var content=document.createElement('div');
		content.className="content";
		body.appendChild(content);
		param.complete=param.complete?param.complete:typeof (complete)=='function'?complete:typeof (type)=='function'?type:typeof (closeTime)=='function'?closeTime:null;
		param.type=param.type?param.type:typeof (complete)=='string'?complete:typeof (type)=='string'?type:typeof (closeTime)=='string'?closeTime:null;
		param.closeTime=param.closeTime!=undefined?param.closeTime:typeof (complete)=='number'?complete:typeof (type)=='number'?type:typeof (closeTime)=='number'?closeTime:null;
		param.buttons=param.buttons?param.buttons:$.alert.buttons;
		var focusButton=null;
		if(param.type!='notify'&&param.type!='wait') {
			var bottom=document.createElement('div');
			bottom.className="input";
			var button;
			if(param.type=='confirm') {
				alert.className='ves confirm';
				button=document.createElement('input');
				button.type='button';
				button.value=param.buttons.no;
				button.className='button cancel';
				$(button).bind('tap',function() {
					$.alert.close(false);
					ves.event.stopParent();
					ves.event.stopDefault();
				});
				bottom.appendChild(button);
			}
			focusButton=document.createElement('input');
			focusButton.type='button';
			focusButton.value=param.buttons.yes;
			focusButton.className='button fix';
			$(focusButton).bind('tap',function() {
				$.alert.close(true);
				ves.event.stopParent();
				ves.event.stopDefault();
			});
			bottom.appendChild(focusButton);
			body.appendChild(bottom);
		}
		else {
			if(param.type=='notify') {
				param.closeTime=param.closeTime!=undefined?param.closeTime:2;
				alert.className='ves notify';
			}
			else if(param.type=='wait') {
				param.closeTime=null;
				alert.className='ves wait';
			}
		}
		_alert.holder=$(alert);
		_alert.content=$(content);
		_alert.targetParent=null;
		_alert.targetNext=null;
		$.body.append(_alert.holder);
		_alert.complete=param.complete;
		_alert.context=param.context;
		if(param.style) _alert.holder.addClass(param.style);
		_alert.content.html('');
		if(param.url&&$(param.content).length==0) {
			$.alert({content:'<span class="ic-load"></span>',type:'wait',style:'clear'});
			$.ajax({ url: param.url,dataType: param.dataType,context: _alert,success: function(request) {
				this.content.html(request);
				setTimeout(function() {
					$.alert.close();
					_alert.holder.addClass('view');
				},0);
			}
			});
		}
		else {
			var target=$(param.content);
			if(target.length>0) {
				_alert.target=target[0];
				_alert.targetParent=target[0].parentNode;
				_alert.targetNext=target[0].nextSibling;
				_alert.content.append(target[0]);
			}
			else _alert.content.html(param.content);
			$('form',_alert.content).each(function() {
				this.onsubmit=function() {
					$.alert.close(true);
					return false;
				}
			});
			setTimeout(function() { _alert.holder.addClass('view'); },0);
		}
		if(param.closeTime!=undefined) {
			var button=$('.button',_alert.holder);
			button.val(param.buttons.yes+' ( '+param.closeTime+' )');
			param.closeTime-=1;
			_alert.closeTimer=window.setInterval(function() {
				if((param.closeTime>0)==false) {
					window.clearInterval(_alert.closeTimer);
					button.val(param.buttons.yes);
					$.alert.close();
					return;
				}
				button.val(param.buttons.yes+' ( '+param.closeTime+' )');
				param.closeTime-=1;
			},1000);
		}
		if(focusButton)
			focusButton.focus();
		_alert.holder[0].scrollTop=0;
		return this;
	};
	$.alert.close=function(ok) {
		if(alerts.length==0) return this;
		var _alert=alerts[alerts.length-1];
		window.clearInterval(_alert.closeTimer);
		if(_alert.target&&_alert.targetParent) {
			if(_alert.targetNext)
				_alert.targetParent.insertBefore(_alert.target,_alert.targetNext);
			else _alert.targetParent.appendChild(_alert.target);
		}
		else {
			var child=_alert.content.children('.alert:first');
			if(child.length==1) {
				ves.body.append(child[0]);
			}
		}
		_alert.holder.remove();
		if(alerts.length==1) $.html.removeClass('ves-alert');
		alerts.pop();
		if(typeof (_alert.complete)=='function') {
			_alert.complete.call(_alert.context,ok);
		}
		return this;
	};
	$.alert.buttons={ no: '取消',yes: '确定' };
	$.alert.closeAll=function(){
		for(var i=0;i<alerts.length;i++){
			$.alert.close();
		}
	};
})();