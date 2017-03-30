手机网站改版
===============

该项目使用的thinkPHP3.2.2框架为底层开发。使用了动静结合的技术开发。
前端使用纯静态的HTML页面，通过同步或异步或跨域等技术使用数据动态化。

项目目录结构如下：
~~~
www  WEB部署目录（或者子目录）
├─README.md             	README文件
├─Framework					框架目录
├─doc						项目文档目录
├─mobile					项目目录
	├─application			应用目录
	│	├─common			公共模块目录（可以更改）
	│	│	├─common		公共文件目录
	│	│	├─extend        扩展类库目录
	│	│	├─conf			公共配置文件目录
	│	│	├─lang			语言包
	│	│	├─controller    公共控制器目录
	│	│	└─model			公共模型目录
	│	├─module_name		模块目录
	│	│	├─controller	控制器目录
	│	│	├─model			目录目录
	│	│	├─view			视图目录
	│	│	├─conf			配置文件目录
	│	│	└─common		公共文件目录
	│	└─ ... 
	├─html					静态页面目录
	├─public				资源目录（存在图片、js、css）
	├─runtime           	应用的运行时目录（可写，可定制）
	├─template				模版目录			
	├─upload				文件上传存储目录
	├─index.php				入口文件
	├─.htaccess				用于apache的重写
	└─nginx.conf			用于nginx的重写
~~~

接口说明：
接口地址使用设置了伪静态模式。
数据接口地址使用.json后缀，并放回json格式数据
其他接口地址使用.shtml后缀，返回对应的页面或信息（例如：输出图片验证码：xxxx.verfiy.shtml？随机数）















	
	