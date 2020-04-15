(function ($) {

    'use strict';

    function Lightbox(element, options) {
        this.el = element;
        this.$element = $(element);
        this.$body = $('body');
        this.objects = {};
        this.lightboxModul = {};
        this.$item = '';
        this.$cont = '';

        this.$items = this.$element.children().find('a:not(.title_url)');
        this.settings = $.extend({}, this.constructor.defaults, options);

        this.init();

        return this;
    }

    Lightbox.defaults = {
        slideAnimationType: 'effect_1',
        arrows: 'arrows_1',
        speed: 600,
        width: '50%',
        height: '70%',
        videoMaxWidth: '790',
        overlayDuration: 100,
        preload: 10,
        openCloseType: {
            0: 'open_1',
            1: 'close_1'
        }
    };

    Lightbox.prototype.init = function () {
        var $object = this, $openCloseType;

        switch(this.settings.openCloseType){
            case 'none':
                $openCloseType = {
                    0: 'open_0',
                    1: 'close_0'
                };
                break;
            case 'unfold':
                $openCloseType = {
                    0: 'open_1',
                    1: 'close_1'
                };
                break;
            case 'unfold_r':
                $openCloseType = {
                    0: 'open_1_r',
                    1: 'close_1_r'
                };
                break;
            case 'blowup':
                $openCloseType = {
                    0: 'open_2',
                    1: 'close_2'
                };
                break;
            case 'blowup_r':
                $openCloseType = {
                    0: 'open_2_r',
                    1: 'close_2_r'
                };
                break;
            case 'roadrunner':
                $openCloseType = {
                    0: 'open_3',
                    1: 'close_3'
                };
                break;
            case 'roadrunner_r':
                $openCloseType = {
                    0: 'open_3_r',
                    1: 'close_3_r'
                };
                break;
            case 'runner':
                $openCloseType = {
                    0: 'open_4',
                    1: 'close_4'
                };
                break;
            case 'runner_r':
                $openCloseType = {
                    0: 'open_4_r',
                    1: 'close_4_r'
                };
                break;
            case 'rotate':
                $openCloseType = {
                    0: 'open_5',
                    1: 'close_5'
                };
                break;
            case 'rotate_r':
                $openCloseType = {
                    0: 'open_5_r',
                    1: 'close_5_r'
                };
                break;
        }

        this.settings.openCloseType = $openCloseType;

        (($object.settings.preload > $object.$items.length) && ($object.settings.preload = $object.$items.length));

        $object.$items.on('click.rwdcustom', function (event) {
            if(!$('.rwd-SlideWrapper').hasClass('lightboxOff')){
                if(!$('.rwd-SlideWrapper').hasClass('rwd-fullscreen-on')){
                    var $disabled = jQuery('.lSAction a').hasClass('disabled');

                    if($disabled){
                        return false;
                    }

                    event = event || window.event;
                    event.preventDefault ? event.preventDefault() : (event.returnValue = false);

                    $object.index = $object.$items.index(this);

                    if (!$object.$body.hasClass('rwd-on')) {
                        $object.build($object.index);
                        $object.$body.addClass('rwd-on');
                    }
                } else {
                    event.preventDefault();
                }
            }
        });

        $object.$body.on('click', function () {
            $object.$_y_ = window.pageYOffset;
        });
    };

    Lightbox.prototype.build = function (index) {

        var $object = this;

        $object.structure(index);

        $object.lightboxModul['modul'] = new $.fn.lightbox.lightboxModul['modul']($object.el);

        $object.slide(index, false, false);

        $object.addKeyEvents();

        if ($object.$items.length > 1) {
            $object.arrow();
        }

        $object.closeGallery();

        $object.$cont.on('click.rwd-container', function () {
            $object.$cont.removeClass('rwd-hide-items');
        });

        $object.calculateDimensions(index);
    };

    Lightbox.prototype.structure = function (index) {

        var $object = this, list = '', controls = '', i,
            subHtmlCont = '', close = '', template;

        this.$body.append(
            this.objects.overlay = $('<div class="rwd-overlay"></div>')
        );
        this.objects.overlay.css('transition-duration', this.settings.overlayDuration + 'ms');

        for (i = 0; i < this.$items.length; i++) {
            list += '<div class="rwd-item"></div>';
        }

        close = '<span class="rwd-close rwd-icon ' + this.settings.arrows + '"></span>';

        if (this.$items.length > 1) {
            controls = '<div class="rwd-arrows ' + this.settings.arrows + '">' +
                '<div class="rwd-prev rwd-icon"></div>' +
                '<div class="rwd-next rwd-icon"></div>' +
                '</div>';
        }

        template = '<div class="rwd-cont ">' +
            '<div class="rwd-container">' +
            '<div class="cont-inner">' + list + '</div>' +
            '<div class="rwd-toolbar group">' +
            close +
            '</div>' +
            controls +
            '</div>' +
            '</div>';


        switch($object.settings.openCloseType[0]){
            case 'open_1':
            case 'open_2':
            case 'open_3':
            case 'open_4':
            case 'open_5':
            case 'open_1_r':
            case 'open_2_r':
            case 'open_3_r':
            case 'open_4_r':
            case 'open_5_r':
                setTimeout(function(){
                    $object.$cont.addClass('rwd-visible');
                    $('.rwd-container').addClass($object.settings.openCloseType[0]);
                }, 500);
                break;
            default:
                $('.rwd-container').addClass($object.settings.openCloseType[0]);
                setTimeout(function () {
                    $object.$cont.addClass('rwd-visible');
                }, this.settings.overlayDuration);
                break;
        }



        this.$body.append(template);
        this.$cont = $('.rwd-cont');
        this.$item = this.$cont.find('.rwd-item');
        this.$cont.addClass('rwd-use');

        $object.calculateDimensions(index);

        this.$item.eq(this.index).addClass('rwd-current');

        if (this.effectsSupport()) {
            this.$cont.addClass('rwd-support');
        } else {
            this.$cont.addClass('rwd-noSupport');
            this.settings.speed = 0;
        }

        this.$cont.addClass('slider_' + this.settings.slideAnimationType);

        this.$cont.addClass('rwd-show-after-load');

        if (this.effectsSupport()) {
            var $inner = this.$cont.find('.cont-inner');
            $inner.css('transition-timing-function', 'ease');
            $inner.css('transition-duration', this.settings.speed + 'ms');
        }

        $object.objects.overlay.addClass('in');

        this.prevScrollTop = $(window).scrollTop();

        $object.objects.content = $('.rwd-container');

        if(jQuery(window).width() < 768){
            $object.objects.content.css({
                'width': '90%',
                'height': '90%'
            });
        } else {
            $object.objects.content.css({
                'width': $object.settings.width,
                'height': $object.settings.height
            });
        }

        jQuery(window).on('resize', function(){
            if(jQuery(window).width() < 768){
                $object.objects.content.css({
                    'width': '90%',
                    'height': '90%'
                });
            } else {
                $object.objects.content.css({
                    'width': $object.settings.width,
                    'height': $object.settings.height
                });
            }
        });
    };

    Lightbox.prototype.calculateDimensions = function (index) {
        var $object = this, $width, $container;

        $width = $('.rwd-current').height() * 16 / 9;

        if ($width > $object.settings.videoMaxWidth) {
            $width = $object.settings.videoMaxWidth;
        }

        $('.rwd-video-cont ').css({
            'max-width': $width + 'px'
        });

        $container = $('.rwd-container');

        var $left, $top, $prev, $next;

        switch(this.settings.arrows){
            case 'arrows_1':
            case 'arrows_2':
            case 'arrows_3':
                $left = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 9;
                $top = ($container.height() - $object.$item.eq(index).find('.rwd-object').height()) / 2 - 16;
                $prev = ($container.width() - $object.$item.eq(index).find('.rwd-object').width()) / 2;
                $next = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 46;
                break;
            case 'arrows_4':
                $left = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 20;
                $top = ($container.height() - $object.$item.eq(index).find('.rwd-object').height()) / 2 - 10.5;
                $prev = ($container.width() - $object.$item.eq(index).find('.rwd-object').width()) / 2 - 30;
                $next = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 16;
                break;
            case 'arrows_5':
                $left = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 5;
                $top = ($container.height() - $object.$item.eq(index).find('.rwd-object').height()) / 2 - 23.5;
                $prev = ($container.width() - $object.$item.eq(index).find('.rwd-object').width()) / 2 - 28;
                $next = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 20;
                break;
            case 'arrows_6':
                $left = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 24;
                $top = ($container.height() - $object.$item.eq(index).find('.rwd-object').height()) / 2 - 11.5;
                $prev = ($container.width() - $object.$item.eq(index).find('.rwd-object').width()) / 2 - 30;
                $next = ($container.width() + $object.$item.eq(index).find('.rwd-object').width()) / 2 - 17;
                break;
        }

        $('.rwd-toolbar').css({
            'top': $top
        });

        $('.rwd-close').css({
            'position': 'absolute',
            'left': $left
        });

        $('.rwd-prev').css({
            'top': '50%',
            'transform': 'translateY(-15px)',
            'left': $prev
        });

        $('.rwd-next').css({
            'top': '50%',
            'transform': 'translateY(-15px)',
            'left': $next
        });

        $object.$element.on('onBeforeSlide.rwd-container', function (event, prevIndex, index) {
            $('.rwd-close, .rwd-prev, .rwd-next').css({
                'visibility': 'hidden',
                'opacity': '0',
                'transition': 'visibility 0s, opacity 0.5s linear'
            });
        });

        $object.$element.on('onAfterSlide.rwd-container', function (event, prevIndex) {
            setTimeout(function(){
                $('.rwd-close, .rwd-prev, .rwd-next').css({
                    'visibility': 'visible',
                    'opacity': '1'
                });
            }, 700);
        });

    };

    Lightbox.prototype.effectsSupport = function () {
        var transition, root, support;
        support = function () {
            transition = ['transition', 'MozTransition', 'WebkitTransition', 'OTransition', 'msTransition', 'KhtmlTransition'];
            root = document.documentElement;
            for (var i = 0; i < transition.length; i++) {
                if (transition[i] in root.style) {
                    return transition[i] in root.style;
                }
            }
        };

        return support();
    };

    Lightbox.prototype.isVideo = function (src, index) {
        var youtube, vimeo;

        if(src !== undefined){
            if(src.indexOf('youtu') !== -1){
                youtube = src.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/);
            }

            if(src.indexOf('vimeo') !== -1){
                vimeo = src.match(/(videos|video|channels|\.com)\/([\d]+)/);
            }
        }

        if (youtube) {
            return {
                youtube: youtube
            };
        } else if (vimeo) {
            return {
                vimeo: vimeo
            };
        }
    };

    Lightbox.prototype.preload = function (index) {
        for (var i = 1; i <= this.settings.preload; i++) {
            if (i >= this.$items.length - index) {
                break;
            }

            this.loadContent(index + i, false, 0);
        }

        for (var j = 1; j <= this.settings.preload; j++) {
            if (index - j < 0) {
                break;
            }

            this.loadContent(index - j, false, 0);
        }
    };

    Lightbox.prototype.loadContent = function (index, rec, delay) {

        var $object, src, isVideo;

        $object = this;

        function isImg() {
            src = $object.$items.eq(index).attr('href');
            return src.match(/\.(jpg|png|gif)\b/);
        }

        src = $object.$items.eq(index).attr('href');

        isVideo = $object.isVideo(src, index);
        if (!$object.$item.eq(index).hasClass('rwd-loaded')) {
            if (isVideo) {
                $object.$item.eq(index).prepend('<div class="rwd-video-cont "><div class="rwd-video"></div></div>');
                $object.$element.trigger('hasVideo.rwd-container', [index, src]);
            } else {
                $object.$item.eq(index).prepend('<div class="rwd-img-wrap"><img class="rwd-object rwd-image" src="' + src + '" /></div>');
            }

            $object.$element.trigger('onAferAppendSlide.rwd-container', [index]);

            $object.$item.eq(index).addClass('rwd-loaded');
        }

        $object.$item.eq(index).find('.rwd-object').on('load.rwd-container error.rwd-container', function () {

            var speed = 0;
            if (delay) {
                speed = delay;
            }

            setTimeout(function () {
                $object.$item.eq(index).addClass('rwd-complete');
            }, speed);

        });

        if (rec === true) {

            if (!$object.$item.eq(index).hasClass('rwd-complete')) {
                $object.$item.eq(index).find('.rwd-object').on('load.rwd-container error.rwd-container', function () {
                    $object.preload(index);
                });
            } else {
                $object.preload(index);
            }
        }
    };

    Lightbox.prototype.slide = function (index, fromSlide, fromThumb) {
        var $object, prevIndex;

        $object = this;
        prevIndex = this.$cont.find('.rwd-current').index();

        var length = this.$item.length,
            next = false,
            prev = false;

        this.$element.trigger('onBeforeSlide.rwd-container', [prevIndex, index, fromSlide, fromThumb]);

        $object.$cont.addClass('rwd-no-trans');

        this.$item.removeClass('rwd-prev-slide rwd-next-slide');
        if (!fromSlide) {

            if (index < prevIndex) {
                prev = true;
                if ((index === 0) && (prevIndex === length - 1) && !fromThumb) {
                    prev = false;
                    next = true;
                }
            } else if (index > prevIndex) {
                next = true;
                if ((index === length - 1) && (prevIndex === 0) && !fromThumb) {
                    prev = true;
                    next = false;
                }
            }

            if (prev) {
                this.$item.eq(index).addClass('rwd-prev-slide');
                this.$item.eq(prevIndex).addClass('rwd-next-slide');
            } else if (next) {
                this.$item.eq(index).addClass('rwd-next-slide');
                this.$item.eq(prevIndex).addClass('rwd-prev-slide');
            }

            setTimeout(function () {
                $object.$item.removeClass('rwd-current');

                $object.$item.eq(index).addClass('rwd-current');

                $object.$cont.removeClass('rwd-no-trans');
            }, 50);
        } else {

            var slidePrev = index - 1;
            var slideNext = index + 1;

            if ((index === 0) && (prevIndex === length - 1)) {

                slideNext = 0;
                slidePrev = length - 1;
            } else if ((index === length - 1) && (prevIndex === 0)) {

                slideNext = 0;
                slidePrev = length - 1;
            }

            this.$item.removeClass('rwd-prev-slide rwd-current rwd-next-slide');
            $object.$item.eq(slidePrev).addClass('rwd-prev-slide');
            $object.$item.eq(slideNext).addClass('rwd-next-slide');
            $object.$item.eq(index).addClass('rwd-current');
        }

        $object.loadContent(index, true, $object.settings.overlayDuration);

        $object.$element.trigger('onAfterSlide.rwd-container', [prevIndex, index, fromSlide, fromThumb]);

        $object.calculateDimensions(index);

        $(window).on('resize.rwd-container', function () {
            $object.calculateDimensions(index);
        });
    };

    Lightbox.prototype.goToNextSlide = function (fromSlide) {
        var $object = this;
        if (($object.index + 1) < $object.$item.length) {
            $object.index++;
            $object.slide($object.index, fromSlide, false);
        } else {
            $object.index = 0;
            $object.slide($object.index, fromSlide, false);
        }
    };

    Lightbox.prototype.goToPrevSlide = function (fromSlide) {
        var $object = this;

        if ($object.index > 0) {
            $object.index--;
            $object.slide($object.index, fromSlide, false);
        } else {
            $object.index = $object.$items.length - 1;
            $object.slide($object.index, fromSlide, false);
        }
    };

    Lightbox.prototype.addKeyEvents = function () {
        var $object = this;

        if (this.$items.length > 1) {
            $(window).on('keyup.rwd-container', function (e) {
                if ($object.$items.length > 1) {
                    if (e.keyCode === 37) {
                        e.preventDefault();
                        $object.goToPrevSlide();
                    }

                    if (e.keyCode === 39) {
                        e.preventDefault();
                        $object.goToNextSlide();
                    }
                }
            });
        }

        $(window).on('keydown.rwd-container', function (e) {
            if (e.keyCode === 27) {
                e.preventDefault();
                $object.destroy();
            }
        });
    };

    Lightbox.prototype.arrow = function () {
        var $object = this;
        this.$cont.find('.rwd-prev').on('click.rwd-container', function () {
            $object.goToPrevSlide();
        });

        this.$cont.find('.rwd-next').on('click.rwd-container', function () {
            $object.goToNextSlide();
        });
    };

    Lightbox.prototype.closeGallery = function () {
        var $object = this, mousedown = false;

        this.$cont.find('.rwd-close').on('click.rwd-container', function () {
            $object.destroy();
        });

        $object.$cont.on('mousedown.rwd-container', function (e) {

            mousedown = ($(e.target).is('.rwd-cont') || $(e.target).is('.rwd-item ') || $(e.target).is('.rwd-img-wrap'));

        });

        $object.$cont.on('mouseup.rwd-container', function (e) {

            if ($(e.target).is('.contInner') || $(e.target).is('.rwd-cont') || $(e.target).is('.rwd-item ') || $(e.target).is('.rwd-img-wrap') && mousedown) {
                if (!$object.$cont.hasClass('rwd-dragEvent')) {
                    $object.destroy();
                }
            }

        });
    };

    Lightbox.prototype.destroy = function (d) {

        var $object = this, $time;

        $('.rwd-container').removeClass(this.settings.openCloseType[0]).addClass(this.settings.openCloseType[1]);

        switch(this.settings.openCloseType[1]){
            case 'close_1':
            case 'close_1_r':
                $time = 1000;
                break;
            case 'close_2':
            case 'close_2_r':
                $time = 300;
                break;
            case 'close_3':
            case 'close_4':
            case 'close_3_r':
            case 'close_4_r':
                $time = 340;
                break;
            case 'close_5':
            case 'close_5_r':
                $time = 250;
                break;
        }

        setTimeout(function(){
            clearInterval($object.interval);

            $object.$body.removeClass('rwd-on');

            $(window).scrollTop($object.prevScrollTop);

            if (d) {
                $.removeData($object.el, 'lightbox');
            }

            ($object.settings.socialSharing && (window.location.hash = ''));

            if ($object.$cont) {
                $object.$cont.removeClass('rwd-visible');
            }

            $object.objects.overlay.removeClass('in');

            setTimeout(function () {
                if ($object.$cont) {
                    $object.$cont.remove();
                }

                $object.objects.overlay.remove();

            }, $object.settings.overlayDuration + 50);

            window.scrollTo(0, $object.$_y_);
        }, $time);
    };

    $.fn.lightbox = function (options) {
        return this.each(function () {
            if (!$.data(this, 'lightbox')) {
                $.data(this, 'lightbox', new Lightbox(this, options));
            }
        });
    };

    $.fn.lightbox.lightboxModul = {};


    var Modul = function (element) {

        this.dataL = $(element).data('lightbox');
        this.$element = $(element);
        this.init();

        this.$el = $(element);

        return this;
    };

    Modul.prototype.init = function () {
        var $object = this;

        $object.dataL.$element.one('hasVideo.rwd-container', function (event, index, src) {
            $object.dataL.$item.eq(index).find('.rwd-video').append($object.loadVideo(src, 'rwd-object', index));
        });

        $object.dataL.$element.on('onAferAppendSlide.rwd-container', function (event, index) {
            $object.dataL.$item.eq(index).find('.rwd-video-cont').css({
                'max-width': '790px'
            });
        });

        $object.dataL.$element.on('onBeforeSlide.rwd-container', function (event, prevIndex, index) {

            var $videoSlide = $object.dataL.$item.eq(prevIndex),
                youtubePlayer = $videoSlide.find('.rwd-youtube').get(0),
                vimeoPlayer = $videoSlide.find('.rwd-vimeo').get(0);

            if (youtubePlayer) {
                youtubePlayer.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            } else if (vimeoPlayer) {
                try {
                    $f(vimeoPlayer).api('pause');
                } catch (e) {
                    console.error('Make sure you have included froogaloop2 js');
                }
            }

            var src;
            src = $object.dataL.$items.eq(index).attr('href');

            var isVideo = $object.dataL.isVideo(src, index) || {};
        });

        $object.dataL.$element.on('onAfterSlide.rwd-container', function (event, prevIndex) {
            $object.dataL.$item.eq(prevIndex).removeClass('rwd-video-playing');
        });
    };

    Modul.prototype.loadVideo = function (src, addClass, index) {
        var video = '',
            autoplay = 0,
            a = '',
            isVideo = this.dataL.isVideo(src, index) || {};

        if (isVideo.youtube) {

            a = '?wmode=opaque&autoplay=' + autoplay + '&enablejsapi=1';

            video = '<iframe class="rwd-video-object rwd-youtube ' + addClass + '" width="560" height="315" src="//www.youtube.com/embed/' + isVideo.youtube[7] + a + '" frameborder="0" allowfullscreen></iframe>';

        } else if (isVideo.vimeo) {

            a = '?autoplay=' + autoplay + '&api=1';

            video = '<iframe class="rwd-video-object rwd-vimeo ' + addClass + '" width="560" height="315"  src="//player.vimeo.com/video/' + isVideo.vimeo[2] + a + '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

        }

        return video;
    };

    $.fn.lightbox.lightboxModul.modul = Modul;

})(jQuery);