jQuery(document).ready(function(){
	var elem = jQuery('#event-listing');
	if ( elem.length == 0 )
		elem = jQuery('#content');

	var inittop = elem.offset().top;

	elem.mousewheel(function(event, delta) {
		var cur = elem.offset();
		var v = 50;
		var last = elem.children('div:last');
		if( (last.offset().top + last.height() < 
			jQuery('#container').offset().top + jQuery('#container').height()) && delta < 0 ||
		    (cur.top >= inittop && delta > 0 ) )
			return true;
		elem.offset({top:cur.top+(delta*v),left:cur.left});
		return false;
	});
});
