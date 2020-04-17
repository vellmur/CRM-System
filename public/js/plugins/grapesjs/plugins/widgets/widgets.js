! function(e, t) {
    "object" == typeof exports && "object" == typeof module ? module.exports = t(require("grapesjs")) : "function" == typeof define && define.amd ? define(["grapesjs"], t) : "object" == typeof exports ? exports["gjs-widgets"] = t(require("grapesjs")) : e["gjs-widgets"] = t(e.grapesjs)
}(this, function(e) {
    return function(e) {
        function t(r) {
            if (n[r]) return n[r].exports;
            var a = n[r] = {
                i: r,
                l: !1,
                exports: {}
            };
            return e[r].call(a.exports, a, a.exports, t), a.l = !0, a.exports
        }
        var n = {};
        return t.m = e, t.c = n, t.d = function(e, n, r) {
            t.o(e, n) || Object.defineProperty(e, n, {
                configurable: !1,
                enumerable: !0,
                get: r
            })
        }, t.n = function(e) {
            var n = e && e.__esModule ? function() {
                return e.default
            } : function() {
                return e
            };
            return t.d(n, "a", n), n
        }, t.o = function(e, t) {
            return Object.prototype.hasOwnProperty.call(e, t)
        }, t.p = "", t(t.s = 0)
    }([function(e, t, n) {
        "use strict";

        function r(e) {
            return e && e.__esModule ? e : {
                default: e
            }
        }
        Object.defineProperty(t, "__esModule", {
            value: !0
        });
        var a = Object.assign || function(e) {
                for (var t = 1; t < arguments.length; t++) {
                    var n = arguments[t];
                    for (var r in n) Object.prototype.hasOwnProperty.call(n, r) && (e[r] = n[r])
                }
                return e
            },
            o = n(1),
            u = r(o),
            s = n(2),
            i = r(s),
            l = n(3),
            d = r(l);
        t.default = u.default.plugins.add("gjs-widgets", function(e) {
            var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                n = a({}, t);
            (0, i.default)(e, n), (0, d.default)(e, n)
        })
    }, function(t, n) {
        t.exports = e
    }, function(e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {
            value: !0
        }), t.default = function(e) {
            var t = (arguments.length > 1 && void 0 !== arguments[1] && arguments[1], e.DomComponents),
                n = t.getType("text"),
                r = n.model,
                a = n.view;
            t.addType("widget-block", {
                model: r.extend({
                    defaults: Object.assign({}, r.prototype.defaults, {
                        name: 'Widgets',
                        badgable: false,
                        copyable: false,
                        hoverable: false,
                        movable: false,
                        stylable: false,
                        droppable: false,
                        resizable: false,
                        editable: false,
                        highlightable: false,
                        layerable: false,
                        selectable: false,
                        draggable: false,
                        removable: false,
                        propagate: ['badgable', 'highlightable', 'draggable', 'selectable', 'droppable', 'movable',
                            'editable', 'hoverable', 'copyable', 'layerable']
                    })
                }, {
                    isComponent: function () {
                        return false;
                    }
                }),
                view: a
            })
        }
    }, function(e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {
            value: !0
        }),
            t.default = function(e) {
            arguments.length > 1 && void 0 !== arguments[1] && arguments[1];
            let options = arguments[0].Config.pluginsOpts;

            if (options.hasOwnProperty('gjs-widgets') === false || options['gjs-widgets'].hasOwnProperty('widgets') === false) {
                return;
            }

            let widgetOptions = options['gjs-widgets'],
                widgets = widgetOptions.widgets;

            for (var i = 0; i < widgets.length; i++) {
                e.BlockManager.add(widgets[i].name, {
                    title: widgets[i].title,
                    get label() {
                        return '<img src="' + widgets[i].icon + '" style="height: 30px;margin-bottom: 10px;"/><br/>'
                        + this.title;
                    },
                    content: {
                        content: widgets[i].content
                    },
                    category: widgetOptions['category'] ? widgetOptions['category'] : 'Widgets'
                });
            }
        }
    }])
});