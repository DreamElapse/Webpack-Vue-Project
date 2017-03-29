/**
 *  微信编辑器
 */
(function($) {
    $.fn.extend({
            insertContent : function(text) {
                var obj= $(this)[0];
                var range, node;
                if(!obj.hasfocus) {
                    obj.focus();
                }
                if (window.getSelection && window.getSelection().getRangeAt) {
                    range = window.getSelection().getRangeAt(0);
                    range.collapse(false);
                    node = range.createContextualFragment(text);
                    var c = node.lastChild;
                    range.insertNode(node);
                    if(c){
                        range.setEndAfter(c);
                        range.setStartAfter(c)
                    }
                    var j = window.getSelection();
                    j.removeAllRanges();
                    j.addRange(range);

                } else if (document.selection && document.selection.createRange) {
                    document.selection.createRange().pasteHTML(text);
                }
            }
        })
})(jQuery);

(function(){
    weditor = function(container) {
        var emotionAlt = ['微笑','撇嘴','色','发呆','得意','流泪','害羞','闭嘴','睡','大哭','尴尬','发怒','调皮','呲牙','惊讶','难过','酷','冷汗','抓狂','吐','偷笑', '可爱', '白眼', '傲慢','饥饿','困','惊恐','流汗','憨笑','大兵','奋斗','咒骂','疑问','嘘','晕','折磨','衰','骷髅','敲打','再见','擦汗','抠鼻','鼓掌','糗大了','坏笑','左哼哼','右哼哼','哈欠','鄙视','委屈','快哭了','阴险','亲亲','吓','可怜','菜刀','西瓜','啤酒','篮球','乒乓','咖啡','饭','猪头','玫瑰','凋谢','示爱','爱心','心碎','蛋糕','闪电','炸弹','刀','足球','瓢虫','便便','月亮','太阳','礼物','拥抱','强','弱','握手','胜利','抱拳','勾引','拳头','差劲','爱你','NO','OK','爱情','飞吻','跳跳','发抖','怄火','转圈','磕头','回头','跳绳','挥手','激动','街舞','献吻','左太极','右太极'];
        var self = this;

        self.getRoot = function (js) {
            var scripts = document.getElementsByTagName("script");
            var path = "";
            for (var i = 0, l = scripts.length; i < l; i++) {
                var src = scripts[i].src;
                if (src.indexOf(js) != -1) {
                    var ss = src.split(js);
                    path = ss[0];
                    break;
                }
            }
            var href = location.href;
            href = href.split("#")[0];
            href = href.split("?")[0];
            var ss = href.split("/");
            ss.length = ss.length - 1;
            href = ss.join("/");
            if (path.indexOf("https:") == -1 && path.indexOf("http:") == -1 && path.indexOf("file:") == -1 && path.indexOf("\/") != 0) {
                path = href + "/" + path;
            }
            return path;
        };

        self.insertText = function(text){
            var obj= self.contentObj[0];
            var range, node;
            if(!obj.hasfocus) {
                obj.focus();
            }
            if (window.getSelection && window.getSelection().getRangeAt) {
                range = window.getSelection().getRangeAt(0);
                range.collapse(false);
                node = range.createContextualFragment(text);
                var c = node.lastChild;
                range.insertNode(node);
                if(c){
                    range.setEndAfter(c);
                    range.setStartAfter(c)
                }
                var j = window.getSelection();
                j.removeAllRanges();
                j.addRange(range);

            } else if (document.selection && document.selection.createRange) {
                document.selection.createRange().pasteHTML(text);
            }
        };

        //初始化
        self.init = function() {
            //创建表单元素
            self.contentObj = self.get('<div/>');
            self.contentObj.css({
                padding: '10px',
                height: self.container.css('height') || '300px'
            });
            self.contentObj.html(self.container.html());
            self.container
                .css({height:'auto', border: '1px solid #DDD'})
                .empty()
                .html(self.contentObj);
            self.setEdit();
            self.setEmotion();
            self.setValue();
        };

        self.setValue = function() {
            var hiddenObjName = container.replace('#', '').replace('.', '');
            var content = self.contentObj.html();
            var hiddenObj = self.container.find('[name="[name]"]'.replace('[name]', hiddenObjName));
            if(hiddenObj.length > 0) {
                hiddenObj.val(content);
            }else {
                hiddenObj = self.get('<input/>');
                hiddenObj.attr({type: 'hidden', name: hiddenObjName});
                hiddenObj.val(content).appendTo(self.container);
            }
            self.container.parent('form').submit(function(){
                self.setValue();
                return true;
            });
        }

        self.setEmotion = function() {
            var divObj = self.get("<div/>");//创建一个DIV
            divObj.css({
                borderTop:'1px solid #DDD',
                height: '35px',
                background: '#ededed',
                position: 'relative'
            });

            //创建图片路径
            var ulObj = self.get('<ul/>');
            var liObj = self.get('<li/>');
            var aObj = self.get('<a href="javascript:;"/>');
            aObj.css({
                'background': 'url("' + self.rootPath + 'images/emotion_editor_z218878.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0)',
                'display': 'inline-block',
                'height': '20px',
                'vertical-align': 'middle',
                'width': '20px',
                'margin-left': '20px',
                'line-height': '999em',
                'margin-top': '8px',
                'overflow': 'hidden'
            }).hover(function(){
                self.get(this).css('background-position', '0 -30px');
            },function(){
                self.get(this).css('background-position', '0 0px');
            }).click(function(){
                self.get(document).bind("click",function(e){
                    var target  = self.get(e.target);
                    if(target.closest(ulObj).length == 0 && target.closest(aObj).length == 0){
                        ulObj.hide();
                    }
                });
                ulObj.show();
            }).appendTo(divObj);

            ulObj.css({
                'width': '420px',
                'height': 'auto',
                'background-color': '#e7e7eb',
                'border-right': '1px solid #e7e7eb',
                'border-top': '1px solid #e7e7eb',
                'overflow': 'hidden'
            }).hide().appendTo(divObj);

            var src = 'https://res.wx.qq.com/mpres/htmledition/images/icon/emotion/[number].gif';
            for(var i = 0; i <= 104; i++) {
                var imgObj = self.get('<img/>');
                imgObj
                    .attr({'src':src.replace('[number]', i), alt: emotionAlt[i]})
                    .appendTo(
                        liObj
                            .clone()
                            .css({
                                'background-color': '#FFF',
                                'border-bottom': '1px solid #e7e7eb',
                                'border-left': '1px solid #e7e7eb',
                                'float': 'left',
                                'font-size': 0,
                                'height': '27px',
                                'line-height': '27px',
                                'text-align': 'center',
                                'width': '27px'
                            })
                            .click(function(){
                                self.get(self.contentObj).insertContent(self.get(this).html());
                            })
                            .appendTo(ulObj)
                    );
            }

            divObj.appendTo(self.container);
        };

        self.setEdit = function() {
            self.contentObj.attr('contentEditable', true);
            document.execCommand('2D-Position', true, true);
        };

        self.getText = function() {
            var contentObj = self.contentObj.clone();
            contentObj.find('img').each(function(){
                var self = self.get(this);
                self.replaceWith('/' + self.attr('alt'));
            });
            return contentObj.html();
        };

        /**
         * 获取对象
         * @param element
         * @returns {*|jQuery|HTMLElement}
         */
        self.get = function(element) {
            return $(element);
        };

        self.container = self.get(container);
        self.contentObj = {};
        self.emotionObj = {};
        self.rootPath = self.getRoot('weditor.js');
        self.init();
        return self;
    }
}());
