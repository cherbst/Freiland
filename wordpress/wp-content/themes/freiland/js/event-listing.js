
jQuery(document).ready(function(){
	jQuery('#eventtypes > ul > li,' + 
	       'ul.children > li').live('click',function(){
		var cat = jQuery(this).attr('class');
    		var xCat=new RegExp('.*cat-item-([0-9]+)');
		var catid=xCat.exec(cat);
		if(!catid) return;
		var allPosts = jQuery('#event-listing > div.post');
		var other = jQuery('#event-listing > div:not(.cat-id-'+catid[1]+')');
		var notfound = jQuery('.error404');
		if ( allPosts.length == other.length ){
			if ( notfound.length == 0 ){
				var href = jQuery(this).children('a').attr('href');
       	 			jQuery.get(href, function(data){
					jQuery('#content').append(jQuery(data).find('.error404'));
				});
			} else notfound.show();
		}else notfound.hide();

		allPosts.show();
		other.hide();
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery(this).addClass('current-cat');
		ec3.reloadCalendar(catid[1]);
		return false;
	});
});

