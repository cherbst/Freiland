function innerScroll(elem){
	var ontop = false;
	var onbottom = false;
	var inittop = elem.offset().top;

	elem.mousewheel(function(event, delta) {
		var cur = elem.offset();
		var v = 50;
		var variance = 100;
		var ret = true;
		var triggerTop = !ontop;
		var triggerBottom = !onbottom;
		var newTop = cur.top+(delta*v);
		var minTop = 
		    jQuery('#container').offset().top + jQuery('#container').height() - elem.height() - variance;

		if ( delta < 0 )
			newTop = Math.max(newTop,minTop);
		else
			newTop = Math.min(newTop,inittop);

		if( newTop <= minTop && delta < 0){
			onbottom = true;
			ontop = false;
		}else if (newTop >= inittop && delta > 0 ){
			ontop = true;
			onbottom = false;
		}else{
			ontop = onbottom = false;
			ret = false;
		}
		if ( newTop < cur.top && delta < 0 || newTop > cur.top && delta > 0 )
			elem.offset({top:newTop,left:cur.left});
		if ( triggerTop || triggerBottom ){
			elem.trigger('hiddenscroll',[triggerTop && ontop,triggerBottom && onbottom]);
		}
		return ret;
	});
}
		
jQuery(document).ready(function(){
	innerScroll(jQuery('#content'));
});
