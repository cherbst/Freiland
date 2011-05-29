function innerScroll(elem){
	var ontop = false;
	var onbottom = false;
	var inittop = elem.offset().top;

	elem.mousewheel(function(event, delta) {
		var cur = elem.offset();
		var v = 50;
		var variance = 100;
		var ret = true;
		var trigger = !(ontop || onbottom);
		var newTop = cur.top+(delta*v);
		var minTop = 
		    jQuery('#container').offset().top + jQuery('#container').height() - elem.height() - variance;
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
}
