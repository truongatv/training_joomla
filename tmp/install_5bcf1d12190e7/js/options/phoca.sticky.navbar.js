jQuery(document).ready(function() {
	
	var phNavbar 			= jQuery("#g-navigation");
	
	var phNavbarMobile		= jQuery(".g-offcanvas-toggle");
	
	var phSticky 			= phNavbar.offset().top;
	
	if (jQuery("#g-showcase").css("marginTop")) {
		var phMain				= jQuery("#g-showcase");
		var phMainMarginTop		= phMain.css("marginTop").replace('px', '');
		
	} else if (jQuery("#g-maintop").css("marginTop")) {
		var phMain				= jQuery("#g-maintop");
		var phMainMarginTop		= phMain.css("marginTop").replace('px', '');
	} else if (jQuery("#g-main").css("marginTop")) {
		var phMain				= jQuery("#g-main");
		var phMainMarginTop		= phMain.css("marginTop").replace('px', '');
	} else {
		var phMain				= jQuery("#g-main");
		var phMainMarginTop		= 0;
	}
	//var phMainMarginTop		= jQuery("#g-main").css("marginTop").replace('px', '');
	var phNavHeight 		= phNavbar.height();
	var phMainMarginTopDefault= phMain.css("marginTop").replace('px', '');
	
	/* Smooth scrolling in Firefox */
	/*phNavbar.height(phNavHeight);*/
	phNavbar.css('max-height',  phNavHeight + 'px');
		
	function phSetSticky() {
		
		
		if (window.pageYOffset >= phSticky) {
			
		
			var phNavHeight 				= phNavbar.height();
			
			
			var phMainMarginTopNavHeight 	= parseInt(phNavHeight) + parseInt(phMainMarginTop);
			phMain.css("marginTop", phMainMarginTopNavHeight);
			
			phNavbar.addClass("ph-sticky");
			/*phNavbarMobile.addClass("ph-sticky");
			phNavbar.removeClass("ph-stickyoff");
			phNavbarMobile.removeClass("ph-stickyoff");*/
			
			
		} else {
			
			
			
			var phMainMarginTopNavHeight 	= phMainMarginTopDefault;
			phMain.css("marginTop", phMainMarginTopNavHeight);
			
			
			phNavbar.removeClass("ph-sticky");
			/*phNavbarMobile.removeClass("ph-sticky");
			phNavbar.addClass("ph-stickyoff");
			phNavbarMobile.addClass("ph-stickyoff");*/
			
		}
		
		if (window.pageYOffset > 0) {
			phNavbarMobile.addClass("ph-sticky");
		} else {
			phNavbarMobile.removeClass("ph-sticky");
		}
	}
	
	window.onscroll = function() {
		phSetSticky();
	}
})