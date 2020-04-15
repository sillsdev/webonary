/*
 * Special thanks to Sachin Choolur
 * for original Slider script.
 * https://github.com/sachinchoolur/lightslider
 */

(function ($) {

    'use strict';

    var defaults = {
        item: 1,
        view: 'thumb_view',
        maxWidth: 900,
        maxHeight: 700,
        mode: 'slide',
        speed: 400,
        pauseOnHover: false,
        pause: 2000,
        controls: false,
        fullscreen: false,
        vertical: false,
        sliderHeight: 500,
        vThumbWidth: 100,
        hThumbHeight: 80,
        thumbItem: 10,
        thumbMargin: 5,
        thumbPosition: false,
        thumbControls: false,
        pager: false,
        gallery: false,
        dragdrop: false,
        swipe: false,
        thumbdragdrop: false,
        thumbswipe: false,
        title: false,
        description: false,
        titlesymbollimit: 20,
        descsymbollimit: 30
    };

    $.fn.RSlider = function (options) {
        if (this.length === 0) {
            return this;
        }

        if (this.length > 1) {
            this.each(function () {
                $(this).RSlider(options);
            });
            return this;
        }

        var plugin = {},
            settings = $.extend(true, {}, defaults, options),
            $el = this;

        plugin.$el = this;

        if (settings.mode === 'fade') {
            settings.vertical = false;
        }
        var $children = $el.children(),
            length = 0,
            w = 0,
            on = false,
            elSize = 0,
            $slide = '',
            scene = 0,
            property = (settings.vertical === true) ? 'height' : 'width',
            gutter = (settings.vertical === true) ? 'margin-bottom' : 'margin-right',
            slideValue = 0,
            pagerWidth = 0,
            slideWidth = 0,
            thumbWidth = 0,
            interval = null,
            isTouch = ('ontouchstart' in document.documentElement);

        var refresh = {};

        refresh.calSW = function () {
            if(settings.vertical){
                if(!$slide.hasClass('rwd-fullscreen-on')){
                    elSize = settings.sliderHeight;
                    slideWidth = elSize / settings.item;
                    $('.rwd-SlideOuter.vertical .thumbAction > .thumbPrev').css('top', -settings.sliderHeight + 20 + 'px');
                } else {
                    slideWidth = ($(window).height() - 30) / settings.item;
                    $('.rwd-SlideOuter.vertical .thumbAction > .thumbPrev').css('top', -($(window).height() - 40) + 'px');
                    $(window).resize();
                    setTimeout(function(){
                        $(window).one('resize');
                    }, 0)
                }
            } else {
                slideWidth = elSize / settings.item;
            }
        };
        refresh.calWidth = function (cln) {
            var ln = cln === true ? $slide.find('.rwd-slide').length : $children.length;
            w = ln * slideWidth;

            return w;
        };
        plugin.doCss = function () {
            function support() {
                var transition = ['transition', 'MozTransition', 'WebkitTransition', 'OTransition', 'msTransition', 'KhtmlTransition'];
                var root = document.documentElement;
                for (var i = 0; i < transition.length; i++) {
                    if (transition[i] in root.style) {
                        return true;
                    }
                }
            }

            return support();
        };
        plugin.videoControl = function(){
            $('.rslider_iframe_cover').on('click', function(){
                $('.rslider_iframe_cover').hide();

                var $iframe = $(this).parent().find('iframe');

                if($iframe && $iframe.attr('src')){
                    if($iframe.attr('src').indexOf('youtube') !== -1){
                        $iframe.get(0).contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                    } else if($iframe.attr('src').indexOf('vimeo') !== -1){
                        try {
                            $f($iframe.get(0)).api('play');
                        } catch (e) {
                            console.error('Make sure you have included froogaloop2 js');
                        }
                    }
                }

                $('.rwd-SlideWrapper').addClass('videoIsPlay');

                $el.pause();
            });
        };
        plugin.keyPress = function () {
            $(document).on('keyup', function (e) {
                if (!$(':focus').is('input, textarea')) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    } else {
                        e.returnValue = false;
                    }
                    if (e.keyCode === 37) {
                        $el.goToPrevSlide();
                    } else if (e.keyCode === 39) {
                        $el.goToNextSlide();
                    }
                }
            });
        };
        plugin.controls = function () {
            if (settings.controls) {
                $el.after('<div class="rwd-Action"><a class="rwd-Prev"></a><a class="rwd-Next"></a></div>');
                if (length <= settings.item) {
                    $slide.find('.rwd-Action').hide();
                }

                $slide.find('.rwd-Action a').on('click', function (e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    } else {
                        e.returnValue = false;
                    }
                    if ($(this).attr('class') === 'rwd-Prev') {
                        $el.goToPrevSlide();
                    } else {
                        $el.goToNextSlide();
                    }
                    return false;
                });
            }
        };
        plugin.thumbControls = function () {
            var $this = this;

            if (settings.thumbControls) {
                if (!settings.vertical && settings.thumbPosition) {
                    setTimeout(function(){
                        $el.parent().parent().prepend('<div class="thumbAction"><a class="thumbPrev"></a><a class="thumbNext"></a></div>');
                    }, 0);
                } else {
                    $el.parent().after('<div class="thumbAction"><a class="thumbPrev"></a><a class="thumbNext"></a></div>');
                }
                if (length <= settings.item) {
                    $slide.find('.thumbAction').hide();
                }
                $(document).on('click', '.thumbAction a', function (e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    } else {
                        e.returnValue = false;
                    }
                    if ($(this).hasClass('thumbPrev')) {
                        $this.prevThumb();
                    } else {
                        $this.nextThumb();
                    }
                    return false;
                });

                if(settings.vertical){
                    if(settings.thumbPosition){
                        $('.rwd-SlideOuter.vertical .thumbAction > a').css('left', settings.vThumbWidth / 2 - 15 + 'px');
                    } else {
                        $('.rwd-SlideOuter.vertical .thumbAction > a').css('right', -settings.vThumbWidth / 2 - 15 + 'px');
                    }
                } else {
                    setTimeout(function(){
                        $('.thumbAction').css('top', (settings.hThumbHeight - 5) / 2 + 'px');
                        $('.thumbPrev').css('left', '10px');
                        $('.thumbNext').css('right', '10px');
                    }, 0);
                }
            }
        };
        plugin.prevThumb = function(){
            var thumbMove = 0, $pager;

            $slide.addClass('thumb_move');

            $pager = $slide.parent().find('.rwd-Pager');

            thumbMove = (+$pager.attr('data-move') || 0) - thumbWidth - settings.thumbMargin;

            if (thumbMove < 0) {
                if(!$('.rwd-SlideWrapper').hasClass('rwd-fullscreen-on')){
                    thumbMove = ($('.rwd-Pager.rwd-Gallery li').length - settings.thumbItem) * (thumbWidth + settings.thumbMargin);
                } else {
                    thumbMove = ($('.rwd-Pager.rwd-Gallery li').length - $('.rwd-SlideWrapper').height() / (thumbWidth + settings.thumbMargin)) * (thumbWidth + settings.thumbMargin);
                }
            }

            $pager.attr('data-move', +thumbMove);

            this.move($pager, thumbMove);
        };
        plugin.nextThumb = function(){
            var thumbMove = 0, $pager;

            $slide.addClass('thumb_move');

            $pager = $slide.parent().find('.rwd-Pager');

            thumbMove = thumbWidth + settings.thumbMargin + (+$pager.attr('data-move') || 0);

            if(settings.vertical){
                if (thumbMove + $('.rwd-SlideWrapper').height() > $('.rwd-Pager.rwd-Gallery').height()) {
                    thumbMove = 0;
                }
            } else {
                if (thumbMove + $('.rwd-SlideWrapper').width() > $('.rwd-Pager.rwd-Gallery').width()) {
                    thumbMove = 0;
                }
            }

            $pager.attr('data-move', +thumbMove);

            this.move($pager, thumbMove);
        };
        plugin.fullscreen = function () {
            var $this = this;

            if (settings.fullscreen) {
                var fullScreen = '<span class="rwd-fullscreen rwd-icon">' +
                    '<svg id="rwd-fullscreen-on" width="20px" height="20px" version="1.1" x="0px" y="0px"' +
                    'viewBox="0 0 35.9 35.9" style="enable-background:new 0 0 35.9 35.9;" xml:space="preserve">' +
                    '<style type="text/css">.st0{stroke:#FFFFFF;stroke-width:2;stroke-miterlimit:10;}</style><g id="fullscreen">' +
                    '<path class="st0" d="M6.5,22.4H1.8v11.7h11.7v-4.7h-7V22.4z M1.8,13h4.7v-7h7V1.3H1.8V13z M30,29.4h-7v4.7h11.7V22.4H30V29.4z' +
                    'M22.9,1.3v4.7h7v7h4.7V1.3H22.9z M22.9,1.3"/></g></svg>' +
                    '<svg id="rwd-fullscreen-off" width="20px" height="20px" x="0px" y="0px"' +
                    'viewBox="0 0 35.9 35.9" style="enable-background:new 0 0 35.9 35.9;" xml:space="preserve">' +
                    '<style type="text/css">.st0{stroke:#FFFFFF;stroke-width:2;stroke-miterlimit:10;}</style><g id="fullscreen-exit">' +
                    '<path class="st0" d="M2.6,27h6.8v6.8h4.5V22.5H2.6V27z M9.4,8.9H2.6v4.5h11.3V2.1H9.4V8.9z M23,33.8h4.5V27h6.8v-4.5H23V33.8z' +
                    'M27.5,8.9V2.1H23v11.3h11.3V8.9H27.5z M27.5,8.9"/></g></svg>' +
                    '</span>';

                $el.after('<div class="fullscreen">' + fullScreen + '</div>');

                $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function() {
                    $('.rwd-SlideWrapper').toggleClass('rwd-fullscreen-on');
                    $('.rwd-Pager.rwd-Gallery').toggleClass('rwd-fullscreen-on');

                    if(!$('.rwd-SlideWrapper').hasClass('rwd-fullscreen-on')){
                        $('.rwd-fullscreen').toggleClass('active');
                        $this.exitFullscreen();
                    }
                });

                $('.rwd-fullscreen svg').on('click', function() {
                    $('.rwd-fullscreen').toggleClass('active');
                    if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                        $this.requestFullscreen();
                    } else {
                        $this.exitFullscreen();
                    }
                });
            }
        };
        plugin.requestFullscreen = function() {
            var el = document.documentElement;

            if (el.requestFullscreen) {
                el.requestFullscreen();
            } else if (el.msRequestFullscreen) {
                el.msRequestFullscreen();
            } else if (el.mozRequestFullScreen) {
                el.mozRequestFullScreen();
            } else if (el.webkitRequestFullscreen) {
                el.webkitRequestFullscreen();
            }

            var $sl = $el.parent().parent().parent();

            $('body').prepend($sl);

            var $h = 0;

            $h = settings.hThumbHeight;

            if($h > 50 && $h <= 100){
                $h *= 1.5;
            } else if($h <= 50){
                $h *= 2;
            } else if($h > 100){
                $h *= 1.25;
            }

            if(settings.title || settings.description) {
                $h += 45;
            }

            setTimeout(function(){
                $('html, body').css('overflow', 'hidden');

                $sl.css({
                    'max-width': 'none',
                    'width': '100%',
                    'height': $(window).height() + 'px',
                    'position': 'fixed',
                    'z-index': '999999999'
                });

                if(!settings.vertical){
                    if(settings.thumbPosition){
                        $('.rwd-SlideWrapper').css({
                            'height': $(window).height() + 'px'
                        });
                    } else {
                        $('.rwd-SlideWrapper').css({
                            'height': $(window).height() - $h + 'px'
                        });
                    }
                }

                $('.rwd-SlideOuter').parent().css({
                    'background': 'white'
                });

                if(!settings.vertical && settings.thumbPosition){
                    setTimeout(function(){
                        $('.rwd-SlideOuter').prepend($('.thumbAction'));
                    }, 300)
                }

            }, 360);

            $('.rwd-SlideOuter').css({
                visibility: 'hidden',
                opacity: '0'
            });

            for(var i = 0; i < 5; i++){
                setTimeout(function(){
                    refresh.init();
                }, i * 400);
            }

            setTimeout(function(){
                $('.rwd-SlideOuter').css({
                    visibility: 'visible',
                    opacity: '1'
                });
            }, 1600);

            $('div[class*=share_buttons_]').hide();
        };
        plugin.exitFullscreen = function() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }

            var $sl = $el.parent().parent().parent(),
                $p = $('.slider-parent');

            $p.after($sl);

            $('html, body').css('overflow', '');

            $sl.css({
                'max-width': '',
                'width': '',
                'height': '',
                'position': '',
                'z-index': ''
            });

            $('.rwd-SlideWrapper').css({
                'height': ''
            });

            $('.rwd-SlideOuter').parent().css({
                'background': ''
            });

            if(!settings.vertical && settings.thumbPosition){
                setTimeout(function(){
                    $('.rwd-SlideOuter').prepend($('.thumbAction'));
                }, 320);
            }

            $('.rwd-SlideOuter').css({
                visibility: 'hidden',
                opacity: '0'
            });

            for(var i = 0; i < 5; i++){
                setTimeout(function(){
                    refresh.init();
                }, i * 400);
            }

            setTimeout(function(){
                $('.rwd-SlideOuter').css({
                    visibility: 'visible',
                    opacity: '1'
                });
            }, 1600);

            $('div[class*=share_buttons_]').show();
        };
        plugin.initialStyle = function () {
            var $this = this;

            $el.addClass('huge_it_slider').wrap('<div class="rwd-SlideOuter"><div class="rwd-SlideWrapper"></div></div>');
            $slide = $el.parent('.rwd-SlideWrapper');
            if (settings.vertical) {
                $slide.parent().addClass('vertical');
                elSize = settings.sliderHeight;
                $slide.css('height', elSize + 'px');
            } else {
                elSize = $el.outerWidth();
            }
            $children.addClass('rwd-slide');
            if (settings.mode === 'slide') {
                refresh.calSW();
                refresh.clone = function () {
                    if (refresh.calWidth(true) > elSize) {
                        var tWr = 0,
                            tI = 0;
                        for (var k = 0; k < $children.length; k++) {
                            tWr += parseInt($el.find('.rwd-slide').eq(k).width());
                            tI++;
                            if (tWr >= elSize) {
                                break;
                            }
                        }
                        var tItem = settings.item;

                        if (tItem < $el.find('.clone.left').length) {
                            for (var i = 0; i < $el.find('.clone.left').length - tItem; i++) {
                                $children.eq(i).remove();
                            }
                        }
                        if (tItem < $el.find('.clone.right').length) {
                            for (var j = $children.length - 1; j > ($children.length - 1 - $el.find('.clone.right').length); j--) {
                                scene--;
                                $children.eq(j).remove();
                            }
                        }

                        for (var n = $el.find('.clone.right').length; n < tItem; n++) {
                            $el.find('.rwd-slide').eq(n).clone().removeClass('rwd-slide').addClass('clone right').appendTo($el);
                            scene++;
                        }
                        for (var m = $el.find('.rwd-slide').length - $el.find('.clone.left').length; m > ($el.find('.rwd-slide').length - tItem); m--) {
                            $el.find('.rwd-slide').eq(m - 1).clone().removeClass('rwd-slide').addClass('clone left').prependTo($el);
                        }
                        $children = $el.children();
                    } else {
                        if ($children.hasClass('clone')) {
                            $el.find('.clone').remove();
                            $this.move($el, 0);
                        }
                    }
                };
                refresh.clone();
            }
            refresh.sSW = function () {
                length = $children.length;
                $children.css(property, slideWidth + 'px');
                $children.css(gutter, '0px');
                w = refresh.calWidth(false);
                $el.css(property, w + 'px');
                if (settings.mode === 'slide') {
                    if (on === false) {
                        scene = $el.find('.clone.left').length;
                    }
                }
            };
            refresh.calL = function () {
                $children = $el.children();
                length = $children.length;
            };
            if (this.doCss()) {
                $slide.addClass('usingCss');
            }
            refresh.calL();
            if (settings.mode === 'slide') {
                refresh.calSW();
                refresh.sSW();

                slideValue = $this.slideValue();
                this.move($el, slideValue);

                if (settings.vertical === false) {
                    this.setHeight($el, false);
                }

            } else {
                this.setHeight($el, true);
                $el.addClass('rwd-Fade');
                if (!this.doCss()) {
                    $children.fadeOut(0);
                    $children.eq(scene).fadeIn(0);
                }
            }
            if (settings.mode === 'slide') {
                $children.eq(scene).addClass('active');
            } else {
                $children.first().addClass('active');
            }

            $('.rslider_iframe_cover').height($('.rslider_iframe_cover').parent().height());
        };
        plugin.pager = function () {
            var $this = this;
            refresh.createPager = function () {
                var $size;

                if (settings.vertical) {
                    $size = settings.sliderHeight;
                } else {
                    $size = elSize;
                }

                thumbWidth = ($size - ((settings.thumbItem * (settings.thumbMargin)) - settings.thumbMargin)) / settings.thumbItem;

                var $children = $slide.find('.rwd-slide');
                var length = $slide.find('.rwd-slide').length;
                var i = 0,
                    pagers = '',
                    v = 0;

                for (i = 0; i < length; i++) {
                    if (settings.mode === 'slide') {
                        v = i * slideWidth;
                    }
                    var thumb = $children.eq(i).attr('data-thumb');
                    var title = $children.eq(i).attr('data-title') || '';
                    var description = $children.eq(i).attr('data-description') || '';

                    if(settings.title && !settings.vertical){
                        title = title.substring(0, settings.titlesymbollimit);
                    }

                    if(settings.description && !settings.vertical){
                        description = description.substring(0, settings.descsymbollimit);
                    }

                    var $shift = '', $wT = '';

                    if(settings.vertical && (settings.title || settings.description)){
                        $shift = 'margin-left';
                        $wT = settings.vThumbWidth + 5;
                    }

                    var $t = '', $d = '', $dH = '';

                    if(settings.vertical){
                        var $h = Math.floor(($('.rwd-Pager.rwd-Gallery li').height() - 21.5) / 16.5) * 16.5;
                        $dH = 'height: ' + $h + 'px;';
                    }

                    if(settings.title){
                        $t= '<p class="thumb_title" style="' + $shift + ':' + $wT + 'px">' + title + '</p>';
                    }

                    if(settings.description){
                        $d = '<p class="thumb_description" style="'+ $dH + $shift + ':' + $wT + 'px">' + description + '</p>';
                    }

                    if (settings.gallery === true) {
                        pagers += '<li style="width:100%;' + property + ':' + thumbWidth + 'px;' + gutter + ':' + settings.thumbMargin + 'px">' +
                            '<a href="#"><img src="' + thumb + '"/>' +
                            $t +
                            $d +
                            '</a></li>';
                    } else {
                        pagers += '<li><a href="#">' + (i + 1) + '</a></li>';
                    }

                    if (settings.mode === 'slide') {
                        if ((v) >= w - elSize) {
                            i = i + 1;
                            var minPgr = 2;
                            if (i < minPgr) {
                                pagers = null;
                                $slide.parent().addClass('noPager');
                            } else {
                                $slide.parent().removeClass('noPager');
                            }
                            break;
                        }
                    }
                }
                var $cSouter = $slide.parent();
                $cSouter.find('.rwd-Pager').html(pagers);
                if (settings.gallery === true) {
                    if (settings.vertical === true) {
                        if(settings.title || settings.description) {
                            $cSouter.find('.rwd-Pager img').css({
                                'width': settings.vThumbWidth + 'px',
                                'float': 'left'
                            });

                            if($el.parent().parent().parent().parent().width() > 600) {
                                $cSouter.find('.rwd-Pager').css('width', (settings.vThumbWidth + 150) + 'px');
                            } else {
                                $cSouter.find('.rwd-Pager').css('width', '100%');
                                $cSouter.find('.rwd-Pager p').css('width', 'calc(100% - 105px)');
                            }
                        } else {
                            $cSouter.find('.rwd-Pager').css('width', settings.vThumbWidth + 'px');
                        }
                    } else {
                        $cSouter.find('.rwd-Pager img').css({
                            'height': settings.hThumbHeight + 'px'
                        });
                    }

                    pagerWidth = (i * (settings.thumbMargin + thumbWidth)) + 0.5;
                    $cSouter.find('.rwd-Pager').css({
                        property: pagerWidth + 'px',
                        'transition-duration': settings.speed + 'ms'
                    });

                    if($el.parent().parent().parent().parent().width() > 600){
                        if (settings.vertical === true && settings.thumbPosition === false) {
                            if(settings.title || settings.description){
                                $slide.parent().css('padding-right', (settings.vThumbWidth + 155) + 'px');
                            } else {
                                $slide.parent().css('padding-right', settings.vThumbWidth + 5 + 'px');
                            }
                        }

                        if(!settings.vertical && !$('.rwd-Pager.rwd-Gallery').hasClass('rwd-fullscreen-on')){
                            $('.thumb_description').show();
                        }
                    } else {
                        if (settings.vertical === true && settings.thumbPosition === false) {
                            $slide.parent().css('padding-right', '');
                        }
                        if(!settings.vertical){
                            $('.thumb_description').hide();
                        }
                    }

                    $cSouter.find('.rwd-Pager').css(property, pagerWidth + 'px');
                }
                var $pager = $cSouter.find('.rwd-Pager').find('li');
                $pager.first().addClass('active');
                $pager.on('click', function () {
                    if(!$slide.hasClass('thumb_moving')){
                        $slide.removeClass('thumb_move');
                    }
                    if (settings.mode === 'slide') {
                        scene = scene + ($pager.index(this) - $cSouter.find('.rwd-Pager').find('li.active').index());
                    } else {
                        scene = $pager.index(this);
                    }
                    $el.mode(false);
                    if (settings.gallery === true) {
                        $this.slideThumb();
                    }
                    return false;
                });

                if(settings.view === 'carousel1') {
                    var $h = (settings.maxHeight / settings.maxWidth) * jQuery('.rwd-SlideOuter').width();
                    jQuery('.rwd-SlideOuter').height($h);
                    if (settings.dotsPos === 'top') {
                        jQuery('.rwd-Pager.rwd-pg').css({
                            top: (jQuery('.rwd-SlideWrapper').height() - jQuery('.rwd-SlideOuter').height()) / 2 + 10 + 'px'
                        });
                    } else {
                        jQuery('.rwd-Pager.rwd-pg').css({
                            bottom: (jQuery('.rwd-SlideWrapper').height() - jQuery('.rwd-SlideOuter').height()) / 2 - jQuery('.rwd-SlideWrapper').height() + 15 + 'px'
                        });
                    }
                }

                if(settings.thumbdragdrop){
                    plugin.enableThumbDrag();
                }

                if(settings.thumbswipe){
                    plugin.enableThumbTouch();
                }

                if(settings.pager){
                    var gMargin = '', $s = '', gM = 5;

                    if(settings.vertical){
                        if(settings.thumbPosition){
                            gMargin = 'margin-left';
                            gM = 0;
                            $slide.parent().find('.rwd-Pager').css({'left': '0px', 'position': 'absolute'});
                            if(settings.title || settings.description){
                                $slide.parent().find('.rwd-SlideWrapper').css('margin-left', (settings.vThumbWidth + 155) + 'px');
                            } else {
                                $slide.parent().find('.rwd-SlideWrapper').css('margin-left', settings.vThumbWidth + 5 + 'px');
                            }
                            if($el.parent().parent().parent().parent().width() > 600) {
                                if (settings.title || settings.description) {
                                    $slide.parent().find('.rwd-SlideWrapper').css('margin-left', (settings.vThumbWidth + 155) + 'px');
                                }

                                $slide.parent().find('.rwd-Pager').css({'position': 'absolute'});

                                $('.rwd-SlideOuter.vertical').prepend($('.rwd-SlideOuter.vertical .rwd-Gallery').parent());

                                $('.rwd-SlideOuter.vertical .rwd-Gallery').parent().css({
                                    'overflow': '',
                                    'margin-top': ''
                                });

                                $('.thumbAction').show();
                            } else {
                                if (settings.title || settings.description) {
                                    $slide.parent().find('.rwd-SlideWrapper').css('margin-left', '');
                                }

                                $slide.parent().find('.rwd-Pager').css({'position': ''});

                                $('.rwd-SlideOuter.vertical').append($('.rwd-SlideOuter.vertical .rwd-Gallery').parent());

                                $('.rwd-SlideOuter.vertical .rwd-Gallery').parent().css({
                                    'overflow': 'hidden',
                                    'margin-top': '5px'
                                });

                                $('.thumbAction').hide();
                            }
                        } else {
                            if($el.parent().parent().parent().parent().width() > 600) {
                                $slide.parent().find('.rwd-Pager').css({'position': 'absolute'});
                                if (settings.title || settings.description) {
                                    $('.rwd-SlideOuter.vertical .rwd-Gallery').css({
                                        'margin-top': -$('.rwd-SlideWrapper').height() + 'px',
                                        'margin-left': 'calc(100% - ' + (settings.vThumbWidth + 150) + 'px)'
                                    });
                                } else {
                                    $('.rwd-SlideOuter.vertical .rwd-Gallery').css({
                                        'margin-top': -$('.rwd-SlideWrapper').height() + 'px',
                                        'margin-left': 'calc(100% - ' + settings.vThumbWidth + 'px)'
                                    });
                                }
                                $('.rwd-SlideOuter.vertical .rwd-Gallery').parent().css({
                                    'overflow': '',
                                    'margin-top': ''
                                });

                                $('.thumbAction').show();
                            } else {
                                $('.rwd-SlideOuter.vertical .rwd-Gallery').css({
                                    'margin-top': '',
                                    'margin-left': '',
                                    'position': ''
                                });
                                $('.rwd-SlideOuter.vertical .rwd-Gallery').parent().css({
                                    'overflow': 'hidden',
                                    'margin-top': '5px'
                                });

                                $('.thumbAction').hide();
                            }
                        }

                        if($el.parent().parent().parent().parent().width() <= 600){
                            $('.rwd-SlideOuter.vertical').css('height', (settings.sliderHeight + 5 + 4 * (settings.sliderHeight - settings.thumbItem * settings.thumbMargin + settings.thumbMargin) / settings.thumbItem) + 'px');
                        } else {
                            $('.rwd-SlideOuter.vertical').css('height', '');
                        }
                    } else {
                        if(settings.thumbPosition){
                            gMargin = 'margin-bottom';
                            $s = $slide.parent().find('.rwd-Pager');
                            $('.rwd-SlideOuter').prepend($s);
                        } else {
                            gMargin = 'margin-top';
                        }
                    }

                    $slide.parent().find('.rwd-Pager').css(gMargin, gM + 'px');
                }
            };

            if (settings.pager) {
                var cl = 'rwd-pg';
                if (settings.gallery) {
                    cl = 'rwd-Gallery';
                }

                if((hugeitSliderObj.navigation_position === 'top' && !settings.gallery) || (settings.vertical && settings.thumbPosition)){
                    $slide.before('<div><ul class="rwd-Pager ' + cl + '"></ul></div>');
                } else {
                    $slide.after('<div><ul class="rwd-Pager ' + cl + '"></ul></div>');
                }

                refresh.createPager();
            }

            setTimeout(function () {
                refresh.init();
            }, 0);
        };
        plugin.setHeight = function (ob, fade) {
            var obj = null,
                $this = this;

            obj = ob.children('.rwd-slide ').first();

            var setCss = function () {
                var tH = obj.outerHeight(),
                    tP = 0,
                    tHT = tH,
                    tH_;

                tH_ = obj.parent().find('li:not(.video_iframe)').outerHeight();

                if(tH < tH_){
                    tH = tH_;
                }

                if (fade) {
                    tH = settings.sliderHeight;
                    tP = ((tHT) * 100) / elSize;
                }

                if(settings.view === 'carousel1'){
                    $('.rwd-SlideWrapper').css({
                        height: tH + 20 + 'px'
                    });
                }

                if(!$slide.hasClass('rwd-fullscreen-on')){
                    ob.css({
                        'height': tH + 'px',
                        'padding-bottom': tP + '%'
                    });
                } else {
                    ob.css({
                        'height': '100%',
                        'padding-bottom': tP + '%'
                    });
                }
            };
            setCss();
            if (obj.find('img, iframe').length) {
                if ( obj.find('img, iframe')[0].complete) {
                    setCss();
                    if (!interval) {
                        $this.auto();
                    }
                }else{
                    obj.find('img, iframe').on('load', function () {
                        setTimeout(function () {
                            setCss();
                            if (!interval) {
                                $this.auto();
                            }
                        }, 100);
                    });
                }
            }else{
                if (!interval) {
                    $this.auto();
                }
            }
        };
        plugin.active = function (ob, t) {
            if (this.doCss() && settings.mode === 'fade') {
                $slide.addClass('on');
            }
            var sc = 0;
            if (scene < length) {
                ob.removeClass('active');
                if (!this.doCss() && settings.mode === 'fade' && t === false) {
                    ob.fadeOut(settings.speed);
                }

                sc = scene;

                var l, nl;
                if (t === true) {
                    l = ob.length;
                    nl = l - 1;
                    if (sc + 1 >= l) {
                        sc = nl;
                    }
                }
                if (settings.mode === 'slide') {
                    if (t === true) {
                        sc = scene - $el.find('.clone.left').length;
                    } else {
                        sc = scene;
                    }
                    if (t === true) {
                        l = ob.length;
                        nl = l - 1;
                        if (sc + 1 === l) {
                            sc = nl;
                        } else if (sc + 1 > l) {
                            sc = 0;
                        }
                    }
                }

                if (!this.doCss() && settings.mode === 'fade' && t === false) {
                    ob.eq(sc).fadeIn(settings.speed);
                }
                ob.eq(sc).addClass('active');
            } else {
                ob.removeClass('active');
                ob.eq(ob.length - 1).addClass('active');
                if (!this.doCss() && settings.mode === 'fade' && t === false) {
                    ob.fadeOut(settings.speed);
                    ob.eq(sc).fadeIn(settings.speed);
                }
            }
        };
        plugin.move = function (ob, v) {
            ob.attr('data-left', v);

            if(settings.view === 'carousel1') {
                if (settings.item === 3) {
                    settings.slideMargin = 20;
                }

                v = v - Math.floor(settings.item / 2) * slideWidth;
            }

            if (this.doCss()) {
                if (settings.vertical === true) {
                    ob.css({
                        'transform': 'translate3d(0px, ' + (-v) + 'px, 0px)',
                        '-webkit-transform': 'translate3d(0px, ' + (-v) + 'px, 0px)'
                    });
                } else {
                    ob.css({
                        'transform': 'translate3d(' + (-v) + 'px, 0px, 0px)',
                        '-webkit-transform': 'translate3d(' + (-v) + 'px, 0px, 0px)'
                    });
                }
            } else {
                if (settings.vertical === true) {
                    ob.css('position', 'relative').animate({
                        top: -v + 'px'
                    }, settings.speed, 'linear');
                } else {
                    ob.css('position', 'relative').animate({
                        left: -v + 'px'
                    }, settings.speed, 'linear');
                }
            }
            var $thumb = $slide.parent().find('.rwd-Pager').find('li');
            this.active($thumb, true);
        };
        plugin.move_ = function (ob, v) {
            if (settings.rtl === true) {
                v = -v;
            }

            if (this.doCss()) {
                ob.css({
                    'transform': 'translate3d(' + (-v) + 'px, 0px, 0px)',
                    '-webkit-transform': 'translate3d(' + (-v) + 'px, 0px, 0px)'
                });
            } else {
                ob.css('position', 'relative').animate({
                    left: -v + 'px'
                }, settings.speed, settings.easing);
            }
            var $thumb = $slide.parent().find('.rwd-Pager').find('li');
            this.active($thumb, true);
        };
        plugin.fade = function () {
            this.active($children, false);
            var $thumb = $slide.parent().find('.rwd-Pager').find('li');
            this.active($thumb, true);
        };
        plugin.slide = function () {
            var $this = this;
            refresh.calSlide = function () {
                if (w > elSize) {
                    slideValue = $this.slideValue();
                    $this.active($children, false);
                    if ((slideValue) > w - elSize) {
                        slideValue = w - elSize;
                    } else if (slideValue < 0) {
                        slideValue = 0;
                    }
                    $this.move($el, slideValue);
                    if (settings.mode === 'slide') {
                        if (scene >= length - $el.find('.clone.left').length) {
                            $this.resetSlide($el.find('.clone.left').length);
                        }
                        if (scene === 0) {
                            $this.resetSlide($slide.find('.rwd-slide').length);
                        }
                    }
                }
            };
            refresh.calSlide();
        };
        plugin.resetSlide = function (s) {
            var $this = this;
            $slide.find('.rwd-Action a').addClass('disabled');
            setTimeout(function () {
                scene = s;
                $slide.css('transition-duration', '0ms');
                slideValue = $this.slideValue();
                $this.active($children, false);
                plugin.move($el, slideValue);
                setTimeout(function () {
                    $slide.css('transition-duration', settings.speed + 'ms');
                    $slide.find('.rwd-Action a').removeClass('disabled');
                }, 50);
            }, settings.speed + 100);
        };
        plugin.slideValue = function () {
            var _sV = 0;
            _sV = scene * slideWidth;

            return _sV;
        };
        plugin.slideThumb = function () {
            var position;

            position = (elSize / 2) - (thumbWidth / 2);

            var sc = scene - $el.find('.clone.left').length;
            var $pager = $slide.parent().find('.rwd-Pager');
            if (settings.mode === 'slide') {
                if (sc >= $pager.children().length) {
                    sc = 0;
                } else if (sc < 0) {
                    sc = $pager.children().length;
                }
            }
            var thumbSlide = sc * ((thumbWidth + settings.thumbMargin)) - (position);
            if ((thumbSlide + elSize) > pagerWidth) {
                thumbSlide = pagerWidth - elSize - settings.thumbMargin;
            }
            if (thumbSlide < 0) {
                thumbSlide = 0;
            }
            if(settings.view === 'carousel1'){
                this.move_($pager, thumbSlide);
            } else {
                if(!$slide.hasClass('thumb_move')){
                    this.move($pager, thumbSlide);
                }
            }
        };
        plugin.auto = function () {
            clearInterval(interval);
            interval = setInterval(function () {
                $el.goToNextSlide();
            }, settings.pause);
        };
        plugin.pauseOnHover = function(){
            var $this = this;
            if (settings.pauseOnHover) {
                $slide.on('mouseenter', function(){
                    $(this).addClass('isHover');
                    $el.pause();
                });
                $slide.on('mouseleave',function(){
                    $(this).removeClass('isHover');
                    if (!$slide.find('.huge_it_slider').hasClass('isGrabbing')) {
                        if(!$('.rwd-SlideWrapper').hasClass('videoIsPlay')){
                            $this.auto();
                        }
                    }
                });
            }
        };
        plugin.touchMove = function (endCoords, startCoords) {
            $slide.css('transition-duration', '0ms');
            if (settings.mode === 'slide') {
                var distance = endCoords - startCoords;
                var swipeVal = slideValue - distance;
                if ((swipeVal) >= w - elSize) {
                    var swipeValT = w - elSize;
                    swipeVal = swipeValT + ((swipeVal - swipeValT) / 5);
                } else if (swipeVal < 0) {
                    swipeVal = swipeVal / 5;
                }
                this.move($el, swipeVal);
            }
        };
        plugin.touchEnd = function (distance) {
            $slide.css('transition-duration', settings.speed + 'ms');
            if (settings.mode === 'slide') {
                var mxVal = false;
                var _next = true;
                slideValue = slideValue - distance;
                if ((slideValue) > w - elSize) {
                    slideValue = w - elSize;
                    mxVal = true;
                } else if (slideValue < 0) {
                    slideValue = 0;
                }
                var gC = function (next) {
                    var ad = 0;
                    if (!mxVal) {
                        if (next) {
                            ad = 1;
                        }
                    }
                    var num = slideValue / slideWidth;
                    scene = parseInt(num) + ad;
                    if (slideValue >= (w - elSize)) {
                        if (num % 1 !== 0) {
                            scene++;
                        }
                    }
                };
                if (distance >= 45) {
                    gC(false);
                    _next = false;
                } else if (distance <= -45) {
                    gC(true);
                    _next = false;
                }
                $el.mode(_next);
                this.slideThumb();
            } else {
                if (distance >= 45) {
                    $el.goToPrevSlide();
                } else if (distance <= -45) {
                    $el.goToNextSlide();
                }
            }
        };
        plugin.enableDrag = function () {
            var $this = this;
            if (!isTouch) {
                var startCoords = 0,
                    endCoords = 0,
                    isDraging = false;
                $slide.find('.huge_it_slider').addClass('isGrab');
                $slide.on('mousedown', function (e) {
                    if (w < elSize) {
                        if (w !== 0) {
                            return false;
                        }
                    }
                    if ($(e.target).attr('class') !== ('rwd-Prev') && $(e.target).attr('class') !== ('rwd-Next')) {
                        startCoords = (settings.vertical === true) ? e.pageY : e.pageX;
                        isDraging = true;
                        if (e.preventDefault) {
                            e.preventDefault();
                        } else {
                            e.returnValue = false;
                        }
                        $slide.scrollLeft += 1;
                        $slide.scrollLeft -= 1;
                        $slide.find('.huge_it_slider').removeClass('isGrab').addClass('isGrabbing');
                        clearInterval(interval);
                    }
                });
                $(window).on('mousemove', function (e) {
                    if (isDraging) {
                        endCoords = (settings.vertical === true) ? e.pageY : e.pageX;
                        $this.touchMove(endCoords, startCoords);
                    }
                });
                $(window).on('mouseup', function (e) {
                    if (isDraging) {
                        $slide.find('.huge_it_slider').removeClass('isGrabbing').addClass('isGrab');
                        isDraging = false;
                        endCoords = (settings.vertical === true) ? e.pageY : e.pageX;
                        var distance = endCoords - startCoords;
                        if (Math.abs(distance) >= 45) {
                            $(window).on('click', function (e) {
                                if (e.preventDefault) {
                                    e.preventDefault();
                                } else {
                                    e.returnValue = false;
                                }
                                e.stopImmediatePropagation();
                                e.stopPropagation();
                                $(window).off('click');
                            });
                        }
                        $this.touchEnd(distance);
                    }
                });
            }
        };
        plugin.enableThumbDrag = function(){
            if (settings.pager && settings.gallery) {
                var $this = this,
                    $pager = $slide.parent().find('.rwd-Pager');

                if (!isTouch) {
                    var tempLeft = 0,
                        startCoords = 0,
                        endCoords = 0,
                        swipeVal = 0,
                        distance,
                        isDraging = false;

                    $slide.find('.rwd-Gallery').addClass('isGrab');

                    $('.rwd-Gallery').on('mousedown', function (e) {
                        $slide.addClass('thumb_move');

                        tempLeft = parseInt($pager.attr('data-left'));

                        startCoords = (settings.vertical === true) ? e.pageY : e.pageX;
                        isDraging = true;
                        if (e.preventDefault) {
                            e.preventDefault();
                        } else {
                            e.returnValue = false;
                        }
                        $slide.scrollLeft += 1;
                        $slide.scrollLeft -= 1;
                        $slide.find('.rwd-Gallery').removeClass('isGrab').addClass('isGrabbing');
                    });

                    $('.rwd-Gallery').on('mousemove', function (e) {
                        if (isDraging) {
                            $pager.css('transition-duration', '0ms');
                            $slide.addClass('thumb_moving');

                            endCoords = (settings.vertical === true) ? e.pageY : e.pageX;

                            distance = endCoords - startCoords;

                            swipeVal = tempLeft - distance;
                            if (settings.vertical === true) {
                                if (swipeVal > $pager.height() - $('.rwd-SlideWrapper').height()) {
                                    swipeVal = $pager.height() - $('.rwd-SlideWrapper').height() - settings.thumbMargin;
                                }
                            } else {
                                if (swipeVal > $pager.width() - $('.rwd-SlideWrapper').width()) {
                                    swipeVal = $pager.width() - $('.rwd-SlideWrapper').width() - settings.thumbMargin;
                                }
                            }

                            if (swipeVal < 0) {
                                swipeVal = 0;
                            }

                            $this.move($pager, swipeVal);
                        }
                    });

                    $('.rwd-Gallery').on('mouseup', function (e) {
                        if (isDraging) {
                            setTimeout(function(){
                                $slide.removeClass('thumb_moving');
                            }, 0);

                            endCoords = (settings.vertical === true) ? e.pageY : e.pageX;

                            distance = endCoords - startCoords;

                            swipeVal = tempLeft - distance;
                            if (settings.vertical === true) {
                                if (swipeVal > $pager.height() - $('.rwd-SlideWrapper').height()) {
                                    swipeVal = $pager.height() - $('.rwd-SlideWrapper').height() - settings.thumbMargin;
                                }
                            } else {
                                if (swipeVal > $pager.width() - $('.rwd-SlideWrapper').width()) {
                                    swipeVal = $pager.width() - $('.rwd-SlideWrapper').width() - settings.thumbMargin;
                                }
                            }

                            if (swipeVal < 0) {
                                swipeVal = 0;
                            }

                            $this.move($pager, swipeVal);

                            $pager.attr('data-left', swipeVal);

                            $slide.find('.rwd-Gallery').removeClass('isGrabbing').addClass('isGrab');
                            isDraging = false;
                            endCoords = (settings.vertical === true) ? e.pageY : e.pageX;
                            var distance = endCoords - startCoords;
                            if (Math.abs(distance) >= 45) {
                                $(window).on('click', function (e) {
                                    if (e.preventDefault) {
                                        e.preventDefault();
                                    } else {
                                        e.returnValue = false;
                                    }
                                    e.stopImmediatePropagation();
                                    e.stopPropagation();
                                    $(window).off('click');
                                });
                            }
                        }
                    });
                }
            }
        };
        plugin.enableTouch = function () {
            var $this = this;
            if (isTouch) {
                var startCoords = {},
                    endCoords = {};
                $slide.on('touchstart', function (e) {
                    endCoords = e.originalEvent.targetTouches[0];
                    startCoords.pageX = e.originalEvent.targetTouches[0].pageX;
                    startCoords.pageY = e.originalEvent.targetTouches[0].pageY;
                    clearInterval(interval);
                });
                $slide.on('touchmove', function (e) {
                    if (w < elSize) {
                        if (w !== 0) {
                            return false;
                        }
                    }
                    var orig = e.originalEvent;
                    endCoords = orig.targetTouches[0];
                    var xMovement = Math.abs(endCoords.pageX - startCoords.pageX);
                    var yMovement = Math.abs(endCoords.pageY - startCoords.pageY);
                    if (settings.vertical === true) {
                        if ((yMovement * 3) > xMovement) {
                            e.preventDefault();
                        }
                        $this.touchMove(endCoords.pageY, startCoords.pageY);
                    } else {
                        if ((xMovement * 3) > yMovement) {
                            e.preventDefault();
                        }
                        $this.touchMove(endCoords.pageX, startCoords.pageX);
                    }

                });
                $slide.on('touchend', function () {
                    if (w < elSize) {
                        if (w !== 0) {
                            return false;
                        }
                    }
                    var distance;
                    if (settings.vertical === true) {
                        distance = endCoords.pageY - startCoords.pageY;
                    } else {
                        distance = endCoords.pageX - startCoords.pageX;
                    }
                    $this.touchEnd(distance);
                });
            }
        };
        plugin.enableThumbTouch = function () {
            if (settings.pager && settings.gallery) {
                var $this = this,
                    $pager = $slide.parent().find('.rwd-Pager');

                if (isTouch) {
                    var startCoords = {},
                        endCoords = {},
                        tempLeft = 0;

                    $('.rwd-Gallery').on('touchstart', function (e) {
                        tempLeft = +$pager.attr('data-left');

                        endCoords = e.originalEvent.targetTouches[0];
                        startCoords.pageX = e.originalEvent.targetTouches[0].pageX;
                        startCoords.pageY = e.originalEvent.targetTouches[0].pageY;
                        clearInterval(interval);
                    });
                    $('.rwd-Gallery').on('touchmove', function (e) {
                        $pager.css('transition-duration', '0ms');

                        $slide.addClass('thumb_move');

                        if (w < elSize) {
                            if (w !== 0) {
                                return false;
                            }
                        }
                        var orig = e.originalEvent;
                        endCoords = orig.targetTouches[0];
                        var xMovement = Math.abs(endCoords.pageX - startCoords.pageX);
                        var yMovement = Math.abs(endCoords.pageY - startCoords.pageY);
                        if (settings.vertical) {
                            if ((yMovement * 3) > xMovement) {
                                e.preventDefault();
                            }

                            tempLeft = tempLeft - (endCoords.pageY - startCoords.pageY);

                            if (tempLeft > $pager.height() - $('.rwd-SlideWrapper').height()) {
                                tempLeft = $pager.height() - $('.rwd-SlideWrapper').height() - settings.thumbMargin;
                            }

                            if (tempLeft < 0) {
                                tempLeft = 0;
                            }

                            $this.move($pager, tempLeft);
                        } else {
                            if ((xMovement * 3) > yMovement) {
                                e.preventDefault();
                            }

                            tempLeft = tempLeft - (endCoords.pageX - startCoords.pageX);

                            if (tempLeft > $pager.width() - $('.rwd-SlideWrapper').width()) {
                                tempLeft = $pager.width() - $('.rwd-SlideWrapper').width();
                            }

                            if (tempLeft < 0) {
                                tempLeft = 0;
                            }

                            $this.move($pager, tempLeft);
                        }
                    });
                    $('.rwd-Gallery').on('touchend', function () {
                        if (settings.vertical) {
                            if ((yMovement * 3) > xMovement) {
                                e.preventDefault();
                            }

                            tempLeft = tempLeft - (endCoords.pageY - startCoords.pageY);

                            if (tempLeft > $pager.height() - $('.rwd-SlideWrapper').height()) {
                                tempLeft = $pager.height() - $('.rwd-SlideWrapper').height() - settings.thumbMargin;
                            }

                            if (tempLeft < 0) {
                                tempLeft = 0;
                            }

                            $this.move($pager, tempLeft);
                        } else {
                            if ((xMovement * 3) > yMovement) {
                                e.preventDefault();
                            }

                            tempLeft = tempLeft - (endCoords.pageX - startCoords.pageX);

                            if (tempLeft > $pager.width() - $('.rwd-SlideWrapper').width()) {
                                tempLeft = $pager.width() - $('.rwd-SlideWrapper').width();
                            }

                            if (tempLeft < 0) {
                                tempLeft = 0;
                            }

                            $this.move($pager, tempLeft);
                        }

                        $pager.attr('data-left', tempLeft);

                        if (w < elSize) {
                            if (w !== 0) {
                                return false;
                            }
                        }
                        var distance;
                        if (settings.vertical === true) {
                            distance = endCoords.pageY - startCoords.pageY;
                        } else {
                            distance = endCoords.pageX - startCoords.pageX;
                        }
                        $this.touchEnd(distance);
                    });
                }
            }
        };
        plugin.build = function () {
            var $this = this;
            $this.initialStyle();
            if (this.doCss()) {
                if(settings.dragdrop){
                    $this.enableDrag();
                }
                if(settings.swipe){
                    $this.enableTouch();
                }
            }

            $(window).on('focus', function(){
                $this.auto();
            });

            $(window).on('blur', function(){
                clearInterval(interval);
            });

            $this.pager();
            $this.pauseOnHover();
            $this.controls();
            $this.thumbControls();
            $this.keyPress();
            $this.fullscreen();
            $this.videoControl();
        };
        plugin.build();
        refresh.init = function () {
            if (settings.vertical) {
                if (settings.item > 1) {
                    elSize = settings.sliderHeight;
                } else {
                    elSize = $children.outerHeight();
                }

                if(settings.vertical){
                    if(!$slide.hasClass('rwd-fullscreen-on')){
                        $slide.css('height', elSize + 'px');
                    } else {
                        $slide.css('height', ($(window).height() - 30) + 'px');
                    }
                } else {
                    $slide.css('height', elSize + 'px');
                }
            } else {
                elSize = $slide.outerWidth();
            }
            if (settings.mode === 'slide') {
                refresh.clone();
            }
            refresh.calL();
            if (settings.mode === 'slide') {
                $el.removeClass('rwd-Slide');
            }
            if (settings.mode === 'slide') {
                refresh.calSW();
                refresh.sSW();
            }
            setTimeout(function () {
                if (settings.mode === 'slide') {
                    $el.addClass('rwd-Slide');
                }
            }, 1000);
            if (settings.pager) {
                refresh.createPager();
            }
            if (settings.mode === 'slide') {
                if (settings.vertical === false) {
                    plugin.setHeight($el, false);
                }else{
                    plugin.auto();
                }
            } else {
                plugin.setHeight($el, true);
            }
            if (settings.gallery === true) {
                plugin.slideThumb();
            }
            if (settings.mode === 'slide') {
                plugin.slide();
            }
            if ($children.length <= settings.item) {
                $slide.find('.rwd-Action').hide();
            } else {
                $slide.find('.rwd-Action').show();
            }
        };
        $el.goToPrevSlide = function () {
            if (scene > 0) {
                scene--;
                $el.mode(false);
                if (settings.gallery === true) {
                    plugin.slideThumb();
                }
            } else {
                if (settings.mode === 'fade') {
                    var l = (length - 1);
                    scene = l;
                }
                $el.mode(false);
                if (settings.gallery === true) {
                    plugin.slideThumb();
                }
            }
        };
        $el.goToNextSlide = function () {
            var nextI = true;
            if (settings.mode === 'slide') {
                var _slideValue = plugin.slideValue();
                nextI = _slideValue < w - elSize;
            }
            if ((scene < length) && nextI) {
                scene++;
                $el.mode(false);
                if (settings.gallery === true) {
                    plugin.slideThumb();
                }
            } else {
                scene = 0;
                $el.mode(false);
                if (settings.gallery === true) {
                    plugin.slideThumb();
                }
            }
        };
        $el.mode = function (b) {
            if (on === false) {
                if (settings.mode === 'slide') {
                    if (plugin.doCss()) {
                        $el.addClass('rwd-Slide');
                        if (settings.speed !== '') {
                            $slide.css('transition-duration', settings.speed + 'ms');
                        }
                        $slide.css('transition-timing-function','ease');
                    }
                } else {
                    if (plugin.doCss()) {
                        if (settings.speed !== '') {
                            $el.css('transition-duration', settings.speed + 'ms');
                        }
                        $el.css('transition-timing-function', 'ease');
                    }
                }
            }
            if (settings.mode === 'slide') {
                plugin.slide();
            } else {
                plugin.fade();
            }
            if (!$slide.hasClass('isHover')) {
                plugin.auto();
            }

            if (!b) {
                $el.onBeforeSlide.call(this);
            }

            setTimeout(function () {
                if (!b) {
                    $el.onAfterSlide.call(this, scene);
                }
            }, settings.speed);
            on = true;
        };
        $el.onBeforeSlide = function(){
            $('.rwd-SlideWrapper').addClass('lightboxOff');

            $('.rslider_iframe_cover').show();

            $('.rwd-SlideWrapper iframe').each(function(){
                if($(this).attr('src').indexOf('youtube') !== -1){
                    $(this).get(0).contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*')
                } else if($(this).attr('src').indexOf('vimeo') !== -1){
                    try {
                        $f($(this).get(0)).api('pause');
                    } catch (e) {
                        console.error('Make sure you have included froogaloop2 js');
                    }
                }
            });

            $('.rwd-SlideWrapper').removeClass('videoIsPlay');
        };
        $el.onAfterSlide = function(scene){
            $('.rwd-SlideWrapper').removeClass('lightboxOff');

            $('.rslider_iframe_cover').show();

            $('.rwd-SlideWrapper iframe').each(function(){
                if($(this).attr('src').indexOf('youtube') !== -1){
                    $(this).get(0).contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*')
                } else if($(this).attr('src').indexOf('vimeo') !== -1){
                    try {
                        $f($(this).get(0)).api('pause');
                    } catch (e) {
                        console.error('Make sure you have included froogaloop2 js');
                    }
                }
            });

            $('.rwd-SlideWrapper').removeClass('videoIsPlay');

            if(parseInt($('.rwd-SlideWrapper ul').attr('data-autoplay')) === 1){
                $('.rslider_iframe_cover').hide();

                var $iframe = $('.rwd-slide').eq(scene - 1).find('iframe');

                if($('.rwd-slide').eq(scene - 1).hasClass('video_iframe')){
                    if($iframe.attr('src').indexOf('youtube') !== -1){
                        $iframe.get(0).contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*')
                    } else if($iframe.attr('src').indexOf('vimeo') !== -1){
                        try {
                            $f($iframe.get(0)).api('play');
                        } catch (e) {
                            console.error('Make sure you have included froogaloop2 js');
                        }
                    }
                }

                $('.rwd-SlideWrapper').addClass('videoIsPlay');

                $el.pause();
            }
        };
        $el.play = function () {
            $el.goToNextSlide();
            plugin.auto();
        };
        $el.pause = function () {
            clearInterval(interval);
        };
        $el.refresh = function () {
            refresh.init();
        };
        $el.getCurrentSlideCount = function () {
            var sc = scene;
            var ln = $slide.find('.rwd-slide').length,
                cl = $el.find('.clone.left').length;
            if (scene <= cl - 1) {
                sc = ln + (scene - cl);
            } else if (scene >= (ln + cl)) {
                sc = scene - ln - cl;
            } else {
                sc = scene - cl;
            }
            return sc + 1;
        };
        $el.getTotalSlideCount = function () {
            return $slide.find('.rwd-slide').length;
        };
        $el.goToSlide = function (s) {
            scene = (s + $el.find('.clone.left').length - 1);

            $el.mode(false);
            if (settings.gallery === true) {
                plugin.slideThumb();
            }
        };
        $el.destroy = function () {
            if ($el.RSlider) {
                $el.goToPrevSlide = function(){};
                $el.goToNextSlide = function(){};
                $el.mode = function(){};
                $el.play = function(){};
                $el.pause = function(){};
                $el.refresh = function(){};
                $el.getCurrentSlideCount = function(){};
                $el.getTotalSlideCount = function(){};
                $el.goToSlide = function(){};
                $el.onBeforeSlide = function(){};
                $el.onAfterSlide = function(){};
                $el.RSlider = null;
                refresh = {
                    init : function(){}
                };
                $el.parent().parent().find('.rwd-Action, .rwd-Pager').remove();
                $el.removeClass('huge_it_slider iSFade iSSlide isGrab isGrabbing leftEnd right').removeAttr('style').unwrap().unwrap();
                $el.children().removeAttr('style');
                $children.removeClass('rwd-slide active');
                $el.find('.clone').remove();
                $children = null;
                interval = null;
                on = false;
                scene = 0;
            }

        };

        $(window).on('resize orientationchange', function (e) {
            setTimeout(function () {
                if (e.preventDefault) {
                    e.preventDefault();
                } else {
                    e.returnValue = false;
                }

                if(!$('.rwd-SlideWrapper').hasClass('rwd-fullscreen-on')){
                    refresh.init();
                }

                if(settings.view === 'carousel1') {
                    var $h = (settings.maxHeight / settings.maxWidth) * jQuery('.rwd-SlideOuter').width();
                    jQuery('.rwd-SlideOuter').height($h);
                    if (settings.dotsPos === 'top') {
                        jQuery('.rwd-Pager.rwd-pg').css({
                            top: (jQuery('.rwd-SlideWrapper').height() - jQuery('.rwd-SlideOuter').height()) / 2 + 10 + 'px'
                        });
                    } else {
                        jQuery('.rwd-Pager.rwd-pg').css({
                            bottom: (jQuery('.rwd-SlideWrapper').height() - jQuery('.rwd-SlideOuter').height()) / 2 - jQuery('.rwd-SlideWrapper').height() + 15 + 'px'
                        });
                    }
                }
            }, 200);
        });

        return this;
    };

})(jQuery);





