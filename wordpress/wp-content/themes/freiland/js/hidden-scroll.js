function innerScroll(elem){
	var ontop = false;
	var onbottom = false;
	var topmargin = elem.offset().top;
	var originalTopmargin = topmargin;
	var oldY;
	var dragging = false;

	elem.css('position','relative');
	elem.css('top',0);
	elem.css('overflow-y','hidden');
	elem.height('auto');

	function getMinTop(){
		return jQuery('#footer').offset().top - elem.height() - topmargin;
	}

	function getTop(){
		return (parseInt(elem.css('top'),10) || 0 );
	}

	function scrollElem(delta,move,v){
		if ( !v ) v = 50;
		var ret = true;
		var triggerTop = !ontop;
		var triggerBottom = !onbottom;
		var curTop = getTop();
		var newTop = curTop+(delta*v);
		var minTop = getMinTop();
		var allmost = 50;

		if ( delta < 0 )
			newTop = Math.max(newTop,minTop);
		else
			newTop = Math.min(newTop,0);

		if( newTop <= minTop + allmost && delta < 0){
			onbottom = true;
			ontop = false;
		}else if (newTop >= - allmost && delta > 0 ){
			ontop = true;
			onbottom = false;
		}else{
			ontop = onbottom = false;
			ret = false;
		}
		if ( (newTop < curTop && delta < 0 || newTop > curTop && delta > 0 ) && move )
			elem.css('top',newTop);
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

	elem.mousewheel(function(event, delta) {
		return scrollElem(delta,true);
	});

	function setContainment(){
		elem.draggable( "option", "containment",  
			[0,Math.min(getMinTop() + topmargin,topmargin),0,  topmargin]);
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
}
		
jQuery(document).ready(function(){
	innerScroll(jQuery('#content'));
});
