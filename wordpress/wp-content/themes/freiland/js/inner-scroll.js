function innerScroll(){
	var ontop = false;
	var onbottom = false;
	var topmargin;
	var originalTopmargin;
	var oldY;
	// if the elem should be allowed to scroll until the top
	var scrollableToTop = false;
	var allmost = 50;
	var topScrollMargin = 50;
	var intervalId = false;
	// used to throttle mouse wheel events
	var fireScroll = true;
	// set if an animation is running
	var animating = false;
	// the element being scrolled
	var elem;
	// scroll controls
	var controls;
	// is mouse over scroll elem
	var mouseOverElem = false;

	function init(e){
		elem = e;
		elem.css({'position' : 'relative',
			  'top':0,
			  'padding-top':0,
			  'padding-bottom':0,
			  'overflow-y': 'hidden'});
		elem.height('auto');
 		topmargin = elem.offset().top;
		originalTopmargin = topmargin;
		innerScroll.addControls(jQuery('#footer'),elem.parent());
		// on each mousedown, re-compute the containment
		elem.mousedown(innerScroll.setContainment);
		jQuery(document).keydown(innerScroll.onKeyDown);
		elem.parent().mousewheel(innerScroll.onMousewheel);
		elem.kinetic({moved: function(settings){
			scrollElem(-settings.velocityY,false,1);
		}});
	}
	innerScroll.init = init;

	// scroll the elem to the give top value
	function scrollToTop(newTop,duration,callback){
		elem.animate({ top: newTop},duration,function(){
			animating = false;
			positionChanged();
			if ( callback ) callback();
		});
	}

	// scroll the elem by the given delta
	function scrollByDelta(delta){
		var v = 100;
		// throttle animations
		if ( elem.queue().length >= 2 ) return;
		elem.promise().done(function(){
			animating = true;
			var newTop = getNewTop(delta,v);
			if ( newTop != getTop() && intervalId === false )
				scrollToTop(newTop,'slow');
			else
				animating = false;
		});
	}

	// scroll elem to the given child
	function scrollToChild(child,duration,callback){
		animating = true;
		var cur = elem.offset();
		var newTop = cur.top - child.offset().top;
		scrollToTop(newTop,duration,callback);
	}
	innerScroll.scrollToChild = scrollToChild;

	function isAnimating(){
		return animating;
	}
	innerScroll.isAnimating = isAnimating;

	function fitsInView(){
		return ( scrollableToTop && topScrollMargin >= elem.height() || 
			!scrollableToTop && elem.height() < elem.parent().height() - 
				topmargin - jQuery('#footer').height() );
	}

	function updateControls(){
		if ( !fitsInView() && mouseOverElem && controls.is(':hidden'))
			controls.fadeIn('fast');
		else if ( fitsInView() || !mouseOverElem && controls.is(':visible') )
			controls.fadeOut('fast');
	}

	// add scroll navigation
	function addControls(container,hoverElem){
		controls = jQuery('<div id="scrollNav"><a id="scrollUp" href="#"><div></div></a>'+
			  	'<a id="scrollDown"href="#" ><div></div></a></div>');
		container.append(controls);
		container.hover(function(){
			mouseOverElem = true;
			updateControls();
		},function(event){
			mouseOverElem = false;
			// delay update
			setTimeout(function(){updateControls();},10);
		});
		hoverElem.hover(function(){
			mouseOverElem = true;
			updateControls();
		},function(event){
			mouseOverElem = false;
			// delay update
			setTimeout(function(){updateControls();},10);
		});

		jQuery('#scrollUp,#scrollDown').mousedown(function(){
			var source = jQuery(this);
			scrollByDelta(source.attr('id')=='scrollUp'?1:-1);
			intervalId = true;
			elem.promise().done( function(){
				if ( intervalId === true && elem.queue().length == 0 ){
					intervalId = setInterval(function(){
						scrollElem(source.attr('id')=='scrollUp'?1:-1,true,10);
					}, 10);
				}
			});
			return false;
		}).mouseup(function() {
			clearInterval(intervalId);
			intervalId = false;
			return false;
		}).mouseleave(function(){
			clearInterval(intervalId);
			intervalId = false;
		}).click(function(){
			return false;
		});
	};
	innerScroll.addControls = addControls;

	function getMinTop(){
		return jQuery('#footer').offset().top - elem.height() - topmargin;
	}

	function getTop(){
		return (parseInt(elem.css('top'),10) || 0 );
	}
	
	function getNewTop(delta,v){
		var newTop = getTop()+(delta*v);
		if ( delta < 0 )
			newTop = Math.max(newTop,scrollableToTop? -elem.height()+topScrollMargin:getMinTop());
		else
			newTop = Math.min(newTop,0);
		return newTop;
	}

	function allowedToMove(newTop,curTop,delta){
		return  (newTop < curTop && delta < 0 || newTop > curTop && delta > 0 );
	}

	function scrollElem(delta,move,v){
		if ( !v ) v = 50;
		var ret = true;
		var triggerTop = !ontop;
		var triggerBottom = !onbottom;
		var curTop = getTop();
		var minTop = getMinTop();
		var newTop = getNewTop(delta,v);

		if( newTop <= minTop + allmost && delta < 0){
			onbottom = true;
			ontop = false;
		}else if (newTop >= - allmost && delta > 0 ){
			ontop = true;
			onbottom = false;
		}else{
			ontop = onbottom = false;
		}
		if ( allowedToMove(newTop,curTop,delta) && move ){
			ret = false;
			elem.css('top',newTop);
		}
		if ( triggerTop || triggerBottom ){
			elem.trigger('hiddenscroll',[triggerTop && ontop,triggerBottom && onbottom]);
		}
		return ret;
	}

	function positionChanged(){
		// call to trigger ontop/onbottom events
		scrollElem(-1,false);
		scrollElem(1,false);
	}

	function onKeyDown(event){
		var delta = 0;
		if ( event.which == 40 )
			delta = -1;
		else if ( event.which == 38 )
			delta = 1;
		if ( delta != 0 )
			if ( !scrollElem(delta,true) )
				event.preventDefault();
	}
	innerScroll.onKeyDown = onKeyDown;

	function onMousewheel(event, delta) {
		if ( !allowedToMove(getTop()+delta,getTop(),delta) )
			return true;

		if ( fireScroll ){
			fireScroll = false;
			setTimeout(function(){ fireScroll = true; },100);
			return scrollElem(delta,true);
		}
		return false;
	}
	innerScroll.onMousewheel = onMousewheel;

	function setContainment(){
		var containment = scrollableToTop?
			[0, - elem.height() + topScrollMargin,0,  0]:
			[0, getMinTop() ,0,  0];

		elem.data( "containment", containment );
	}
	innerScroll.setContainment = setContainment;

	function setTopmargin(margin){
		topmargin = margin;
	};
	innerScroll.setTopmargin = setTopmargin;

	function resetTopmargin(){
		topmargin = originalTopmargin;
	};
	innerScroll.resetTopmargin = resetTopmargin;

	function updateDimensions(){
		ontop = onbottom = false;
		setContainment();
	};
	innerScroll.updateDimensions = updateDimensions;

	function setRelativeTop(offset){
		updateDimensions();
		elem.css({top:getTop()+offset});
	};
	innerScroll.setRelativeTop = setRelativeTop;

	function setTop(offset){
		elem.css({top:offset});
	};
	innerScroll.setTop = setTop;

	function setScrollableToTop(val,margin){
		scrollableToTop = val;
		if ( margin != 'undefined' && margin != null)
			topScrollMargin = margin;
		updateControls();
	}
	innerScroll.setScrollableToTop = setScrollableToTop;
}

innerScroll();
jQuery(document).ready(function(){
	innerScroll.init(jQuery('#content'));
});
