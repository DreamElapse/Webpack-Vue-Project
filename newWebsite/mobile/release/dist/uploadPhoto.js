webpackJsonp([47],{245:function(e,t,n){var i,r,o;(function(e){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}var s=n(112),l=a(s);!function(n,a){if("object"==(0,l.default)(t)&&"object"==(0,l.default)(e))e.exports=a();else{r=[],i=a,o="function"==typeof i?i.apply(t,r):i,!(void 0!==o&&(e.exports=o))}}(void 0,function(){return function(e){function t(i){if(n[i])return n[i].exports;var r=n[i]={exports:{},id:i,loaded:!1};return e[i].call(r.exports,r,r.exports,t),r.loaded=!0,r.exports}var n={};return t.m=e,t.c=n,t.p="",t(0)}([function(e,t,n){n(6),n(7),e.exports=n(8)},function(e,t,n){(function(t){!function(n){function i(e,t){return function(){e.apply(t,arguments)}}function r(e){if("object"!=(0,l.default)(this))throw new TypeError("Promises must be constructed via new");if("function"!=typeof e)throw new TypeError("not a function");this._state=null,this._value=null,this._deferreds=[],c(e,i(a,this),i(s,this))}function o(e){var t=this;return null===this._state?void this._deferreds.push(e):void f(function(){var n=t._state?e.onFulfilled:e.onRejected;if(null===n)return void(t._state?e.resolve:e.reject)(t._value);var i;try{i=n(t._value)}catch(t){return void e.reject(t)}e.resolve(i)})}function a(e){try{if(e===this)throw new TypeError("A promise cannot be resolved with itself.");if(e&&("object"==("undefined"==typeof e?"undefined":(0,l.default)(e))||"function"==typeof e)){var t=e.then;if("function"==typeof t)return void c(i(t,e),i(a,this),i(s,this))}this._state=!0,this._value=e,d.call(this)}catch(e){s.call(this,e)}}function s(e){this._state=!1,this._value=e,d.call(this)}function d(){for(var e=0,t=this._deferreds.length;t>e;e++)o.call(this,this._deferreds[e]);this._deferreds=null}function u(e,t,n,i){this.onFulfilled="function"==typeof e?e:null,this.onRejected="function"==typeof t?t:null,this.resolve=n,this.reject=i}function c(e,t,n){var i=!1;try{e(function(e){i||(i=!0,t(e))},function(e){i||(i=!0,n(e))})}catch(e){if(i)return;i=!0,n(e)}}var f="function"==typeof t&&t||function(e){setTimeout(e,1)},h=Array.isArray||function(e){return"[object Array]"===Object.prototype.toString.call(e)};r.prototype.catch=function(e){return this.then(null,e)},r.prototype.then=function(e,t){var n=this;return new r(function(i,r){o.call(n,new u(e,t,i,r))})},r.all=function(){var e=Array.prototype.slice.call(1===arguments.length&&h(arguments[0])?arguments[0]:arguments);return new r(function(t,n){function i(o,a){try{if(a&&("object"==("undefined"==typeof a?"undefined":(0,l.default)(a))||"function"==typeof a)){var s=a.then;if("function"==typeof s)return void s.call(a,function(e){i(o,e)},n)}e[o]=a,0===--r&&t(e)}catch(e){n(e)}}if(0===e.length)return t([]);for(var r=e.length,o=0;o<e.length;o++)i(o,e[o])})},r.resolve=function(e){return e&&"object"==("undefined"==typeof e?"undefined":(0,l.default)(e))&&e.constructor===r?e:new r(function(t){t(e)})},r.reject=function(e){return new r(function(t,n){n(e)})},r.race=function(e){return new r(function(t,n){for(var i=0,r=e.length;r>i;i++)e[i].then(t,n)})},r._setImmediateFn=function(e){f=e},r.prototype.always=function(e){var t=this.constructor;return this.then(function(n){return t.resolve(e()).then(function(){return n})},function(n){return t.resolve(e()).then(function(){throw n})})},"undefined"!=typeof e&&e.exports?e.exports=r:n.Promise||(n.Promise=r)}(this)}).call(t,n(2).setImmediate)},function(e,t,n){(function(e,i){function r(e,t){this._id=e,this._clearFn=t}var o=n(3).nextTick,a=Function.prototype.apply,s=Array.prototype.slice,l={},d=0;t.setTimeout=function(){return new r(a.call(setTimeout,window,arguments),clearTimeout)},t.setInterval=function(){return new r(a.call(setInterval,window,arguments),clearInterval)},t.clearTimeout=t.clearInterval=function(e){e.close()},r.prototype.unref=r.prototype.ref=function(){},r.prototype.close=function(){this._clearFn.call(window,this._id)},t.enroll=function(e,t){clearTimeout(e._idleTimeoutId),e._idleTimeout=t},t.unenroll=function(e){clearTimeout(e._idleTimeoutId),e._idleTimeout=-1},t._unrefActive=t.active=function(e){clearTimeout(e._idleTimeoutId);var t=e._idleTimeout;t>=0&&(e._idleTimeoutId=setTimeout(function(){e._onTimeout&&e._onTimeout()},t))},t.setImmediate="function"==typeof e?e:function(e){var n=d++,i=!(arguments.length<2)&&s.call(arguments,1);return l[n]=!0,o(function(){l[n]&&(i?e.apply(null,i):e.call(null),t.clearImmediate(n))}),n},t.clearImmediate="function"==typeof i?i:function(e){delete l[e]}}).call(t,n(2).setImmediate,n(2).clearImmediate)},function(e,t){function n(){d=!1,a.length?l=a.concat(l):u=-1,l.length&&i()}function i(){if(!d){var e=setTimeout(n);d=!0;for(var t=l.length;t;){for(a=l,l=[];++u<t;)a&&a[u].run();u=-1,t=l.length}a=null,d=!1,clearTimeout(e)}}function r(e,t){this.fun=e,this.array=t}function o(){}var a,s=e.exports={},l=[],d=!1,u=-1;s.nextTick=function(e){var t=new Array(arguments.length-1);if(arguments.length>1)for(var n=1;n<arguments.length;n++)t[n-1]=arguments[n];l.push(new r(e,t)),1!==l.length||d||setTimeout(i,0)},r.prototype.run=function(){this.fun.apply(null,this.array)},s.title="browser",s.browser=!0,s.env={},s.argv=[],s.version="",s.versions={},s.on=o,s.addListener=o,s.once=o,s.off=o,s.removeListener=o,s.removeAllListeners=o,s.emit=o,s.binding=function(e){throw new Error("process.binding is not supported")},s.cwd=function(){return"/"},s.chdir=function(e){throw new Error("process.chdir is not supported")},s.umask=function(){return 0}},function(e,t){function n(){var e=~navigator.userAgent.indexOf("Android")&&~navigator.vendor.indexOf("Google")&&!~navigator.userAgent.indexOf("Chrome");return e&&navigator.userAgent.match(/AppleWebKit\/(\d+)/).pop()<=534||/MQQBrowser/g.test(navigator.userAgent)}function i(){var e=this,t=[],n=Array(21).join("-")+(+new Date*(1e16*Math.random())).toString(36),i=XMLHttpRequest.prototype.send;this.getParts=function(){return t.toString()},this.append=function(e,i,r){t.push("--"+n+'\r\nContent-Disposition: form-data; name="'+e+'"'),i instanceof Blob?(t.push('; filename="'+(r||"blob")+'"\r\nContent-Type: '+i.type+"\r\n\r\n"),t.push(i)):t.push("\r\n\r\n"+i),t.push("\r\n")},XMLHttpRequest.prototype.send=function(o){var a,s,l=this;o===e?(t.push("--"+n+"--\r\n"),s=new r(t),a=new FileReader,a.onload=function(){i.call(l,a.result)},a.onerror=function(e){throw e},a.readAsArrayBuffer(s),this.setRequestHeader("Content-Type","multipart/form-data; boundary="+n),XMLHttpRequest.prototype.send=i):i.call(this,o)}}var r=function(){try{return new Blob,!0}catch(e){return!1}}()?window.Blob:function(e,t){var n=new(window.BlobBuilder||window.WebKitBlobBuilder||window.MSBlobBuilder||window.MozBlobBuilder);return e.forEach(function(e){n.append(e)}),n.getBlob(t?t.type:void 0)};e.exports={Blob:r,FormData:n()?i:FormData}},function(e,t,n){var i,r;(function(){function n(e){return!!e.exifdata}function o(e,t){t=t||e.match(/^data\:([^\;]+)\;base64,/im)[1]||"",e=e.replace(/^data\:([^\;]+)\;base64,/gim,"");for(var n=atob(e),i=n.length,r=new ArrayBuffer(i),o=new Uint8Array(r),a=0;i>a;a++)o[a]=n.charCodeAt(a);return r}function a(e,t){var n=new XMLHttpRequest;n.open("GET",e,!0),n.responseType="blob",n.onload=function(e){(200==this.status||0===this.status)&&t(this.response)},n.send()}function s(e,t){function n(n){var i=d(n),r=u(n);e.exifdata=i||{},e.iptcdata=r||{},t&&t.call(e)}if(e.src)if(/^data\:/i.test(e.src)){var i=o(e.src);n(i)}else if(/^blob\:/i.test(e.src)){var r=new FileReader;r.onload=function(e){n(e.target.result)},a(e.src,function(e){r.readAsArrayBuffer(e)})}else{var s=new XMLHttpRequest;s.onload=function(){200==this.status||0===this.status?n(s.response):t(new Error("Could not load image")),s=null},s.open("GET",e.src,!0),s.responseType="arraybuffer",s.send(null)}else if(window.FileReader&&(e instanceof window.Blob||e instanceof window.File)){var r=new FileReader;r.onload=function(e){v&&console.log("Got file of length "+e.target.result.byteLength),n(e.target.result)},r.readAsArrayBuffer(e)}}function d(e){var t=new DataView(e);if(v&&console.log("Got file of length "+e.byteLength),255!=t.getUint8(0)||216!=t.getUint8(1))return v&&console.log("Not a valid JPEG"),!1;for(var n,i=2,r=e.byteLength;r>i;){if(255!=t.getUint8(i))return v&&console.log("Not a valid marker at offset "+i+", found: "+t.getUint8(i)),!1;if(n=t.getUint8(i+1),v&&console.log(n),225==n)return v&&console.log("Found 0xFFE1 marker"),g(t,i+4,t.getUint16(i+2)-2);i+=2+t.getUint16(i+2)}}function u(e){var t=new DataView(e);if(v&&console.log("Got file of length "+e.byteLength),255!=t.getUint8(0)||216!=t.getUint8(1))return v&&console.log("Not a valid JPEG"),!1;for(var n=2,i=e.byteLength,r=function(e,t){return 56===e.getUint8(t)&&66===e.getUint8(t+1)&&73===e.getUint8(t+2)&&77===e.getUint8(t+3)&&4===e.getUint8(t+4)&&4===e.getUint8(t+5)};i>n;){if(r(t,n)){var o=t.getUint8(n+7);o%2!==0&&(o+=1),0===o&&(o=4);var a=n+8+o,s=t.getUint16(n+6+o);return c(e,a,s)}n++}}function c(e,t,n){for(var i,r,o,a,s,l=new DataView(e),d={},u=t;t+n>u;)28===l.getUint8(u)&&2===l.getUint8(u+1)&&(a=l.getUint8(u+2),a in _&&(o=l.getInt16(u+3),s=o+5,r=_[a],i=p(l,u+5,o),d.hasOwnProperty(r)?d[r]instanceof Array?d[r].push(i):d[r]=[d[r],i]:d[r]=i)),u++;return d}function f(e,t,n,i,r){var o,a,s,l=e.getUint16(n,!r),d={};for(s=0;l>s;s++)o=n+12*s+2,a=i[e.getUint16(o,!r)],!a&&v&&console.log("Unknown tag: "+e.getUint16(o,!r)),d[a]=h(e,o,t,n,r);return d}function h(e,t,n,i,r){var o,a,s,l,d,u,c=e.getUint16(t+2,!r),f=e.getUint32(t+4,!r),h=e.getUint32(t+8,!r)+n;switch(c){case 1:case 7:if(1==f)return e.getUint8(t+8,!r);for(o=f>4?h:t+8,a=[],l=0;f>l;l++)a[l]=e.getUint8(o+l);return a;case 2:return o=f>4?h:t+8,p(e,o,f-1);case 3:if(1==f)return e.getUint16(t+8,!r);for(o=f>2?h:t+8,a=[],l=0;f>l;l++)a[l]=e.getUint16(o+2*l,!r);return a;case 4:if(1==f)return e.getUint32(t+8,!r);for(a=[],l=0;f>l;l++)a[l]=e.getUint32(h+4*l,!r);return a;case 5:if(1==f)return d=e.getUint32(h,!r),u=e.getUint32(h+4,!r),s=new Number(d/u),s.numerator=d,s.denominator=u,s;for(a=[],l=0;f>l;l++)d=e.getUint32(h+8*l,!r),u=e.getUint32(h+4+8*l,!r),a[l]=new Number(d/u),a[l].numerator=d,a[l].denominator=u;return a;case 9:if(1==f)return e.getInt32(t+8,!r);for(a=[],l=0;f>l;l++)a[l]=e.getInt32(h+4*l,!r);return a;case 10:if(1==f)return e.getInt32(h,!r)/e.getInt32(h+4,!r);for(a=[],l=0;f>l;l++)a[l]=e.getInt32(h+8*l,!r)/e.getInt32(h+4+8*l,!r);return a}}function p(e,t,n){var i,r="";for(i=t;t+n>i;i++)r+=String.fromCharCode(e.getUint8(i));return r}function g(e,t){if("Exif"!=p(e,t,4))return v&&console.log("Not valid EXIF data! "+p(e,t,4)),!1;var n,i,r,o,a,s=t+6;if(18761==e.getUint16(s))n=!1;else{if(19789!=e.getUint16(s))return v&&console.log("Not valid TIFF data! (no 0x4949 or 0x4D4D)"),!1;n=!0}if(42!=e.getUint16(s+2,!n))return v&&console.log("Not valid TIFF data! (no 0x002A)"),!1;var l=e.getUint32(s+4,!n);if(8>l)return v&&console.log("Not valid TIFF data! (First offset less than 8)",e.getUint32(s+4,!n)),!1;if(i=f(e,s,s+l,y,n),i.ExifIFDPointer){o=f(e,s,s+i.ExifIFDPointer,w,n);for(r in o){switch(r){case"LightSource":case"Flash":case"MeteringMode":case"ExposureProgram":case"SensingMethod":case"SceneCaptureType":case"SceneType":case"CustomRendered":case"WhiteBalance":case"GainControl":case"Contrast":case"Saturation":case"Sharpness":case"SubjectDistanceRange":case"FileSource":o[r]=S[r][o[r]];break;case"ExifVersion":case"FlashpixVersion":o[r]=String.fromCharCode(o[r][0],o[r][1],o[r][2],o[r][3]);break;case"ComponentsConfiguration":o[r]=S.Components[o[r][0]]+S.Components[o[r][1]]+S.Components[o[r][2]]+S.Components[o[r][3]]}i[r]=o[r]}}if(i.GPSInfoIFDPointer){a=f(e,s,s+i.GPSInfoIFDPointer,b,n);for(r in a){switch(r){case"GPSVersionID":a[r]=a[r][0]+"."+a[r][1]+"."+a[r][2]+"."+a[r][3]}i[r]=a[r]}}return i}var v=!1,m=function e(t){return t instanceof e?t:this instanceof e?void(this.EXIFwrapped=t):new e(t)};"undefined"!=typeof e&&e.exports&&(t=e.exports=m),t.EXIF=m;var w=m.Tags={36864:"ExifVersion",40960:"FlashpixVersion",40961:"ColorSpace",40962:"PixelXDimension",40963:"PixelYDimension",37121:"ComponentsConfiguration",37122:"CompressedBitsPerPixel",37500:"MakerNote",37510:"UserComment",40964:"RelatedSoundFile",36867:"DateTimeOriginal",36868:"DateTimeDigitized",37520:"SubsecTime",37521:"SubsecTimeOriginal",37522:"SubsecTimeDigitized",33434:"ExposureTime",33437:"FNumber",34850:"ExposureProgram",34852:"SpectralSensitivity",34855:"ISOSpeedRatings",34856:"OECF",37377:"ShutterSpeedValue",37378:"ApertureValue",37379:"BrightnessValue",37380:"ExposureBias",37381:"MaxApertureValue",37382:"SubjectDistance",37383:"MeteringMode",37384:"LightSource",37385:"Flash",37396:"SubjectArea",37386:"FocalLength",41483:"FlashEnergy",41484:"SpatialFrequencyResponse",41486:"FocalPlaneXResolution",41487:"FocalPlaneYResolution",41488:"FocalPlaneResolutionUnit",41492:"SubjectLocation",41493:"ExposureIndex",41495:"SensingMethod",41728:"FileSource",41729:"SceneType",41730:"CFAPattern",41985:"CustomRendered",41986:"ExposureMode",41987:"WhiteBalance",41988:"DigitalZoomRation",41989:"FocalLengthIn35mmFilm",41990:"SceneCaptureType",41991:"GainControl",41992:"Contrast",41993:"Saturation",41994:"Sharpness",41995:"DeviceSettingDescription",41996:"SubjectDistanceRange",40965:"InteroperabilityIFDPointer",42016:"ImageUniqueID"},y=m.TiffTags={256:"ImageWidth",257:"ImageHeight",34665:"ExifIFDPointer",34853:"GPSInfoIFDPointer",40965:"InteroperabilityIFDPointer",258:"BitsPerSample",259:"Compression",262:"PhotometricInterpretation",274:"Orientation",277:"SamplesPerPixel",284:"PlanarConfiguration",530:"YCbCrSubSampling",531:"YCbCrPositioning",282:"XResolution",283:"YResolution",296:"ResolutionUnit",273:"StripOffsets",278:"RowsPerStrip",279:"StripByteCounts",513:"JPEGInterchangeFormat",514:"JPEGInterchangeFormatLength",301:"TransferFunction",318:"WhitePoint",319:"PrimaryChromaticities",529:"YCbCrCoefficients",532:"ReferenceBlackWhite",306:"DateTime",270:"ImageDescription",271:"Make",272:"Model",305:"Software",315:"Artist",33432:"Copyright"},b=m.GPSTags={0:"GPSVersionID",1:"GPSLatitudeRef",2:"GPSLatitude",3:"GPSLongitudeRef",4:"GPSLongitude",5:"GPSAltitudeRef",6:"GPSAltitude",7:"GPSTimeStamp",8:"GPSSatellites",9:"GPSStatus",10:"GPSMeasureMode",11:"GPSDOP",12:"GPSSpeedRef",13:"GPSSpeed",14:"GPSTrackRef",15:"GPSTrack",16:"GPSImgDirectionRef",17:"GPSImgDirection",18:"GPSMapDatum",19:"GPSDestLatitudeRef",20:"GPSDestLatitude",21:"GPSDestLongitudeRef",22:"GPSDestLongitude",23:"GPSDestBearingRef",24:"GPSDestBearing",25:"GPSDestDistanceRef",26:"GPSDestDistance",27:"GPSProcessingMethod",28:"GPSAreaInformation",29:"GPSDateStamp",30:"GPSDifferential"},S=m.StringValues={ExposureProgram:{0:"Not defined",1:"Manual",2:"Normal program",3:"Aperture priority",4:"Shutter priority",5:"Creative program",6:"Action program",7:"Portrait mode",8:"Landscape mode"},MeteringMode:{0:"Unknown",1:"Average",2:"CenterWeightedAverage",3:"Spot",4:"MultiSpot",5:"Pattern",6:"Partial",255:"Other"},LightSource:{0:"Unknown",1:"Daylight",2:"Fluorescent",3:"Tungsten (incandescent light)",4:"Flash",9:"Fine weather",10:"Cloudy weather",11:"Shade",12:"Daylight fluorescent (D 5700 - 7100K)",13:"Day white fluorescent (N 4600 - 5400K)",14:"Cool white fluorescent (W 3900 - 4500K)",15:"White fluorescent (WW 3200 - 3700K)",17:"Standard light A",18:"Standard light B",19:"Standard light C",20:"D55",21:"D65",22:"D75",23:"D50",24:"ISO studio tungsten",255:"Other"},Flash:{0:"Flash did not fire",1:"Flash fired",5:"Strobe return light not detected",7:"Strobe return light detected",9:"Flash fired, compulsory flash mode",13:"Flash fired, compulsory flash mode, return light not detected",15:"Flash fired, compulsory flash mode, return light detected",16:"Flash did not fire, compulsory flash mode",24:"Flash did not fire, auto mode",25:"Flash fired, auto mode",29:"Flash fired, auto mode, return light not detected",31:"Flash fired, auto mode, return light detected",32:"No flash function",65:"Flash fired, red-eye reduction mode",69:"Flash fired, red-eye reduction mode, return light not detected",71:"Flash fired, red-eye reduction mode, return light detected",73:"Flash fired, compulsory flash mode, red-eye reduction mode",77:"Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",79:"Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",89:"Flash fired, auto mode, red-eye reduction mode",93:"Flash fired, auto mode, return light not detected, red-eye reduction mode",95:"Flash fired, auto mode, return light detected, red-eye reduction mode"},SensingMethod:{1:"Not defined",2:"One-chip color area sensor",3:"Two-chip color area sensor",4:"Three-chip color area sensor",5:"Color sequential area sensor",7:"Trilinear sensor",8:"Color sequential linear sensor"},SceneCaptureType:{0:"Standard",1:"Landscape",2:"Portrait",3:"Night scene"},SceneType:{1:"Directly photographed"},CustomRendered:{0:"Normal process",1:"Custom process"},WhiteBalance:{0:"Auto white balance",1:"Manual white balance"},GainControl:{0:"None",1:"Low gain up",2:"High gain up",3:"Low gain down",4:"High gain down"},Contrast:{0:"Normal",1:"Soft",2:"Hard"},Saturation:{0:"Normal",1:"Low saturation",2:"High saturation"},Sharpness:{0:"Normal",1:"Soft",2:"Hard"},SubjectDistanceRange:{0:"Unknown",1:"Macro",2:"Close view",3:"Distant view"},FileSource:{3:"DSC"},Components:{0:"",1:"Y",2:"Cb",3:"Cr",4:"R",5:"G",6:"B"}},_={120:"caption",110:"credit",25:"keywords",55:"dateCreated",80:"byline",85:"bylineTitle",122:"captionWriter",105:"headline",116:"copyright",15:"category"};m.getData=function(e,t){return!((e instanceof Image||e instanceof HTMLImageElement)&&!e.complete)&&(n(e)?t&&t.call(e):s(e,t),!0)},m.getTag=function(e,t){return n(e)?e.exifdata[t]:void 0},m.getAllTags=function(e){if(!n(e))return{};var t,i=e.exifdata,r={};for(t in i)i.hasOwnProperty(t)&&(r[t]=i[t]);return r},m.pretty=function(e){if(!n(e))return"";var t,i=e.exifdata,r="";for(t in i)i.hasOwnProperty(t)&&(r+="object"==(0,l.default)(i[t])?i[t]instanceof Number?t+" : "+i[t]+" ["+i[t].numerator+"/"+i[t].denominator+"]\r\n":t+" : ["+i[t].length+" values]\r\n":t+" : "+i[t]+"\r\n");return r},m.readFromBinaryFile=function(e){return d(e)},i=[],r=function(){return m}.apply(t,i),!(void 0!==r&&(e.exports=r))}).call(this)},function(e,t,n){var i,r;!function(){function n(e){var t=e.naturalWidth,n=e.naturalHeight;if(t*n>1048576){var i=document.createElement("canvas");i.width=i.height=1;var r=i.getContext("2d");return r.drawImage(e,-t+1,0),0===r.getImageData(0,0,1,1).data[3]}return!1}function o(e,t,n){var i=document.createElement("canvas");i.width=1,i.height=n;var r=i.getContext("2d");r.drawImage(e,0,0);for(var o=r.getImageData(0,0,1,n).data,a=0,s=n,l=n;l>a;){var d=o[4*(l-1)+3];0===d?s=l:a=l,l=s+a>>1}var u=l/n;return 0===u?1:u}function a(e,t,n){var i=document.createElement("canvas");return s(e,i,t,n),i.toDataURL("image/jpeg",t.quality||.8)}function s(e,t,i,r){var a=e.naturalWidth,s=e.naturalHeight,d=i.width,u=i.height,c=t.getContext("2d");c.save(),l(t,c,d,u,i.orientation);var f=n(e);f&&(a/=2,s/=2);var h=1024,p=document.createElement("canvas");p.width=p.height=h;for(var g=p.getContext("2d"),v=r?o(e,a,s):1,m=Math.ceil(h*d/a),w=Math.ceil(h*u/s/v),y=0,b=0;s>y;){for(var S=0,_=0;a>S;)g.clearRect(0,0,h,h),g.drawImage(e,-S,-y),c.drawImage(p,0,0,h,h,_,b,m,w),S+=h,_+=m;y+=h,b+=w}c.restore(),p=g=null}function l(e,t,n,i,r){switch(r){case 5:case 6:case 7:case 8:e.width=i,e.height=n;break;default:e.width=n,e.height=i}switch(r){case 2:t.translate(n,0),t.scale(-1,1);break;case 3:t.translate(n,i),t.rotate(Math.PI);break;case 4:t.translate(0,i),t.scale(1,-1);break;case 5:t.rotate(.5*Math.PI),t.scale(1,-1);break;case 6:t.rotate(.5*Math.PI),t.translate(0,-i);break;case 7:t.rotate(.5*Math.PI),t.translate(n,-i),t.scale(-1,1);break;case 8:t.rotate(-.5*Math.PI),t.translate(-n,0)}}function d(e){if(window.Blob&&e instanceof Blob){var t=new Image,n=window.URL&&window.URL.createObjectURL?window.URL:window.webkitURL&&window.webkitURL.createObjectURL?window.webkitURL:null;if(!n)throw Error("No createObjectURL function found to create blob url");t.src=n.createObjectURL(e),this.blob=e,e=t}if(!e.naturalWidth&&!e.naturalHeight){var i=this;e.onload=function(){var e=i.imageLoadListeners;if(e){i.imageLoadListeners=null;for(var t=0,n=e.length;n>t;t++)e[t]()}},this.imageLoadListeners=[]}this.srcImage=e}d.prototype.render=function(e,t,n){if(this.imageLoadListeners){var i=this;return void this.imageLoadListeners.push(function(){i.render(e,t,n)})}t=t||{};var r=this.srcImage,o=r.src,l=o.length,d=r.naturalWidth,u=r.naturalHeight,c=t.width,f=t.height,h=t.maxWidth,p=t.maxHeight,g=this.blob&&"image/jpeg"===this.blob.type||0===o.indexOf("data:image/jpeg")||o.indexOf(".jpg")===l-4||o.indexOf(".jpeg")===l-5;c&&!f?f=u*c/d<<0:f&&!c?c=d*f/u<<0:(c=d,f=u),h&&c>h&&(c=h,f=u*c/d<<0),p&&f>p&&(f=p,c=d*f/u<<0);var v={width:c,height:f};for(var m in t)v[m]=t[m];var w=e.tagName.toLowerCase();"img"===w?e.src=a(this.srcImage,v,g):"canvas"===w&&s(this.srcImage,e,v,g),"function"==typeof this.onrender&&this.onrender(e),n&&n()},i=[],r=function(){return d}.apply(t,i),!(void 0!==r&&(e.exports=r))}()},function(e,t){function n(e){function t(e){for(var t=[16,11,10,16,24,40,51,61,12,12,14,19,26,58,60,55,14,13,16,24,40,57,69,56,14,17,22,29,51,87,80,62,18,22,37,56,68,109,103,77,24,35,55,64,81,104,113,92,49,64,78,87,103,121,120,101,72,92,95,98,112,100,103,99],n=0;64>n;n++){var i=I((t[n]*e+50)/100);1>i?i=1:i>255&&(i=255),P[N[n]]=i}for(var r=[17,18,24,47,99,99,99,99,18,21,26,66,99,99,99,99,24,26,56,99,99,99,99,99,47,66,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99],o=0;64>o;o++){var a=I((r[o]*e+50)/100);1>a?a=1:a>255&&(a=255),L[N[o]]=a}for(var s=[1,1.387039845,1.306562965,1.175875602,1,.785694958,.5411961,.275899379],l=0,d=0;8>d;d++)for(var u=0;8>u;u++)F[l]=1/(P[N[l]]*s[d]*s[u]*8),D[l]=1/(L[N[l]]*s[d]*s[u]*8),l++}function n(e,t){for(var n=0,i=0,r=new Array,o=1;16>=o;o++){for(var a=1;a<=e[o];a++)r[t[i]]=[],r[t[i]][0]=n,r[t[i]][1]=o,i++,n++;n*=2}return r}function i(){y=n(z,H),b=n(V,X),S=n(W,q),_=n($,J)}function r(){for(var e=1,t=2,n=1;15>=n;n++){for(var i=e;t>i;i++)C[32767+i]=n,U[32767+i]=[],U[32767+i][1]=n,U[32767+i][0]=i;for(var r=-(t-1);-e>=r;r++)C[32767+r]=n,U[32767+r]=[],U[32767+r][1]=n,U[32767+r][0]=t-1+r;e<<=1,t<<=1}}function o(){for(var e=0;256>e;e++)E[e]=19595*e,E[e+256>>0]=38470*e,E[e+512>>0]=7471*e+32768,E[e+768>>0]=-11059*e,E[e+1024>>0]=-21709*e,E[e+1280>>0]=32768*e+8421375,E[e+1536>>0]=-27439*e,E[e+1792>>0]=-5329*e}function a(e){for(var t=e[0],n=e[1]-1;n>=0;)t&1<<n&&(M|=1<<k),n--,k--,0>k&&(255==M?(s(255),s(0)):s(M),k=7,M=0)}function s(e){R.push(B[e])}function l(e){s(e>>8&255),s(255&e)}function d(e,t){var n,i,r,o,a,s,l,d,u,c=0,f=8,h=64;for(u=0;f>u;++u){n=e[c],i=e[c+1],r=e[c+2],o=e[c+3],a=e[c+4],s=e[c+5],l=e[c+6],d=e[c+7];var p=n+d,g=n-d,v=i+l,m=i-l,w=r+s,y=r-s,b=o+a,S=o-a,_=p+b,x=p-b,I=v+w,P=v-w;e[c]=_+I,e[c+4]=_-I;var L=.707106781*(P+x);e[c+2]=x+L,e[c+6]=x-L,_=S+y,I=y+m,P=m+g;var F=.382683433*(_-P),D=.5411961*_+F,U=1.306562965*P+F,C=.707106781*I,A=g+C,R=g-C;e[c+5]=R+D,e[c+3]=R-D,e[c+1]=A+U,e[c+7]=A-U,c+=8}for(c=0,u=0;f>u;++u){n=e[c],i=e[c+8],r=e[c+16],o=e[c+24],a=e[c+32],s=e[c+40],l=e[c+48],d=e[c+56];var M=n+d,k=n-d,G=i+l,O=i-l,j=r+s,B=r-s,E=o+a,N=o-a,z=M+E,H=M-E,W=G+j,q=G-j;e[c]=z+W,e[c+32]=z-W;var V=.707106781*(q+H);e[c+16]=H+V,e[c+48]=H-V,z=N+B,W=B+O,q=O+k;var X=.382683433*(z-q),$=.5411961*z+X,J=1.306562965*q+X,K=.707106781*W,Q=k+K,Y=k-K;e[c+40]=Y+$,e[c+24]=Y-$,e[c+8]=Q+J,e[c+56]=Q-J,c++}var Z;for(u=0;h>u;++u)Z=e[u]*t[u],T[u]=Z>0?Z+.5|0:Z-.5|0;return T}function u(){l(65504),l(16),s(74),s(70),s(73),s(70),s(0),s(1),s(1),s(0),l(1),l(1),s(0),s(0)}function c(e,t){l(65472),l(17),s(8),l(t),l(e),s(3),s(1),s(17),s(0),s(2),s(17),s(1),s(3),s(17),s(1)}function f(){l(65499),l(132),s(0);for(var e=0;64>e;e++)s(P[e]);s(1);for(var t=0;64>t;t++)s(L[t])}function h(){l(65476),l(418),s(0);for(var e=0;16>e;e++)s(z[e+1]);for(var t=0;11>=t;t++)s(H[t]);s(16);for(var n=0;16>n;n++)s(W[n+1]);for(var i=0;161>=i;i++)s(q[i]);s(1);for(var r=0;16>r;r++)s(V[r+1]);for(var o=0;11>=o;o++)s(X[o]);s(17);for(var a=0;16>a;a++)s($[a+1]);for(var d=0;161>=d;d++)s(J[d])}function p(){l(65498),l(12),s(3),s(1),s(0),s(2),s(17),s(3),s(17),s(0),s(63),s(0)}function g(e,t,n,i,r){for(var o,s=r[0],l=r[240],u=16,c=63,f=64,h=d(e,t),p=0;f>p;++p)A[N[p]]=h[p];var g=A[0]-n;n=A[0],0==g?a(i[0]):(o=32767+g,a(i[C[o]]),a(U[o]));for(var v=63;v>0&&0==A[v];v--);if(0==v)return a(s),n;for(var m,w=1;v>=w;){for(var y=w;0==A[w]&&v>=w;++w);var b=w-y;if(b>=u){m=b>>4;for(var S=1;m>=S;++S)a(l);b&=15}o=32767+A[w],a(r[(b<<4)+C[o]]),a(U[o]),w++}return v!=c&&a(s),n}function v(){for(var e=String.fromCharCode,t=0;256>t;t++)B[t]=e(t)}function m(e){if(0>=e&&(e=1),e>100&&(e=100),x!=e){var n=0;n=50>e?Math.floor(5e3/e):Math.floor(200-2*e),t(n),x=e}}function w(){var t=(new Date).getTime();e||(e=50),v(),i(),r(),o(),m(e),(new Date).getTime()-t}var y,b,S,_,x,I=(Math.round,Math.floor),P=new Array(64),L=new Array(64),F=new Array(64),D=new Array(64),U=new Array(65535),C=new Array(65535),T=new Array(64),A=new Array(64),R=[],M=0,k=7,G=new Array(64),O=new Array(64),j=new Array(64),B=new Array(256),E=new Array(2048),N=[0,1,5,6,14,15,27,28,2,4,7,13,16,26,29,42,3,8,12,17,25,30,41,43,9,11,18,24,31,40,44,53,10,19,23,32,39,45,52,54,20,22,33,38,46,51,55,60,21,34,37,47,50,56,59,61,35,36,48,49,57,58,62,63],z=[0,0,1,5,1,1,1,1,1,1,0,0,0,0,0,0,0],H=[0,1,2,3,4,5,6,7,8,9,10,11],W=[0,0,2,1,3,3,2,4,3,5,5,4,4,0,0,1,125],q=[1,2,3,0,4,17,5,18,33,49,65,6,19,81,97,7,34,113,20,50,129,145,161,8,35,66,177,193,21,82,209,240,36,51,98,114,130,9,10,22,23,24,25,26,37,38,39,40,41,42,52,53,54,55,56,57,58,67,68,69,70,71,72,73,74,83,84,85,86,87,88,89,90,99,100,101,102,103,104,105,106,115,116,117,118,119,120,121,122,131,132,133,134,135,136,137,138,146,147,148,149,150,151,152,153,154,162,163,164,165,166,167,168,169,170,178,179,180,181,182,183,184,185,186,194,195,196,197,198,199,200,201,202,210,211,212,213,214,215,216,217,218,225,226,227,228,229,230,231,232,233,234,241,242,243,244,245,246,247,248,249,250],V=[0,0,3,1,1,1,1,1,1,1,1,1,0,0,0,0,0],X=[0,1,2,3,4,5,6,7,8,9,10,11],$=[0,0,2,1,2,4,4,3,4,7,5,4,4,0,1,2,119],J=[0,1,2,3,17,4,5,33,49,6,18,65,81,7,97,113,19,34,50,129,8,20,66,145,161,177,193,9,35,51,82,240,21,98,114,209,10,22,36,52,225,37,241,23,24,25,26,38,39,40,41,42,53,54,55,56,57,58,67,68,69,70,71,72,73,74,83,84,85,86,87,88,89,90,99,100,101,102,103,104,105,106,115,116,117,118,119,120,121,122,130,131,132,133,134,135,136,137,138,146,147,148,149,150,151,152,153,154,162,163,164,165,166,167,168,169,170,178,179,180,181,182,183,184,185,186,194,195,196,197,198,199,200,201,202,210,211,212,213,214,215,216,217,218,226,227,228,229,230,231,232,233,234,242,243,244,245,246,247,248,249,250];this.encode=function(e,t,n){var i=(new Date).getTime();t&&m(t),R=new Array,M=0,k=7,l(65496),u(),f(),c(e.width,e.height),h(),p();var r=0,o=0,s=0;M=0,k=7,this.encode.displayName="_encode_";for(var d,v,w,x,I,P,L,U,C,T=e.data,A=e.width,B=e.height,N=4*A,z=0;B>z;){for(d=0;N>d;){for(I=N*z+d,P=I,L=-1,U=0,C=0;64>C;C++)U=C>>3,L=4*(7&C),P=I+U*N+L,z+U>=B&&(P-=N*(z+1+U-B)),d+L>=N&&(P-=d+L-N+4),v=T[P++],w=T[P++],x=T[P++],G[C]=(E[v]+E[w+256>>0]+E[x+512>>0]>>16)-128,O[C]=(E[v+768>>0]+E[w+1024>>0]+E[x+1280>>0]>>16)-128,j[C]=(E[v+1280>>0]+E[w+1536>>0]+E[x+1792>>0]>>16)-128;r=g(G,F,r,y,S),o=g(O,D,o,b,_),s=g(j,D,s,b,_),d+=32}z+=8}if(k>=0){var H=[];H[1]=k+1,H[0]=(1<<k+1)-1,a(H)}if(l(65497),n){for(var W=R.length,q=new Uint8Array(W),V=0;W>V;V++)q[V]=R[V].charCodeAt();return R=[],(new Date).getTime()-i,q}var X="data:image/jpeg;base64,"+btoa(R.join(""));return R=[],(new Date).getTime()-i,X},w()}e.exports=n},function(e,t,n){function i(e,t){var n=this;if(!e)throw new Error("没有收到图片，可能的解决方案：https://github.com/think2011/localResizeIMG/issues/7");t=t||{},n.defaults={width:null,height:null,fieldName:"file",quality:.7},n.file=e;for(var i in t)t.hasOwnProperty(i)&&(n.defaults[i]=t[i]);return this.init()}function r(e){var t=null;return t=e?[].filter.call(document.scripts,function(t){return-1!==t.src.indexOf(e)})[0]:document.scripts[document.scripts.length-1],t?t.src.substr(0,t.src.lastIndexOf("/")):null}function o(e){var t;t=e.split(",")[0].indexOf("base64")>=0?atob(e.split(",")[1]):unescape(e.split(",")[1]);for(var n=e.split(",")[0].split(":")[1].split(";")[0],i=new Uint8Array(t.length),r=0;r<t.length;r++)i[r]=t.charCodeAt(r);return new s.Blob([i.buffer],{type:n})}n.p=r("lrz")+"/",window.URL=window.URL||window.webkitURL;var a=n(1),s=n(4),d=n(5),u=function(e){var t=/OS (\d)_.* like Mac OS X/g.exec(e),n=/Android (\d.*?);/g.exec(e)||/Android\/(\d.*?) /g.exec(e);return{oldIOS:!!t&&+t.pop()<8,oldAndroid:!!n&&+n.pop().substr(0,3)<4.5,iOS:/\(i[^;]+;( U;)? CPU.+Mac OS X/.test(e),android:/Android/g.test(e),mQQBrowser:/MQQBrowser/g.test(e)}}(navigator.userAgent);i.prototype.init=function(){var e=this,t=e.file,n="string"==typeof t,i=/^data:/.test(t),r=new Image,d=document.createElement("canvas"),u=n?t:URL.createObjectURL(t);if(e.img=r,e.blob=u,e.canvas=d,n?e.fileName=i?"base64.jpg":t.split("/").pop():e.fileName=t.name,!document.createElement("canvas").getContext)throw new Error("浏览器不支持canvas");return new a(function(n,a){r.onerror=function(){throw new Error("加载图片文件失败")},r.onload=function(){e._getBase64().then(function(e){return e.length<10&&a("生成base64失败"),e}).then(function(i){var r=null;"object"==(0,l.default)(e.file)&&i.length>e.file.size?(r=new FormData,t=e.file):(r=new s.FormData,t=o(i)),r.append(e.defaults.fieldName,t,e.fileName.replace(/\..+/g,".jpg")),n({formData:r,fileLen:+t.size,base64:i,base64Len:i.length,origin:e.file});for(var a in e)e.hasOwnProperty(a)&&(e[a]=null);URL.revokeObjectURL(e.blob)})},!i&&(r.crossOrigin="*"),r.src=u})},i.prototype._getBase64=function(){var e=this,t=e.img,n=e.file,i=e.canvas;return new a(function(r){try{d.getData("object"==("undefined"==typeof n?"undefined":(0,l.default)(n))?n:t,function(){e.orientation=d.getTag(this,"Orientation"),e.resize=e._getResize(),e.ctx=i.getContext("2d"),i.width=e.resize.width,i.height=e.resize.height,e.ctx.fillStyle="#fff",e.ctx.fillRect(0,0,i.width,i.height),u.oldIOS?e._createBase64ForOldIOS().then(r):e._createBase64().then(r)})}catch(e){throw new Error(e)}})},i.prototype._createBase64ForOldIOS=function(){var e=this,t=e.img,i=e.canvas,r=e.defaults,o=e.orientation;return new a(function(e){!function(){var a=[n(6)];(function(n){var a=new n(t);"5678".indexOf(o)>-1?a.render(i,{width:i.height,height:i.width,orientation:o}):a.render(i,{width:i.width,height:i.height,orientation:o}),e(i.toDataURL("image/jpeg",r.quality))}).apply(null,a)}()})},i.prototype._createBase64=function(){var e=this,t=e.resize,i=e.img,r=e.canvas,o=e.ctx,s=e.defaults,l=e.orientation;switch(l){case 3:o.rotate(180*Math.PI/180),o.drawImage(i,-t.width,-t.height,t.width,t.height);break;case 6:o.rotate(90*Math.PI/180),o.drawImage(i,0,-t.width,t.height,t.width);break;case 8:o.rotate(270*Math.PI/180),o.drawImage(i,-t.height,0,t.height,t.width);break;case 2:o.translate(t.width,0),o.scale(-1,1),o.drawImage(i,0,0,t.width,t.height);break;case 4:o.translate(t.width,0),o.scale(-1,1),o.rotate(180*Math.PI/180),o.drawImage(i,-t.width,-t.height,t.width,t.height);break;case 5:o.translate(t.width,0),o.scale(-1,1),o.rotate(90*Math.PI/180),o.drawImage(i,0,-t.width,t.height,t.width);break;case 7:o.translate(t.width,0),o.scale(-1,1),o.rotate(270*Math.PI/180),o.drawImage(i,-t.height,0,t.height,t.width);break;default:o.drawImage(i,0,0,t.width,t.height)}return new a(function(e){u.oldAndroid||u.mQQBrowser||!navigator.userAgent?!function(){var t=[n(7)];(function(t){var n=new t,i=o.getImageData(0,0,r.width,r.height);e(n.encode(i,100*s.quality))}).apply(null,t)}():e(r.toDataURL("image/jpeg",s.quality))})},i.prototype._getResize=function(){var e=this,t=e.img,n=e.defaults,i=n.width,r=n.height,o=e.orientation,a={width:t.width,height:t.height};if("5678".indexOf(o)>-1&&(a.width=t.height,a.height=t.width),a.width<i||a.height<r)return a;var s=a.width/a.height;for(i&&r?s>=i/r?a.width>i&&(a.width=i,a.height=Math.ceil(i/s)):a.height>r&&(a.height=r,a.width=Math.ceil(r*s)):i?i<a.width&&(a.width=i,a.height=Math.ceil(i/s)):r&&r<a.height&&(a.width=Math.ceil(r*s),a.height=r);a.width>=3264||a.height>=2448;)a.width*=.8,
a.height*=.8;return a},window.lrz=function(e,t){return new i(e,t)},window.lrz.version="4.8.35",e.exports=window.lrz}])})}).call(t,n(246)(e))},246:function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children=[],e.webpackPolyfill=1),e}},365:function(e,t,n){e.exports={default:n(366),__esModule:!0}},366:function(e,t,n){var i=n(63),r=i.JSON||(i.JSON={stringify:JSON.stringify});e.exports=function(e){return r.stringify.apply(r,arguments)}},461:function(e,t,n){var i,r,o={};n(462),i=n(464),r=n(465),e.exports=i||{},e.exports.__esModule&&(e.exports=e.exports.default);var a="function"==typeof e.exports?e.exports.options||(e.exports.options={}):e.exports;r&&(a.template=r),a.computed||(a.computed={}),Object.keys(o).forEach(function(e){var t=o[e];a.computed[e]=function(){return t}})},462:function(e,t,n){var i=n(463);"string"==typeof i&&(i=[[e.id,i,""]]);n(22)(i,{});i.locals&&(e.exports=i.locals)},463:function(e,t,n){t=e.exports=n(3)(),t.push([e.id,'.s_tit[_v-578f00d7],.team[_v-578f00d7]{padding-top:1.5rem;text-align:center}.s_tit span[_v-578f00d7]{display:inline-block;width:25.3%}.team span[_v-578f00d7]{display:inline-block;width:76.1%}.s_tit span img[_v-578f00d7]{width:100%;max-width:162px}.team span img[_v-578f00d7]{width:100%;max-width:487px}.apply[_v-578f00d7]{width:100%;text-align:center;margin-top:1.2rem}.apply p[_v-578f00d7]{width:76.1%;display:inline-block;text-align:left;color:#58595b}.info[_v-578f00d7]{width:76.1%;margin:1rem auto 0}.info dl[_v-578f00d7]{display:table;margin-top:1rem;width:100%;overflow:hidden}.info dl dt[_v-578f00d7]{display:table-cell;vertical-align:middle;width:42%;font-size:.85rem;color:#58595b}.info dl dt b[_v-578f00d7]{color:#b01116;vertical-align:middle}.info dl dd[_v-578f00d7]{display:table-cell;vertical-align:middle;width:58%;padding-left:.5rem}.info dl dd input[_v-578f00d7]{border:1px solid #f26649;height:1.2rem;line-height:1.2em;display:inline-block;font-size:1rem;width:85%}.info dl dd a[_v-578f00d7]{display:inline-block;width:58%}.info dl.file dd a[_v-578f00d7]{display:inline-block;width:58%;max-width:161px;background:url(/public/images/uploadphoto/sub-btn.png) no-repeat 50%;background-size:cover}.info dl.file dd input[_v-578f00d7]{opacity:0;filter:alpha(opacity=0)}.server[_v-578f00d7]{width:76.1%;margin:4rem auto 0}.sun-btn[_v-578f00d7]{width:100%;text-align:center;margin-top:1.5rem}.sun-btn a[_v-578f00d7]{display:inline-block;width:50%;background:#d20000;color:#fff;padding:.4rem 0;border-radius:2.5px}.upload-preview[_v-578f00d7]{padding-top:1rem}.upload-preview li[_v-578f00d7]{width:49%;height:9rem;margin:.2rem 0;border:1px solid #e1e1e1;display:inline-block;vertical-align:middle;position:relative;float:left}.upload-preview li[_v-578f00d7]:nth-child(2n){float:right}.upload-preview li .font[_v-578f00d7]{font-size:1rem;position:absolute;right:.4rem;top:.4rem;background:#5d5d5d;color:#f7ebeb;font-style:normal;width:1.2rem;height:1.2rem;line-height:1rem;text-align:center;border-radius:50%}.upload-info[_v-578f00d7]{padding-top:.8rem}.upload-info span[_v-578f00d7]{width:50%;white-space:nowrap;float:left;font-size:.8rem}[v-cloak][_v-578f00d7]{display:none}.upload-preview img[_v-578f00d7]{width:100%}.upload-preview img.height[_v-578f00d7]{height:100%;width:auto}.loadprogress[_v-578f00d7]{height:2.5rem;line-height:2.5rem;text-align:center;font-size:.85rem}.loadprogress[_v-578f00d7]:after{content:"";display:inline-block;width:2rem;height:2rem;vertical-align:middle;text-align:center;background:url(/public/images/common/load_bg.gif) no-repeat 50%}',""])},464:function(e,t,n){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var r=n(151),o=i(r),a=n(365),s=i(a),l=n(245),d=i(l),u=document.querySelector(".fixed-footer"),c=document.querySelector(".header"),f=u.style.display,h=c.style.display;t.default={route:{deactivate:function(e){u.style.display=f,c.style.display=h,e.next()},data:function(){document.title="자빛韩国瓷肌中国官方商城肌肤问题图片上传",document.querySelector("#app").style.paddingTop="0px",u.style.display="none",c.style.display="none"}},ready:function(){},data:function(){return{phoneNum:"",uploadList:[],count:0,size:0,isLoad:!1,loadtips:""}},methods:{formSubmit:function(){var e=this;if(""==this.phoneNum||0==/^1[3-9]{1}[0-9]{9}$/.test(this.phoneNum)&&0==/^0\d{2,3}(\-)?\d{7,8}$/.test(this.phoneNum))return this.$dispatch("popup","请输入正确的手机号!"),!1;if(this.uploadList.length<=0)return this.$dispatch("popup","请上传合适尺寸的图片!"),!1;if(this.size>=2048)return this.$dispatch("popup","上传的图片总大小不能超过2M!"),!1;var t={phoneNum:this.phoneNum};this.isLoad=!0,this.loadtips="上传中，请稍后",t.upload_preview=[];var n=!0,i=!1,r=void 0;try{for(var a,l=(0,o.default)(this.uploadList);!(n=(a=l.next()).done);n=!0){var d=a.value;t.upload_preview.push(d.base64)}}catch(e){i=!0,r=e}finally{try{!n&&l.return&&l.return()}finally{if(i)throw r}}t.upload_preview=(0,s.default)(t.upload_preview),this.$http.post("/UpImage/index.json",t).then(function(t){t=t.json(),1==t.status?(e.isLoad=!1,e.$dispatch("popup","上传成功"),e.phoneNum="",e.uploadList=[]):e.$dispatch("popup",t.msg)})},uploadHandle:function(e){if(this.uploadList.length>=10)return void this.$dispatch("popup","最多只能上传10张图片");var t=this,n=!1;if(0!==e.target.files.length){var i=e.target.files[0];this.isLoad=!0,this.loadtips="图片加载中，请稍后",(0,d.default)(i).then(function(e){var i=new Image;i.onload=function(){n=i.width<i.height,e.isHeight=n,t.isLoad=!1,t.uploadList.push(e)},i.src=e.base64})}},removeList:function(e){this.uploadList.splice(e,1)}},computed:{count:function(){return this.uploadList.length},size:function(){var e=0,t=!0,n=!1,i=void 0;try{for(var r,a=(0,o.default)(this.uploadList);!(t=(r=a.next()).done);t=!0){var s=r.value;e+=Math.round(s.fileLen/1024)}}catch(e){n=!0,i=e}finally{try{!t&&a.return&&a.return()}finally{if(n)throw i}}return e}}}},465:function(e,t){e.exports=' <div class=contain _v-578f00d7=""> <div class=s_tit _v-578f00d7=""> <span _v-578f00d7=""><img src=/public/images/uploadphoto/cj.png alt="" _v-578f00d7=""></span> </div> <div class=team _v-578f00d7=""> <span _v-578f00d7=""><img src=/public/images/uploadphoto/team.png alt="" _v-578f00d7=""></span> </div> <div class=apply _v-578f00d7=""><p _v-578f00d7="">韩国瓷肌具有专业的皮肤科医生团队为您提供关于问题肌肤的诊断及免费的专业建议。</p></div> <div class=info _v-578f00d7=""> <form _v-578f00d7=""> <dl _v-578f00d7=""> <dt style="letter-spacing: 0.15em" _v-578f00d7=""><b _v-578f00d7="">*</b>请输入手机号</dt><dd _v-578f00d7=""><input type=tel maxlength=11 name=phoneNum v-model=phoneNum onkeyup="this.value=this.value.replace(/\\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\\D/g,\'\')" _v-578f00d7=""></dd> </dl> <dl class=file _v-578f00d7=""> <dt _v-578f00d7=""><b _v-578f00d7="">*</b>请上传面部照片</dt><dd _v-578f00d7=""><a href=javascript:; _v-578f00d7=""><input type=file name=file v-on:change="uploadHandle( $event)" _v-578f00d7=""></a></dd> </dl> <div class=loadprogress v-show=isLoad _v-578f00d7=""><span _v-578f00d7="">{{loadtips}}</span></div> <ul class="upload-preview clf" v-show="uploadList.length>0" v-cloak="" _v-578f00d7=""> <li v-for="item in uploadList" _v-578f00d7=""> <img :src=item.base64 :class={height:item.isHeight} _v-578f00d7=""><i class=font @click=removeList($index) _v-578f00d7="">x</i> </li> </ul> <div class="upload-info clf" _v-578f00d7=""> <span _v-578f00d7="">上传张数：<em id=count _v-578f00d7="">{{count}}</em>/10</span><span _v-578f00d7="">上传大小：<em id=size _v-578f00d7="">{{size}}</em>KB/2048KB</span> </div> <div class=sun-btn @click=formSubmit _v-578f00d7=""><a href=javascript:; _v-578f00d7="">提交</a></div> </form> </div> <p class=server _v-578f00d7="">我们将在三天内对您的皮肤问题做出诊断及建议，谢谢！</p> </div> '}});