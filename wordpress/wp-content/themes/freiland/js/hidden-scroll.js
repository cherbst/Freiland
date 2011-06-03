function innerScroll(elem){
	var ontop = false;
	var onbottom = false;

	elem.css('position','relative');
	elem.css('top',0);
	elem.mousewheel(function(event, delta) {
		var v = 50;
		var variance = 100;
		var ret = true;
		var triggerTop = !ontop;
		var triggerBottom = !onbottom;
		var curTop = parseInt(elem.css('top'),10);
		var newTop = curTop+(delta*v);
		var minTop = - elem.height() + elem.parent().height()/2;

		if ( delta < 0 )
			newTop = Math.max(newTop,minTop);
		else
			newTop = Math.min(newTop,0);

		if( newTop <= minTop && delta < 0){
			onbottom = true;
			ontop = false;
		}else if (newTop >= 0 && delta > 0 ){
			ontop = true;
			onbottom = false;
		}else{
			ontop = onbottom = false;
			ret = false;
		}
		if ( newTop < curTop && delta < 0 || newTop > curTop && delta > 0 )
			elem.css('top',newTop);
		if ( triggerTop || triggerBottom ){
			elem.trigger('hiddenscroll',[triggerTop && ontop,triggerBottom && onbottom]);
		}
		return ret;
	});
}
		
jQuery(document).ready(function(){
	innerScroll(jQuery('#content'));
});
