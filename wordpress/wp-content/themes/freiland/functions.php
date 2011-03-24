<?php
/**
 * Freiland functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Freiland
 * @since freiland 0.1
 */

  function get_event_meta_content($post_id){
	$return = '<table class="event_meta">';
	$metadata = get_post_custom_keys();
	foreach( $metadata as $key ){
		$keyt = trim($key);
		if ( '_' == $keyt{0} || 'homepage' == $keyt )
			continue;
		$meta = get_post_meta($post_id,$key,true);
		if ( $meta == null ) continue;
		$return .= '<tr><td class="'.$key.'-label">'.$key.'</td>';
		$return .= '<td class="'.$key.'-content">'.$meta.'</td></tr>';
	}
	$homepage = get_post_meta($post_id,'homepage',true);
	$homepagelabel = get_post_meta($post_id,'homepagelabel',true);
	if ( $homepage != null ){
		foreach( $homepage as $key => $link ){
			$return .= '<tr><td class=homepage"'.$key.'-label">homapage'.$key.'</td>';
			$return .= '<td class=homepage"'.$key.'-content">';
			$label = ( isset($homepagelabel[$key])?$homepagelabel[$key]:$link );
			$return .= '<a href="'.$link.'">'.$label.'</a></td></tr>';
		}
	}
	$return .= '</table>';
	$args = array(  'post_type' => 'attachment', 
			'numberposts' => -1, 
			'post_status' => null, 
			'post_parent' => $post_id ); 
	$attachments = get_posts($args);
	if ($attachments) {
		foreach ( $attachments as $attachment ) {
			$return .= wp_get_attachment_image( $attachment->ID );
		}
	}else $return .= 'No images in this event:'.$post_id;

	return $return;
  }

  function get_news_content(){
	return 'NEWS:';
  }

  function get_press_content(){
	return 'PRESS:';
  }

?>
