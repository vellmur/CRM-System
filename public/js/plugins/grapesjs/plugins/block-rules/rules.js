! function(e, t) {
    "object" == typeof exports && "object" == typeof module ? module.exports = t(require("grapesjs")) : "function" == typeof define && define.amd ? define(["grapesjs"], t) : "object" == typeof exports ? exports["blocks-rules"] = t(require("grapesjs")) : e["blocks-rules"] = t(e.grapesjs)
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
        t.default = u.default.plugins.add("blocks-rules", function(e) {
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

            t.addType("section-viewonly", {
                model: r.extend({
                    defaults: Object.assign({}, r.prototype.defaults, {
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
                            'editable', 'hoverable', 'layerable']
                    })
                }, {
                    isComponent: function (e) {
                        if (e.classList && e.classList.contains('section-viewonly')) {
                            return {type: 'section-viewonly'}
                        }

                        return false;
                    }
                }),
                view: a
            });


            t.addType("element-viewonly", {
                model: r.extend({
                    defaults: Object.assign({}, r.prototype.defaults, {
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
                        removable: false
                    })
                }, {
                    isComponent: function (e) {
                        if (e.classList && e.classList.contains('element-viewonly')) {
                            return {type: 'element-viewonly'}
                        }

                        return false;
                    }
                }),
                view: a
            });

            t.addType("section-editonly", {
                model: r.extend({
                    defaults: Object.assign({}, r.prototype.defaults, {
                        copyable: false,
                        draggable: false,
                        droppable: false,
                        name: 'Editable area',
                        removable: false,
                        stylable: false,
                        propagate: ['badgable', 'highlightable', 'draggable','droppable', 'selectable', 'removable', 'copyable']
                    })
                }, {
                    isComponent: function (element) {
                        if (element.classList && element.classList.contains('section-editonly')) {
                            return {type: 'section-editonly'}
                        }

                        return false;
                    }
                }),
                view: a
            });

            t.addType("section-editable", {
                model: r.extend({
                    defaults: Object.assign({}, r.prototype.defaults, {
                        name: 'Editable section',
                        copyable: false,
                        draggable: false,
                        removable: false,
                        editable: false,
                        movable: false,
                        layerable: false,
                        selectable: false
                    })
                }, {
                    isComponent: function (e) {
                        if (e.classList && e.classList.contains('section-editable')) {
                            return {type: 'section-editable'}
                        }

                        return false;
                    }
                }),
                view: a
            });

            t.getWrapper().set('droppable', false);
            t.getWrapper().set('selectable', false);
            t.getWrapper().set('highlightable', false);
        }
    }, function(e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {
            value: !0
        }),
            t.default = function(e) {
                arguments.length > 1 && void 0 !== arguments[1] && arguments[1];
            }
    }])
});