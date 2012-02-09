if ( typeof lightbox_path == "undefined" ) var lightbox_path = 'http://'+location.hostname+'/wp-content/plugins/lightbox-gallery/';
if ( typeof hs != "undefined" ) {
	if ( typeof graphicsDir != "undefined" ) hs.graphicsDir = graphicsDir;
	else  hs.graphicsDir = 'http://'+location.hostname+'/wp-content/plugins/lightbox-gallery/graphics/';
}


if ( typeof hs == "undefined" ) {
  	jQuery(document).ajaxComplete(function(){
		initlightbox();
	});

	jQuery(document).ready(function () {
		initlightbox();
	});
	
	function initlightbox(){
// If you make images display slowly, use following two lines;
//	var i = 0;
//	showImg(i);

		jQuery('a[rel*=lightbox]').lightBox();
		jQuery('.gallery a').removeAttr("title");
		jQuery('.gallery1 a').lightBox({captionPosition:'gallery'});

// Add these lines if you want to handle multiple galleries in one page.
// You need to add into a [gallery] shorttag. ex) [gallery class="gallery2"] 
//  jQuery('.gallery2 a').lightBox({captionPosition:'gallery'});
//  jQuery('.gallery3 a').lightBox({captionPosition:'gallery'});
	}

	function showImg(i){
		if(i == jQuery('img').length){
			return;
		}else{
			jQuery(jQuery('img')[i]).animate({opacity:'show'},"normal",function(){i++;showImg(i)});
		}
	}
}
