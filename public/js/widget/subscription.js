function createWidget(config)
{
    var Util = {
        extendObject: function(a, b) {
            for(prop in b){a[prop] = b[prop];}
            return a;
        },
        proto: 'https:' === document.location.protocol ? 'https://' : 'http://'
    };

    var params = Util.extendObject(config),
        parentScript = document.getElementById(params.widget + 'Widget'),
        token = parentScript.getAttribute('data-token'),
        parent = encodeURIComponent(document.location.href);

    params.widget_url = [Util.proto,params.host,"/widget-load/",params.lang,"/",token,"/",params.widget,"?parentFrame=",parent].join("");

    var style = document.createElement('link');style.rel='stylesheet';style.type='text/css';
    style.href = Util.proto + params.host + "/css/widget/modal-fixed.css";
    document.getElementsByTagName('head')[0].appendChild(style);

    var Widget = {
        created: false,
        widgetElement: null,
        show: function() {
            var elem = this;
            if (elem.created) return;
            elem.widgetElement = document.createElement('div');
            elem.widgetElement.style.visibility = 'hidden';
            elem.widgetElement.setAttribute('id', params.widget + '_widget_container');
            elem.widgetElement.innerHTML = '<iframe id="'+params.widget+'_widget_iframe" name="'+params.widget+'_widget_iframe"' +
                ' src="'+params.widget_url+'" scrolling="no" frameborder="0" width="100%"></iframe>';
            document.body.appendChild(this.widgetElement);
            elem.created = true;

            if (!document.cookie.match("\\bsubscriptionClosed=([^;]*)\\b")) {
                setTimeout(function () { elem.widgetElement.style.visibility = ''; }, 5000);
            }
        }
    };

    // Get or append XD library for communication between widget server (external) and current server
    xdScript = document.getElementById('xdScript');

    if (!xdScript) {
        var xdScript = document.createElement('script');
        xdScript.src = Util.proto + params.host + "/js/widget/xd.js";
        xdScript.id = 'xdScript';
        document.getElementsByTagName('head')[0].appendChild(xdScript);
    }

    Widget.show();
}