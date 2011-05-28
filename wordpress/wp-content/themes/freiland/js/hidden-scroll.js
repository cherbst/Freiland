jQuery(document).ready(function(){
	var elem = jQuery('#event-listing');
	if ( elem.length == 0 )
		elem = jQuery('#content');

	var ontop = false;
	var onbottom = false;

	elem.mousewheel(function(event, delta) {
		var inittop = event_listing.topmargin;
		var cur = elem.offset();
		var v = 50;
		var ret = true;
		var trigger = !(ontop || onbottom);
		var newTop = cur.top+(delta*v);
		var minTop = 
		    jQuery('#container').offset().top + jQuery('#container').height() - elem.height();
		newTop = Math.max(newTop,minTop);
		newTop = Math.min(newTop,inittop);

		if( newTop <= minTop && delta < 0){
			onbottom = true;
		}else if (newTop >= inittop && delta > 0 ){
			ontop = true;
		}else{
			ontop = onbottom = false;
			ret = false;
			elem.offset({top:newTop,left:cur.left});
		}
		if ( trigger )
			elem.trigger('hiddenscroll',[ontop,onbottom]);
		return ret;
	});
});
