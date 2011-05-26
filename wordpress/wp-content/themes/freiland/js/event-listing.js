function event_listing(){

	var topmargin;
	// count ajax requests for posts
	var postreq = 0;
	var next_href;
	var prev_href;
	var curPost;
	var curCat,topCat;
	var pageLoaded = true;
	var updateCal = true;
	// all elements that need to be shown/hidden when loading one event
	var listingElements = '#mainpost-banner,#event-listing,#wp-calendar,#eventtypes';
	// all event title links
	var eventSelector = '#event-listing h2.entry-title > a';
	// all subcategory links
	var subcatSelector = '#eventtypes > ul > li > a,ul.children > li > a,ul.event-subcat > li > a';
	// next/prev post
	var nextPrevSelector = '.nav-previous > a,.nav-next > a';
	var baseURL;


	var debug = function(text){
		jQuery('#topright > span').append(text+"</br>");
	};

	function init(){
		topmargin = jQuery('#event-listing > div.post:first').offset().top;
		next_href = jQuery('#ec3_next:first').attr('href');
		prev_href = jQuery('#ec3_prev:first').attr('href');

		// initialize current cat from 'events' menu item
		curCat = event_listing.getCatId(jQuery('.current-menu-item'));
		// the top-level category, this never changes
		topCat = curCat;
		// set 'current-cat' class
		jQuery('#eventtypes ul li.cat-item-'+curCat).addClass('current-cat');

		// the post which is currently on top
		curPost = event_listing.getPostFromCalDay(jQuery('#today'));

		// when loading the page, scroll to next active event
		event_listing.scrollToPost(curPost,0);
		// on page load, check for update
		event_listing.checkForUpdate();
	};
	event_listing.init = init;

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

	var findNextCurPost = function(){
		var newPost = curPost;
		// select nearest visible post
		if ( newPost.filter(':visible').length == 0 ){
			var nextPost = newPost.nextAll(':visible').first();
			var prevPost = newPost.prevAll(':visible').first();

			if ( nextPost.length == 0 && prevPost.length == 0 )
				return;
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
			if ( updateCal )
				updateCalendar(jQuery('#wp-calendar > table').filter(':visible'));
		});
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

	// scroll to given post
	function scrollToPost(post,duration,callback){
		if ( post.length == 0 || duration === false ){
			if ( callback ) callback();
			return ;
		}
		var offset = post.offset().top - (topmargin);
		jQuery('html,body').animate({scrollTop:offset},duration,callback);
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

	// load new events with ajax
	// apend/prepend them to the list and filter them
	loadNewEvents = function(append,callback){
		if ( postreq > 0 ) return;
		postreq++;
	 	jQuery.get((append?next_href:prev_href), function(data){
			var content = jQuery(data).find('#event-listing').contents();
			if ( content.length > 0 ){
				if ( append ){
					jQuery('#event-listing').append(content);
				}else{
					jQuery('#event-listing').prepend(content);
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
					scrollToMonth(curCal,requestNextMonth,'slow',function(){
						updateCal = true;
					});
				});
			else scrollToMonth(curCal,'slow',function(){
				updateCal = true;
			});
		});
	};
	event_listing.nextPrevClicked = nextPrevClicked;

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
	function initAddress(){
                // Initializes the plugin
		baseURL = jQuery.address.baseURL().replace('events','');
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
	};

	function checkForUpdate(){
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
	event_listing.checkForUpdate = checkForUpdate;

	// update calendar and load new events when scrolling through event list
	function onScrolled(){
		var elem = jQuery(window);
		// XXX: workaround to prevent scrolling to top on ajax page reload
		if ( pageLoaded ) {
			if ( elem.scrollTop() == 0 )
				scrollToPost(curPost);
			pageLoaded = false;
			return;
		}

		// do nothing when showing single posts
		if ( jQuery('#single-post').length > 0 ) return;

		if ( updateCal ) 
			updateCurPost();

//		debug('curPost:'+curPost.find('a').attr('title')+':updateCal:'+updateCal);
		checkForUpdate();
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

	// update calendar and load new events when scrolling through event list
	jQuery(document).scroll(function(e){
		event_listing.onScrolled();
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

