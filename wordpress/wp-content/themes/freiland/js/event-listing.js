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
		var param = "&ajax_list=1";
		next_href = jQuery('#ec3_next:first').attr('href') + param;
		prev_href = jQuery('#ec3_prev:first').attr('href') + param;
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

		// preload prev and next month
		event_listing.loadEvents(prev_href);
		event_listing.loadEvents(next_href);
		// pretend we are on top to show previous month
		event_listing.checkForUpdate(true,false);
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
		var href = baseURL + 'events404/';
		postreq++;
		ec3.set_spinner(1);
		jQuery.get(href, function(data){
			if( jQuery('.error404').length == 0 ){
				jQuery('#content').append(jQuery(data).find('.error404'));
			}
			postreq--;
			ec3.set_spinner(0);
			runRequestQueue();
		});
	}

	function getLastPostHeight(){
		var lastPost = jQuery('#event-listing > div > div.post').filter(':visible').last();
		var padding = (parseInt(lastPost.css('padding-top'),10) || 0)
			    + (parseInt(lastPost.css('padding-bottom'),10) || 0);
		return lastPost.height() + padding;
	};

	// filter posts for given category
	// show 404 if no posts found
	function filterPosts(cat,onShown,callback){
		// wait until animations are finished
		if ( innerScroll.isAnimating() ) {
			setTimeout(function(){filterPosts(cat,onShown,callback);},100);
			return;
		}

		var monthId = '#month_'+getCurMonth();
		var filter = '.cat-id-'+cat;
		var allPosts = jQuery('#event-listing > div > div.post');
		var curMonthPosts = jQuery(monthId).children();
		var other, toShow;
		var duration = 2000;// 'slow';

		if ( curCat != topCat && curMonthPosts.filter(filter).length == 0 ){
			// keep only posts of current month
			other = jQuery('.month_container').not(monthId)
				.children().add(curMonthPosts.not(filter));
			toShow = curMonthPosts.filter(filter).not(':visible');
		}else{
			// keep all posts matching filter
			other = allPosts.not(filter);
			toShow = allPosts.filter(filter).not(':visible');
		}

		var toHide = other.filter(':visible');
		var postsToShow = ( allPosts.length != other.length );
		var notfound = jQuery('.error404');


		var groups = {};
		// compute show/hide groups
		toHide.add(toShow).each(function(){
			var upper_id = "begin"; 
			var lower_id = "end"; // find next post from this not in toShow/toHide

//			console.log('cur post:'+jQuery(this).attr('id'));
			var upper = jQuery(this);
			while ( upper.length > 0 &&
				( toShow.index(upper) != -1 ||
				  toHide.index(upper) != -1 )) {
//				console.log('cur upper:'+upper.attr('id'));
				var prev = upper.prev();
//				console.log('cur prev:'+prev.attr('id'));
				if ( prev.length == 0 ){
					var curparent = upper.parent();
//					console.log('cur parent:'+curparent.attr('id'));
					do{
						curparent = curparent.prev();
					//	console.log('cur parent:'+curparent.attr('id'));
					}while(curparent.prev().length > 0 && curparent.children().length == 0);
					prev = curparent.children().last();
				}
				upper = prev;
//				console.log('cur upper:'+upper.attr('id'));
			}
			if ( upper.length > 0 )
				upper_id = upper.attr('id'); 

			var lower = jQuery(this);
			while ( lower.length > 0 &&
				( toShow.index(lower) != -1 ||
				  toHide.index(lower) != -1 ) ){
				var next = lower.next();
				if ( next.length == 0 ){
					var curparent = lower.parent();
					do{
						curparent = curparent.next();
					}while(curparent.next().length > 0 && curparent.children().length == 0);
					next = curparent.children().first();
				}
				lower = next;
			}
			if ( lower.length > 0 )
				lower_id = lower.attr('id'); 

/*			jQuery(this).prevAll().each(function(){
				 // find prev post from this not in toShow/toHide
				if ( toShow.index(jQuery(this)) == -1 &&
				     toHide.index(jQuery(this)) == -1 ){
					upper_id = jQuery(this).attr('id');
					return false;
				}
			});
			jQuery(this).nextAll().each(function(){
				 // find next post from this not in toShow/toHide
				if ( toShow.index(jQuery(this)) == -1 &&
				     toHide.index(jQuery(this)) == -1 ){
					lower_id = jQuery(this).attr('id');
					return false;
				}
			});*/

			var id = upper_id+'_'+lower_id;
			var group;
			if ( id in groups )
				group = groups[id];
			else{
				group = {
					toHide : [],
					toShow : [],
					hideHeight: 0,
					showHeight : 0
				};
				groups[id] = group;
			}
			if ( toShow.index(jQuery(this)) != -1 ){
				group.toShow.push(jQuery(this));
				group.showHeight += jQuery(this).height();
			}else{
				group.toHide.push(jQuery(this));
				group.hideHeight += jQuery(this).height();
			}
		});
		
		for(key in groups) {
			var group = groups[key];
			var diff = group.hideHeight - group.showHeight;
			console.log("key is "+[key]+", show height is "+groups[key].showHeight+
				", hide height is "+groups[key].hideHeight );
			if ( group.showHeight > group.hideHeight ){
				for (var i = 0; i < group.toShow.length; i++) {
					var post = group.toShow[i];
					post.data('height',post.height());
					var newHeight = post.height() + (post.height() / group.showHeight)*diff;
					console.log("Expanding "+ post.attr('id') +" from:"+newHeight+" to:"+post.height());
					post.height(newHeight);
				}
			}else{
				for (var i = 0; i < group.toHide.length; i++) {
					var post = group.toHide[i];
					var newHeight = post.height() - (post.height() / group.hideHeight)*diff;
					post.data('newheight',newHeight);
					post.data('height',post.height());
					//post.animate({height:newHeight},duration);
					console.log("Shrinking "+ post.attr('id') +" from:"+post.height()+" to:"+newHeight);
				}
			}
		}
//		toHide.fadeOut(duration);
//		toHide.animate({opacity: 0},duration);
		toHide.each(function(){
			jQuery(this).animate({opacity: 0, height: parseInt(jQuery(this).data('newheight'),10)},duration);
		});

		toHide.promise().done(function(){
			toHide.hide();
			toHide.each(function(){
				jQuery(this).height(parseInt(jQuery(this).data('height'),10));
			});
			if ( !postsToShow ){
				if ( notfound.length == 0 ){
					requests.push(function(){ get404(); });
					if ( postreq == 0 )
						runRequestQueue();
				} else notfound.show();
				innerScroll.setTop(0);
				innerScroll.setScrollableToTop(false);
				if ( callback ) callback();
				return;
			}
			notfound.hide();

			if ( !onShown ){
				var firstPost = curPost.is(':visible')?curPost:allPosts.filter(':visible').first();
				var diff;
				if ( firstPost.length > 0 )
					diff = firstPost.offset().top;
			}

			toShow.css('opacity',0);
			toShow.show();

			if ( onShown )
				onShown();
			else if ( firstPost.length > 0 ){
				diff = diff - firstPost.offset().top;
				innerScroll.setRelativeTop( diff );
			}

//			toShow.fadeTo(duration,1);
			toShow.each(function(){
				jQuery(this).animate({opacity: 1,height: parseInt(jQuery(this).data('height'),10)},duration);
			});
			toShow.promise().done(function(){
				innerScroll.updateDimensions();
				// not set scrollable when showing single posts
				if ( jQuery('#single-post').length == 0 )
					innerScroll.setScrollableToTop(true,getLastPostHeight());
				if ( callback ) callback();
			});
		});
	};

	// return the category id of this element
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
		// the events have already been loaded
		if ( monthContainer ){
			if ( callback ) callback(monthContainer,month);
			return;
		}
		var request = jQuery('#event-listing').data('request_'+month);
		// a request for these events is ongoing
		if ( request ){
			request.done(function(){
				// request is done, get the events again
				loadEvents(href,callback);	
			});
			return;
		}
		// make the ajax request
		ec3.set_spinner(1);
		request = jQuery.get(href, function(data){
			jQuery('#event-listing').data('request_'+month,null);
			var content = jQuery(data).contents();
			monthContainer = getMonthContainer(month);
			monthContainer.append(content);
			jQuery('#event-listing').data('month_'+month,monthContainer);
			ec3.set_spinner(0);
			if ( callback ) callback(monthContainer,month);
		});
		jQuery('#event-listing').data('request_'+month,request);
	}
	event_listing.loadEvents = loadEvents;

	// load new events with ajax
	// apend/prepend them to the list and filter them
	function doRequest(append,callback){
		if ( postreq > 0 ) return;
		postreq++;
		var href = (append?next_href:prev_href);
		loadEvents(href,function(monthContainer,month){
			monthContainer.children().hide();
			if ( append ){
				jQuery('#event-listing').append(monthContainer);
				next_href = incrementHref(next_href);
			}else{
				jQuery('#event-listing').prepend(monthContainer);
				prev_href = decrementHref(prev_href);
			}
			filterPosts(curCat,null,function(){
				innerScroll.updateDimensions();	
				if ( callback ) callback();
				postreq--;
				runRequestQueue();
			});
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

		// if an animation is in progress, do not interfere
		if ( innerScroll.isAnimating() ) {
			setTimeout("unloadMonths()",100);
			return;
		}
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
		innerScroll.scrollToChild(post,duration,callback);
	};
	event_listing.scrollToPost = scrollToPost;

	// scroll to next post following the given cal day
	function scrollToCalDay(elem,duration,callback){
		scrollToPost(getPostFromCalDay(elem),duration,callback);
	};
	event_listing.scrollToCalDay = scrollToCalDay;

	// return the first/last event day from given calendar
	function getEventDay(curCal,first){
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

	function getTimeFromMonth(month){
		month = month.split('_');
		return new Date(month[0],month[1]-1,1).getTime();
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

		var callback = function(curCal){
			return function(){ 
				scrollToMonth(curCal,true,'slow',function(){
					updateCal = true;
					nextPrevReq--;
				});
			};
		};

		fun(function(curCal){
			updateCal = false;
			if ( reload ) 
				loadNewEvents(requestNextMonth,callback(curCal));
			else
				filterPosts(curCat,null,callback(curCal));
		});
	};
	event_listing.nextPrevClicked = nextPrevClicked;

	function onCategoryChanged(){
		filterPosts(curCat,function(){
			if ( !curPost.is(':visible') || curCat != topCat ||
				getCurMonth() != getPostMonth(curPost) ){
				scrollToMonth(getCurCalendar(),true,0);
			}else scrollToPost(curPost,0);
		});
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

	function setPostImageTop(){
		var top = jQuery('#single-post .entry-content').offset().top;
		top += (parseInt(jQuery('#single-post .entry-content').css('padding-top'),10)||0);
		var left = jQuery('#post-images').offset().left;
		jQuery('#post-images').offset({top:top,left:left});	
	}

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
				var spinner = jQuery('#freiland_spinner,#ec3_spinner');
				if ( spinner.length > 0 ) spinner.show();
				jQuery.get(jQuery(this).attr('href'), function(data){
					jQuery('#content #single-post').remove();
					jQuery('#post-images').remove();
					jQuery('#main').prepend(jQuery(data).find('#post-images'));
					jQuery('#content').append(getSinglePost(data));
					jQuery(listingElements).hide();
					jQuery('#event-listing > div > div.post').hide();
					jQuery('body').toggleClass('category-events',false);
					jQuery('body').toggleClass('single',true);
					scrollDiv.css({top:0});
					setPostImageTop();
					innerScroll.setTopmargin(scrollDiv.offset().top);
					innerScroll.setScrollableToTop(false);
					innerScroll.updateDimensions();	
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

	function getMonthIdFromHref(href){
		href = parseHref(href);
		return href[1]+'_'+href[2]
	}

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
		else if (callback) callback();
	}

	// update the current post to the first one shown
	// call updateCalendar
	updateCurPost = function(){
		if ( curPost.length != 0 ) {
			var newPost = curPost;
			while ( newPost.offset().top-topmargin < 0 ){
				var nextPost = newPost.nextAll(':visible').first();
				if ( nextPost.length == 0 ){
					var curparent = newPost.parent();
					do{
						curparent = curparent.next();
					}while(curparent.next().length > 0 && curparent.children(':visible').length == 0);
					nextPost = curparent.children(':visible').first();
					if ( nextPost.length == 0 )					
						break;
				}
				newPost = nextPost;
			}

			// only go to prev month if there was no reload before
			while ( newPost.offset().top-topmargin > 0 ) {
				var prevPost = newPost.prevAll(':visible').first();
				if ( prevPost.length == 0 ){
					var curparent = newPost.parent();
					do{
						curparent = curparent.prev();
					}while(curparent.prev().length > 0 && curparent.children(':visible').length == 0);
					prevPost = curparent.children(':visible').last();
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

	function checkForUpdate(ontop,onbottom){
		// scroll reached the bottom
		if ( onbottom ){
			var nextMonth = getTimeFromMonth(getMonthIdFromHref(next_href));
			if ( nextMonth <= getTimeFromMonth(ec3.last_month) )
				loadNewEvents(true,unloadMonths);
		}

		// scroll reached the top
		else if ( ontop ){
			var prevMonth = getTimeFromMonth(getMonthIdFromHref(prev_href));
			if ( prevMonth >= getTimeFromMonth(ec3.first_month) )
				loadNewEvents(false,unloadMonths);
		}
	};
	event_listing.checkForUpdate = checkForUpdate;

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

