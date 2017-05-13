
import Banner from '../coffee/banner';
import Concert from '../coffee/concert';

$(document).ready(function() {
	var banner = new Banner();
	banner.render();
	Concert.getInstance().load();
});

$(document).bind('scroll', function() {
	var percent = document.body.scrollTop / document.body.clientHeight;
	var fixed = false;
	if (window.devicePixelRatio < 1.6) {
		if (document.body.clientWidth > 640 && percent > 0.19) fixed = true;
		else if (document.body.clientWidth < 640 && percent > 0.14) fixed = true;
	} else {
		if (percent > 0.09) fixed = true;
	}

	if(fixed && !$('#menu').hasClass('fixed')) {
		$('#menu').addClass('fixed')
	} else if(!fixed && $('#menu').hasClass('fixed')) {
		$('#menu').removeClass('fixed')
	}
});
