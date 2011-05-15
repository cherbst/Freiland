
jQuery(document).ready(function(){
	var topmargin = jQuery('#event-listing > div.post:first').offset().top;
	// count ajax requests for posts
	var postreq = 0;

	// filter posts for given category
	// show 404 if no posts found
	var filterPosts = function(cat,href){
		var allPosts = jQuery('#event-listing > div.post');
		var other = jQuery('#event-listing > div:not(.cat-id-'+cat+')');
		var notfound = jQuery('.error404');
		if ( allPosts.length == other.length ){
			if ( notfound.length == 0 && postreq == 0 ){
				postreq++;
       	 			jQuery.get(href, function(data){
					jQuery('#content').append(jQuery(data).find('.error404'));
					postreq--;
				});
			} else notfound.show();
		}else notfound.hide();
		ec3.reloadCalendar(cat);
		allPosts.show();
		other.hide();
	};

	var getCatId = function(elem){
		var cat = elem.attr('class');
    		var xCat=new RegExp('.*cat-item-([0-9]+)');
		var catid=xCat.exec(cat);
		if(!catid) return null;
		return catid[1];
	};

	var curCat = getCatId(jQuery('.current-menu-item'));

	// get the events of next/prev month
	// and add them to the event listing
	jQuery('#wp-calendar #next > a,' + 
	       '#wp-calendar #prev > a,').live('click',function(){
		if ( postreq > 0 ) return false;
	
		var href = jQuery(this).attr('href');
		var id = jQuery(this).attr('id');
		var reload = false;
		if ( id == 'ec3_next' ){
			reload = !ec3.get_next_cal();
			ec3.go_next();
		}else{
			reload = !ec3.get_prev_cal();
			ec3.go_prev();
		}
		if ( reload ){
			postreq++;
	       	 	jQuery.get(href, function(data){
				var content = jQuery(data).find('#event-listing').contents();
				if ( id == 'ec3_next' ){
					jQuery('#event-listing').append(content);
				}else{
					jQuery('#event-listing').prepend(content);
				}
				filterPosts(curCat);
				postreq--;
			});
		}
		return false;
	}); 

	// scroll to corrsponding post when click on day 
	jQuery('td.ec3_postday > a').live('click',function(){
		var eventDay = jQuery();
		var curDay = jQuery(this).parent();
		while ( eventDay.length == 0 ){
			if ( curDay.hasClass('ec3_eventday') )
				eventDay = curDay;
			else
				eventDay = curDay.nextAll('.ec3_eventday').filter(':visible').first();
			curDay = curDay.parent().next().children().first();
		}
		eventDay = eventDay.children('a');
		var id = eventDay.attr('postids');
		id=id.split(",");
		if(!id) return false;
		id = id[0].trim();
		var post = jQuery('#post-'+id);
		if ( post.length == 0 ) return false;
		var offset = post.offset().top - topmargin;
		jQuery('body').animate({scrollTop:offset},'slow');
		return false;
	});

	// filter posts when clicking on event sub categories
	jQuery('#eventtypes > ul > li,' + 
	       'ul.children > li').live('click',function(){
		curCat = getCatId(jQuery(this));
		if(!curCat) return;
		filterPosts(curCat,ec3.get_current_month_link(getCatId(jQuery(this))));
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery(this).addClass('current-cat');
		return false;
	});
});

