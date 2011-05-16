
jQuery(document).ready(function(){
	var topmargin = jQuery('#event-listing > div.post:first').offset().top;
	// count ajax requests for posts
	var postreq = 0;
	var next_href = jQuery('#ec3_next:first').attr('href');
	var prev_href = jQuery('#ec3_prev:first').attr('href');
	var curPost;
	var updateCal = true;

	var debug = function(text){	
		jQuery('#topright > span').html(text);
	};

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
		var hidden = allPosts.filter(':hidden').length;
		allPosts.show();
		other.hide();
		// post listing has changed
		if ( other.length > 0 || hidden > 0 ){
			var newPost = curPost;
			// select nearest visible post
			if ( newPost.filter(':visible').length == 0 ){
				var nextPost = newPost.nextAll(':visible').first();
				var prevPost = newPost.prevAll(':visible').first();

				if ( nextPost.length == 0 )
					newPost = prevPost;
				else if ( prevPost.length == 0 )
					newPost = nextPost;
				else if ( newPost.prevAll().index(prevPost) < newPost.nextAll().index(nextPost) )
					newPost = prevPost;
				else
					newPost = nextPost;

				curPost = newPost;
			}
			scrollToPost(curPost,0,function(){
				updateCalendar(jQuery('#wp-calendar > table').filter(':visible'));
			});
		}
	};

	// return the categroy id of this element
	var getCatId = function(elem){
		var cat = elem.attr('class');
    		var xCat=new RegExp('.*cat-item-([0-9]+)');
		var catid=xCat.exec(cat);
		if(!catid) return null;
		return catid[1];
	};

	// initialize current cat from 'events' menu item
	var curCat = getCatId(jQuery('.current-menu-item'));

	// get the next event following the given cal day
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

	// scroll to given post
	var scrollToPost = function(post,duration,callback){
		if ( post.length == 0 || duration === false ) 
			return;
		var offset = post.offset().top - (topmargin);
		jQuery('html,body').animate({scrollTop:offset},duration,callback);
	};

	// scroll to next post following the given cal day
	var scrollToCalDay = function(elem,duration,callback){
		scrollToPost(getPostFromCalDay(elem),duration,callback);
	};

	// when loading the page, scroll to next active event
	scrollToCalDay(jQuery('#today'),0);

	// return the first/last event day from given calendar
	getEventDay = function(curCal,first){
		var days = jQuery(curCal).find('td.ec3_eventday');
		if ( days.length == 0 ) return jQuery();
		return (first?days.first():days.last());
	}
	// the post which is currently on top
	var curPost = getPostFromCalDay(jQuery('#today'));

	// scroll to first/last event in month shown by the given calendar
	scrollToMonth = function(curCal,first,scroll,callback){
		if ( scroll === false ) return;
		scrollToCalDay(getEventDay(curCal,first),scroll,callback);
	};

	// load new events with ajax
	// apend/prepend them to the list and filter them
	loadNewEvents = function(href,append,callback){
		if ( postreq > 0 ) return;
		postreq++;
	 	jQuery.get(href, function(data){
			var content = jQuery(data).find('#event-listing').contents();
			if ( content.length > 0 ){
				if ( append ){
					jQuery('#event-listing').append(content);
				}else{
					jQuery('#event-listing').prepend(content);
				}
				filterPosts(curCat);
			}
			if ( callback ) callback();
			postreq--;
		});
	};

	// parse a calendar month url into [begin,year,month,rest]
	parseHref = function(href){
	 	var reg = /(.+m=)(\d\d\d\d)(\d\d)(.+)/;
		reg.exec(href);
		var month = parseInt(RegExp.$3,10);
		var year = parseInt(RegExp.$2,10);
		return [RegExp.$1, year, month, RegExp.$4];
	}

	// test if the month of href is between prev_href and next_href
	hrefLoaded = function(href){
		href = parseHref(href);
		var nh = parseHref(next_href);
		var ph = parseHref(prev_href);
		var hrefDate = new Date(href[1],href[2]-1,1).getTime();
		var nDate = new Date(nh[1],nh[2]-1,1).getTime();
		var pDate = new Date(ph[1],ph[2]-1,1).getTime();
		return ( hrefDate > pDate && hrefDate < nDate);
	}

	// get the events of next/prev month
	// and add them to the event listing
	jQuery('#wp-calendar #next > a,' + 
	       '#wp-calendar #prev > a,').live('click',function(){
		if ( postreq > 0 ) return false;

		var newMonthHref = jQuery(this).attr('href');
		var id = jQuery(this).attr('id');
		var requestNextMonth = ( id == 'ec3_next' );
		var fun = null;
		var reload = !hrefLoaded(newMonthHref);
		if ( requestNextMonth ){
			fun = ec3.go_next;
		}else{
			fun = ec3.go_prev;
		}
		fun(function(curCal){
			updateCal = false;
			if ( reload ) 
				loadNewEvents(newMonthHref,requestNextMonth,function(){
					if ( requestNextMonth )
						next_href = incrementHref(next_href);
					else
						prev_href = decrementHref(prev_href);
					scrollToMonth(curCal,requestNextMonth,'slow',function(){
						updateCal = true;
					});
				});
			else scrollToMonth(curCal,'slow',function(){
				updateCal = true;
			});
		});
		return false;
	}); 

	// scroll to corrsponding post when click on day 
	jQuery('td.ec3_postday > a').live('click',function(){
		scrollToCalDay(jQuery(this).parent());
		return false;
	});

	// all elements that need to be shown/hidden when loading one event
	var listingElements = '#mainpost-banner,#event-listing,#wp-calendar,#eventtypes';

	// filter posts when clicking on event sub categories
	jQuery('#eventtypes > ul > li,' + 
	       'ul.children > li,' +
	       'ul.event-subcat > li').live('click',function(){
		// remove any single events
		jQuery('#content #single-post').remove();
		jQuery(listingElements).show();

		curCat = getCatId(jQuery(this));
		ec3.set_cur_cat(curCat);
		if(!curCat) return;
		filterPosts(curCat,ec3.get_current_month_link(getCatId(jQuery(this))));
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery(this).addClass('current-cat');
		return false;
	});

	// get a single post from 'data' and wrap it into a div
	getSinglePost = function(data){
		var content = jQuery(data).find('#content').contents();
		var container = jQuery('<div id="single-post"></div>');
		container.append(content);
		return container;
	}

	// load post content
	jQuery('#event-listing > div a').live('click',function(){
		jQuery(listingElements).hide();
       	 	jQuery.get(jQuery(this).attr('href'), function(data){
			// store id of added content
			jQuery('#content').append(getSinglePost(data));
		});
		return false;
	});

	// load next/prev post
	jQuery('.nav-previous > a,.nav-next > a').live('click',function(){
       	 	jQuery.get(jQuery(this).attr('href'), function(data){
			jQuery('#content #single-post').remove();
			jQuery('#content').append(getSinglePost(data));
		});
		return false;
	});

	// scroll to first of month when clicking on month link
	jQuery('#wp-calendar caption > a').live('click',function(){
		scrollToMonth(jQuery(this).closest('table'),true);
		return false;
	});

	// return the url pointing to the previous month of given url
	decrementHref = function(href){
		href = parseHref(href);
		var month = href[2] - 1;
		var year = href[1];
		if ( month == 0 ) {
			year--;
			month=12;
		}
		if ( month < 10 ) month = "0" + month;
		return (href[0] + year + "" + month  + href[3]);
	};

	// return the url pointing to the next month of given url
	incrementHref = function(href){
		href = parseHref(href);
		var month = href[2] + 1;
		var year = href[1];
		if ( month == 13 ) {
			year++;
			month=1;
		}
		if ( month < 10 ) month = "0" + month;
		return (href[0] + year + "" + month  + href[3]);
	};

	// update calendar to match current post month
	updateCalendar = function(curCal){
		curCal = jQuery(curCal);
		var calDate = curCal.attr('id').split('_');
		var postDate = curPost.find('.begin_date').attr('id').split('_');	
		calDate = new Date(calDate[1],calDate[2]-1,1).getTime();
		postDate = new Date(postDate[0],postDate[1]-1,1).getTime();
		if( calDate < postDate )
			ec3.go_next(updateCalendar);
		else if( calDate > postDate )
			ec3.go_prev(updateCalendar);
	}

	// update the current post to the first one shown
	// call updateCalendar
	updateCurPost = function(callback){
		var elem = jQuery(window);
		if ( curPost.length != 0 ) {
			var newPost = curPost;
			while ( newPost.offset().top-(topmargin-newPost.height()) < elem.scrollTop() ){
				var nextPost = newPost.nextAll(':visible').first();
				if ( nextPost.length == 0 ) break;
				newPost = nextPost;
			}

			// only go to prev month if there was no reload before
			while ( newPost.offset().top-(topmargin+newPost.height()) > elem.scrollTop() ) {
				var prevPost = newPost.prevAll(':visible').first();
				if ( prevPost.length == 0 ) break;
				newPost = prevPost;
			}
//			curPost.css('background','white');
			if ( newPost != curPost ){
				curPost = newPost;
				updateCalendar(jQuery('#wp-calendar > table').filter(':visible'));
			}
//			curPost.css('background','grey');
		}

		if ( callback ) callback();
	}

	// update calendar and load new events when scrolling through event list
	jQuery(document).scroll(function(e){
		var elem = jQuery(window);
		var variance = 5;
		if ( updateCal ) 
			updateCurPost();

		// scroll reached the bottom
 		if (jQuery(document).height() <= (elem.scrollTop() + elem.height() + variance)) {
			loadNewEvents(next_href,true,function(){
				next_href = incrementHref(next_href);
			});
		}
		// scroll reached the top
		else if (elem.scrollTop() == 0 ){
			var firstPost = jQuery('#event-listing > div:first');
			loadNewEvents(prev_href,false,function(){
				prev_href = decrementHref(prev_href);
				scrollToPost(firstPost,0);
			});
		}
	});
});

