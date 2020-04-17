// XD library for communication between 2 different domains
var XD = function() {
    var interval_id, last_hash, cache_bust = 1, attached_callback, window = this;
    return {
        postMessage: function(message, target_url, target) {
            if (!target_url) { return; }
            target = target || parent;
            if (window['postMessage']) {
                target['postMessage'](message, target_url.replace( /([^:]+:\/\/[^\/]+).*/, '$1'));
            } else if (target_url) {
                target.location = target_url.replace(/#.*$/, '') + '#' + (+new Date) + (cache_bust++) + '&' + message;
            }
        },
        receiveMessage: function(callback, source_origin) {
            if (window['postMessage']) {
                if (callback) {
                    attached_callback = function(e) {
                        if ((typeof source_origin === 'string' && e.origin !== source_origin)
                            || (Object.prototype.toString.call(source_origin) === "[object Function]" && source_origin(e.origin) === !1)) {
                            return !1;
                        } callback(e);
                    };
                }
                if (window['addEventListener']) {
                    window[callback ? 'addEventListener' : 'removeEventListener']('message', attached_callback, !1);
                } else {
                    window[callback ? 'attachEvent' : 'detachEvent']('onmessage', attached_callback);
                }
            } else {
                // a polling loop is started & callback is called whenever the location.hash changes
                interval_id && clearInterval(interval_id);
                interval_id = null;
                if (callback) {
                    interval_id = setInterval(function(){
                        var hash = document.location.hash, re = /^#?\d+&/;
                        if (hash !== last_hash && re.test(hash)) {
                            last_hash = hash;
                            callback({data: hash.replace(re, '')});
                        }
                    }, 100);
                }
            }
        }
    };
}();

var xdScript = document.getElementById('xdScript');

if (xdScript) {
    // xdScript contain url of domain to external host with iframe for communication
    var url = xdScript.getAttribute('src').split("/"), origin = url[0] + "//" + url[2];

    XD.receiveMessage(function(message) {
        if (message.data) {
            if ('cookie' in message.data) {
                document.cookie = message.data.cookie;
            }
            if ('hideWidget' in message.data) {
                document.getElementById(message.data.hideWidget + "_widget_container").style.visibility = 'hidden';
            }

            var widgetIframe = document.getElementById(message.data.widget + "_widget_iframe");

            if (widgetIframe && message.data.height) {
                if (message.data.height > 0) widgetIframe.height = message.data.height;
                widgetIframe.width = message.data.width > 0 ? message.data.width : '100%';
            }
        }
    }, origin);
}