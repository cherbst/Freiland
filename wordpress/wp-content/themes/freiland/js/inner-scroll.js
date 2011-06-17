function innerScroll(elem){
	var ontop = false;
	var onbottom = false;
	var topmargin = elem.offset().top;
	var originalTopmargin = topmargin;
	var oldY;
	var dragging = false;
	// if the elem should be allowed to scroll until the top
	var scrollableToTop = false;
	var allmost = 50;
	var topScrollMargin = 50;

	elem.css('position','relative');
	elem.css('top',0);
	elem.css('overflow-y','hidden');
	elem.height('auto');


	function animateScroll(delta){
//		var newTop = getTop() + offset;
		var v = 100;
		elem.promise().done(function(){
			var newTop = getNewTop(delta,v);
			if ( newTop != getTop() )
				elem.animate({ top: newTop},'slow',function(){
					positionChanged();
				});
		});
	}

	function addControls(){
		var nav = jQuery('<div id="scrollNav"><a id="scrollUp" href="#"><div></div></a>'+
			  	'<a id="scrollDown"href="#" ><div></div></a></div>');
		jQuery('#footer').append(nav);
		jQuery('#footer').hover(function(){
			if ( elem.height() > elem.parent().height() - topmargin )
				nav.fadeIn('fast');
		},function(event){
			if ( elem.find('#'+jQuery(event.relatedTarget).attr('id')).length == 0 )
				nav.fadeOut('fast');
		});
		elem.hover(function(){
			if ( elem.height() > elem.parent().height() - topmargin )
				nav.fadeIn('fast');
		},function(event){
			if ( jQuery(event.relatedTarget).attr('id') != 'footer' )
				nav.fadeOut('fast');
		});
		nav.fadeOut('slow');
		jQuery('#scrollUp').click(function(){
			animateScroll(1);
			return false;
		});
		jQuery('#scrollDown').click(function(){
			animateScroll(-1);
			return false;
		});
	};
	addControls();


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
		if ( (newTop < curTop && delta < 0 || newTop > curTop && delta > 0 ) && move ){
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
	innerScroll.positionChanged = positionChanged;

	jQuery(document).keydown(function(event){
		var delta = 0;
		if ( event.which == 40 )
			delta = -1;
		else if ( event.which == 38 )
			delta = 1;
		if ( delta != 0 )
			if ( !scrollElem(delta,true) )
				event.preventDefault();
	});

	elem.parent().mousewheel(function(event, delta) {
		return scrollElem(delta,true);
	});

	function setContainment(){
		var containment = scrollableToTop?
			[0, - elem.height() + topmargin + topScrollMargin,0,  topmargin]:
			[0,Math.min(getMinTop() + topmargin,topmargin),0,  topmargin];

		elem.draggable( "option", "containment", containment );
	}
	// on each mousedown, re-compute the containment
	elem.mousedown(setContainment);

	elem.draggable({
		start: function(event,ui){
			dragging = true;
			oldY = ui.position.top;
		},
		stop: function(){
			dragging = false;
		}, 
		drag: function(event,ui) {
			var delta = ui.position.top - oldY;
			oldY = ui.position.top;
			scrollElem(delta,false,1);
		},
		axis: "y",
		cancel: "#summary-text,p,a"
	});

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
		if ( dragging )
			elem.data('draggable').offset.click.top -= offset;
		elem.css({top:getTop()+offset});
	};
	innerScroll.setRelativeTop = setRelativeTop;

	function setTop(offset){
		if ( dragging )
			elem.data('draggable').offset.click.top = offset;
		elem.css({top:offset});
	};
	innerScroll.setTop = setTop;

	function setScrollableToTop(val,margin){
		scrollableToTop = val;
		if ( margin != 'undefined' && margin != null)
			topScrollMargin = margin;
	}
	innerScroll.setScrollableToTop = setScrollableToTop;
}
		
jQuery(document).ready(function(){
	innerScroll(jQuery('#content'));
});
