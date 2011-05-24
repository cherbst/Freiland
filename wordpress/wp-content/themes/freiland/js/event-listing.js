
jQuery(document).ready(function(){

	var topmargin = jQuery('#event-listing').offset().top;
	// count ajax requests for posts
	var postreq = 0;
	var next_href = jQuery('#ec3_next:first').attr('href');
	var prev_href = jQuery('#ec3_prev:first').attr('href');
	var curPost;
	var updateCal = true;

//	jQuery('#topright').show();
//	jQuery('#topright').css('z-index',300);
	var debug = function(text){
		jQuery('#topright > span').append(text+"</br>");
	};

	getMonthContainer = function(month){
		return jQuery('<div id="month_'+month+'" class="month_container"></div>');
	};

	// create initial month container
	var curMonth = jQuery('#wp-calendar > table').first().attr('id').substring(4);
	var monthContainer = getMonthContainer(curMonth);
	monthContainer.append(jQuery('#event-listing').contents());
	jQuery('#event-listing').append(monthContainer);

	// filter posts for given category
	// show 404 if no posts found
	var filterPosts = function(cat,href){
		var allPosts = jQuery('#event-listing > div > div.post');
		var other = jQuery('#event-listing > div > div:not(.cat-id-'+cat+')');
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

	var findNextCurPost = function(){
		// select nearest visible post
		if ( !curPost.is(':visible') ){
			var allPosts = jQuery('#event-listing > div > div.post');
			var visiblePosts = allPosts.filter(':visible');

			if ( visiblePosts.length == 0 )
				return;
			var oldIndex = allPosts.index(curPost);
			var diff = allPosts.length - 1;
			visiblePosts.each(function(){
				var curIndex = allPosts.index(jQuery(this));
				var newDiff = Math.abs( oldIndex - curIndex );
				if ( newDiff  < diff ){
					diff = newDiff;
					curPost = jQuery(this);
					if ( diff == 1 ) return false;
				}
			});
		}
		scrollToPost(curPost,0,function(){
			if ( updateCal )
				updateCalendar(jQuery('#wp-calendar > table').filter(':visible'));
		});
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
	// the top-level category, this never changes
	var topCat = curCat;
	// set 'current-cat' class
	jQuery('#eventtypes ul li.cat-item-'+curCat).addClass('current-cat');

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

	// load new events with ajax
	// apend/prepend them to the list and filter them
	loadNewEvents = function(append,callback){
		if ( postreq > 0 ) return;
		postreq++;
		var href = (append?next_href:prev_href);
	 	jQuery.get(href, function(data){
			var content = jQuery(data).find('#event-listing').contents();
			if ( content.length > 0 ){
				var month = parseHref(href);
				month = month[1] + '_' + month[2];
				monthContainer = getMonthContainer(month);
				monthContainer.append(content);
				if ( append ){
					jQuery('#event-listing').append(monthContainer);
				}else{
					jQuery('#event-listing').prepend(monthContainer);
				}
				filterPosts(curCat);
			}
			if ( append )
				next_href = incrementHref(next_href);
			else
				prev_href = decrementHref(prev_href);

			if ( callback ) callback();
			postreq--;
		});
	};

	unloadMonths = function(){
		var listing = jQuery('#event-listing');
		var height = listing.height();
		var container = jQuery('.month_container').first();
				
		if ( container.offset().top + container.height() < 0 ){
			var next = container.next();
			container.remove();
			// adjust new top
			var diff = height - listing.height();
			listing.offset([listing.offset().top + diff,listing.offset().left]);
			prev_href = incrementHref(prev_href);
			container = next;
			scrollToPost(curPost,0);
		}

		container = jQuery('.month_container').last();
				
		if ( container.offset().top > jQuery(window).height() ){
			var prev = container.prev();
			container.remove();
			next_href = decrementHref(next_href);
			container = prev;
			scrollToPost(curPost,0);
		}
	}

	// scroll to given post
	var scrollToPost = function(post,duration,callback){
		if ( post.length == 0 || duration === false ){
			if ( callback ) callback();
			return ;
		}
		if ( duration == null || duration == 'slow' )
			duration = 600;
		curPost = post;
		var cur = jQuery('#event-listing').offset();
		var offset = cur.top - post.offset().top; 
		jQuery('#event-listing').animate({ top: offset},duration,function(){
			if( jQuery('#event-listing').offset().top + jQuery('#event-listing').height() < 
				jQuery('#footer').offset().top )
				loadNewEvents(true,function(){
					unloadMonths();
					if ( callback ) callback();
				});
			else if( jQuery('#event-listing').offset().top > 0 )
				loadNewEvents(false,function(){
					unloadMonths();
					if ( callback ) callback();
				});
			else{
				unloadMonths();
				if ( callback ) callback();
			}
		});
	};

	// scroll to next post following the given cal day
	var scrollToCalDay = function(elem,duration,callback){
		scrollToPost(getPostFromCalDay(elem),duration,callback);
	};

	// return the first/last event day from given calendar
	getEventDay = function(curCal,first){
		var days = jQuery(curCal).find('td.ec3_eventday');
		if ( days.length == 0 ) return jQuery();
		return (first?days.first():days.last());
	}
	// the post which is currently on top
	var curPost = getPostFromCalDay(jQuery('#today'));
	// page was just loaded;
	var pageLoaded = true;

	// when loading the page, scroll to next active event
	scrollToPost(curPost,0);

	// scroll to first/last event in month shown by the given calendar
	scrollToMonth = function(curCal,first,scroll,callback){
		if ( scroll === false ) return;
		scrollToCalDay(getEventDay(curCal,first),scroll,callback);
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

		var id = jQuery(this).attr('id');
		var requestNextMonth = ( id == 'ec3_next' );
		var fun = null;
		var reload = !hrefLoaded(jQuery(this).attr('href'));
		if ( requestNextMonth ){
			fun = ec3.go_next;
		}else{
			fun = ec3.go_prev;
		}
		fun(function(curCal){
			updateCal = false;
			if ( reload ) 
				loadNewEvents(requestNextMonth,function(){
					scrollToMonth(curCal,true,'slow',function(){
						updateCal = true;
					});
				});
			else scrollToMonth(curCal,true,'slow',function(){
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
	// all event title links
	var eventSelector = '#event-listing > div a';
	// all subcategory links
	var subcatSelector = '#eventtypes > ul > li > a,ul.children > li > a,ul.event-subcat > li > a';
	// next/prev post
	var nextPrevSelector = '.nav-previous > a,.nav-next > a';


	// filter posts when clicking on event sub categories
	subcatClicked = function(newCat){
		// remove any single events
		jQuery('#content #single-post').remove();
		jQuery('#post-images').remove();
		jQuery(listingElements).show();

		curCat = newCat;
		ec3.set_cur_cat(curCat);
		if(!curCat) return;
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery('.cat-item-'+curCat).addClass('current-cat');
		filterPosts(curCat,ec3.get_current_month_link(curCat));

		if ( curCat == topCat )
			scrollToPost(curPost,0);
		else
			findNextCurPost();
	};

	// get a single post from 'data' and wrap it into a div
	getSinglePost = function(data){
		var content = jQuery(data).find('#content').contents();
		var container = jQuery('<div id="single-post"></div>');
		container.append(content);
		return container;
	};

	// jquery - address plugin
	// this provides browser history for ajax links
	// used for subcategory and post links
	var baseURL;
	jQuery.address.init(function(event) {
                // Initializes the plugin
		baseURL = jQuery.address.baseURL().replace('events','');
		jQuery(eventSelector + ',' + 
		       subcatSelector + ',' +
		       nextPrevSelector).address(function() {
			return jQuery(this).attr('href').replace(baseURL,'');
		});
	}).change(function(event) {
		var orgvalue = event.value.replace(/^\//, '');
		var value = baseURL + event.value.replace(/^\//, '');

		// load event listing
		if ( orgvalue == '' ){
			// XXX: workaround to prevent scrolling to 0
			pageLoaded = true;
			subcatClicked(topCat);
			return;
		}

		var found = false;
		// load a subcategory
		jQuery(subcatSelector).each(function() {
			if (jQuery(this).attr('href') == value) {
				subcatClicked(getCatId(jQuery(this).parent()));
				found = true;
			}
		});

		if ( found ) return;

		// load post content
		jQuery(eventSelector + ',' + nextPrevSelector).filter(':visible').each(function() {
			if (jQuery(this).attr('href') == value) {
				jQuery.get(jQuery(this).attr('href'), function(data){
					jQuery('#content #single-post').remove();
					jQuery('#post-images').remove();
					jQuery('#main').append(jQuery(data).find('#post-images'));
					jQuery('#content').append(getSinglePost(data));
					jQuery(listingElements).hide();
					jQuery('html,body').scrollTop(0);
				});
			} 
		});
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
		if ( curPost.length != 0 && jQuery('#event-listing > div').filter(':visible').length > 0 ) {
			var newPost = curPost;
			while ( newPost.offset().top-(topmargin-newPost.height()) < elem.scrollTop() ){
				var nextPost = newPost.nextAll(':visible').first();
				if ( nextPost.length == 0 ){
					nextPost = newPost.parent().next().children(':visible').first();
					if ( nextPost.length == 0 )					
						break;
				}
				newPost = nextPost;
			}

			// only go to prev month if there was no reload before
			while ( newPost.offset().top-(topmargin+newPost.height()) > elem.scrollTop() ) {
				var prevPost = newPost.prevAll(':visible').first();
				if ( prevPost.length == 0 ){
					prevPost = newPost.parent().prev().children(':visible').last();
					if ( prevPost.length == 0 )
						break;
				}
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
/*
	var checkForUpdate = function(){
		var elem = jQuery(window);
		var variance = 5;
		// scroll reached the bottom
 		if (jQuery(document).height() <= (elem.scrollTop() + elem.height() + variance)) {
			loadNewEvents(true);
		}
		// scroll reached the top
		else if (elem.scrollTop() == 0 ){
			var firstPost = jQuery('#event-listing > div:first');
			loadNewEvents(false,function(){
				scrollToPost(firstPost,0);
			});
		}
	};
	// on page load, check for update
	checkForUpdate();
*/
	// update calendar and load new events when scrolling through event list
	jQuery('#event-listing').bind('hiddenscroll',function(e){
/*		var elem = jQuery(window);
		// XXX: workaround to prevent scrolling to top on ajax page reload
		if ( pageLoaded ) {
			if ( elem.scrollTop() == 0 )
				scrollToPost(curPost);
			pageLoaded = false;
			return;
		}
*/
		// do nothing when showing single posts
		if ( jQuery('#single-post').length > 0 ) return;

		if ( updateCal ) 
			updateCurPost();

//		debug('curPost:'+curPost.find('a').attr('title')+':updateCal:'+updateCal);
//		checkForUpdate();
	});

});

