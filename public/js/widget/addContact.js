function createWidget(config)
{
    var Util = {
        extendObject: function(a, b) {
            for(prop in b){
                a[prop] = b[prop];
            }
            return a;
        },
        proto: 'https:' === document.location.protocol ? 'https://' : 'http://'
    };

    var params = Util.extendObject(config),
        parentScript = document.getElementById(params.widget + 'Widget'),
        token = parentScript.getAttribute('data-token'),
        parent = encodeURIComponent(document.location.href);

    params.widget_url = [Util.proto,params.host,"/widget-load/",params.lang,"/",token,"/",params.widget,"?parentFrame=",parent].join("");

    var Widget = {
        created: false,
        widgetElement: null,
        show: function() {
            if (this.created) return;
            this.widgetElement = document.createElement('div');
            this.widgetElement.setAttribute('id', params.widget + '_widget_container');
            this.widgetElement.innerHTML = ' \
				<iframe id="' + params.widget + '_widget_iframe" name="' + params.widget + '_widget_iframe" src="' + params.widget_url + '" scrolling="no" frameborder="0" width="100%"></iframe>';
            parentScript.parentNode.insertBefore(this.widgetElement, parentScript.nextSibling);
            this.created = true;
        }
    };

    // Get XD library for communication between widget server (external) and current server
    xdScript = document.getElementById('xdScript');

    // If XD library not created in the document => create it
    if (!xdScript) {
        var xdScript = document.createElement('script');
        xdScript.src = Util.proto + params.host + "/js/widget/xd.js?v=1";
        xdScript.id = 'xdScript';
        document.getElementsByTagName('head')[0].appendChild(xdScript);
    }

    Widget.show();
}