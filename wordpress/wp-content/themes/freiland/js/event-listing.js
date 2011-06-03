function event_listing(){

	var topmargin;
	// count ajax requests for posts
	var postreq = 0;
	var requests = new Array();
	var next_href;
	var prev_href;
	var curPost;
	var curCat,topCat;
	var updateCal = true;
	// all elements that need to be shown/hidden when loading one event
	var listingElements = '#banner-container,#wp-calendar,#eventtypes';
	// all event title links
	var eventSelector = '#event-listing h2.entry-title > a';
	// all subcategory links
	var subcatSelector = '#eventtypes > ul > li > a,ul.children > li > a,ul.event-subcat > li > a';
	// next/prev post
	var nextPrevSelector = '.nav-previous > a,.nav-next > a';
	// the url of the site
	var baseURL;
	// pixel distance from visible area for loading/unloading new events
	var delta = 100;
	var scrollDiv;


//	jQuery('#topright').css('z-index',300);
	var debug = function(text){
		jQuery('#topright > span').append(text+"</br>");
	};

	function initHrefs(){
		next_href = jQuery('#ec3_next:first').attr('href');
		prev_href = jQuery('#ec3_prev:first').attr('href');
	}

	function init(){
		scrollDiv = jQuery('#content');
		topmargin = scrollDiv.offset().top;
		innerScroll(scrollDiv);	

		// update calendar and load new events when scrolling through event list
		scrollDiv.bind('hiddenscroll',function(e,ontop,onbottom){
			event_listing.onScrolled(ontop,onbottom);
		});

		// create initial month container
		var monthContainer = event_listing.getMonthContainer(getCurMonth());
		monthContainer.append(jQuery('#event-listing').contents());
		jQuery('#event-listing').append(monthContainer);
		initHrefs();

		// initialize current cat from 'events' menu item
		curCat = event_listing.getCatId(jQuery('.current-menu-item'));
		// the top-level category, this never changes
		topCat = curCat;
		// set 'current-cat' class
		jQuery('#eventtypes ul li.cat-item-'+curCat).addClass('current-cat');

		// the post which is currently on top
		curPost = event_listing.getPostFromCalDay(jQuery('#today'));

		// if there are no events left in this month, take the first 
		// event from this month
		if ( curPost.length == 0 )
			curPost = jQuery('#event-listing > div > div.post').first();
	};
	event_listing.init = init;

	function getMonthContainer(month){
		return jQuery('<div id="month_'+month+'" class="month_container"></div>');
	};
	event_listing.getMonthContainer = getMonthContainer;

	getPostMonth = function(post){
		if ( post.length == 0 ) return;
		return post.parent().attr('id').substring(6);
	}

	getCurCalendar = function(){
		return	jQuery('#wp-calendar > table').filter(':visible').first();
	}

	getCurMonth = function(){
		var cal = getCurCalendar();
		return cal.attr('id').substring(4);
	}

	// filter posts for given category
	// show 404 if no posts found
	var filterPosts = function(cat){
		var allPosts = jQuery('#event-listing > div > div.post');
		var other = jQuery('#event-listing > div > div').not('.cat-id-'+cat);
		var notfound = jQuery('.error404');
		var href = baseURL + '/events404';
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
		var scroll = false;

		// select nearest visible post
		if ( !curPost.is(':visible') && getCurMonth() == getPostMonth(curPost) ){
			var allPosts = jQuery('#event-listing > div > div.post');
			var visiblePosts = allPosts.filter(':visible');

			if ( visiblePosts.length == 0 )
				return;
			var oldIndex = allPosts.index(curPost);
			var diff = allPosts.length - 1;
			visiblePosts.each(function(){
				var curIndex = allPosts.index(jQuery(this));
				var newDiff = Math.abs( oldIndex - curIndex );
				if ( newDiff  <= diff ){
					diff = newDiff;
					curPost = jQuery(this);
					scroll = true;
					if ( diff == 1 ) return false;
				}
			});
		}else{
			if ( getCurMonth() != getPostMonth(curPost) ){
				// has calendar changed?
				// new cur is first of new month
				var newPost = getPostFromCalDay(getEventDay(getCurCalendar(),true));
				if ( newPost.length > 0 ){
					curPost = newPost;
					scroll = true;
				}
			}else scroll = true;
		}
		if ( scroll ){
			scrollToPost(curPost,0,function(){
				if ( updateCal )
					updateCalendar(getCurCalendar());
			});
		}
	};

	// return the categroy id of this element
	function getCatId(elem){
		var cat = elem.attr('class');
    		var xCat=new RegExp('.*cat-item-([0-9]+)');
		var catid=xCat.exec(cat);
		if(!catid) return null;
		return catid[1];
	};
	event_listing.getCatId = getCatId;

	// get the next event following the given cal day
	function getPostFromCalDay(elem){
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
		id = parseInt(id[0],10);
		return jQuery('#post-'+id);
	};
	event_listing.getPostFromCalDay = getPostFromCalDay;

	function runRequestQueue(){
		if ( requests.length > 0 ){
			var f =	requests.shift();
			f();
		}
	}

	// load new events with ajax
	// apend/prepend them to the list and filter them
	function doRequest(append,callback){
		if ( postreq > 0 ) return;
		postreq++;
		var href = (append?next_href:prev_href);
	 	jQuery.get(href, function(data){
			var content = jQuery(data).find('#event-listing').contents();
			var month = event_listing.parseHref(href);
			month = month[1] + '_' + month[2];
			if ( content.filter('.cat-id-'+curCat).length > 0 ||
			     month == getCurMonth() ){
				var monthContainer = getMonthContainer(month);
				monthContainer.append(content);
				if ( append ){
					jQuery('#event-listing').append(monthContainer);
				}else{
					var curOffset = scrollDiv.offset();
					var diff  = scrollDiv.height();
					jQuery('#event-listing').prepend(monthContainer);
					diff = scrollDiv.height() - diff;
					scrollDiv.offset({top:curOffset.top - diff,
						left:curOffset.left});
				}
				if ( content.length > 0 )
					filterPosts(curCat);
				if ( append )
					next_href = incrementHref(next_href);
				else
					prev_href = decrementHref(prev_href);
			}
			if ( callback ) callback();
			postreq--;
			runRequestQueue();
		});
	};

	function loadNewEvents(append,callback){
		requests.push(function(){ doRequest(append,callback); });
		if ( postreq == 0 )
			runRequestQueue();
	}

	unloadMonths = function(){
		var container = jQuery('.month_container').first();

		while ( container.offset().top + container.height() + delta < 0 ){
			var next = container.next();
			var height = scrollDiv.height();
			container.remove();
			// adjust new top
			var diff = height - scrollDiv.height();
			scrollDiv.offset({top:scrollDiv.offset().top + diff,left:scrollDiv.offset().left});
			prev_href = incrementHref(prev_href);
			container = next;
		}

		container = jQuery('.month_container').last();
				
		while ( container.length > 0 && container.offset().top > jQuery('#container').offset().top +
			jQuery('#container').height() + delta ){
			var prev = container.prev();
			container.remove();
			next_href = decrementHref(next_href);
			container = prev;
		}
	}

	// scroll to given post
	function scrollToPost(post,duration,callback){
		if ( post.length == 0 || duration === false ){
			if ( callback ) callback();
			return ;
		}
		if ( duration == null || duration == 'slow' )
			duration = 600;
		curPost = post;
		var cur = scrollDiv.offset();
		var offset = cur.top - post.offset().top;
		scrollDiv.animate({ top: offset},duration,function(){
			if( scrollDiv.offset().top + scrollDiv.height() < 
				jQuery('#footer').offset().top ){
				loadNewEvents(true,function(){
					unloadMonths();
					if ( callback ) callback();
				});
			}else{
				unloadMonths();
				if ( callback ) callback();
			}
		});
	};
	event_listing.scrollToPost = scrollToPost;

	// scroll to next post following the given cal day
	function scrollToCalDay(elem,duration,callback){
		scrollToPost(getPostFromCalDay(elem),duration,callback);
	};
	event_listing.scrollToCalDay = scrollToCalDay;

	// return the first/last event day from given calendar
	getEventDay = function(curCal,first){
		var days = jQuery(curCal).find('td.ec3_eventday');
		if ( days.length == 0 ) return jQuery();
		return (first?days.first():days.last());
	}

	// scroll to first/last event in month shown by the given calendar
	function scrollToMonth(curCal,first,scroll,callback){
		if ( scroll === false ) return;
		scrollToCalDay(getEventDay(curCal,first),scroll,callback);
	};
	event_listing.scrollToMonth = scrollToMonth;

	// parse a calendar month url into [begin,year,month,rest]
	function parseHref(href){
	 	var reg = /(.+m=)(\d\d\d\d)(\d\d)(.+)/;
		reg.exec(href);
		var month = parseInt(RegExp.$3,10);
		var year = parseInt(RegExp.$2,10);
		return [RegExp.$1, year, month, RegExp.$4];
	}
	event_listing.parseHref = parseHref;

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

	function nextPrevClicked(elem){
		if ( postreq > 0 ) return false;

		var id = elem.attr('id');
		var requestNextMonth = ( id == 'ec3_next' );
		var fun = null;
		var reload = !hrefLoaded(elem.attr('href'));
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
	};
	event_listing.nextPrevClicked = nextPrevClicked;

	function keepOnlyCurrentMonth(){
		jQuery('#event-listing > div.month_container').not('#month_'+getCurMonth()).remove();
		initHrefs();
	}

	function onCategoryChanged(){
		if ( curCat != topCat ){
			keepOnlyCurrentMonth();
		}
		filterPosts(curCat);
		jQuery('#event-listing').show();
		findNextCurPost();
	}

	// filter posts when clicking on event sub categories
	subcatClicked = function(newCat){
		// remove any single events
		var updateNeeded = false;
		var singlePost = jQuery('#content #single-post');
		if ( singlePost.length > 0 ){
			updateNeeded = true;
			singlePost.remove();
			jQuery('#post-images').remove();
			jQuery('body').toggleClass('category-events',true);
			jQuery('body').toggleClass('single',false);
		}
		jQuery(listingElements).show();

		if(!newCat) return;
		curCat = newCat;
		jQuery('#eventtypes > ul > li').removeClass('current-cat'); 
		jQuery('.cat-item-'+curCat).addClass('current-cat');

		ec3.set_cur_cat(curCat, function() {
			if ( updateNeeded )
				updateCalendar(getCurCalendar(),onCategoryChanged);
			else
				onCategoryChanged();
		});
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
	function initAddress(){
                // Initializes the plugin
		eventUrl = jQuery.address.baseURL();
		baseURL = eventUrl.replace('events','');
		jQuery(eventSelector + ',' + 
		       subcatSelector + ',' +
		       nextPrevSelector).address(function() {
			return jQuery(this).attr('href').replace(baseURL,'');
		});
	};
	event_listing.initAddress = initAddress;

	function addressChanged(event){
		var orgvalue = event.value.replace(/^\//, '');
		var value = baseURL + event.value.replace(/^\//, '');

		// load event listing
		if ( orgvalue == '' ){
			subcatClicked(topCat);
			return;
		}

		var found = false;
		// load a subcategory
		jQuery(subcatSelector).each(function() {
			if (jQuery(this).attr('href') == value) {
				subcatClicked(getCatId(jQuery(this).parent()));
				found = true;
				return false;
			}
		});

		if ( found ) return;

		// load post content
		jQuery(eventSelector + ',' + nextPrevSelector).filter(':visible').each(function() {
			if (jQuery(this).attr('href') == value) {
				jQuery.get(jQuery(this).attr('href'), function(data){
					curPost = jQuery('#'+jQuery(data).find('div.post').attr('id'));
					jQuery('#content #single-post').remove();
					jQuery('#post-images').remove();
					jQuery('#main').prepend(jQuery(data).find('#post-images'));
					jQuery('#content').append(getSinglePost(data));
					jQuery(listingElements + ",#event-listing").hide();
					jQuery('body').toggleClass('category-events',false);
					jQuery('body').toggleClass('single',true);
					jQuery('html,body').scrollTop(0);
				});
			} 
		});
	};
	event_listing.addressChanged = addressChanged;

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
	updateCalendar = function(curCal,callback){
		curCal = jQuery(curCal);
		var calDate = curCal.attr('id').split('_');
		var postDate = curPost.find('.begin_date').attr('id').split('_');	
		calDate = new Date(calDate[1],calDate[2]-1,1).getTime();
		postDate = new Date(postDate[0],postDate[1]-1,1).getTime();
		if( calDate < postDate )
			ec3.go_next(function(curCal){
				updateCalendar(curCal,callback);
			});
		else if( calDate > postDate )
			ec3.go_prev(function(curCal){
				updateCalendar(curCal,callback);
			});
		else if (callback){
			 callback();
		}
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
				updateCalendar(getCurCalendar());
			}
//			curPost.css('background','grey');
		}

		if ( callback ) callback();
	};

	var checkForUpdate = function(ontop,onbottom){

		// scroll reached the bottom
		if ( onbottom ) {
			loadNewEvents(true,function(){
				unloadMonths();
			});
		}
		// scroll reached the top
		else if ( ontop ){
			loadNewEvents(false,function(){
				scrollToPost(curPost,0);
			});
		}
	};

	function onScrolled(ontop,onbottom){
		// do nothing when showing single posts
		if ( jQuery('#single-post').length > 0 ) return;

		if ( updateCal ) 
			updateCurPost();

		checkForUpdate(ontop,onbottom);
	};
	event_listing.onScrolled = onScrolled;
}

event_listing();
jQuery(document).ready(function(){
	event_listing.init();	

	// get the events of next/prev month
	// and add them to the event listing
	jQuery('#wp-calendar #next > a,' + 
	       '#wp-calendar #prev > a,').live('click',function(){
		event_listing.nextPrevClicked(jQuery(this));
		return false;
	}); 

	// scroll to corrsponding post when click on day 
	jQuery('td.ec3_postday > a').live('click',function(){
		event_listing.scrollToCalDay(jQuery(this).parent());
		return false;
	});

	// scroll to first of month when clicking on month link
	jQuery('#wp-calendar caption > a').live('click',function(){
		event_listing.scrollToMonth(jQuery(this).closest('table'),true);
		return false;
	});

	jQuery.address.init(function(event) {
		event_listing.initAddress();
	}).change(function(event) {
		event_listing.addressChanged(event);
	});

});

