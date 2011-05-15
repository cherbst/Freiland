
jQuery(document).ready(function(){
	var topmargin = jQuery('#event-listing > div.post:first').offset().top;
	// count ajax requests for posts
	var postreq = 0;
	var reload = false;
	var newMonthHref;
	var scroll = false;
	var prev_cal = null;
	var next_cal = null;
	var next_month_post = jQuery();
	var prev_month_post = jQuery();

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

	var getPostFromCalDay = function(elem){
		var eventDay = jQuery();
		var curDay = elem;
		while ( eventDay.length == 0 && curDay.length!=0 ){
			if ( curDay.hasClass('ec3_eventday') )
				eventDay = curDay;
			else
				eventDay = curDay.nextAll('.ec3_eventday').filter(':visible').first();
			curDay = curDay.parent().next().children().first();
		}
		if ( eventDay.length == 0 ) return jQuery();
		eventDay = eventDay.children('a');
		var id = eventDay.attr('postids');
		id=id.split(",");
		if(!id) return jQuery();
		id = id[0].trim();
		return jQuery('#post-'+id);
	};
	
	var scrollTo = function(elem,duration){
		if ( duration === false ) return;
		var post = getPostFromCalDay(elem);
		if ( post.length == 0 ) return false;
		var offset = post.offset().top - topmargin;
		jQuery('body').animate({scrollTop:offset},duration);
	};

	// scroll to next active event
	scrollTo(jQuery('#today'),0);

	getEventDay = function(curCal,first){
		var days = jQuery(curCal).find('td.ec3_eventday');
		if ( days.length == 0 ) return jQuery();
		return (first?days.first():days.last());
	}

	scrollToMonth = function(curCal,scroll){
		if ( scroll === false ) return;
		scrollTo(getEventDay(curCal,requestNextMonth),scroll);
	};

	loadNewEvents = function(curCal){
		// update prev/next cal
		next_cal = ec3.get_next_cal();
		next_month_post = getPostFromCalDay(getEventDay(next_cal,true));
		prev_cal = ec3.get_prev_cal();
		prev_month_post = getPostFromCalDay(getEventDay(prev_cal,false));
		if ( reload ){
			postreq++;
	       	 	jQuery.get(newMonthHref, function(data){
				var content = jQuery(data).find('#event-listing').contents();
				if ( requestNextMonth ){
					jQuery('#event-listing').append(content);
				}else{
					jQuery('#event-listing').prepend(content);
				}
				filterPosts(curCat);
				scrollToMonth(curCal,scroll);
				postreq--;
			});
		}else scrollToMonth(curCal,scroll);
	};

	// get the events of next/prev month
	// and add them to the event listing
	jQuery('#wp-calendar #next > a,' + 
	       '#wp-calendar #prev > a,').live('click',function(){
		if ( postreq > 0 ) return false;

		newMonthHref = jQuery(this).attr('href');
		var id = jQuery(this).attr('id');
		requestNextMonth = ( id == 'ec3_next' );
		scroll = true;
		if ( requestNextMonth ){
			reload = !next_cal;
			ec3.go_next(loadNewEvents);
		}else{
			reload = !prev_cal;
			ec3.go_prev(loadNewEvents);
		}
		return false;
	}); 

	// scroll to corrsponding post when click on day 
	jQuery('td.ec3_postday > a').live('click',function(){
		scrollTo(jQuery(this).parent());
		return false;
	});

	// filter posts when clicking on event sub categories
	jQuery('#eventtypes > ul > li,' + 
	       'ul.children > li').live('click',function(){
		curCat = getCatId(jQuery(this));
		ec3.set_cur_cat(curCat);
		if(!curCat) return;
		filterPosts(curCat,ec3.get_current_month_link(getCatId(jQuery(this))));
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery(this).addClass('current-cat');
		return false;
	});

	// scroll to first of month when clicking on month link
	jQuery('#wp-calendar caption > a').live('click',function(){
		scrollToMonth(jQuery(this).closest('table'));
		return false;
	});

	// update calendar when scrolling through events
	jQuery(document).scroll(function(e){
		var elem = jQuery(window);
		var obj = jQuery('body');

		if ( next_month_post.length != 0 ) {
			if ( next_month_post.offset().top-topmargin < obj.scrollTop() ){
				scroll = false;
				reload = false;
				// load next cal month 
				ec3.go_next(loadNewEvents);
			}
		}

		if ( prev_month_post.length != 0 ) {
			// only go to prev month if there was no reload before
			if ( prev_month_post.offset().top-topmargin > obj.scrollTop() && !reload ||
			     prev_month_post.offset().top-(elem.height()-200) > obj.scrollTop() ) {
				scroll = false;
				reload = false;
				// load prev cal month 
				ec3.go_prev(loadNewEvents);
			}
		}

 		if (jQuery(document).height() <= (obj.scrollTop() + elem.height())) {
			newMonthHref = jQuery('#ec3_next:last').attr('href');
			requestNextMonth = true;
			scroll = false;
			reload = true;
			ec3.go_next(loadNewEvents);
		}else if (obj.scrollTop() == 0 ){
			newMonthHref = jQuery('#ec3_prev:first').attr('href');
			requestNextMonth = false;
			scroll = 0;
			reload = true;
			ec3.go_prev(loadNewEvents);
		} 

	});
});