//////////////////////////////////////////





(function ($, window, document) {

    'use strict';

    var defaults = {
        maxWidth: 900,
        maxHeight: 700,
        transition: 'random',
        customTransitions: [],
        fallback3d: 'fade',
        perspective: 1000,
        navigation: +hugeitSliderObj.show_arrows,
        thumbMargin: .5,
        autoPlay: true,
        controls: 'dot',
        cropImage: 'stretch',
        delay: 5000,
        transitionDuration: 2000,
        pauseOnHover: true,
        startSlide: 0,
        keyNav: false
    };

    function Slider(elem, settings) {
        this.$slider = $(elem).addClass('huge-it-slider');
        this.settings = $.extend({}, defaults, settings);
        this.$slides = this.$slider.find('> li');
        this.totalSlides = this.$slides.length;
        this.cssTransitions = testBrowser.cssTransitions();
        this.cssTransforms3d = testBrowser.cssTransforms3d();
        this.currentPlace = this.settings.startSlide;
        this.$currentSlide = this.$slides.eq(this.currentPlace);
        this.inProgress = false;
        this.$sliderWrap = this.$slider.wrap('<div class="huge-it-wrap" />').parent();
        this.$sliderBG = this.$slider.wrap('<div class="huge-it-slide-bg" />').parent();
        this.settings.slider = this;

        this.init();
    }

    Slider.prototype.cycling = null;

    Slider.prototype.$slideImages = null;

    Slider.prototype.init = function () {

        var _this = this;

        this.captions();

        (this.settings.transition === 'custom') && (this.nextAnimIndex = -1);

        +this.settings.navigation && this.setArrows();


        this.settings.keyNav && this.setKeys();

        for (var i = 0; i < this.totalSlides; i++) {
            this.$slides.eq(i).addClass('huge-it-slide-' + i);
        }

        this.settings.autoPlay && this.setAutoPlay();

        if (+this.settings.pauseOnHover) {
            this.$slider.hover(function () {

                _this.$slider.addClass('slidePause');
                _this.setPause();

            }, function () {

                _this.$slider.removeClass('slidePause');

                if(!jQuery('.huge-it-wrap').hasClass('isPlayed')){
                    _this.setAutoPlay();
                }

            });
        }

        jQuery('.playSlider').on('click', function () {
            _this.setAutoPlay();
            jQuery('.huge-it-wrap').removeClass('isPlayed');
        });
        jQuery('.pauseSlider').on('click', function () {
            _this.setPause();
            jQuery('.huge-it-wrap').addClass('isPlayed');
        });

        this.$slideImages = this.$slides.find('img:eq(0)').addClass('huge-it-slide-image');

        this.setup();

        var $id = $(this)[0].$currentSlide.context.id;

        jQuery(window).resize(function(){
            _this.cropImage();

            if(_this.settings.controls === 'thumbnail'){
                jQuery('.huge-it-wrap').height(jQuery('#' + $id).height() + +hugeitSliderObj.thumb_height);
            } else {
                jQuery('.huge-it-wrap').height(jQuery('#' + $id).height());
            }

        });

        if(_this.settings.controls === 'thumbnail'){
            jQuery('.huge-it-wrap').height(jQuery('#' + $id).height() + +hugeitSliderObj.thumb_height);
        } else {
            jQuery('.huge-it-wrap').height(jQuery('#' + $id).height());
        }
    };

    Slider.prototype.setup = function () {
        var sliderWidth, sliderHeight;
        sliderWidth = +this.settings.maxWidth;
        sliderHeight = (this.settings.controls === 'thumbnail') ?
        +this.settings.maxHeight + +hugeitSliderObj.thumb_height + 3 * +hugeitSliderObj.slideshow_border_size + 2 * this.settings.thumbMargin
            : +this.settings.maxHeight;
        this.$sliderWrap.css({
            maxWidth: sliderWidth + 'px',
            maxHeight: sliderHeight + 'px'
        });

        switch (this.settings.controls) {
            case 'dot':
                this.setDots();
                break;
            case 'thumbnail':
                this.setThumbs();
                break;
            case 'none':
                break;
        }

        jQuery('.slider-description div').each(function(){
            if(jQuery(this).text().length > 300){
                var text = jQuery(this).text();
                jQuery(this).attr('title', text);
                text = jQuery(this).text().substring(0, 300) + '...';
                jQuery(this).text(text);
            }
        });

        this.cropImage();

        this.$currentSlide.css({'opacity': 1, 'z-index': 2});
    };

    Slider.prototype.cropImage = function(){

        var w = this.settings.maxWidth,
            h = this.settings.maxHeight,
            wT, hT, r, d, mTop, mLeft;

        if(jQuery(window).width() < +this.settings.maxWidth || jQuery(window).height() < +this.settings.maxHeight){
            w = jQuery(window).width();
            h = +this.settings.maxHeight / +this.settings.maxWidth * w;
        }

        if(jQuery('.huge-it-slide-bg').width() < +this.settings.maxWidth || jQuery('.huge-it-slide-bg').height() < +this.settings.maxHeight){
            w = jQuery('.huge-it-slide-bg').width();
        }

        switch (hugeitSliderObj.crop_image) {
            case 'stretch':
                this.$slideImages.css({
                    'width': '100%',
                    'height': h + 'px',
                    'visibility': 'visible',
                    'max-height': 'none'
                });
                break;
            case 'fill':
                this.$slideImages.each(function () {
                    wT = $(this)[0].naturalWidth;
                    hT = $(this)[0].naturalHeight;
                    if ((wT / hT) < (w / h)) {
                        r = w / wT;
                        d = (Math.abs(h - (hT * r))) * 0.5;
                        mTop = '-' + d + 'px';
                        $(this).css({
                            'height': hT * r,
                            'margin-left': 0,
                            'margin-right': 0,
                            'margin-top': mTop,
                            'visibility': 'visible',
                            'width': w,
                            'max-width': 'none',
                            'max-height': 'none'
                        });
                    } else {
                        r = h / hT;
                        d = (Math.abs(w - (wT * r))) * 0.5;
                        mLeft = '-' + d + 'px';
                        $(this).css({
                            'height': h,
                            'margin-left': mLeft,
                            'margin-right': mLeft,
                            'margin-top': 0,
                            'visibility': 'visible',
                            'width': wT * r,
                            'max-width': 'none',
                            'max-height': 'none'
                        });
                    }
                });
                break;
        }
    };

    Slider.prototype.setArrows = function () {
        var _this = this;

        this.$sliderWrap.append('<a href="#" class="huge-it-arrows huge-it-prev"></a><a href="#" class="huge-it-arrows huge-it-next"></a>');

        if(hugeitSliderObj.navigation_type === '17' || hugeitSliderObj.navigation_type === '18' || hugeitSliderObj.navigation_type === '19' || hugeitSliderObj.navigation_type === '20' || hugeitSliderObj.navigation_type === '21'){
            var $_next = '<svg class="next_bg" width="22px" height="22px" fill="#999" viewBox="-333 335.5 31.5 31.5" >' +
                '<path d="M-311.8,340.5c-0.4-0.4-1.1-0.4-1.6,0c-0.4,0.4-0.4,1.1,0,1.6l8,8h-26.6c-0.6,0-1.1,0.5-1.1,1.1s0.5,1.1,1.1,1.1h26.6l-8,8c-0.4,0.4-0.4,1.2,0,1.6c0.4,0.4,1.2,0.4,1.6,0l10-10c0.4-0.4,0.4-1.1,0-1.6L-311.8,340.5z"/>' +
                '</svg>';
            var $_prev = '<svg class="prev_bg" width="22px" height="22px" fill="#999" viewBox="-333 335.5 31.5 31.5" >' +
                '<path d="M-322.7,340.5c0.4-0.4,1.1-0.4,1.6,0c0.4,0.4,0.4,1.1,0,1.6l-8,8h26.6c0.6,0,1.1,0.5,1.1,1.1c0,0.6-0.5,1.1-1.1,1.1h-26.6l8,8c0.4,0.4,0.4,1.2,0,1.6c-0.4,0.4-1.1,0.4-1.6,0l-10-10c-0.4-0.4-0.4-1.1,0-1.6L-322.7,340.5z"/>' +
                '</svg>';
            jQuery('.huge-it-prev').append($_prev);
            jQuery('.huge-it-next').append($_next);

            if(hugeitSliderObj.navigation_type === '21'){
                jQuery('.huge-it-prev').append('<p class="prev_title"></p>');
                jQuery('.huge-it-next').append('<p class="next_title"></p>');
            }

            var $nextIndex, $prevIndex, $nextImg, $prevImg, $nextTitle = '', $prevTitle = '';


            if(hugeitSliderObj.navigation_type !== '17'){
                jQuery('.huge-it-next').hover(function(){
                    if(_this.currentPlace + 1 == _this.totalSlides){
                        $nextIndex = 0;
                    } else {
                        $nextIndex = _this.currentPlace + 1;
                    }

                    $nextImg = jQuery('li.group').eq($nextIndex).find('img').attr('src');
                    $nextTitle = jQuery('li.group').eq($nextIndex).find('img').attr('alt');

                    jQuery(this).find('.next_title').text($nextTitle);
                    jQuery(this).css({
                        backgroundImage: 'url(' + $nextImg + ')',
                        backgroundPosition: 'left center',
                        backgroundSize: '100px 90px',
                        backgroundRepeat: 'no-repeat'

                    });
                }, function(){
                    jQuery(this).find('.next_title').text('');
                    jQuery(this).css({
                        backgroundImage: ''
                    });
                });
                jQuery('.huge-it-prev').hover(function(){
                    if(_this.currentPlace - 1 < 0){
                        $prevIndex = _this.totalSlides - 1;
                    } else {
                        $prevIndex = _this.currentPlace - 1;
                    }

                    $prevImg = jQuery('li.group').eq($prevIndex).find('img').attr('src');
                    $prevTitle = jQuery('li.group').eq($prevIndex).find('img').attr('alt');

                    jQuery(this).find('.prev_title').text($prevTitle);
                    jQuery(this).css({
                        backgroundImage: 'url(' + $prevImg + ')',
                        backgroundPosition: 'right center',
                        backgroundSize: '100px 90px',
                        backgroundRepeat: 'no-repeat'
                    });
                }, function(){
                    jQuery(this).find('.prev_title').text('');
                    jQuery(this).css({
                        backgroundImage: ''
                    });
                });
            } else {
                jQuery('.huge-it-next').hover(function(){
                    if(_this.currentPlace + 1 == _this.totalSlides){
                        $nextIndex = 0;
                    } else {
                        $nextIndex = _this.currentPlace + 1;
                    }
                    $nextImg = jQuery('li.group').eq($nextIndex).find('img').attr('src');
                    jQuery(this).css({
                        backgroundImage: 'url(' + $nextImg + ')',
                        backgroundPosition: 'center center'

                    });
                }, function(){
                    jQuery(this).css({
                        backgroundImage: ''
                    });
                });
                jQuery('.huge-it-prev').hover(function(){
                    if(_this.currentPlace - 1 < 0){
                        $prevIndex = _this.totalSlides - 1;
                    } else {
                        $prevIndex = _this.currentPlace - 1;
                    }

                    $prevImg = jQuery('li.group').eq($prevIndex).find('img').attr('src');
                    jQuery(this).css({
                        backgroundImage: 'url(' + $prevImg + ')',
                        backgroundPosition: 'center center'
                    });
                }, function(){
                    jQuery(this).css({
                        backgroundImage: ''
                    });
                });
            }
        }

        $('.huge-it-next', this.$sliderWrap).on('click', function (e) {
            e.preventDefault();
            _this.next();
        });

        $('.huge-it-prev', this.$sliderWrap).on('click', function (e) {
            e.preventDefault();
            _this.prev();
        });

        if(this.settings.controls === 'thumbnail'){
            this.$sliderWrap.append('<a href="#" class="thumb_arr thumb_prev"></a><a href="#" class="thumb_arr thumb_next"></a>');
        }

        $('.thumb_next').on('click', function (e) {
            e.preventDefault();
            var width = (Math.min(jQuery('.huge-it-slide-bg').width(), +_this.settings.maxWidth) - (2 * +hugeitSliderObj.thumb_count_slides * _this.settings.thumbMargin)) / +hugeitSliderObj.thumb_count_slides + 1,
                position = parseFloat($('.huge-it-thumb-wrap').css('marginLeft')) || 0;

            position = +position.toFixed(4) - +width.toFixed(4);

            if (position >= (_this.totalSlides - hugeitSliderObj.thumb_count_slides) * (-width)) {
                $('.huge-it-thumb-wrap').css({
                    'marginLeft': position + 'px'
                });
            }

            if (_this.currentPlace == 0) {
                $('.huge-it-thumb-wrap').css({
                    'marginLeft': '0'
                });
            }
        });

        $('.thumb_prev').on('click', function (e) {
            e.preventDefault();
            var width = (Math.min(jQuery('.huge-it-slide-bg').width(), +_this.settings.maxWidth) - (2 * +hugeitSliderObj.thumb_count_slides * _this.settings.thumbMargin)) / +hugeitSliderObj.thumb_count_slides + 1,
                position = parseFloat($('.huge-it-thumb-wrap').css('marginLeft')) || 0;

            position = +position.toFixed(4) + +width.toFixed(4);

            if (position <= 0) {
                $('.huge-it-thumb-wrap').css({
                    'marginLeft': position + 'px'
                });
            }

            if (this.currentPlace == _this.totalSlides - 1) {
                position = (_this.totalSlides - hugeitSliderObj.thumb_count_slides) * (-width);
                $('.huge-it-thumb-wrap').css({
                    'marginLeft': position + 'px'
                });
            }
        });
    };

    Slider.prototype.next = function () {
        if (this.settings.transition === 'custom') {
            this.nextAnimIndex++;
        }

        if (this.currentPlace === this.totalSlides - 1) {
            this.transition(0, true);
        } else {
            this.transition(this.currentPlace + 1, true);
        }

        if(jQuery('li.group').eq(this.currentPlace).hasClass('video_iframe') && jQuery('.huge-it-slider').attr('data-autoplay') == 1){
            jQuery('li.group').eq(this.currentPlace).find('.playButton').click();
        }

        var width = (Math.min(jQuery('.huge-it-slide-bg').width(), +this.settings.maxWidth) - (2 * +hugeitSliderObj.thumb_count_slides * this.settings.thumbMargin)) / +hugeitSliderObj.thumb_count_slides + 1;

        $('.huge-it-thumb-wrap').css({
            'marginLeft': -this.currentPlace * width + 'px'
        });

        if(this.totalSlides - +hugeitSliderObj.thumb_count_slides <= this.currentPlace){
            $('.huge-it-thumb-wrap').css({
                'marginLeft': -(this.totalSlides - +hugeitSliderObj.thumb_count_slides) * width + 'px'
            });
        }

        if (this.currentPlace == 0) {
            $('.huge-it-thumb-wrap').css({
                'marginLeft': '0'
            });
        }
    };

    Slider.prototype.prev = function () {
        if (this.settings.transition === 'custom') {
            this.nextAnimIndex--;
        }

        if (this.currentPlace == 0) {
            this.transition(this.totalSlides - 1, false);
        } else {
            this.transition(this.currentPlace - 1, false);
        }

        if(jQuery('li.group').eq(this.currentPlace).hasClass('video_iframe') && jQuery('.huge-it-slider').attr('data-autoplay') == 1){
            jQuery('li.group').eq(this.currentPlace).find('.playButton').click();
        }

        var width = (Math.min(jQuery('.huge-it-slide-bg').width(), +this.settings.maxWidth) - (2 * +hugeitSliderObj.thumb_count_slides * this.settings.thumbMargin)) / +hugeitSliderObj.thumb_count_slides + 1;

        $('.huge-it-thumb-wrap').css({
            'marginLeft': -this.currentPlace * width + 'px'
        });

        if(this.totalSlides - +hugeitSliderObj.thumb_count_slides <= this.currentPlace){
            $('.huge-it-thumb-wrap').css({
                'marginLeft': -(this.totalSlides - +hugeitSliderObj.thumb_count_slides) * width + 'px'
            });
        }
    };

    Slider.prototype.setKeys = function () {
        var _this = this;

        $(document).on('keydown', function (e) {
            if (e.keyCode === 39) {
                _this.next();
            } else if (e.keyCode === 37) {
                _this.prev();
            }
        });
    };

    Slider.prototype.setAutoPlay = function () {
        var _this = this;

        if(!this.$slider.hasClass('slidePause')){
            this.cycling = setTimeout(function () {
                _this.next();
            }, this.settings.delay);
        }
    };

    Slider.prototype.setPause = function () {
        clearTimeout(this.cycling);
    };

    Slider.prototype.setDots = function () {
        var _this = this;

        this.$dotWrap = $('<div class="huge-it-dot-wrap" />').appendTo(this.$sliderWrap);

        for (var i = 0; i < this.totalSlides; i++) {
            var $thumb = $('<a />')
                .attr('href', '#')
                .data('huge-it-num', i);

            $thumb.appendTo(this.$dotWrap);
        }

        this.$dotWrapLinks = this.$dotWrap.find('a');

        this.$dotWrapLinks.eq(this.settings.startSlide).addClass('active');

        this.$dotWrap.on('click', 'a', function (e) {
            e.preventDefault();

            _this.transition(parseInt($(this).data('huge-it-num')));
        });
    };

    Slider.prototype.setThumbs = function () {
        var _this = this,
            width = (Math.min(jQuery('.huge-it-slide-bg').width(), +this.settings.maxWidth) - (2 * +hugeitSliderObj.thumb_count_slides * this.settings.thumbMargin)) / +hugeitSliderObj.thumb_count_slides;

        this.$thumbWrap = $('<div class="huge-it-thumb-wrap" />').appendTo(this.$sliderWrap);

        this.$slider.parents('.huge-it-wrap').find('.huge-it-thumb-wrap').css({
            width: this.totalSlides * (width + 2) + 'px',
            position: 'absolute'
        });

        var k = +this.settings.maxHeight / +this.settings.maxWidth * jQuery(window).width() + +hugeitSliderObj.thumb_height + 1;

        $('.huge-it-wrap').height(k);

        for (var i = 0; i < this.totalSlides; i++) {
            var $thumb = $('<a />')
                .css({
                    width: width + 'px',
                    margin: this.settings.thumbMargin + 'px'
                })
                .attr('href', '#')
                .data('huge-it-num', i);

            this.$slideImages.eq(i).clone()
                .removeAttr('style')
                .appendTo(this.$thumbWrap)
                .wrap($thumb);
        }

        this.$thumbWrapLinks = this.$thumbWrap.find('a');

        this.$thumbWrap.children().last().css('margin-right', -10);

        this.$thumbWrapLinks.eq(this.settings.startSlide).addClass('active');

        this.$thumbWrap.on('click', 'a', function (e) {
            e.preventDefault();

            _this.transition(parseInt($(this).data('huge-it-num')));
        });
    };

    Slider.prototype.captions = function () {
        var _this = this,
            $captions = this.$slides.find('.huge-it-caption');

        $captions.css({
            opacity: 0
        });

        this.$currentSlide.find('.huge-it-caption').css('opacity', 1);

        $captions.each(function () {
            $(this).css({
                transition: 'opacity ' + _this.settings.transitionDuration + 'ms linear',
                backfaceVisibility: 'hidden'
            });
        });
    };

    Slider.prototype.transition = function (slideNum, forward) {
        if (!this.inProgress) {
            if (slideNum !== this.currentPlace) {
                if (typeof forward === 'undefined') {
                    forward = (slideNum > this.currentPlace);
                }

                switch (this.settings.controls) {
                    case 'dot':
                        this.$dotWrapLinks.eq(this.currentPlace).removeClass('active');
                        this.$dotWrapLinks.eq(slideNum).addClass('active');
                        break;
                    case 'thumbnail':
                        this.$thumbWrapLinks.eq(this.currentPlace).removeClass('active');
                        this.$thumbWrapLinks.eq(slideNum).addClass('active');
                        break;
                    case 'none':
                        break;
                }

                this.$nextSlide = this.$slides.eq(slideNum);

                this.currentPlace = slideNum;

                if (jQuery('li.group').eq(this.currentPlace - 1).hasClass('video_iframe') || jQuery('li.group').eq(this.currentPlace).hasClass('video_iframe')) {
                    var streffect = this.settings.transition;
                    if (streffect == "cube_v" || streffect == "cube_h" || streffect == "none" || streffect == "fade") {
                        new Transition(this, this.settings.transition, forward);
                    } else {
                        new Transition(this, 'fade', forward);
                    }
                } else {
                    new Transition(this, this.settings.transition, forward);
                }

            }
        }
    };

    function Transition(Slider, transition, forward) {
        this.Slider = Slider;
        this.Slider.inProgress = true;
        this.forward = forward;
        this.transition = transition;

        if (this.transition === 'custom') {
            this.customAnims = this.Slider.settings.customTransitions;
        }

        if (this.transition === 'custom') {
            var _this = this;
            $.each(this.customAnims, function (i, obj) {
                if ($.inArray(obj, _this.anims) === -1) {
                    _this.customAnims.splice(i, 1);
                }
            });
        }

        this.fallback3d = this.Slider.settings.fallback3d;

        this.init();
    }

    Transition.prototype.fallback = 'fade';

    Transition.prototype.anims = ['cube_h', 'cube_v', 'fade', 'slice_h', 'slice_v', 'slide_h', 'slide_v', 'scale_out', 'scale_in', 'block_scale', 'kaleidoscope', 'fan', 'blind_h', 'blind_v'];

    Transition.prototype.customAnims = [];

    Transition.prototype.init = function () {
        this[this.transition]();
    };

    Transition.prototype.before = function (callback) {
        var _this = this;

        this.Slider.$currentSlide.css('z-index', 2);
        this.Slider.$nextSlide.css({'opacity': 1, 'z-index': 1});

        if (this.Slider.cssTransitions) {
            this.Slider.$currentSlide.find('.huge-it-caption').css('opacity', 0);
            this.Slider.$nextSlide.find('.huge-it-caption').css('opacity', 1);
        } else {
            this.Slider.$currentSlide.find('.huge-it-caption').animate({'opacity': 0}, _this.Slider.settings.transitionDuration);
            this.Slider.$nextSlide.find('.huge-it-caption').animate({'opacity': 1}, _this.Slider.settings.transitionDuration);
        }

        if (typeof this.setup === 'function') {
            var transition = this.setup();

            setTimeout(function () {
                callback(transition);
            }, 20);
        } else {
            this.execute();
        }

        if (this.Slider.cssTransitions) {
            $(this.listenTo).one('webkitTransitionEnd transitionend otransitionend oTransitionEnd mstransitionend', $.proxy(this.after, this));
        }
    };

    Transition.prototype.after = function () {
        this.Slider.$sliderBG.removeAttr('style');
        this.Slider.$slider.removeAttr('style');
        this.Slider.$currentSlide.removeAttr('style');
        this.Slider.$nextSlide.removeAttr('style');

        this.Slider.$currentSlide.css({
            zIndex: 1,
            opacity: 0
        });
        this.Slider.$nextSlide.css({
            zIndex: 2,
            opacity: 1
        });

        if (typeof this.reset === 'function') {
            this.reset();
        }

        if (this.Slider.settings.autoPlay && !jQuery('.huge-it-wrap').hasClass('isPlayed')) {
            clearTimeout(this.Slider.cycling);
            this.Slider.setAutoPlay();
        }

        this.Slider.$currentSlide = this.Slider.$nextSlide;

        this.Slider.inProgress = false;

    };

    Transition.prototype.fade = function () {
        var _this = this;

        if (this.Slider.cssTransitions) {
            this.setup = function () {
                _this.listenTo = _this.Slider.$currentSlide;

                _this.Slider.$currentSlide.css('transition', 'opacity ' + _this.Slider.settings.transitionDuration + 'ms linear');
            };

            this.execute = function () {
                _this.Slider.$currentSlide.css('opacity', 0);
            }
        } else {
            this.execute = function () {
                _this.Slider.$currentSlide.animate({'opacity': 0}, _this.Slider.settings.transitionDuration, function () {
                    _this.after();
                });
            }
        }

        this.before($.proxy(this.execute, this));
    };

    Transition.prototype.cube = function (tz, ntx, nty, nrx, nry, wrx, wry) {
        if (!this.Slider.cssTransitions || !this.Slider.cssTransforms3d) {
            return this[this['fallback3d']]();
        }

        var _this = this;

        this.setup = function () {
            _this.listenTo = _this.Slider.$slider;

            this.Slider.$sliderBG.css('perspective', 1000);

            _this.Slider.$currentSlide.css({
                transform: 'translateZ(' + tz + 'px)',
                backfaceVisibility: 'hidden'
            });

            _this.Slider.$nextSlide.css({
                opacity: 1,
                backfaceVisibility: 'hidden',
                transform: 'translateY(' + nty + 'px) translateX(' + ntx + 'px) rotateY(' + nry + 'deg) rotateX(' + nrx + 'deg)'
            });

            _this.Slider.$slider.css({
                transform: 'translateZ(-' + tz + 'px)',
                transformStyle: 'preserve-3d'
            });
        };

        this.execute = function () {
            _this.Slider.$slider.css({
                transition: 'all ' + _this.Slider.settings.transitionDuration + 'ms ease-in-out',
                transform: 'translateZ(-' + tz + 'px) rotateX(' + wrx + 'deg) rotateY(' + wry + 'deg)'
            });
        };

        this.before($.proxy(this.execute, this));
    };

    Transition.prototype.none = function () {

        this.Slider.settings.transitionDuration = 1;

        if (this.forward) {
            this.cube(1, 1, 0, 0, 0, 0, 0);
        } else {
            this.cube(1, -1, 0, 0, 0, 0, 0);
        }
    };

    Transition.prototype.cube_h = function () {
        var dimension = $(this.Slider.$slides).width() / 2;

        if (this.forward) {
            this.cube(dimension, dimension, 0, 0, 90, 0, -90);
        } else {
            this.cube(dimension, -dimension, 0, 0, -90, 0, 90);
        }
    };

    Transition.prototype.cube_v = function () {
        var dimension = $(this.Slider.$slides).height() / 2;

        if (this.forward) {
            this.cube(dimension, 0, -dimension, 90, 0, -90, 0);
        } else {
            this.cube(dimension, 0, dimension, -90, 0, 90, 0);
        }
    };

    Transition.prototype.grid = function (cols, rows, ro, tx, ty, sc, op) {
        if (!this.Slider.cssTransitions) {
            return this[this['fallback']]();
        }

        var _this = this;

        this.setup = function () {
            var count = (_this.Slider.settings.transitionDuration) / (cols + rows);

            function gridlet(width, height, t, l, top, left, src, imgWidth, imgHeight, c, r) {
                var delay = (c + r) * count;

                return $('<div class="huge-it-gridlet" />').css({
                    width: width,
                    height: height,
                    top: t,
                    left: l,
                    backgroundImage: 'url(' + src + ')',
                    backgroundPosition: '-' + left + 'px -' + top + 'px',
                    backgroundSize: imgWidth + 'px ' + imgHeight + 'px',
                    transition: 'all ' + _this.Slider.settings.transitionDuration + 'ms ease-in-out ' + delay + 'ms',
                    transform: 'none'
                });
            }

            _this.$img = _this.Slider.$currentSlide.find('img.huge-it-slide-image');

            _this.$grid = $('<div />').addClass('huge-it-grid');

            _this.Slider.$currentSlide.prepend(_this.$grid);

            var imgWidth = _this.$img.width(),
                imgHeight = _this.$img.height(),
                imgSrc = _this.$img.attr('src'),
                colWidth = Math.floor(imgWidth / cols),
                rowHeight = Math.floor(imgHeight / rows),
                colRemainder = imgWidth - (cols * colWidth),
                colAdd = Math.ceil(colRemainder / cols),
                rowRemainder = imgHeight - (rows * rowHeight),
                rowAdd = Math.ceil(rowRemainder / rows),
                leftDist = 0,
                l = (_this.$grid.width() - _this.$img.width()) / 2;

            tx = tx === 'auto' ? imgWidth : tx;
            tx = tx === 'min-auto' ? -imgWidth : tx;
            ty = ty === 'auto' ? imgHeight : ty;
            ty = ty === 'min-auto' ? -imgHeight : ty;

            for (var i = 0; i < cols; i++) {
                var t = (_this.$grid.height() - _this.$img.height()) / 2,
                    topDist = 0,
                    newColWidth = colWidth;

                if (colRemainder > 0) {
                    var add = colRemainder >= colAdd ? colAdd : colRemainder;
                    newColWidth += add;
                    colRemainder -= add;
                }

                for (var j = 0; j < rows; j++) {
                    var newRowHeight = rowHeight,
                        newRowRemainder = rowRemainder;

                    if (newRowRemainder > 0) {
                        add = newRowRemainder >= rowAdd ? rowAdd : rowRemainder;
                        newRowHeight += add;
                        newRowRemainder -= add;
                    }

                    _this.$grid.append(gridlet(newColWidth, newRowHeight, t, l, topDist, leftDist, imgSrc, imgWidth, imgHeight, i, j));

                    topDist += newRowHeight;
                    t += newRowHeight;
                }

                leftDist += newColWidth;
                l += newColWidth;
            }

            _this.listenTo = _this.$grid.children().last();

            _this.$grid.show();
            _this.$img.css('opacity', 0);

            _this.$grid.children().first().addClass('huge-it-top-left');
            _this.$grid.children().last().addClass('huge-it-bottom-right');
            _this.$grid.children().eq(rows - 1).addClass('huge-it-bottom-left');
            _this.$grid.children().eq(-rows).addClass('huge-it-top-right');
        };

        this.execute = function () {
            _this.$grid.children().css({
                opacity: op,
                transform: 'rotate(' + ro + 'deg) translateX(' + tx + 'px) translateY(' + ty + 'px) scale(' + sc + ')'
            });
        };

        this.before($.proxy(this.execute, this));

        this.reset = function () {
            _this.$img.css('opacity', 1);
            _this.$grid.remove();
        }
    };

    Transition.prototype.slice_h = function () {
        this.grid(1, 8, 0, 'min-auto', 0, 1, 0);
    };

    Transition.prototype.slice_v = function () {
        this.grid(10, 1, 0, 0, 'auto', 1, 0);
    };

    Transition.prototype.slide_v = function () {
        var dir = this.forward ?
            'min-auto' :
            'auto';

        this.grid(1, 1, 0, 0, dir, 1, 1);
    };

    Transition.prototype.slide_h = function () {
        var dir = this.forward ?
            'min-auto' :
            'auto';

        this.grid(1, 1, 0, dir, 0, 1, 1);
    };

    Transition.prototype.scale_out = function () {
        this.grid(1, 1, 0, 0, 0, 1.5, 0);
    };

    Transition.prototype.scale_in = function () {
        this.grid(1, 1, 0, 0, 0, .5, 0);
    };

    Transition.prototype.block_scale = function () {
        this.grid(8, 6, 0, 0, 0, .6, 0);
    };

    Transition.prototype.kaleidoscope = function () {
        this.grid(10, 8, 0, 0, 0, 1, 0);
    };

    Transition.prototype.fan = function () {
        this.grid(1, 10, 45, 100, 0, 1, 0);
    };

    Transition.prototype.blind_v = function () {
        this.grid(1, 8, 0, 0, 0, .7, 0);
    };

    Transition.prototype.blind_h = function () {
        this.grid(10, 1, 0, 0, 0, .7, 0);
    };

    Transition.prototype.random = function () {
        this[this.anims[Math.floor(Math.random() * this.anims.length)]]();
    };

    Transition.prototype.custom = function () {
        if (this.Slider.nextAnimIndex < 0) {
            this.Slider.nextAnimIndex = this.customAnims.length - 1;
        }
        if (this.Slider.nextAnimIndex === this.customAnims.length) {
            this.Slider.nextAnimIndex = 0;
        }

        this[this.customAnims[this.Slider.nextAnimIndex]]();
    };

    var testBrowser = {
        browserVendors: ['', '-webkit-', '-moz-', '-ms-', '-o-', '-khtml-'],

        domPrefixes: ['', 'Webkit', 'Moz', 'ms', 'O', 'Khtml'],

        testDom: function (prop) {
            var i = this.domPrefixes.length;

            while (i--) {
                if (typeof document.body.style[this.domPrefixes[i] + prop] !== 'undefined') {
                    return true;
                }
            }

            return false;
        },

        cssTransitions: function () {
            if (typeof window.Modernizr !== 'undefined' && Modernizr.csstransitions !== 'undefined') {
                return Modernizr.csstransitions;
            }

            return this.testDom('Transition');
        },

        cssTransforms3d: function () {
            if (typeof window.Modernizr !== 'undefined' && Modernizr.csstransforms3d !== 'undefined') {
                return Modernizr.csstransforms3d;
            }

            if (typeof document.body.style['perspectiveProperty'] !== 'undefined') {
                return true;
            }

            return this.testDom('Perspective');
        }
    };

    $.fn['sliderPlugin'] = function (settings) {
        return this.each(function () {
            if (!$.data(this, 'sliderPlugin')) {
                $.data(this, 'sliderPlugin', new Slider(this, settings));
            }
        });
    }

})(window.jQuery, window, window.document);

jQuery(window).load(function () {
    jQuery('div[class*=slider-loader-]').css({
        display: 'none'
    });
    jQuery('.huge-it-wrap, .rwd-SlideOuter, .huge-it-slider').css({
        opacity: '1'
    });
});