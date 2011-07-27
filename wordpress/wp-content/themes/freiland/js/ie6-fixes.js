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
	// set thumbnail size on hover in gallery pages
	jQuery('.category-images #content div img,' +
	       '.single-format-gallery #content div img').hover(
		function(){
			jQuery(this).css( {'width' : '80',
					   'height': '75',
					   'padding-left'  : '-=5',
					   'padding-right' : '-=5',
					   'padding-top'   : '-=5',
					   'padding-bottom': '-=5'});
		},
		function(){
			jQuery(this).css( {'width' : '70',
					   'height': '65',
					   'padding-left'  : '+=5',
					   'padding-right' : '+=5',
					   'padding-top'   : '+=5',
					   'padding-bottom': '+=5'});
		}
	);
});
