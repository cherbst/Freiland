jQuery(document).ready(function(){
	// load transparency fix
	jQuery('#upper-transparency').supersleight();
	jQuery('#lower-transparency').supersleight();
	// show/hide submenu
	jQuery('#menu-main-menu li').hover(
		function(){
			jQuery(this).find('ul.children').show();
		},
		function(){
			jQuery(this).find('ul.children').hide();
		}
	);
	// set class attribute from link title
	jQuery('#menu-extern li a').each(function(){
		jQuery(this).addClass(jQuery(this).attr('title'));
	});
});
