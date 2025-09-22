$=jQuery.noConflict();

/*!
 * jQuery Cookie Plugin
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */
(function(a){a.cookie=function(g,f,k){if(arguments.length>1&&(!/Object/.test(Object.prototype.toString.call(f))||f===null||f===undefined)){k=a.extend({},k);if(f===null||f===undefined){k.expires=-1}if(typeof k.expires==="number"){var h=k.expires,j=k.expires=new Date();
j.setDate(j.getDate()+h)}f=String(f);return(document.cookie=[encodeURIComponent(g),"=",k.raw?f:encodeURIComponent(f),k.expires?"; expires="+k.expires.toUTCString():"",k.path?"; path="+k.path:"",k.domain?"; domain="+k.domain:"",k.secure?"; secure":""].join(""))}k=f||{};var b=k.raw?function(i){return i}:decodeURIComponent;var c=document.cookie.split("; ");for(var e=0,d;d=c[e]&&c[e].split("=");e++){if(b(d[0])===g){return b(d[1]||"")}}return null}})(jQuery);

/* VTip */

this.vtip=function(){this.xOffset=-10;this.yOffset=10;$(".vtip").unbind().hover(function(e){this.t=this.title;this.title='';this.top=(e.pageY+yOffset);this.left=(e.pageX+xOffset);$('body').append('<p id="vtip"><img id="vtipArrow" />'+this.t+'</p>');$('p#vtip').css("top",this.top+"px").css("left",this.left+"px").fadeIn("slow");},function(){this.title=this.t;$("p#vtip").fadeOut("slow").remove();}).mousemove(function(e){this.top=(e.pageY+yOffset);this.left=(e.pageX+xOffset);$("p#vtip").css("top",this.top+"px").css("left",this.left+"px");});};

jQuery(document).ready(function($){
	vtip();
		
	var itemList = $('#images');

    itemList.sortable({
        update: function(event, ui) {
            $('#loading-animation').show(); // Show the animate loading gif while waiting

            opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'item_sort', // Tell WordPress how to handle this ajax request
                    order: itemList.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                    $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    //alert('There was an error saving the updates');
                    $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            $.ajax(opts);
        }
    }); 
    
});

