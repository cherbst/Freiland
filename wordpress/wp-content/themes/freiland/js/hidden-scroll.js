jQuery(document).ready(function(){
	var elem = jQuery('#event-listing');
	if ( elem.length == 0 )
		elem = jQuery('#content');

	var inittop = jQuery('#header').height();
	var ontop = false;
	var onbottom = false;

	elem.mousewheel(function(event, delta) {
		var cur = elem.offset();
		var v = 50;
		var last = elem.children('div:last');
		var ret = true;
		var trigger = !(ontop || onbottom);
		if( last.offset().top + last.height() < 
		    jQuery('#container').offset().top + jQuery('#container').height() &&
 		    delta < 0){
			onbottom = true;
		}else if (cur.top >= inittop && delta > 0 ){
			ontop = true;
		}else{
			ontop = onbottom = false;
			ret = false;
			elem.offset({top:cur.top+(delta*v),left:cur.left});
		}
		if ( trigger )
			elem.trigger('hiddenscroll',[ontop,onbottom]);
		return ret;
	});
});
