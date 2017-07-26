var Ping = function(opt) {
    this.opt = opt || {};
    this.favicon = this.opt.favicon || "/favicon.ico";
    this.timeout = this.opt.timeout || 0;
};
Ping.prototype.ping = function(source, callback) {
    this.img = new Image();
    var timer;

    
    this.img.onload = pingCheck;
    this.img.onerror = pingCheck;
    if (this.timeout) { timer = setTimeout(pingCheck, this.timeout); }
	var start = new Date();
    function pingCheck(e) {
        if (timer) { clearTimeout(timer); }
        var pong = new Date() - start;

        if (typeof callback === "function") {
            if (e.type === "error") {
                console.error("error loading resource");
                return callback("error", pong);
            }
            return callback(null, pong);
        }
    }

    this.img.src = source + this.favicon + "?" + (+new Date()); // Trigger image load with cache buster
};
var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
function base64encode(str) {
    var out, i, len;
    var c1, c2, c3;
    len = str.length;
    i = 0;
    out = "";
    while (i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == len) {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt((c1 & 0x3) << 4);
            out += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if (i == len) {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            out += base64EncodeChars.charAt((c2 & 0xF) << 2);
            out += "=";
            break;
        }
        c3 = str.charCodeAt(i++);
        out += base64EncodeChars.charAt(c1 >> 2);
        out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
        out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
        out += base64EncodeChars.charAt(c3 & 0x3F);
    }
    return out;
}
$(document).ready(function() {
    $('button[name="ping"]').on('click',function() {
        var ping = new Ping();
        var address = $(this).attr('data-host');
        var timeout = 1000;
        var _this  = $(this);
        ping.ping('http://' + address,function(err,data) {
			if (err) {
				data = data + " " + err;
				_this.parents('td').html('<span class="badge badge-danger">' + data + '</span>');
			}else{
				_this.parents('td').html('<span class="badge badge-primary">' + data + '</span>');
			}
        });
    });
	jQuery(document).ready(function($) {
		$("button[name='qrcode']").on('click',function() {
			if($(this).attr('data-type').indexOf("ssr")!=-1){
				str = $(this).attr('data-params') + base64encode($(this).attr('data-pass')) + '/?obfsparam=' + base64encode($(this).attr('data-obfsparam')) + '&protoparam=' + base64encode($(this).attr('data-protoparam')) +  '&remarks=' + $(this).attr('data-note') ;
			} else {
				str = $(this).attr('data-params-SS');
			}
			str = base64encode(str);
			str = $(this).attr('data-type') + '://' + str;
			layer.open({
				type: 1,
				title: $(this).attr('data-type'),
				offset: 'auto',
				closeBtn: 1,
				shadeClose: true,
				content: '<img style="position: relative; width: 100%; height: 100%;" src="http://pan.baidu.com/share/qrcode?w=300&h=300&url=' + str + '"/>'
			});
		});
		$("button[name='url']").on('click',function() {
			if($(this).attr('data-type').indexOf("ssr")!=-1){
				str = $(this).attr('data-params') + base64encode($(this).attr('data-pass')) + '/?obfsparam=' + base64encode($(this).attr('data-obfsparam')) + '&protoparam=' + base64encode($(this).attr('data-protoparam')) +  '&remarks=' + $(this).attr('data-note') ;
				str = base64encode(str);
				str = 'ssr://' + str;
			} else {
				str = $(this).attr('data-params-SS');
				str = base64encode(str);
				str = 'ss://' + str;
			}
			layer.alert(str);
		});
	});
});
