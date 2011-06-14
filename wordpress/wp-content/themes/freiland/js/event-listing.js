function event_listing(){

	var topmargin;
	// count ajax requests for posts
	var postreq = 0;
	// count requests for next/prev month, only one should 
	// be processed at a time
	var nextPrevReq = 0;
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
	// the div that is scrolled
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
		return post.find('.begin_date').attr('id');
	}

	getCurCalendar = function(){
		return	jQuery('#ec3_cal_tables > table').filter(':visible').first();
	}

	getCurMonth = function(){
		var cal = getCurCalendar();
		return cal.attr('id').substring(4);
	}

	function get404(){
		var href = baseURL + '/events404';
		postreq++;
		jQuery.get(href, function(data){
			if( jQuery('.error404').length == 0 ){
				jQuery('#content').append(jQuery(data).find('.error404'));
			}
			postreq--;
			runRequestQueue();
		});
	}

	// filter posts for given category
	// show 404 if no posts found
	var filterPosts = function(cat){
		if ( curCat != topCat ){
			var id = '#month_'+getCurMonth();
			if ( jQuery(id).children().filter('.cat-id-'+curCat).length == 0 )
				keepOnlyCurrentMonth();
		}
		var allPosts = jQuery('#event-listing > div > div.post');
		var other = jQuery('#event-listing > div > div').not('.cat-id-'+cat);
		var notfound = jQuery('.error404');
		if ( allPosts.length == other.length ){
			 if ( notfound.length == 0 ){
				requests.push(function(){ get404(); });
				if ( postreq == 0 )
					runRequestQueue();
                        } else notfound.show();
			innerScroll.setTop(0);
		}else notfound.hide();
		allPosts.show();
		other.hide();
		innerScroll.updateDimensions();	
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

	function loadEvents(href,callback){
		var month = event_listing.parseHref(href);
		month = month[1] + '_' + month[2];
		var monthContainer = jQuery('#event-listing').data('month_'+month);
		if ( monthContainer ){
			if ( callback ) callback(monthContainer,month);
			return;
		}
		ec3.set_spinner(1);
		jQuery.get(href, function(data){
			var content = jQuery(data).find('#event-listing').contents();
			monthContainer = getMonthContainer(month);
			monthContainer.append(content);
			ec3.set_spinner(0);
			if ( callback ) callback(monthContainer,month);
		});
	}

	// load new events with ajax
	// apend/prepend them to the list and filter them
	function doRequest(append,callback){
		if ( postreq > 0 ) return;
		postreq++;
		var href = (append?next_href:prev_href);
		loadEvents(href,function(monthContainer,month){
			if ( monthContainer.children().filter('.cat-id-'+curCat).length > 0 ||
			     month == getCurMonth() ){
				if ( append ){
					jQuery('#event-listing').append(monthContainer);
					next_href = incrementHref(next_href);
				}else{
					var diff  = scrollDiv.height();
					jQuery('#event-listing').prepend(monthContainer);
					diff = scrollDiv.height() - diff;
					innerScroll.setRelativeTop( - diff );
					prev_href = decrementHref(prev_href);
				}
				innerScroll.updateDimensions();	
				filterPosts(curCat);
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
		// pixel distance from visible area for unloading months
		var delta = 100;

		while ( container.offset().top + container.height() + delta < 0 ){
			var next = container.next();
			var height = scrollDiv.height();
			jQuery('#event-listing').data(container.attr('id'),container.detach());
			// adjust new top
			var diff = height - scrollDiv.height();
			innerScroll.setRelativeTop(diff);
			prev_href = incrementHref(prev_href);
			container = next;
		}

		container = jQuery('.month_container').last();
				
		while ( container.length > 0 && container.offset().top > jQuery('#container').offset().top +
			jQuery('#container').height() + delta ){
			var prev = container.prev();
			jQuery('#event-listing').data(container.attr('id'),container.detach());
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
			innerScroll.positionChanged();
			if ( callback ) callback();
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
		if ( nextPrevReq > 0 ) return false;
		nextPrevReq++;

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
						nextPrevReq--;
					});
				});
			else scrollToMonth(curCal,true,'slow',function(){
				updateCal = true;
				nextPrevReq--;
			});
		});
	};
	event_listing.nextPrevClicked = nextPrevClicked;

	function keepOnlyCurrentMonth(){
		jQuery('#event-listing > div.month_container').not('#month_'+getCurMonth()).remove();
		initHrefs();
	}

	function onCategoryChanged(){
		filterPosts(curCat);
		jQuery('#event-listing').show();
		findNextCurPost();
	}

	function getFirstMatchingPost(cat,id){
		var post = jQuery('#'+id);
		var newPost = post.prevAll('.cat-id-'+cat).last();
		if ( newPost.length == 0 )
			newPost = post;
		return newPost; 
	};

	// filter posts when clicking on event sub categories
	subcatClicked = function(newCat){
		// remove any single events
		var updateNeeded = false;
		var reloadHref = false;
		var singlePost = jQuery('#content #single-post');
		var post = singlePost.find('div.post');
		var singlePostId;
		if ( singlePost.length > 0 ){
			innerScroll.resetTopmargin();
			singlePostId = post.attr('id');
			var pDate = getPostMonth(post);
			if ( jQuery('#month_' + pDate).length == 0 ){
				var href = parseHref(next_href);
				var date = pDate.split('_');
				reloadHref = buildHref(href,date[0],date[1]);
			}else
				curPost = getFirstMatchingPost(newCat,singlePostId);
			updateNeeded = true;
			singlePost.remove();
			jQuery('#post-images').remove();
			jQuery('body').toggleClass('category-events',true);
			jQuery('body').toggleClass('single',false);
		}
		jQuery(listingElements).show();

		if(!newCat) return;
		curCat = newCat;
		jQuery('#eventtypes > ul > li,.children > li').removeClass('current-cat'); 
		jQuery('.cat-item-'+curCat).addClass('current-cat');

		ec3.set_cur_cat(curCat, function() {
			if ( updateNeeded ){
				if ( reloadHref )
					loadEvents(reloadHref,function(monthContainer){
						jQuery('#event-listing > div.month_container').remove();
						jQuery('#event-listing').append(monthContainer);
						curPost = getFirstMatchingPost(curCat,singlePostId);
						updateCalendar(getCurCalendar(),function(){
							initHrefs();
							onCategoryChanged();
						});
					});
				else
					updateCalendar(getCurCalendar(),onCategoryChanged);
			}else
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
				var spinner = jQuery('#freiland_spinner');
				if ( spinner.length > 0 ) spinner.show();
				jQuery.get(jQuery(this).attr('href'), function(data){
					jQuery('#content #single-post').remove();
					jQuery('#post-images').remove();
					jQuery('#main').prepend(jQuery(data).find('#post-images'));
					jQuery('#content').append(getSinglePost(data));
					jQuery(listingElements + ",#event-listing").hide();
					jQuery('body').toggleClass('category-events',false);
					jQuery('body').toggleClass('single',true);
					scrollDiv.css({top:0});
					innerScroll.setTopmargin(scrollDiv.offset().top);
					if ( spinner.length > 0 ) spinner.hide();
				});
			} 
		});
	};
	event_listing.addressChanged = addressChanged;

	function buildHref(href,year,month){
		if ( month < 10 ) month = "0" + month;
		return (href[0] + year + "" + month  + href[3]);
	}

	// return the url pointing to the previous month of given url
	decrementHref = function(href){
		href = parseHref(href);
		var month = href[2] - 1;
		var year = href[1];
		if ( month == 0 ) {
			year--;
			month=12;
		}
		return buildHref(href,year,month);
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
		return buildHref(href,year,month);
	};

	// update calendar to match current post month
	updateCalendar = function(curCal,callback){
		curCal = jQuery(curCal);
		var calDate = curCal.attr('id').split('_');
		var postDate = getPostMonth(curPost).split('_');	
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
	updateCurPost = function(){
		var elem = jQuery(window);
		if ( curPost.length != 0 && jQuery('#event-listing > div').filter(':visible').length > 0 ) {
			var newPost = curPost;
			while ( newPost.offset().top-(topmargin-newPost.height()) < 0 ){
				var nextPost = newPost.nextAll(':visible').first();
				if ( nextPost.length == 0 ){
					nextPost = newPost.parent().next().children(':visible').first();
					if ( nextPost.length == 0 )					
						break;
				}
				newPost = nextPost;
			}

			// only go to prev month if there was no reload before
			while ( newPost.offset().top-(topmargin+newPost.height()) > 0 ) {
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
	};

	var checkForUpdate = function(ontop,onbottom){
		// scroll reached the bottom
		if ( onbottom && getCurMonth() != ec3.last_month )
			loadNewEvents(true,unloadMonths);

		// scroll reached the top
		else if ( ontop && getCurMonth() != ec3.first_month )
			loadNewEvents(false,unloadMonths);
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
	if ( jQuery('#event-listing').length == 0 )
		return;
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

