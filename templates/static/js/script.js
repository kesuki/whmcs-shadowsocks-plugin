/*
 *  ping.js - v0.1.0
 *  Ping Utilities in Javascript
 *  http://github.com/alfg/ping.js
 *
 *  Made by Alfred Gutierrez
 *  Under MIT License
 */
/**
 * Creates a Ping instance.
 * @returns {Ping}
 * @constructor
 */
var Ping = function() {};
/**
 * Pings source and triggers a callback when completed.
 * @param source Source of the website or server, including protocol and port.
 * @param callback Callback function to trigger when completed.
 * @param timeout Optional number of milliseconds to wait before aborting.
 */
Ping.prototype.ping = function(source, callback, timeout) {
    this.img = new Image();
    timeout = timeout || 0;
    var timer;
    var start = new Date();
    this.img.onload = this.img.onerror = pingCheck;
    if (timeout) { timer = setTimeout(pingCheck, timeout); }
    /**
     * Times ping and triggers callback.
     */
    function pingCheck() {
        if (timer) { clearTimeout(timer);}
        var pong = new Date() - start;
        if (typeof callback === "function") {
            callback(pong);
        }
    }
    this.img.src = source + "/?" + (+new Date()); // Trigger image load with cache buster
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
    /* Custom */
    $('button[name="ping"]').on('click',function() {
        var ping = new Ping();
        var address = $(this).attr('data-host');
        var timeout = 1000;
        var _this  = $(this);
        ping.ping('http://' + address,function(data) {
            if(data >= timeout) {
                _this.parents('td').html('<span class="badge badge-danger">超时</span>');
            } else {
                _this.parents('td').html('<span class="badge badge-primary">在线</span>');
            }
        },timeout);
    });
	jQuery(document).ready(function($) {
		$("button[name='qrcode']").on('click',function() {
			if($(this).attr('data-type').indexOf("ssr")!=-1){
				str = base64encode($(this).attr('data-params') + ':' + base64encode($(this).attr('data-pass')));
			} else {
				str = base64encode($(this).attr('data-params'));
			}
			str = $(this).attr('data-type') + '://' + str;
			layer.open({
				type: 1,
				title: $(this).attr('data-title'),
				shade: [0.8, '#000'],
				skin: 'layui-layer-demo',
				offset: 'auto',
				closeBtn: 1,
				shift: 2,
				shadeClose: true,
				content: '<img style="position: relative; left: 600px; width: 100%; height: 100%;" src="http://pan.baidu.com/share/qrcode?w=300&h=300&url=' + str + '"/><div style="position: relative; overflow: auto; text-align: center; margin-bottom: 10px; font-size: 12px;">请使用 Shadowsocks 客户端进行扫描</div>'
			});
		});
	});
});
