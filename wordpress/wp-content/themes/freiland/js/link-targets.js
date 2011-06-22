jQuery(document).ready(function(){
	function setLinkTargets(){
		jQuery('.widget_nav_menu a,' +
		       '.homepage > a,' + 
		       '.Linklist > li > a').attr('target','_blank');
	};
	setLinkTargets();
	jQuery(document).ajaxComplete(setLinkTargets);
});
