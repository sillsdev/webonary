/*if(typeof HUGEIT_YT_IFRAMES == "undefined") {
var Froogaloop=function(){function e(a){return new e.fn.init(a)}function h(a,c,b){if(!b.contentWindow.postMessage)return!1;var f=b.getAttribute("src").split("?")[0],a=JSON.stringify({method:a,value:c});"//"===f.substr(0,2)&&(f=window.location.protocol+f);b.contentWindow.postMessage(a,f)}function j(a){var c,b;try{c=JSON.parse(a.data),b=c.event||c.method}catch(f){}"ready"==b&&!i&&(i=!0);if(a.origin!=k)return!1;var a=c.value,e=c.data,g=""===g?null:c.player_id;c=g?d[g][b]:d[b];b=[];if(!c)return!1;void 0!==a&&b.push(a);e&&b.push(e);g&&b.push(g);return 0<b.length?c.apply(null,b):c.call()}function l(a,c,b){b?(d[b]||(d[b]={}),d[b][a]=c):d[a]=c}var d={},i=!1,k="";e.fn=e.prototype={element:null,init:function(a){"string"===typeof a&&(a=document.getElementById(a));this.element=a;a=this.element.getAttribute("src");"//"===a.substr(0,2)&&(a=window.location.protocol+a);for(var a=a.split("/"),c="",b=0,f=a.length;b<f;b++){if(3>b)c+=a[b];else break;2>b&&(c+="/")}k=c;return this},api:function(a,c){if(!this.element||!a)return!1;var b=this.element,f=""!==b.id?b.id:null,d=!c||!c.constructor||!c.call||!c.apply?c:null,e=c&&c.constructor&&c.call&&c.apply?c:null;e&&l(a,e,f);h(a,d,b);return this},addEvent:function(a,c){if(!this.element)return!1;var b=this.element,d=""!==b.id?b.id:null;l(a,c,d);"ready"!=a?h("addEventListener",a,b):"ready"==a&&i&&c.call(null,d);return this},removeEvent:function(a){if(!this.element)return!1;var c=this.element,b;a:{if((b=""!==c.id?c.id:null)&&d[b]){if(!d[b][a]){b=!1;break a}d[b][a]=null}else{if(!d[a]){b=!1;break a}d[a]=null}b=!0}"ready"!=a&&b&&h("removeEventListener",a,c)}};e.fn.init.prototype=e.fn;window.addEventListener?window.addEventListener("message",j,!1):window.attachEvent("onmessage",j);return window.Froogaloop=window.$f=e}();
}*/
if(typeof HUGEIT_VIMEOS== "undefined") { HUGEIT_VIMEOS = {};
;(function($, window) {

    var vimeoJqueryAPI = {

        //catches return messages when methods like getVolume are called. 
        //counter is if multiple calls are made before one returns.
        catchMethods : {methodreturn:[], count:0},

        //This kicks things off on window message event
        init : function(d){

            var vimeoVideo,
                vimeoAPIurl,
                data;

            //is this window message from vimeo?
            if(!d.originalEvent.origin.match(/vimeo/g)){
                return;
            }

            //make sure data was sent
            if(!("data" in d.originalEvent)){
                return;
            }

            //store data as JSON object
            data = $.type(d.originalEvent.data) === "string" ? $.parseJSON(d.originalEvent.data) : d.originalEvent.data;

            //make sure data is not blank
            if(!data){
                return;
            }

            //get the id of this vimeo video, hopefully they set it.
            vimeoVideo = this.setPlayerID(data);

            //check to see if player_ids were set in query string. If not, wait until next message comes through.
            if(vimeoVideo.length){

                vimeoAPIurl  = this.setVimeoAPIurl(vimeoVideo);

                //If this is an event message, like ready or paused
                if(data.hasOwnProperty("event"))
                    this.handleEvent(data, vimeoVideo, vimeoAPIurl);

                //IF this is a return event message, like getVolume or getCurrentTime
                if(data.hasOwnProperty("method"))
                    this.handleMethod(data, vimeoVideo, vimeoAPIurl);

            }

        },

        setPlayerID : function(d){

            return $("iframe[src*=" + d.player_id + "]");

        },

        setVimeoAPIurl : function(d){

            //prepend vimeo url with proper protocol
            if(d.attr('src').substr(0, 4) !== 'http'){
                return 'https:'+d.attr('src').split('?')[0];
            } else {
                return d.attr('src').split('?')[0];
            }
        },

        handleMethod : function(d, vid, api){

            //If the message is returned from a method call, store it for later.
            this.catchMethods.methodreturn.push(d.value);

        },

        handleEvent : function(d, vid, api){
            switch (d.event.toLowerCase()) {
                case 'ready':

                    //Go through all events attached to this element, and set an event listener
                    for(var prop in $._data(vid[0], "events")){
                        if(prop.match(/loadProgress|playProgress|play|pause|finish|seek|cuechange/)){
                            vid[0].contentWindow.postMessage(JSON.stringify({method: 'addEventListener', value: prop}), api);
                        }
                    }

                    //if methods are sent before video is ready, call them now
                    if(vid.data("vimeoAPICall")){
                        var vdata = vid.data("vimeoAPICall");
                        for(var i=0; i< vdata.length; i++){
                            vid[0].contentWindow.postMessage(JSON.stringify(vdata[i].message), vdata[i].api);
                        }
                        vid.removeData("vimeoAPICall");
                    }

                    //this video is ready
                    vid.data("vimeoReady", true);
                    vid.triggerHandler("ready");

                    break;

                case 'seek':
                    vid.triggerHandler("seek", [d.data]);
                    break;

                case 'loadprogress':
                    vid.triggerHandler("loadProgress", [d.data]);
                    break;

                case 'playprogress':
                    vid.triggerHandler("playProgress", [d.data]);
                    break;

                case 'pause':
                    vid.triggerHandler("pause");
                    break;

                case 'finish':
                    vid.triggerHandler("finish");
                    break;

                case 'play':
                    vid.triggerHandler("play");
                    break;

                case 'cuechange':
                    vid.triggerHandler("cuechange");
                    break;
            }
        }
    };

    jQuery(document).ready(function(){

        //go through every iframe with "vimeo.com" in src attribute, and verify it has "player_id" query string
        $("iframe[src*='vimeo.com']").each(function(index){

            //save the current src attribute
            var url = $(this).attr('src');

            //if they haven't added "player_id" in their query string, let's add one.
            if(url.match(/player_id/g) === null){

                //is there already a query string? If so, use &, otherwise ?. 
                var firstSeperator = (url.indexOf('?') === -1 ? '?' : '&');

                //setup a serialized player_id with jQuery (use an unusual name in case someone manually sets the same name)
                var param = $.param({"api": 1, "player_id": "vvvvimeoVideo-" + index});
                
                //reload the vimeo videos that don't have player_id
                $(this).attr("src", url + firstSeperator + param);

            } 

        });
    });
    

    //this is what kicks things off. Whenever Vimeo sends window message to us, was check to see what it is.
    $(window).on("message", function(e){ vimeoJqueryAPI.init(e); });


    /**
     *  Vimeo jQuery method plugin
     *
     * @param element {jQuery Object} The element this was called on (verifies it's an iframe)
     * @param option1 {string} The method to send to vimeo.
     * @param option2 {string|function} If a string, it's the value (i.e. setVolume 2) otherwise, it's a callback function
     */
    $.vimeo = function(element, option1, option2) {

        var message = {},
            catchMethodLength = vimeoJqueryAPI.catchMethods.methodreturn.length;

        if(typeof option1 === "string")  
            message.method = option1;

        if(typeof option2 !== undefined && typeof option2 !== "function") 
            message.value  = option2;

        //call method, but check if video was ready, otherwise cue it up with jQuery data to be called when video is ready
        if(element.prop("tagName").toLowerCase() === 'iframe' && message.hasOwnProperty("method")){
            if(element.data("vimeoReady")){
                element[0].contentWindow.postMessage(JSON.stringify(message), vimeoJqueryAPI.setVimeoAPIurl(element));
            } else {
                var _data = element.data("vimeoAPICall") ? element.data("vimeoAPICall") : [];
                _data.push({message:message, api:vimeoJqueryAPI.setVimeoAPIurl(element)});
                element.data("vimeoAPICall", _data);
            }
        }

        //If this method will return data, (starts with "get") then use callback once return message comes through
        if((option1.toString().substr(0, 3) === "get" || option1.toString() === "paused") && typeof option2 === "function"){
            (function(cml, func, i){
                var interval = window.setInterval(function(){

                    if(vimeoJqueryAPI.catchMethods.methodreturn.length != cml){
                        window.clearInterval(interval);
                        func(vimeoJqueryAPI.catchMethods.methodreturn[i]);
                    }
                }, 10);
            })(catchMethodLength, option2, vimeoJqueryAPI.catchMethods.count);
            vimeoJqueryAPI.catchMethods.count++;
        } 
        return element;
    };

    $.fn.vimeo = function(option1, option2) {
            return $.vimeo(this, option1, option2);
    };


})(jQuery, window);
}