!function(e) {
    function t(o) {
        if (n[o])
            return n[o].exports;
        var r = n[o] = {
            exports: {},
            id: o,
            loaded: !1
        };
        return e[o].call(r.exports, r, r.exports, t), r.loaded = !0, r.exports
    }
    var n = {};
    return t.m = e, t.c = n, t.p = "", t(0)
}([function(e, t, n) {
    n(1), n(3), n(25), e.exports = n(27)
}, function(e, t, n) {
    var o = n(2);
    Y.use("node", function(e) {
        window.Singleton.create({
            ready: function() {
                if (e.one(".collection-type-index")) {
                    if (window.innerWidth <= 640)
                        return !1;
                    this.initializer(), this.bindUI(), this.syncUI()
                }
            },
            initializer: function() {
                this.navShowPosition = 0, this.el = e.one(".show-on-scroll"), this.initElOffset()
            },
            initElOffset: function() {
                return this.el ? (this.elOffset = e.one(this.el.getData("offset-el")), this.offsetBehavior = this.el.getData("offset-behavior") || "top", this.elOffset ? (e.one("body").prepend(e.Node.create('<div class="show-on-scroll-wrapper" id="showOnScrollWrapper"></div>')), this.wrapper = e.one("#showOnScrollWrapper"), this.wrapper.setHTML(this.el._node.outerHTML), void 0) : void console.warn("No show on scroll offset element found.")) : void console.warn("No show on scroll element found.")
            },
            bindUI: function() {
                this.scrollEvents(), e.one(window).on("resize", function() {
                    this.syncUI()
                }, this)
            },
            syncUI: function() {
                this.getVariables()
            },
            getVariables: function() {
                (this.elOffset || (this.initElOffset(), this.elOffset)) && ("bottom" === this.offsetBehavior ? this.navShowPosition = this.elOffset.getY() + this.elOffset.get("offsetHeight") : this.navShowPosition = this.elOffset.getY())
            },
            scrollEvents: function() {
                this.scrolling = !1, e.one(window).on("scroll", function() {
                    this.scrolling === !1 && (this.scrolling = !0, this.scrollLogic(), o(function() {
                        this.scrolling = !1
                    }, 300, this))
                }, this)
            },
            scrollLogic: function() {
                window.pageYOffset > this.navShowPosition ? this.wrapper.addClass("show") : this.wrapper.removeClass("show"), e.later(100, this, function() {
                    this.scrolling === !0 && window.requestAnimationFrame(e.bind(function() {
                        this.scrollLogic()
                    }, this))
                })
            }
        })
    })
}, function(e, t) {
    function n(e, t, n) {
        t = t || 100, n = n || window, e && (o && o.cancel(), o = Y.later(t, n, e))
    }
    var o;
    e.exports = n
}, function(e, t, n) {
    var o = n(4),
        r = n(2),
        i = n(5).VideoBackground,
        a = n(5).getVideoProps;
    Y.use(["node", "squarespace-gallery-ng"], function(e) {
        window.Singleton.create({
            ready: function() {
                this.resetGalleryPosition(), e.one(".collection-type-index") && this.resetIndexGalleryPosition(), e.one(".collection-type-blog.view-list .sqs-featured-posts-gallery") && e.one("body").addClass("has-banner-image"), this.init(), this.bindUI(), this.syncUI()
            },
            init: function() {
                if (this.setupUserAccountLinks(), this.forceMobileNav(), this.promotedGalleryShrink(), e.one(".has-promoted-gallery") ? (this.textShrink(".meta-description p > strong", "p"), this.textShrink(".meta-description p > em > strong", "p")) : (this.textShrink(".desc-wrapper p > strong", "p"), this.textShrink(".desc-wrapper p > em > strong", "p")), this.textShrink(".post-title a", ".post-title"), this.textShrink(".blog-item-wrapper .post-title", ".title-desc-wrapper"), this._touch = e.one(".touch-styles"), e.one(".collection-type-blog.view-list .sqs-featured-posts-gallery") && this.makeFeaturedGallery(".posts", ".post"), this.hideArrowsWhenOneSlide(), this.repositionCartButton(), !this._touch) {
                    var t = e.one("#preFooter");
                    t.inViewportRegion() === !1 && t.addClass("unscrolled"), e.one(window).on("scroll", function() {
                        t.hasClass("unscrolled") && t.toggleClass("unscrolled", !t.inViewportRegion())
                    })
                }
                var n = Array.prototype.slice.call(document.body.querySelectorAll("div.sqs-video-background"));
                n.map(function(e) {
                    var t = a(e);
                    t.customFallbackImage = t.container.querySelector(".custom-fallback-image"), t.scaleFactor = 1.1, new i(t)
                })
            },
            setupUserAccountLinks: function() {
                e.all(".user-account-link").each(function(e) {
                    var t = o.isUserAuthenticated() ? ".unauth" : ".auth",
                        n = e.one(t);
                    n.remove(), e.on("click", function(e) {
                        e.preventDefault(), o.openAccountScreen()
                    })
                })
            },
            bindUI: function() {
                e.one(window).on("resize", this.syncUI, this), e.all(".mobile-nav-toggle, .body-overlay").each(function(t) {
                    t.on("click", function() {
                        e.one("body").toggleClass("mobile-nav-open")
                    })
                });
                var t = e.throttle(e.bind(function() {
                    this.bindScroll("#preFooter", .6 * e.one("#preFooter").height())
                }, this), 200);
                this._touch || e.one(window).on("scroll", t), e.all(".subnav").each(function(t) {
                    var n = t._node.getBoundingClientRect();
                    n.right > e.config.win.innerWidth && t.addClass("right")
                });
                var n = '#sidecarNav a[href^="#"], #sidecarNav a[href^="/#"], #sidecarNav a[href^="/"][href*="#"]';
                e.all(n).each(function(t) {
                    t.on("click", function(t) {
                        e.one("body").removeClass("mobile-nav-open")
                    }, this)
                }, this), this.showIndexNavOnScroll(), this.disableHoverOnScroll()
            },
            syncUI: function() {
                this.forceMobileNav(), r(function() {
                    this.addPaddingToFooter()
                }, 100, this)
            },
            bindScroll: function(t, n) {
                var o;
                if (o || (o = e.one(t + ".unscrolled")), o) {
                    var r = window.pageYOffset + e.one("body").get("winHeight"),
                        i = o.getY() + (n || 0);
                    r >= i && o.removeClass("unscrolled")
                }
            },
            _atLeast: 0,
            forceMobileNav: function() {
                var t = e.one("#mainNavWrapper");
                if (t) {
                    var n,
                        o,
                        r,
                        i = e.one("body").get("winWidth"),
                        a = e.one("#header");
                    r = e.one("#logoWrapper") ? parseInt(e.Squarespace.Template.getTweakValue("logoContainerWidth"), 10) : parseInt(e.Squarespace.Template.getTweakValue("siteTitleContainerWidth"), 10), i > this._atLeast ? (e.one("body").removeClass("force-mobile-nav"), n = a.get("offsetWidth") - parseInt(a.getStyle("paddingLeft"), 10) - parseInt(a.getStyle("paddingRight"), 10), o = t.get("offsetWidth"), o > n - r && (e.one("body").addClass("force-mobile-nav"), this._atLeast = i)) : e.one("body").addClass("force-mobile-nav")
                }
            },
            makeFeaturedGallery: function(t, n) {
                new e.Squarespace.Gallery2({
                    autoHeight: !1,
                    container: t,
                    slides: n,
                    elements: {
                        next: ".next-slide, .simple .next, .sqs-gallery-controls .next",
                        previous: ".previous-slide, .simple .previous, .sqs-gallery-controls .previous",
                        controls: ".dots, .circles",
                        currentIndex: ".current-index",
                        totalSlides: ".total-slides"
                    },
                    loop: !0,
                    loaderOptions: {
                        load: !0
                    },
                    design: "stacked",
                    designOptions: {
                        transition: "fade",
                        clickBehavior: "auto"
                    },
                    refreshOnResize: !0
                })
            },
            promotedGalleryShrink: function() {
                var t,
                    n,
                    o,
                    r = ".has-promoted-gallery #promotedGalleryWrapper .meta";
                e.one(r) && (t = e.one("#promotedGalleryWrapper").get("offsetHeight"), e.one(".transparent-header") && (t -= 90), e.all(r).each(function(e) {
                    e.setStyle("display", "block"), n = e.get("offsetHeight"), n > t && (o = e.ancestor(".slide"), o.addClass("reduce-text-size"), n = e.get("offsetHeight"), n > t && (o.removeClass("reduce-text-size"), o.addClass("hide-body-text"), n = e.get("offsetHeight"), n > t && o.addClass("reduce-text-size"))), e.setAttribute("style", "")
                }))
            },
            textShrink: function(t, n) {
                e.one(t) && e.one(t).ancestor(n) && e.all(t).each(function(t) {
                    t.plug(e.Squarespace.TextShrink, {
                        parentEl: t.ancestor(n)
                    })
                })
            },
            resetIndexGalleryPosition: function() {
                var t = ".collection-type-index .index-section .sqs-layout > .sqs-row:first-child > .sqs-col-12 > .gallery-block:first-child .sqs-gallery-block-slideshow",
                    n = ".collection-type-index .index-section .promoted-gallery-wrapper ~ .index-section-wrapper .sqs-layout > .sqs-row:first-child > .sqs-col-12 > .gallery-block:first-child",
                    o = e.one(".collection-type-index .index-section:first-child .sqs-layout > .sqs-row:first-child > .sqs-col-12 > .gallery-block:first-child .sqs-gallery-block-slideshow");
                o && e.one("body").addClass("has-banner-image"), e.one(t) && (e.one("body").addClass("has-promoted-gallery"), e.all(n).each(function(e) {
                    e.one(".sqs-gallery-block-slideshow") && e.ancestor(".index-section-wrapper").previous(".promoted-gallery-wrapper").addClass("promoted-full").append(e)
                }))
            },
            resetGalleryPosition: function() {
                var t = e.one(".collection-type-page .main-content .sqs-layout > .sqs-row:first-child > .sqs-col-12 > .gallery-block:first-child .sqs-gallery-block-slideshow"),
                    n = e.one(".collection-type-page .main-content .sqs-layout > .sqs-row:first-child > .sqs-col-12 > .gallery-block:first-child");
                t && (e.one("#promotedGalleryWrapper .row .col").append(n), e.one("body").addClass("has-promoted-gallery").addClass("has-banner-image"))
            },
            hideArrowsWhenOneSlide: function() {
                e.one(".posts .post:only-child") && e.all(".circles").addClass("hidden")
            },
            repositionCartButton: function() {
                var t = e.one("#header").get("offsetHeight"),
                    n = e.one(".sqs-cart-dropzone");
                n && (e.one(".transparent-header.has-banner-image") ? n.setStyle("top", t) : n.setStyle("top", t + 20))
            },
            showIndexNavOnScroll: function() {
                var t,
                    n = function() {
                        if (e.one(".index-section")) {
                            var n = e.one(".index-section").getDOMNode();
                            t = n.getBoundingClientRect().bottom + window.pageYOffset
                        }
                    };
                if (n(), e.one(".collection-type-index") && window.innerWidth <= 640) {
                    var o = function() {
                        t - window.pageYOffset <= 0 ? e.one("body").addClass("fix-header-nav") : e.one("body").removeClass("fix-header-nav")
                    };
                    e.one(window).on("resize", function() {
                        n()
                    }), o(), e.one(window).on("scroll", function() {
                        o()
                    }, this), e.one(".mobile-nav-toggle.fixed-nav-toggle").on("click", function() {
                        e.one("body").hasClass("fix-header-nav") && e.one("body").removeClass("fix-header-nav")
                    }), e.one(window).on(["touchstart", "MSPointerDown"], function() {
                        this._timeout && this._timeout.cancel(), this.isHidden = !0, this.isHidden === !0 && (e.one(".mobile-nav-toggle.fixed-nav-toggle").setStyle("opacity", 1), this.isHidden = !1)
                    }, this), e.one(window).on(["touchend", "MSPointerUp"], function() {
                        this._timeout = e.later(1500, this, function() {
                            this.isHidden = !0, e.one(".mobile-nav-toggle.fixed-nav-toggle").setStyle("opacity", 0)
                        })
                    }, this)
                }
            },
            addPaddingToFooter: function() {
                var t = parseInt(e.one("#footer").getStyle("paddingBottom"), 10),
                    n = e.one("#siteWrapper").get("offsetHeight"),
                    o = e.one("body").get("winHeight");
                n - t <= o && e.one("#footer").setStyle("paddingBottom", o - (n - t))
            },
            disableHoverOnScroll: function() {
                if (e.UA.mobile)
                    return !1;
                var t,
                    n = ".disable-hover:not(.sqs-layout-editing), .disable-hover:not(.sqs-layout-editing) * { pointer-events: none  ; }",
                    o = document.head || document.getElementsByTagName("head")[0],
                    r = document.createElement("style"),
                    i = document.body;
                r.type = "text/css", r.styleSheet ? r.styleSheet.cssText = n : r.appendChild(document.createTextNode(n)), o.appendChild(r), window.addEventListener("scroll", function() {
                    clearTimeout(t), i.classList.contains("disable-hover") || i.classList.add("disable-hover"), t = setTimeout(function() {
                        i.classList.remove("disable-hover")
                    }, 300)
                }, !1)
            }
        })
    })
}, function(e, t) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });

    var n = "UserAccounts API not available",
        o = window.UserAccountApi,
        r = function() {
            console.warn(n)
        },
        i = o ? o.isUserAuthenticated : r,
        a = o ? o.openAccountScreen : r;
    t.default = {
        isUserAuthenticated: i,
        openAccountScreen: a
    }, e.exports = t.default
}, function(e, t, n) {
    var o = n(6).VideoBackground,
        r = n(24);
    e.exports = {
        VideoBackground: o,
        getVideoProps: r
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.videoAutoplayTest = t.VideoFilterPropertyValues = t.VideoBackground = void 0, n(7);
    var o = n(8);
    t.VideoBackground = o.VideoBackground, t.VideoFilterPropertyValues = o.VideoFilterPropertyValues, t.videoAutoplayTest = o.videoAutoplayTest
}, function(e, t) {
    !function() {
        function e(e, t) {
            t = t || {
                    bubbles: !1,
                    cancelable: !1,
                    detail: void 0
                };
            var n = document.createEvent("CustomEvent");
            return n.initCustomEvent(e, t.bubbles, t.cancelable, t.detail), n
        }
        return "function" != typeof window.CustomEvent && (e.prototype = window.Event.prototype, void (window.CustomEvent = e))
    }()
}, function(e, t, n) {
    "use strict";
    function o(e) {
        return e && e.__esModule ? e : {
            default: e
        }
    }
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.videoAutoplayTest = t.VideoFilterPropertyValues = t.VideoBackground = void 0;
    var r = n(9),
        i = o(r),
        a = n(23),
        s = n(12),
        l = o(s);
    t.VideoBackground = i.default, t.VideoFilterPropertyValues = a.filterProperties, t.videoAutoplayTest = l.default
}, function(e, t, n) {
    "use strict";
    function o(e) {
        return e && e.__esModule ? e : {
            default: e
        }
    }
    function r(e, t) {
        if (!(e instanceof t))
            throw new TypeError("Cannot call a class as a function")
    }
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    var i = function() {
            function e(e, t) {
                for (var n = 0; n < t.length; n++) {
                    var o = t[n];
                    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
                }
            }
            return function(t, n, o) {
                return n && e(t.prototype, n), o && e(t, o), t
            }
        }(),
        a = n(10),
        s = o(a),
        l = n(12),
        c = o(l),
        u = n(14),
        d = n(22),
        A = n(16),
        f = n(23),
        h = n(15),
        p = {
            vimeo: {
                api: u.initializeVimeoAPI,
                player: u.initializeVimeoPlayer
            },
            youtube: {
                api: d.initializeYouTubeAPI,
                player: d.initializeYouTubePlayer
            }
        },
        y = function() {
            function e(t) {
                var n = this,
                    o = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : window;
                r(this, e), this.windowContext = o, this.events = [], this.browserCanAutoPlay = !1, this.videoCanAutoPlay = !1, this.setInstanceProperties(t), (0, c.default)().then(function(e) {
                    n.logger(e), n.browserCanAutoPlay = !0, n.initializeVideoAPI()
                }, function(e) {
                    n.logger(e), n.browserCanAutoPlay = !1, n.renderFallbackBehavior()
                }).then(function() {
                    n.setDisplayEffects(), n.bindUI(), n.DEBUG.enabled === !0 && (window.vdbg = n)
                })
            }
            return i(e, [{
                key: "destroy",
                value: function() {
                    this.events && this.events.forEach(function(e) {
                        return e.target.removeEventListener(e.type, e.handler, !0)
                    }), this.events = null, this.player && "function" == typeof this.player.destroy && (this.player.iframe.classList.remove("ready"), clearTimeout(this.playTimeout), this.playTimeout = null, this.player.destroy(), this.player = {}), "number" == typeof this.timer && (clearTimeout(this.timer), this.timer = null)
                }
            }, {
                key: "bindUI",
                value: function() {
                    var e = this,
                        t = function() {
                            e.windowContext.requestAnimationFrame(function() {
                                e.scaleVideo()
                            })
                        };
                    this.events.push({
                        target: this.windowContext,
                        type: "resize",
                        handler: t
                    }), this.windowContext.addEventListener("resize", t, !0)
                }
            }, {
                key: "setInstanceProperties",
                value: function() {
                    var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {};
                    return e = (0, s.default)({}, A.DEFAULT_PROPERTY_VALUES, e), 1 === e.container.nodeType ? this.container = e.container : "string" == typeof e.container && (this.container = document.querySelector(e.container)), this.container ? (this.videoSource = (0, h.getVideoSource)(e.url), this.videoId = (0, h.getVideoID)(e.url, this.videoSource), this.customFallbackImage = (0, h.validatedImage)(e.customFallbackImage), this.filter = e.filter, this.filterStrength = e.filterStrength, this.fitMode = e.fitMode, this.scaleFactor = e.scaleFactor, this.playbackSpeed = parseFloat(e.playbackSpeed) < .5 ? 1 : parseFloat(e.playbackSpeed), this.timeCode = {
                        start: (0, h.getStartTime)(e.url, this.videoSource) || e.timeCode.start,
                        end: e.timeCode.end
                    }, this.player = {}, void (this.DEBUG = e.DEBUG)) : (console.error("Container " + e.container + " not found"), !1)
                }
            }, {
                key: "setFallbackImage",
                value: function() {
                    var e = this.customFallbackImage;
                    if (!(!e || this.browserCanAutoPlay && this.videoCanAutoPlay))
                        return e.addEventListener("load", function() {
                            e.classList.add("loaded")
                        }, {
                            once: !0
                        }), this.windowContext.ImageLoader ? void this.windowContext.ImageLoader.load(e, {
                            load: !0
                        }) : void (e.src = e.src)
                }
            }, {
                key: "initializeVideoAPI",
                value: function() {
                    var e = this;
                    if (this.browserCanAutoPlay && this.videoSource && this.videoId) {
                        this.player.ready = !1;
                        var t = p[this.videoSource].api,
                            n = t(this.windowContext);
                        n.then(function(t) {
                            e.logger(t), e.player.ready = !1, e.initializeVideoPlayer()
                        }).catch(function(t) {
                            e.renderFallbackBehavior(), document.body.classList.add("ready"), e.logger(t)
                        })
                    } else
                        this.renderFallbackBehavior(), document.body.classList.add("ready")
                }
            }, {
                key: "initializeVideoPlayer",
                value: function() {
                    var e = this;
                    if (this.player.ready) {
                        try {
                            this.player.destroy()
                        } catch (e) {}
                        this.player.ready = !1
                    }
                    var t = p[this.videoSource].player,
                        n = t({
                            instance: this,
                            container: this.container,
                            win: this.windowContext,
                            videoId: this.videoId,
                            startTime: this.timeCode.start,
                            speed: this.playbackSpeed,
                            readyCallback: function(t, n) {
                                e.player.iframe.classList.add("background-video"), e.videoAspectRatio = (0, h.findPlayerAspectRatio)(e.container, e.player, e.videoSource), e.syncPlayer();
                                var o = new CustomEvent("ready");
                                e.container.dispatchEvent(o)
                            },
                            stateChangeCallback: function(t, n) {
                                switch (t) {
                                    case "buffering":
                                        e.testVideoEmbedAutoplay();
                                        break;
                                    case "playing":
                                        null === e.playTimeout && e.videoCanAutoPlay || e.testVideoEmbedAutoplay(!0)
                                }
                                t && e.logger(t), n && e.logger(n)
                            }
                        });
                    n.then(function(t) {
                        e.player = t
                    }, function(t) {
                        e.logger(t), e.testVideoEmbedAutoplay(!1)
                    })
                }
            }, {
                key: "testVideoEmbedAutoplay",
                value: function() {
                    var e = this,
                        t = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : void 0;
                    return void 0 === t && (this.logger("test video autoplay: begin"), this.playTimeout && (clearTimeout(this.playTimeout), this.playTimeout = null), this.playTimeout = setTimeout(function() {
                        e.testVideoEmbedAutoplay(!1)
                    }, A.TIMEOUT)), t === !0 ? (clearTimeout(this.playTimeout), this.logger("test video autoplay: success"), this.playTimeout = null, this.videoCanAutoPlay = !0, this.player.ready = !0, this.player.iframe.classList.add("ready"), void this.container.classList.remove("mobile")) : t === !1 ? (clearTimeout(this.playTimeout), this.logger("test video autoplay: failure"), this.playTimeout = null, this.videoCanAutoPlay = !1, void this.renderFallbackBehavior()) : void 0
                }
            }, {
                key: "renderFallbackBehavior",
                value: function() {
                    this.setFallbackImage(), this.container.classList.add("mobile"), this.logger("added mobile")
                }
            }, {
                key: "syncPlayer",
                value: function() {
                    this.setDisplayEffects(), this.setSpeed(), this.scaleVideo()
                }
            }, {
                key: "scaleVideo",
                value: function(e) {
                    this.setFallbackImage();
                    var t = this.player.iframe;
                    if (t) {
                        var n = e || this.scaleFactor;
                        if ("fill" !== this.fitMode)
                            return t.style.width = "", void (t.style.height = "");
                        var o = t.parentNode.clientWidth,
                            r = t.parentNode.clientHeight,
                            i = o / r,
                            a = 0,
                            s = 0;
                        i > this.videoAspectRatio ? (a = o * n, s = o * n / this.videoAspectRatio) : this.videoAspectRatio > i ? (a = r * n * this.videoAspectRatio, s = r * n) : (a = o * n, s = r * n), t.style.width = a + "px", t.style.height = s + "px", t.style.left = 0 - (a - o) / 2 + "px", t.style.top = 0 - (s - r) / 2 + "px"
                    }
                }
            }, {
                key: "setSpeed",
                value: function(e) {
                    this.playbackSpeed = parseFloat(this.playbackSpeed), this.player.setPlaybackRate && this.player.setPlaybackRate(this.playbackSpeed)
                }
            }, {
                key: "setDisplayEffects",
                value: function() {
                    this.setFilter()
                }
            }, {
                key: "setFilter",
                value: function() {
                    var e = this.container.style,
                        t = f.filterOptions[this.filter - 1],
                        n = "";
                    "none" !== t && (n = this.getFilterStyle(t, this.filterStrength));
                    var o = "blur" === t;
                    e.webkitFilter = o ? "" : n, e.filter = o ? "" : n, this.container.classList.toggle("filter-blur", o), Array.prototype.slice.call(this.container.children).forEach(function(e) {
                        e.style.webkitFilter = o ? n : "", e.style.filter = o ? n : ""
                    })
                }
            }, {
                key: "getFilterStyle",
                value: function(e, t) {
                    return e + "(" + (f.filterProperties[e].modifier(t) + f.filterProperties[e].unit) + ")"
                }
            }, {
                key: "logger",
                value: function(e) {
                    this.DEBUG.enabled && this.DEBUG.verbose && this.windowContext.console.log(e)
                }
            }]), e
        }();
    t.default = y
}, function(e, t, n) {
    (function(e, n) {
        function o(e, t) {
            return e.set(t[0], t[1]), e
        }
        function r(e, t) {
            return e.add(t), e
        }
        function i(e, t, n) {
            switch (n.length) {
                case 0:
                    return e.call(t);
                case 1:
                    return e.call(t, n[0]);
                case 2:
                    return e.call(t, n[0], n[1]);
                case 3:
                    return e.call(t, n[0], n[1], n[2])
            }
            return e.apply(t, n)
        }
        function a(e, t) {
            for (var n = -1, o = e ? e.length : 0; ++n < o && t(e[n], n, e) !== !1;)
                ;
            return e
        }
        function s(e, t) {
            for (var n = -1, o = t.length, r = e.length; ++n < o;)
                e[r + n] = t[n];
            return e
        }
        function l(e, t, n, o) {
            var r = -1,
                i = e ? e.length : 0;
            for (o && i && (n = e[++r]); ++r < i;)
                n = t(n, e[r], r, e);
            return n
        }
        function c(e, t) {
            for (var n = -1, o = Array(e); ++n < e;)
                o[n] = t(n);
            return o
        }
        function u(e) {
            return function(t) {
                return e(t)
            }
        }
        function d(e, t) {
            return null == e ? void 0 : e[t]
        }
        function A(e) {
            var t = !1;
            if (null != e && "function" != typeof e.toString)
                try {
                    t = !!(e + "")
                } catch (e) {}
            return t
        }
        function f(e) {
            var t = -1,
                n = Array(e.size);
            return e.forEach(function(e, o) {
                n[++t] = [o, e]
            }), n
        }
        function h(e, t) {
            return function(n) {
                return e(t(n))
            }
        }
        function p(e) {
            var t = -1,
                n = Array(e.size);
            return e.forEach(function(e) {
                n[++t] = e
            }), n
        }
        function y(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function g() {
            this.__data__ = rn ? rn(null) : {}
        }
        function v(e) {
            return this.has(e) && delete this.__data__[e]
        }
        function m(e) {
            var t = this.__data__;
            if (rn) {
                var n = t[e];
                return n === De ? void 0 : n
            }
            return Gt.call(t, e) ? t[e] : void 0
        }
        function b(e) {
            var t = this.__data__;
            return rn ? void 0 !== t[e] : Gt.call(t, e)
        }
        function w(e, t) {
            var n = this.__data__;
            return n[e] = rn && void 0 === t ? De : t, this
        }
        function E(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function T() {
            this.__data__ = []
        }
        function k(e) {
            var t = this.__data__,
                n = L(t, e);
            if (n < 0)
                return !1;
            var o = t.length - 1;
            return n == o ? t.pop() : zt.call(t, n, 1), !0
        }
        function S(e) {
            var t = this.__data__,
                n = L(t, e);
            return n < 0 ? void 0 : t[n][1]
        }
        function _(e) {
            return L(this.__data__, e) > -1
        }
        function x(e, t) {
            var n = this.__data__,
                o = L(n, e);
            return o < 0 ? n.push([e, t]) : n[o][1] = t, this
        }
        function P(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function I() {
            this.__data__ = {
                hash: new y,
                map: new (en || E),
                string: new y
            }
        }
        function R(e) {
            return pe(this, e).delete(e)
        }
        function C(e) {
            return pe(this, e).get(e)
        }
        function F(e) {
            return pe(this, e).has(e)
        }
        function V(e, t) {
            return pe(this, e).set(e, t), this
        }
        function B(e) {
            this.__data__ = new E(e)
        }
        function Y() {
            this.__data__ = new E
        }
        function j(e) {
            return this.__data__.delete(e)
        }
        function U(e) {
            return this.__data__.get(e)
        }
        function G(e) {
            return this.__data__.has(e)
        }
        function O(e, t) {
            var n = this.__data__;
            if (n instanceof E) {
                var o = n.__data__;
                if (!en || o.length < Ne - 1)
                    return o.push([e, t]), this;
                n = this.__data__ = new P(o)
            }
            return n.set(e, t), this
        }
        function M(e, t) {
            var n = pn(e) || Pe(e) ? c(e.length, String) : [],
                o = n.length,
                r = !!o;
            for (var i in e)
                !t && !Gt.call(e, i) || r && ("length" == i || be(i, o)) || n.push(i);
            return n
        }
        function N(e, t, n) {
            (void 0 === n || xe(e[t], n)) && ("number" != typeof t || void 0 !== n || t in e) || (e[t] = n)
        }
        function D(e, t, n) {
            var o = e[t];
            Gt.call(e, t) && xe(o, n) && (void 0 !== n || t in e) || (e[t] = n)
        }
        function L(e, t) {
            for (var n = e.length; n--;)
                if (xe(e[n][0], t))
                    return n;
            return -1
        }
        function Q(e, t) {
            return e && de(t, Ue(t), e)
        }
        function Z(e, t, n, o, r, i, s) {
            var l;
            if (o && (l = i ? o(e, r, i, s) : o(e)), void 0 !== l)
                return l;
            if (!Ve(e))
                return e;
            var c = pn(e);
            if (c) {
                if (l = ge(e), !t)
                    return ue(e, l)
            } else {
                var u = hn(e),
                    d = u == qe || u == Je;
                if (yn(e))
                    return ne(e, t);
                if (u == $e || u == Qe || d && !i) {
                    if (A(e))
                        return i ? e : {};
                    if (l = ve(d ? {} : e), !t)
                        return Ae(e, Q(l, e))
                } else {
                    if (!Et[u])
                        return i ? e : {};
                    l = me(e, u, Z, t)
                }
            }
            s || (s = new B);
            var f = s.get(e);
            if (f)
                return f;
            if (s.set(e, l), !c)
                var h = n ? he(e) : Ue(e);
            return a(h || e, function(r, i) {
                h && (i = r, r = e[i]), D(l, i, Z(r, t, n, o, i, e, s))
            }), l
        }
        function H(e) {
            return Ve(e) ? Ht(e) : {}
        }
        function W(e, t, n) {
            var o = t(e);
            return pn(e) ? o : s(o, n(e))
        }
        function z(e) {
            return Mt.call(e)
        }
        function q(e) {
            if (!Ve(e) || Te(e))
                return !1;
            var t = Ce(e) || A(e) ? Nt : mt;
            return t.test(_e(e))
        }
        function J(e) {
            return Be(e) && Fe(e.length) && !!wt[Mt.call(e)]
        }
        function X(e) {
            if (!ke(e))
                return Xt(e);
            var t = [];
            for (var n in Object(e))
                Gt.call(e, n) && "constructor" != n && t.push(n);
            return t
        }
        function K(e) {
            if (!Ve(e))
                return Se(e);
            var t = ke(e),
                n = [];
            for (var o in e)
                ("constructor" != o || !t && Gt.call(e, o)) && n.push(o);
            return n
        }
        function $(e, t, n, o, r) {
            if (e !== t) {
                if (!pn(t) && !gn(t))
                    var i = K(t);
                a(i || t, function(a, s) {
                    if (i && (s = a, a = t[s]), Ve(a))
                        r || (r = new B), ee(e, t, s, n, $, o, r);
                    else {
                        var l = o ? o(e[s], a, s + "", e, t, r) : void 0;
                        void 0 === l && (l = a), N(e, s, l)
                    }
                })
            }
        }
        function ee(e, t, n, o, r, i, a) {
            var s = e[n],
                l = t[n],
                c = a.get(l);
            if (c)
                return void N(e, n, c);
            var u = i ? i(s, l, n + "", e, t, a) : void 0,
                d = void 0 === u;
            d && (u = l, pn(l) || gn(l) ? pn(s) ? u = s : Re(s) ? u = ue(s) : (d = !1, u = Z(l, !0)) : Ye(l) || Pe(l) ? Pe(s) ? u = je(s) : !Ve(s) || o && Ce(s) ? (d = !1, u = Z(l, !0)) : u = s : d = !1), d && (a.set(l, u), r(u, l, o, i, a), a.delete(l)), N(e, n, u)
        }
        function te(e, t) {
            return t = Kt(void 0 === t ? e.length - 1 : t, 0), function() {
                for (var n = arguments, o = -1, r = Kt(n.length - t, 0), a = Array(r); ++o < r;)
                    a[o] = n[t + o];
                o = -1;
                for (var s = Array(t + 1); ++o < t;)
                    s[o] = n[o];
                return s[t] = a, i(e, this, s)
            }
        }
        function ne(e, t) {
            if (t)
                return e.slice();
            var n = new e.constructor(e.length);
            return e.copy(n), n
        }
        function oe(e) {
            var t = new e.constructor(e.byteLength);
            return new Qt(t).set(new Qt(e)), t
        }
        function re(e, t) {
            var n = t ? oe(e.buffer) : e.buffer;
            return new e.constructor(n, e.byteOffset, e.byteLength)
        }
        function ie(e, t, n) {
            var r = t ? n(f(e), !0) : f(e);
            return l(r, o, new e.constructor)
        }
        function ae(e) {
            var t = new e.constructor(e.source, vt.exec(e));
            return t.lastIndex = e.lastIndex, t
        }
        function se(e, t, n) {
            var o = t ? n(p(e), !0) : p(e);
            return l(o, r, new e.constructor)
        }
        function le(e) {
            return An ? Object(An.call(e)) : {}
        }
        function ce(e, t) {
            var n = t ? oe(e.buffer) : e.buffer;
            return new e.constructor(n, e.byteOffset, e.length)
        }
        function ue(e, t) {
            var n = -1,
                o = e.length;
            for (t || (t = Array(o)); ++n < o;)
                t[n] = e[n];
            return t
        }
        function de(e, t, n, o) {
            n || (n = {});
            for (var r = -1, i = t.length; ++r < i;) {
                var a = t[r],
                    s = o ? o(n[a], e[a], a, n, e) : void 0;
                D(n, a, void 0 === s ? e[a] : s)
            }
            return n
        }
        function Ae(e, t) {
            return de(e, fn(e), t)
        }
        function fe(e) {
            return te(function(t, n) {
                var o = -1,
                    r = n.length,
                    i = r > 1 ? n[r - 1] : void 0,
                    a = r > 2 ? n[2] : void 0;
                for (i = e.length > 3 && "function" == typeof i ? (r--, i) : void 0, a && we(n[0], n[1], a) && (i = r < 3 ? void 0 : i, r = 1), t = Object(t); ++o < r;) {
                    var s = n[o];
                    s && e(t, s, o, i)
                }
                return t
            })
        }
        function he(e) {
            return W(e, Ue, fn)
        }
        function pe(e, t) {
            var n = e.__data__;
            return Ee(t) ? n["string" == typeof t ? "string" : "hash"] : n.map
        }
        function ye(e, t) {
            var n = d(e, t);
            return q(n) ? n : void 0
        }
        function ge(e) {
            var t = e.length,
                n = e.constructor(t);
            return t && "string" == typeof e[0] && Gt.call(e, "index") && (n.index = e.index, n.input = e.input), n
        }
        function ve(e) {
            return "function" != typeof e.constructor || ke(e) ? {} : H(Zt(e))
        }
        function me(e, t, n, o) {
            var r = e.constructor;
            switch (t) {
                case at:
                    return oe(e);
                case He:
                case We:
                    return new r(+e);
                case st:
                    return re(e, o);
                case lt:
                case ct:
                case ut:
                case dt:
                case At:
                case ft:
                case ht:
                case pt:
                case yt:
                    return ce(e, o);
                case Xe:
                    return ie(e, o, n);
                case Ke:
                case ot:
                    return new r(e);
                case tt:
                    return ae(e);
                case nt:
                    return se(e, o, n);
                case rt:
                    return le(e)
            }
        }
        function be(e, t) {
            return t = null == t ? Le : t, !!t && ("number" == typeof e || bt.test(e)) && e > -1 && e % 1 == 0 && e < t
        }
        function we(e, t, n) {
            if (!Ve(n))
                return !1;
            var o = typeof t;
            return !!("number" == o ? Ie(n) && be(t, n.length) : "string" == o && t in n) && xe(n[t], e)
        }
        function Ee(e) {
            var t = typeof e;
            return "string" == t || "number" == t || "symbol" == t || "boolean" == t ? "__proto__" !== e : null === e
        }
        function Te(e) {
            return !!jt && jt in e
        }
        function ke(e) {
            var t = e && e.constructor,
                n = "function" == typeof t && t.prototype || Bt;
            return e === n
        }
        function Se(e) {
            var t = [];
            if (null != e)
                for (var n in Object(e))
                    t.push(n);
            return t
        }
        function _e(e) {
            if (null != e) {
                try {
                    return Ut.call(e)
                } catch (e) {}
                try {
                    return e + ""
                } catch (e) {}
            }
            return ""
        }
        function xe(e, t) {
            return e === t || e !== e && t !== t
        }
        function Pe(e) {
            return Re(e) && Gt.call(e, "callee") && (!Wt.call(e, "callee") || Mt.call(e) == Qe)
        }
        function Ie(e) {
            return null != e && Fe(e.length) && !Ce(e)
        }
        function Re(e) {
            return Be(e) && Ie(e)
        }
        function Ce(e) {
            var t = Ve(e) ? Mt.call(e) : "";
            return t == qe || t == Je
        }
        function Fe(e) {
            return "number" == typeof e && e > -1 && e % 1 == 0 && e <= Le
        }
        function Ve(e) {
            var t = typeof e;
            return !!e && ("object" == t || "function" == t)
        }
        function Be(e) {
            return !!e && "object" == typeof e
        }
        function Ye(e) {
            if (!Be(e) || Mt.call(e) != $e || A(e))
                return !1;
            var t = Zt(e);
            if (null === t)
                return !0;
            var n = Gt.call(t, "constructor") && t.constructor;
            return "function" == typeof n && n instanceof n && Ut.call(n) == Ot
        }
        function je(e) {
            return de(e, Ge(e))
        }
        function Ue(e) {
            return Ie(e) ? M(e) : X(e)
        }
        function Ge(e) {
            return Ie(e) ? M(e, !0) : K(e)
        }
        function Oe() {
            return []
        }
        function Me() {
            return !1
        }
        var Ne = 200,
            De = "__lodash_hash_undefined__",
            Le = 9007199254740991,
            Qe = "[object Arguments]",
            Ze = "[object Array]",
            He = "[object Boolean]",
            We = "[object Date]",
            ze = "[object Error]",
            qe = "[object Function]",
            Je = "[object GeneratorFunction]",
            Xe = "[object Map]",
            Ke = "[object Number]",
            $e = "[object Object]",
            et = "[object Promise]",
            tt = "[object RegExp]",
            nt = "[object Set]",
            ot = "[object String]",
            rt = "[object Symbol]",
            it = "[object WeakMap]",
            at = "[object ArrayBuffer]",
            st = "[object DataView]",
            lt = "[object Float32Array]",
            ct = "[object Float64Array]",
            ut = "[object Int8Array]",
            dt = "[object Int16Array]",
            At = "[object Int32Array]",
            ft = "[object Uint8Array]",
            ht = "[object Uint8ClampedArray]",
            pt = "[object Uint16Array]",
            yt = "[object Uint32Array]",
            gt = /[\\^$.*+?()[\]{}|]/g,
            vt = /\w*$/,
            mt = /^\[object .+?Constructor\]$/,
            bt = /^(?:0|[1-9]\d*)$/,
            wt = {};
        wt[lt] = wt[ct] = wt[ut] = wt[dt] = wt[At] = wt[ft] = wt[ht] = wt[pt] = wt[yt] = !0, wt[Qe] = wt[Ze] = wt[at] = wt[He] = wt[st] = wt[We] = wt[ze] = wt[qe] = wt[Xe] = wt[Ke] = wt[$e] = wt[tt] = wt[nt] = wt[ot] = wt[it] = !1;
        var Et = {};
        Et[Qe] = Et[Ze] = Et[at] = Et[st] = Et[He] = Et[We] = Et[lt] = Et[ct] = Et[ut] = Et[dt] = Et[At] = Et[Xe] = Et[Ke] = Et[$e] = Et[tt] = Et[nt] = Et[ot] = Et[rt] = Et[ft] = Et[ht] = Et[pt] = Et[yt] = !0, Et[ze] = Et[qe] = Et[it] = !1;
        var Tt = "object" == typeof e && e && e.Object === Object && e,
            kt = "object" == typeof self && self && self.Object === Object && self,
            St = Tt || kt || Function("return this")(),
            _t = "object" == typeof t && t && !t.nodeType && t,
            xt = _t && "object" == typeof n && n && !n.nodeType && n,
            Pt = xt && xt.exports === _t,
            It = Pt && Tt.process,
            Rt = function() {
                try {
                    return It && It.binding("util")
                } catch (e) {}
            }(),
            Ct = Rt && Rt.isTypedArray,
            Ft = Array.prototype,
            Vt = Function.prototype,
            Bt = Object.prototype,
            Yt = St["__core-js_shared__"],
            jt = function() {
                var e = /[^.]+$/.exec(Yt && Yt.keys && Yt.keys.IE_PROTO || "");
                return e ? "Symbol(src)_1." + e : ""
            }(),
            Ut = Vt.toString,
            Gt = Bt.hasOwnProperty,
            Ot = Ut.call(Object),
            Mt = Bt.toString,
            Nt = RegExp("^" + Ut.call(Gt).replace(gt, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$"),
            Dt = Pt ? St.Buffer : void 0,
            Lt = St.Symbol,
            Qt = St.Uint8Array,
            Zt = h(Object.getPrototypeOf, Object),
            Ht = Object.create,
            Wt = Bt.propertyIsEnumerable,
            zt = Ft.splice,
            qt = Object.getOwnPropertySymbols,
            Jt = Dt ? Dt.isBuffer : void 0,
            Xt = h(Object.keys, Object),
            Kt = Math.max,
            $t = ye(St, "DataView"),
            en = ye(St, "Map"),
            tn = ye(St, "Promise"),
            nn = ye(St, "Set"),
            on = ye(St, "WeakMap"),
            rn = ye(Object, "create"),
            an = _e($t),
            sn = _e(en),
            ln = _e(tn),
            cn = _e(nn),
            un = _e(on),
            dn = Lt ? Lt.prototype : void 0,
            An = dn ? dn.valueOf : void 0;
        y.prototype.clear = g, y.prototype.delete = v, y.prototype.get = m, y.prototype.has = b, y.prototype.set = w, E.prototype.clear = T, E.prototype.delete = k, E.prototype.get = S, E.prototype.has = _, E.prototype.set = x, P.prototype.clear = I, P.prototype.delete = R, P.prototype.get = C, P.prototype.has = F, P.prototype.set = V, B.prototype.clear = Y, B.prototype.delete = j, B.prototype.get = U, B.prototype.has = G, B.prototype.set = O;
        var fn = qt ? h(qt, Object) : Oe,
            hn = z;
        ($t && hn(new $t(new ArrayBuffer(1))) != st || en && hn(new en) != Xe || tn && hn(tn.resolve()) != et || nn && hn(new nn) != nt || on && hn(new on) != it) && (hn = function(e) {
            var t = Mt.call(e),
                n = t == $e ? e.constructor : void 0,
                o = n ? _e(n) : void 0;
            if (o)
                switch (o) {
                    case an:
                        return st;
                    case sn:
                        return Xe;
                    case ln:
                        return et;
                    case cn:
                        return nt;
                    case un:
                        return it
                }
            return t
        });
        var pn = Array.isArray,
            yn = Jt || Me,
            gn = Ct ? u(Ct) : J,
            vn = fe(function(e, t, n) {
                $(e, t, n)
            });
        n.exports = vn
    }).call(t, function() {
        return this
    }(), n(11)(e))
}, function(e, t) {
    e.exports = function(e) {
        return e.webpackPolyfill || (e.deprecate = function() {}, e.paths = [], e.children = [], e.webpackPolyfill = 1), e
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    var o = !1,
        r = n(13),
        i = r.OggVideo,
        a = r.Mp4Video,
        s = function() {
            return new Promise(function(e, t) {
                if ("resolve" === o)
                    return void e("resolved for debugging");
                if ("reject" === o)
                    return void t("rejected for debugging");
                var n = document.createElement("video");
                n.autoplay = !0, n.setAttribute("autoplay", !0), n.muted = !0, n.setAttribute("muted", !0), n.playsinline = !0, n.setAttribute("playsinline", !0), n.volume = 0, n.setAttribute("data-is-playing", "false"), n.setAttribute("style", "width: 1px; height: 1px; position: fixed; top: 0; left: 0; z-index: 100;"), document.body.appendChild(n);
                var r = null,
                    s = function() {
                        r && (clearTimeout(r), r = null);
                        try {
                            document.body.removeChild(n)
                        } catch (e) {
                            return
                        }
                    };
                try {
                    if (n.canPlayType('video/ogg; codecs="theora"').match(/^(probably)|(maybe)/))
                        n.src = i;
                    else {
                        if (!n.canPlayType('video/mp4; codecs="avc1.42E01E"').match(/^(probably)|(maybe)/))
                            return s(), void t("no autoplay: element does not support mp4 or ogg format");
                        n.src = a
                    }
                } catch (e) {
                    return s(), void t("no autoplay: " + e)
                }
                n.addEventListener("play", function() {
                    n.setAttribute("data-is-playing", "true"), r = setTimeout(function() {
                        s(), t("no autoplay: unsure")
                    }, 3e3)
                }), n.addEventListener("canplay", function() {
                    return "true" === n.getAttribute("data-is-playing") ? (s(), e("autoplay supported"), !0) : (s(), t("no autoplay: browser does not support autoplay"), !1)
                }), n.load(), n.play()
            })
        };
    t.default = s
}, function(e, t) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    var n = "data:video/ogg;base64,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",
        o = "data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAAs1tZGF0AAACrgYF//+q3EXpvebZSLeWLNgg2SPu73gyNjQgLSBjb3JlIDE0OCByMjYwMSBhMGNkN2QzIC0gSC4yNjQvTVBFRy00IEFWQyBjb2RlYyAtIENvcHlsZWZ0IDIwMDMtMjAxNSAtIGh0dHA6Ly93d3cudmlkZW9sYW4ub3JnL3gyNjQuaHRtbCAtIG9wdGlvbnM6IGNhYmFjPTEgcmVmPTMgZGVibG9jaz0xOjA6MCBhbmFseXNlPTB4MzoweDExMyBtZT1oZXggc3VibWU9NyBwc3k9MSBwc3lfcmQ9MS4wMDowLjAwIG1peGVkX3JlZj0xIG1lX3JhbmdlPTE2IGNocm9tYV9tZT0xIHRyZWxsaXM9MSA4eDhkY3Q9MSBjcW09MCBkZWFkem9uZT0yMSwxMSBmYXN0X3Bza2lwPTEgY2hyb21hX3FwX29mZnNldD0tMiB0aHJlYWRzPTEgbG9va2FoZWFkX3RocmVhZHM9MSBzbGljZWRfdGhyZWFkcz0wIG5yPTAgZGVjaW1hdGU9MSBpbnRlcmxhY2VkPTAgYmx1cmF5X2NvbXBhdD0wIGNvbnN0cmFpbmVkX2ludHJhPTAgYmZyYW1lcz0zIGJfcHlyYW1pZD0yIGJfYWRhcHQ9MSBiX2JpYXM9MCBkaXJlY3Q9MSB3ZWlnaHRiPTEgb3Blbl9nb3A9MCB3ZWlnaHRwPTIga2V5aW50PTI1MCBrZXlpbnRfbWluPTEwIHNjZW5lY3V0PTQwIGludHJhX3JlZnJlc2g9MCByY19sb29rYWhlYWQ9NDAgcmM9Y3JmIG1idHJlZT0xIGNyZj0yMy4wIHFjb21wPTAuNjAgcXBtaW49MCBxcG1heD02OSBxcHN0ZXA9NCBpcF9yYXRpbz0xLjQwIGFxPTE6MS4wMACAAAAAD2WIhAA3//728P4FNjuZQQAAAu5tb292AAAAbG12aGQAAAAAAAAAAAAAAAAAAAPoAAAAZAABAAABAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAACGHRyYWsAAABcdGtoZAAAAAMAAAAAAAAAAAAAAAEAAAAAAAAAZAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAAgAAAAIAAAAAACRlZHRzAAAAHGVsc3QAAAAAAAAAAQAAAGQAAAAAAAEAAAAAAZBtZGlhAAAAIG1kaGQAAAAAAAAAAAAAAAAAACgAAAAEAFXEAAAAAAAtaGRscgAAAAAAAAAAdmlkZQAAAAAAAAAAAAAAAFZpZGVvSGFuZGxlcgAAAAE7bWluZgAAABR2bWhkAAAAAQAAAAAAAAAAAAAAJGRpbmYAAAAcZHJlZgAAAAAAAAABAAAADHVybCAAAAABAAAA+3N0YmwAAACXc3RzZAAAAAAAAAABAAAAh2F2YzEAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAgACAEgAAABIAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAY//8AAAAxYXZjQwFkAAr/4QAYZ2QACqzZX4iIhAAAAwAEAAADAFA8SJZYAQAGaOvjyyLAAAAAGHN0dHMAAAAAAAAAAQAAAAEAAAQAAAAAHHN0c2MAAAAAAAAAAQAAAAEAAAABAAAAAQAAABRzdHN6AAAAAAAAAsUAAAABAAAAFHN0Y28AAAAAAAAAAQAAADAAAABidWR0YQAAAFptZXRhAAAAAAAAACFoZGxyAAAAAAAAAABtZGlyYXBwbAAAAAAAAAAAAAAAAC1pbHN0AAAAJal0b28AAAAdZGF0YQAAAAEAAAAATGF2ZjU2LjQwLjEwMQ==";
    t.OggVideo = n, t.Mp4Video = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.initializeVimeoPlayer = t.initializeVimeoAPI = void 0;
    var o = n(15),
        r = n(16),
        i = void 0,
        a = "*",
        s = null,
        l = function() {
            return new Promise(function(e, t) {
                e("no api needed")
            })
        },
        c = function(e, t) {
            var n = {
                method: e
            };
            t && (n.value = t);
            var o = JSON.stringify(n);
            i.ownerDocument.defaultView.eval("(function(playerIframe){ playerIframe.contentWindow.postMessage(" + o + ", " + JSON.stringify(a) + ") })")(i)
        },
        u = function(e) {
            var t = e.win,
                n = e.instance,
                l = e.container,
                u = e.videoId,
                d = e.startTime,
                A = e.readyCallback,
                f = e.stateChangeCallback;
            return new Promise(function(e, h) {
                var p = n.logger || function() {};
                i = t.document.createElement("iframe"), i.id = "vimeoplayer";
                var y = "&background=1";
                i.src = "//player.vimeo.com/video/" + u + "?api=1" + y;
                var g = (0, o.getPlayerElement)(l);
                g.appendChild(i);
                var v = {
                    iframe: i,
                    setPlaybackRate: function() {}
                };
                e(v);
                var m = function() {
                        c("getDuration"), c("getVideoHeight"), c("getVideoWidth")
                    },
                    b = null,
                    w = function() {
                        var e = arguments.length > 0 && void 0 !== arguments[0] && arguments[0];
                        (e || v.dimensions.width && v.dimensions.height && v.duration) && (e && m(), v.dimensions.width = v.dimensions.width || v.iframe.parentNode.offsetWidth, v.dimensions.height = v.dimensions.height || v.iframe.parentNode.offsetHeight, v.duration = v.duration || 10, c("setVolume", "0"), c("setLoop", "true"), c("seekTo", d), c("addEventListener", "playProgress"), A(v))
                    },
                    E = function() {
                        s && (clearTimeout(s), s = null), v.dimensions || (v.dimensions = {}, m(), f("buffering"), b = setTimeout(function() {
                            p.call(n, "retrying"), w(!0)
                        }, .75 * r.TIMEOUT))
                    },
                    T = function(e) {
                        if (!/^https?:\/\/player.vimeo.com/.test(e.origin))
                            return !1;
                        a = e.origin;
                        var t = e.data;
                        switch ("string" == typeof t && (t = JSON.parse(t)), t.event) {
                            case "ready":
                                E(a);
                                break;
                            case "playProgress":
                            case "timeupdate":
                                b && (clearTimeout(b), b = null), f("playing", t), c("setVolume", "0"), t.data.percent >= .98 && d > 0 && c("seekTo", d)
                        }
                        switch (t.method) {
                            case "getVideoHeight":
                                p.call(n, t.method), v.dimensions.height = t.value, w();
                                break;
                            case "getVideoWidth":
                                p.call(n, t.method), v.dimensions.width = t.value, w();
                                break;
                            case "getDuration":
                                p.call(n, t.method), v.duration = t.value, d >= v.duration && (d = 0), w()
                        }
                    },
                    k = function(e) {
                        T(e)
                    };
                t.addEventListener("message", k, !1), v.destroy = function() {
                    t.removeEventListener("message", k), v.iframe.parentElement && v.iframe.parentElement.removeChild(v.iframe)
                }, s = setTimeout(function() {
                    h("Ran out of time")
                }, r.TIMEOUT)
            })
        };
    t.initializeVimeoAPI = l, t.initializeVimeoPlayer = u
}, function(e, t, n) {
    "use strict";
    function o(e) {
        return e && e.__esModule ? e : {
            default: e
        }
    }
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.validatedImage = t.getVideoSource = t.getVideoID = t.getStartTime = t.getPlayerElement = t.findPlayerAspectRatio = void 0;
    var r = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(e) {
            return typeof e
        } : function(e) {
            return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
        },
        i = n(16),
        a = n(17),
        s = o(a),
        l = n(21),
        c = o(l),
        u = function(e) {
            var t = void 0,
                n = void 0;
            for (var o in e) {
                var i = e[o];
                if ("object" === ("undefined" == typeof i ? "undefined" : r(i)) && i.width && i.height) {
                    t = i.width, n = i.height;
                    break
                }
            }
            return {
                w: t,
                h: n
            }
        },
        d = function(e) {
            var t = void 0,
                n = void 0;
            return e.dimensions ? (t = e.dimensions.width, n = e.dimensions.height) : e.iframe && (t = e.iframe.clientWidth, n = e.iframe.clientHeight), {
                w: t,
                h: n
            }
        },
        A = {
            youtube: {
                parsePath: "query.t",
                timeRegex: /[hms]/,
                idRegex: i.YOUTUBE_REGEX,
                getDimensions: u
            },
            vimeo: {
                parsePath: null,
                timeRegex: /[#t=s]/,
                idRegex: i.VIMEO_REGEX,
                getDimensions: d
            }
        },
        f = function(e, t) {
            return A[t].parsePath ? (0, c.default)(e, A[t].parsePath) : null
        },
        h = function(e, t) {
            var n = new s.default(e, !0),
                o = f(n, t);
            if (o) {
                var r = o.split(A[t].timeRegex).filter(Boolean),
                    i = parseInt(r.pop(), 10) || 0,
                    a = 60 * parseInt(r.pop(), 10) || 0,
                    l = 3600 * parseInt(r.pop(), 10) || 0;
                return l + a + i
            }
        },
        p = function() {
            var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : i.DEFAULT_PROPERTY_VALUES.url,
                t = e.match(i.YOUTUBE_REGEX);
            return t && t[2].length ? "youtube" : (t = e.match(i.VIMEO_REGEX), t && t[2].length ? "vimeo" : void console.error("Video source " + e + " does not match supported types"))
        },
        y = function() {
            var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : i.DEFAULT_PROPERTY_VALUES.url,
                t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : null,
                n = e.match(A[t].idRegex);
            return n && n[2].length ? n[2] : void console.error("Video id at " + e + " is not valid")
        },
        g = function(e) {
            if (!e)
                return !1;
            var t = "IMG" === e.nodeName && e;
            return t || console.warn("Element is not a valid image element."), t
        },
        v = function(e, t, n) {
            var o = void 0,
                r = void 0;
            if (t) {
                var i = A[n].getDimensions(t);
                o = i.w, r = i.h
            }
            return o && r || (o = e.clientWidth, r = e.clientHeight, console.warn("No width and height found in " + n + " player " + t + ". Using container dimensions.")), parseInt(o, 10) / parseInt(r, 10)
        },
        m = function(e) {
            var t = e.querySelector("#player");
            return t || (t = e.ownerDocument.createElement("div"), t.id = "player", e.appendChild(t)), t.setAttribute("style", "position: absolute; top: 0; bottom: 0; left: 0; right: 0;"), t
        };
    t.findPlayerAspectRatio = v, t.getPlayerElement = m, t.getStartTime = h, t.getVideoID = y, t.getVideoSource = p, t.validatedImage = g
}, function(e, t) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    var n = {
            enabled: !0,
            verbose: !1
        },
        o = {
            container: "body",
            url: "https://youtu.be/xkEmYQvJ_68",
            source: "youtube",
            fitMode: "fill",
            scaleFactor: 1,
            playbackSpeed: 1,
            filter: 1,
            filterStrength: 50,
            timeCode: {
                start: 0,
                end: null
            },
            DEBUG: n
        },
        r = 2500,
        i = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]{11}).*/,
        a = /^.*(vimeo\.com\/)([0-9]{7,}(#t\=.*s)?)/;
    t.DEBUG = n, t.DEFAULT_PROPERTY_VALUES = o, t.TIMEOUT = r, t.YOUTUBE_REGEX = i, t.VIMEO_REGEX = a
}, function(e, t, n) {
    "use strict";
    function o(e) {
        var t = c.exec(e);
        return {
            protocol: t[1] ? t[1].toLowerCase() : "",
            slashes: !!t[2],
            rest: t[3] ? t[3] : ""
        }
    }
    function r(e, t, n) {
        if (!(this instanceof r))
            return new r(e, t, n);
        var c,
            d,
            A,
            f,
            h = l.test(e),
            p = typeof t,
            y = this,
            g = 0;
        "object" !== p && "string" !== p && (n = t, t = null), n && "function" != typeof n && (n = s.parse), t = a(t);
        var v = o(e);
        for (y.protocol = v.protocol || t.protocol || "", y.slashes = v.slashes || t.slashes, e = v.rest; g < u.length; g++)
            d = u[g], c = d[0], f = d[1], c !== c ? y[f] = e : "string" == typeof c ? ~(A = e.indexOf(c)) && ("number" == typeof d[2] ? (y[f] = e.slice(0, A), e = e.slice(A + d[2])) : (y[f] = e.slice(A), e = e.slice(0, A))) : (A = c.exec(e)) && (y[f] = A[1], e = e.slice(0, e.length - A[0].length)), y[f] = y[f] || (d[3] || "port" === f && h ? t[f] || "" : ""), d[4] && (y[f] = y[f].toLowerCase());
        n && (y.query = n(y.query)), i(y.port, y.protocol) || (y.host = y.hostname, y.port = ""), y.username = y.password = "", y.auth && (d = y.auth.split(":"), y.username = d[0] || "", y.password = d[1] || ""), y.href = y.toString()
    }
    var i = n(18),
        a = n(19),
        s = n(20),
        l = /^\/(?!\/)/,
        c = /^([a-z0-9.+-]+:)?(\/\/)?(.*)$/i,
        u = [["#", "hash"], ["?", "query"], ["/", "pathname"], ["@", "auth", 1], [NaN, "host", void 0, 1, 1], [/\:(\d+)$/, "port"], [NaN, "hostname", void 0, 1, 1]];
    r.prototype.set = function(e, t, n) {
        var o = this;
        return "query" === e ? ("string" == typeof t && t.length && (t = (n || s.parse)(t)), o[e] = t) : "port" === e ? (o[e] = t, i(t, o.protocol) ? t && (o.host = o.hostname + ":" + t) : (o.host = o.hostname, o[e] = "")) : "hostname" === e ? (o[e] = t, o.port && (t += ":" + o.port), o.host = t) : "host" === e ? (o[e] = t, /\:\d+/.test(t) && (t = t.split(":"), o.hostname = t[0], o.port = t[1])) : "protocol" === e ? (o.protocol = t, o.slashes = !n) : o[e] = t, o.href = o.toString(), o
    }, r.prototype.toString = function(e) {
        e && "function" == typeof e || (e = s.stringify);
        var t,
            n = this,
            o = n.protocol;
        o && ":" !== o.charAt(o.length - 1) && (o += ":");
        var r = o + (n.slashes ? "//" : "");
        return n.username && (r += n.username, n.password && (r += ":" + n.password), r += "@"), r += n.hostname, n.port && (r += ":" + n.port), r += n.pathname, t = "object" == typeof n.query ? e(n.query) : n.query, t && (r += "?" !== t.charAt(0) ? "?" + t : t), n.hash && (r += n.hash), r
    }, r.qs = s, r.location = a, e.exports = r
}, function(e, t) {
    "use strict";
    e.exports = function(e, t) {
        if (t = t.split(":")[0], e = +e, !e)
            return !1;
        switch (t) {
            case "http":
            case "ws":
                return 80 !== e;
            case "https":
            case "wss":
                return 443 !== e;
            case "ftp":
                return 21 !== e;
            case "gopher":
                return 70 !== e;
            case "file":
                return !1
        }
        return 0 !== e
    }
}, function(e, t, n) {
    (function(t) {
        "use strict";
        var o,
            r = /^[A-Za-z][A-Za-z0-9+-.]*:\/\//,
            i = {
                hash: 1,
                query: 1
            };
        e.exports = function(e) {
            e = e || t.location || {}, o = o || n(17);
            var a,
                s = {},
                l = typeof e;
            if ("blob:" === e.protocol)
                s = new o(unescape(e.pathname), {});
            else if ("string" === l) {
                s = new o(e, {});
                for (a in i)
                    delete s[a]
            } else if ("object" === l) {
                for (a in e)
                    a in i || (s[a] = e[a]);
                void 0 === s.slashes && (s.slashes = r.test(e.href))
            }
            return s
        }
    }).call(t, function() {
        return this
    }())
}, function(e, t) {
    "use strict";
    function n(e) {
        for (var t, n = /([^=?&]+)=?([^&]*)/g, o = {}; t = n.exec(e); o[decodeURIComponent(t[1])] = decodeURIComponent(t[2]))
            ;
        return o
    }
    function o(e, t) {
        t = t || "";
        var n = [];
        "string" != typeof t && (t = "?");
        for (var o in e)
            r.call(e, o) && n.push(encodeURIComponent(o) + "=" + encodeURIComponent(e[o]));
        return n.length ? t + n.join("&") : ""
    }
    var r = Object.prototype.hasOwnProperty;
    t.stringify = o, t.parse = n
}, function(e, t) {
    (function(t) {
        function n(e, t) {
            return null == e ? void 0 : e[t]
        }
        function o(e) {
            var t = !1;
            if (null != e && "function" != typeof e.toString)
                try {
                    t = !!(e + "")
                } catch (e) {}
            return t
        }
        function r(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function i() {
            this.__data__ = ye ? ye(null) : {}
        }
        function a(e) {
            return this.has(e) && delete this.__data__[e]
        }
        function s(e) {
            var t = this.__data__;
            if (ye) {
                var n = t[e];
                return n === L ? void 0 : n
            }
            return ue.call(t, e) ? t[e] : void 0
        }
        function l(e) {
            var t = this.__data__;
            return ye ? void 0 !== t[e] : ue.call(t, e)
        }
        function c(e, t) {
            var n = this.__data__;
            return n[e] = ye && void 0 === t ? L : t, this
        }
        function u(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function d() {
            this.__data__ = []
        }
        function A(e) {
            var t = this.__data__,
                n = E(t, e);
            if (n < 0)
                return !1;
            var o = t.length - 1;
            return n == o ? t.pop() : he.call(t, n, 1), !0
        }
        function f(e) {
            var t = this.__data__,
                n = E(t, e);
            return n < 0 ? void 0 : t[n][1]
        }
        function h(e) {
            return E(this.__data__, e) > -1
        }
        function p(e, t) {
            var n = this.__data__,
                o = E(n, e);
            return o < 0 ? n.push([e, t]) : n[o][1] = t, this
        }
        function y(e) {
            var t = -1,
                n = e ? e.length : 0;
            for (this.clear(); ++t < n;) {
                var o = e[t];
                this.set(o[0], o[1])
            }
        }
        function g() {
            this.__data__ = {
                hash: new r,
                map: new (pe || u),
                string: new r
            }
        }
        function v(e) {
            return x(this, e).delete(e)
        }
        function m(e) {
            return x(this, e).get(e)
        }
        function b(e) {
            return x(this, e).has(e)
        }
        function w(e, t) {
            return x(this, e).set(e, t), this
        }
        function E(e, t) {
            for (var n = e.length; n--;)
                if (Y(e[n][0], t))
                    return n;
            return -1
        }
        function T(e, t) {
            t = I(t, e) ? [t] : _(t);
            for (var n = 0, o = t.length; null != e && n < o;)
                e = e[F(t[n++])];
            return n && n == o ? e : void 0
        }
        function k(e) {
            if (!U(e) || C(e))
                return !1;
            var t = j(e) || o(e) ? Ae : ee;
            return t.test(V(e))
        }
        function S(e) {
            if ("string" == typeof e)
                return e;
            if (O(e))
                return ve ? ve.call(e) : "";
            var t = e + "";
            return "0" == t && 1 / e == -Q ? "-0" : t
        }
        function _(e) {
            return be(e) ? e : me(e)
        }
        function x(e, t) {
            var n = e.__data__;
            return R(t) ? n["string" == typeof t ? "string" : "hash"] : n.map
        }
        function P(e, t) {
            var o = n(e, t);
            return k(o) ? o : void 0
        }
        function I(e, t) {
            if (be(e))
                return !1;
            var n = typeof e;
            return !("number" != n && "symbol" != n && "boolean" != n && null != e && !O(e)) || (q.test(e) || !z.test(e) || null != t && e in Object(t))
        }
        function R(e) {
            var t = typeof e;
            return "string" == t || "number" == t || "symbol" == t || "boolean" == t ? "__proto__" !== e : null === e
        }
        function C(e) {
            return !!le && le in e
        }
        function F(e) {
            if ("string" == typeof e || O(e))
                return e;
            var t = e + "";
            return "0" == t && 1 / e == -Q ? "-0" : t
        }
        function V(e) {
            if (null != e) {
                try {
                    return ce.call(e)
                } catch (e) {}
                try {
                    return e + ""
                } catch (e) {}
            }
            return ""
        }
        function B(e, t) {
            if ("function" != typeof e || t && "function" != typeof t)
                throw new TypeError(D);
            var n = function() {
                var o = arguments,
                    r = t ? t.apply(this, o) : o[0],
                    i = n.cache;
                if (i.has(r))
                    return i.get(r);
                var a = e.apply(this, o);
                return n.cache = i.set(r, a), a
            };
            return n.cache = new (B.Cache || y), n
        }
        function Y(e, t) {
            return e === t || e !== e && t !== t
        }
        function j(e) {
            var t = U(e) ? de.call(e) : "";
            return t == Z || t == H
        }
        function U(e) {
            var t = typeof e;
            return !!e && ("object" == t || "function" == t)
        }
        function G(e) {
            return !!e && "object" == typeof e
        }
        function O(e) {
            return "symbol" == typeof e || G(e) && de.call(e) == W
        }
        function M(e) {
            return null == e ? "" : S(e)
        }
        function N(e, t, n) {
            var o = null == e ? void 0 : T(e, t);
            return void 0 === o ? n : o
        }
        var D = "Expected a function",
            L = "__lodash_hash_undefined__",
            Q = 1 / 0,
            Z = "[object Function]",
            H = "[object GeneratorFunction]",
            W = "[object Symbol]",
            z = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/,
            q = /^\w*$/,
            J = /^\./,
            X = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g,
            K = /[\\^$.*+?()[\]{}|]/g,
            $ = /\\(\\)?/g,
            ee = /^\[object .+?Constructor\]$/,
            te = "object" == typeof t && t && t.Object === Object && t,
            ne = "object" == typeof self && self && self.Object === Object && self,
            oe = te || ne || Function("return this")(),
            re = Array.prototype,
            ie = Function.prototype,
            ae = Object.prototype,
            se = oe["__core-js_shared__"],
            le = function() {
                var e = /[^.]+$/.exec(se && se.keys && se.keys.IE_PROTO || "");
                return e ? "Symbol(src)_1." + e : ""
            }(),
            ce = ie.toString,
            ue = ae.hasOwnProperty,
            de = ae.toString,
            Ae = RegExp("^" + ce.call(ue).replace(K, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$"),
            fe = oe.Symbol,
            he = re.splice,
            pe = P(oe, "Map"),
            ye = P(Object, "create"),
            ge = fe ? fe.prototype : void 0,
            ve = ge ? ge.toString : void 0;
        r.prototype.clear = i, r.prototype.delete = a, r.prototype.get = s, r.prototype.has = l, r.prototype.set = c, u.prototype.clear = d, u.prototype.delete = A, u.prototype.get = f, u.prototype.has = h, u.prototype.set = p, y.prototype.clear = g, y.prototype.delete = v, y.prototype.get = m, y.prototype.has = b, y.prototype.set = w;
        var me = B(function(e) {
            e = M(e);
            var t = [];
            return J.test(e) && t.push(""), e.replace(X, function(e, n, o, r) {
                t.push(o ? r.replace($, "$1") : n || e)
            }), t
        });
        B.Cache = y;
        var be = Array.isArray;
        e.exports = N
    }).call(t, function() {
        return this
    }())
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.initializeYouTubePlayer = t.initializeYouTubeAPI = void 0;
    var o = n(15),
        r = function(e) {
            return new Promise(function(t, n) {
                if (e.document.documentElement.querySelector('script[src*="www.youtube.com/iframe_api"].loaded'))
                    return void t("already loaded");
                var o = e.document.createElement("script");
                o.src = "https://www.youtube.com/iframe_api";
                var r = e.document.getElementsByTagName("script")[0];
                r.parentNode.insertBefore(o, r), o.addEventListener("load", function(e) {
                    e.currentTarget.classList.add("loaded"), t("api script tag created and loaded")
                }, !0), o.addEventListener("error", function(e) {
                    n("Failed to load YouTube script: ", e)
                })
            })
        },
        i = function(e, t) {
            var n = e.target;
            n.iframe = n.getIframe(), n.mute(), n.ready = !0, n.seekTo(t < n.getDuration() ? t : 0), n.playVideo()
        },
        a = function(e, t, n) {
            var o = arguments.length > 3 && void 0 !== arguments[3] ? arguments[3] : 1,
                r = e.target,
                i = (r.getDuration() - t) / o,
                a = function e() {
                    r.getCurrentTime() + .1 >= r.getDuration() && (r.pauseVideo(), r.seekTo(t), r.playVideo()), requestAnimationFrame(e)
                };
            return e.data === n.YT.PlayerState.BUFFERING && 1 !== r.getVideoLoadedFraction() && (0 === r.getCurrentTime() || r.getCurrentTime() > i - -.1) ? "buffering" : e.data === n.YT.PlayerState.PLAYING ? (requestAnimationFrame(a), "playing") : void (e.data === n.YT.PlayerState.ENDED && r.playVideo())
        },
        s = function(e) {
            var t = e.container,
                n = e.win,
                r = e.videoId,
                s = e.startTime,
                l = e.speed,
                c = e.readyCallback,
                u = e.stateChangeCallback,
                d = (0, o.getPlayerElement)(t),
                A = function() {
                    return new n.YT.Player(d, {
                        videoId: r,
                        playerVars: {
                            autohide: 1,
                            autoplay: 0,
                            controls: 0,
                            enablejsapi: 1,
                            iv_load_policy: 3,
                            loop: 0,
                            modestbranding: 1,
                            playsinline: 1,
                            rel: 0,
                            showinfo: 0,
                            wmode: "opaque"
                        },
                        events: {
                            onReady: function(e) {
                                i(e, s), c(e.target)
                            },
                            onStateChange: function(e) {
                                var t = a(e, s, n, l);
                                u(t, t)
                            }
                        }
                    })
                };
            return new Promise(function(e, t) {
                var o = function t() {
                    1 === n.YT.loaded ? e(A()) : setTimeout(t, 100)
                };
                o()
            })
        };
    t.initializeYouTubeAPI = r, t.initializeYouTubePlayer = s
}, function(e, t) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    var n = ["none", "blur", "brightness", "contrast", "invert", "opacity", "saturate", "sepia", "drop-shadow", "grayscale", "hue-rotate"],
        o = {
            blur: {
                modifier: function(e) {
                    return .3 * e
                },
                unit: "px"
            },
            brightness: {
                modifier: function(e) {
                    return .009 * e + .1
                },
                unit: ""
            },
            contrast: {
                modifier: function(e) {
                    return .4 * e + 80
                },
                unit: "%"
            },
            grayscale: {
                modifier: function(e) {
                    return e
                },
                unit: "%"
            },
            "hue-rotate": {
                modifier: function(e) {
                    return 3.6 * e
                },
                unit: "deg"
            },
            invert: {
                modifier: function(e) {
                    return 1
                },
                unit: ""
            },
            opacity: {
                modifier: function(e) {
                    return e
                },
                unit: "%"
            },
            saturate: {
                modifier: function(e) {
                    return 2 * e
                },
                unit: "%"
            },
            sepia: {
                modifier: function(e) {
                    return e
                },
                unit: "%"
            }
        };
    t.filterOptions = n, t.filterProperties = o
}, function(e, t) {
    var n = function(e) {
        var t = {
            container: e
        };
        return e.getAttribute("data-config-url") && (t.url = e.getAttribute("data-config-url")), e.getAttribute("data-config-playback-speed") && (t.playbackSpeed = e.getAttribute("data-config-playback-speed")), e.getAttribute("data-config-filter") && (t.filter = e.getAttribute("data-config-filter")), e.getAttribute("data-config-filter-strength") && (t.filterStrength = e.getAttribute("data-config-filter-strength")), t
    };
    e.exports = n
}, function(e, t, n) {
    var o = n(2),
        r = n(26),
        i = n(17);
    Y.use("node", function() {
        window.Singleton.create({
            ready: function() {
                this._touch = Y.one(".touch-styles"), this.bindUI(), this._touch || (this.folderRedirect(".folder-toggle", "#headerNav"), this.folderRedirect(".folder-toggle", "#footer")), this.folderActive(".folder-toggle", "#mobileNavigation"), this.folderActive(".folder-toggle", "#headerNav"), this.folderActive(".folder-toggle", "#footer"), this.folderNavExpand(".folder-nav-toggle", "#folderNav"), this.folderNavExpand(".category-nav-toggle", "#categoryNav")
            },
            bindUI: function() {
                this.dataToggleBody(), this.dataToggleEl(), this.dataLightbox(), this.scrollAnchors(), Y.one(window).on("resize", this.syncUI, this)
            },
            syncUI: function() {
                o(function() {
                    r()
                }, 100, this)
            },
            folderNavExpand: function(e, t) {
                var n = Y.one(t);
                n && n.one(e).on("click", function() {
                    n.toggleClass("expanded")
                })
            },
            folderActive: function(e, t) {
                e = e || ".folder-toggle", t = t || "body";
                var n = Y.all(t);
                n.size() > 0 && n.each(function(t) {
                    t.delegate("click", function(e) {
                        e.preventDefault(), e.currentTarget.toggleClass("active"), e.currentTarget.ancestor(".folder").siblings(".folder").each(function(e) {
                            e.one(".folder-toggle").removeClass("active")
                        })
                    }, e)
                })
            },
            folderRedirect: function(e, t) {
                e = e || ".folder-toggle", t = t || "body";
                var n = Y.all(t);
                n.size() > 0 && n.each(function(t) {
                    t.delegate("click", function(e) {
                        e.preventDefault();
                        var t = e.currentTarget.getData("href");
                        t ? window.location = t : console.warn("folderRedirect: You must add a data-href attribute to the label.")
                    }, e)
                })
            },
            dataLightbox: function() {
                var e = {};
                Y.all("[data-lightbox]").each(function(t) {
                    var n = t.getAttribute("data-lightbox");
                    e[n] = e[n] || [], e[n].push({
                        content: t,
                        meta: t.getAttribute("alt")
                    }), t.on("click", function(o) {
                        o.halt(), new Y.Squarespace.Lightbox2({
                            set: e[n],
                            currentSetIndex: Y.all("[data-lightbox]").indexOf(t),
                            controls: {
                                previous: !0,
                                next: !0
                            }
                        }).render()
                    })
                })
            },
            dataToggleBody: function() {
                Y.one("body").delegate("click", function(e) {
                    Y.one("body").toggleClass(e.currentTarget.getData("toggle-body"))
                }, "[data-toggle-body]")
            },
            dataToggleEl: function() {
                Y.one("body").delegate("click", function(e) {
                    var t = e.currentTarget;
                    t.toggleClass(t.getData("toggle"))
                }, "[data-toggle]")
            },
            scrollAnchors: function() {
                if (!history.pushState)
                    return !1;
                var e = 'a[href*="#"]';
                Y.one("body").delegate("click", function(e) {
                    var t = e.currentTarget.get("href"),
                        n = this._getSamePageHash(t);
                    n && Y.one(n) && (e.halt(), Y.one("#mobileNavToggle") && Y.one("#mobileNavToggle").set("checked", !1).simulate("change"), this.smoothScrollTo(Y.one(n).getY()), history.pushState({}, n, n))
                }, e, this)
            },
            _getSamePageHash: function(e) {
                var e = new i(e),
                    t = new i(window.location.href);
                return e.host !== t.host || e.pathname !== t.pathname || "" === e.hash ? null : e.hash
            },
            smoothScrollTo: function(e) {
                if (!Y.Lang.isNumber(e))
                    try {
                        e = parseInt(e)
                    } catch (e) {
                        return console.warn("helpers.js: scrollTo was passed an invalid argument."), !1
                    }
                var t = Y.UA.gecko || Y.UA.ie || navigator.userAgent.match(/Trident.*rv.11\./) ? "html" : "body",
                    n = new Y.Anim({
                        node: Y.one(document.scrollingElement || t),
                        to: {
                            scrollTop: e
                        },
                        duration: .4,
                        easing: "easeOut"
                    });
                n.run(), n.on("end", function() {
                    n.destroy()
                })
            }
        })
    })
}, function(e, t) {
    function n(e) {
        e = e || "img[data-src]", Y.one(e) && Y.all(e).each(function(e) {
            ImageLoader.load(e)
        })
    }
    e.exports = n
}, function(e, t, n) {
    var o = n(2),
        r = n(26),
        i = null !== document.documentElement.getAttribute("data-authenticated-account");
    i && Y.use("node", function(e) {
        window.Singleton.create({
            ready: function() {
                this.bindUI()
            },
            bindUI: function() {
                e.Global.on("tweak:change", function(t) {
                    var n = e.one("#mobileNavToggle");
                    if (n) {
                        var i = t.config && "Site Navigation" === t.config.category;
                        n.set("checked", i)
                    }
                    "transparent-header" === t.getName() && o(function() {
                        r()
                    }, 100, this)
                });
                var t = e.one("body.transparent-header");
                t && (t = t.getDOMNode(), ["sqs-stacked-items-dom-deleted", "sqs-stacked-items-dom-reorder"].forEach(function(n) {
                    e.config.win.addEventListener(n, function(e) {
                        var n = t.querySelector("#content > div");
                        n.querySelector(".banner-thumbnail-wrapper") || n.querySelector(".promoted-gallery-wrapper").children.length ? t.classList.add("has-banner-image") : t.classList.remove("has-banner-image")
                    }.bind(this))
                }.bind(this)))
            }
        })
    })
}]);