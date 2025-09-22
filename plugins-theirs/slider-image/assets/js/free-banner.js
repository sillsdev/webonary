jQuery(document).ready(function() {
	jQuery(".close_banner").on("click",function(){
        jQuery(".free_version_banner").css("display", "none");
        jQuery(".wrap").css("border", "0px");
		hugeitSliderSetCookie( 'hugeitSliderShowBanner', 'no', {expires:86400} );
	});
});

function hugeitSliderSetCookie(name, value, options) {
	options = options || {};

	var expires = options.expires;

	if (typeof expires === "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}


	if(typeof value === "object"){
		value = JSON.stringify(value);
	}
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;

	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}

	document.cookie = updatedCookie;
}