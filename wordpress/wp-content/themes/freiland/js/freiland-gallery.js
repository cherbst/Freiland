
jQuery(document).ready(function(){
	var container = jQuery('<div id="gallery-description"></div>');
	jQuery('#container').append(container);
//	container.hide();
	jQuery('.entry-content > div.gallery-thumb').hover(function(){
		var title = jQuery(this).parent().siblings('.entry-title').clone();
		var description = jQuery(this).siblings().clone();
		container.append(title);
		container.append(description);
		container.find('p > em').hide();
		title.show();
		description.show();
	},function(){
		container.empty();
	});
});
