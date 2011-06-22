jQuery(document).ready(function(){
	function resizeImages(){
		var imgs = 'div.sponsor > a > img';
		var num = jQuery(imgs).length;
		var paddingRight = (parseInt(jQuery(imgs).first().css('padding-right'),10) || 0 )
		var paddingLeft =  (parseInt(jQuery(imgs).first().css('padding-left'),10) || 0 );
		var maxWidth =  (parseInt(jQuery(imgs).first().css('max-width'),10) || 0 );
		var minWidth =  (parseInt(jQuery(imgs).first().css('min-width'),10) || 0 );
		var padding = paddingLeft + paddingRight;
		var newWidth;
		var availableWidth = jQuery('div.sponsor').width();

		do{
			newWidth = (availableWidth-padding*(num-1))/num;
			if ( maxWidth > 0 ) newWidth = Math.min(newWidth,maxWidth); 
			availableWidth *= 2;
		}while( newWidth < minWidth );

		jQuery(imgs).css('width',newWidth);
		if ( paddingLeft > 0 )
			jQuery(imgs).last().css('padding-left',0);
		else if ( paddingRight > 0 )
			jQuery(imgs).last().css('padding-right',0);
	}

	resizeImages();
  	jQuery(document).ajaxComplete(function(){
		resizeImages();
	});
});
