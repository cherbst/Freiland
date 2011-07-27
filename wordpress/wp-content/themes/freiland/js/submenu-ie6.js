jQuery(document).ready(function(){
	jQuery('#menu-main-menu li').hover(
		function(){
			jQuery(this).find('ul.children').show();
		},
		function(){
			jQuery(this).find('ul.children').hide();
		}
	);
});
