var LazyLoad = function(config) {
	this.imgs = document.querySelectorAll(config.query || "img.lazy");
	this.top = config.top ? config.top : 0;
	this.bottom = config.top ? config.top : 0;
	this.data = "src";
	this.placeholder = config.placeholder ? config.placeholder : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OEE0RTNERUQwMDI4MTFFNThFNTZFNDBCODk3RjgxQTQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OEE0RTNERUUwMDI4MTFFNThFNTZFNDBCODk3RjgxQTQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4QTRFM0RFQjAwMjgxMUU1OEU1NkU0MEI4OTdGODFBNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4QTRFM0RFQzAwMjgxMUU1OEU1NkU0MEI4OTdGODFBNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhSZ6Q0AAAAQSURBVHjaYv7//z8DQIABAAkLAwFHaU43AAAAAElFTkSuQmCC";
	this.init(config);
}
LazyLoad.prototype = {
	init: function(config) {
		var _this = this;
		_this.imgs = Array.prototype.slice.call(_this.imgs);
		_this.setPlaceholder();
		_this.forDom();
		_this.bind(window, 'scroll', function() {
			_this.forDom();
		});
	},
	setPlaceholder: function(){
		for(var i = 0; i < this.imgs.length; i++){
			if(this.imgs[i].src === "" || this.imgs[i].getAttribute("src") === null){
				this.imgs[i].src = this.placeholder;
			}
		}
	},
	forDom: function() {
		for (var i = 0; i < this.imgs.length; i++) {
			var img = this.imgs[i];
			if (this.appear(img)) {
				this.setSrc(img, img.dataset[this.data]);
				this.imgs.splice(i, 1);
				i--;
			}
		}
	},
	appear: function(id) {
		var obj = typeof id == 'object' ? id : document.getElementById(id),
			objTop = obj.getBoundingClientRect().top,
			docHeight = document.documentElement.clientHeight;
		return objTop + this.top <= docHeight && objTop - this.bottom > 0;
	},
	setSrc: function(target, src) {
		var c = new Image();
		c.onload = function(){
			target.src = src;
			target.style.opacity = 0;
			setTimeout(function(){
				target.style.webkitTransitionDuration = "0.2s";
				target.style.transitionDuration = "0.2s";
				target.style.opacity = 1;
				var r = target.className;
				target.className = r.replace("lazy", "");
			}, 20);
			c.onload = null;
		}
		c.src = src;
	},
	bind: function(obj, type, fn) {
		if (obj.addEventListener) {
			obj.addEventListener(type, fn, false);
		} else if (obj.attachEvent) {
			obj.attachEvent('on' + type, fn);
		} else {
			obj['on' + type] = fn;
		}
	}
}

$(function(){
	new LazyLoad({
		top: -50,
		bottom: 0,
		placeholder: "../public/images/common/transparent.png"
	});
});