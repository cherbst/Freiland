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
add_filter( 'the_content', 'freiland_filter_the_content',7 );
function freiland_filter_the_content( $post_content ) {
       if ( in_category( 'events' ) )
               return freiland_get_event_content($post_content);
       else if ( in_category( 'news') )
               return freiland_get_news_content($post_content);
       else if ( in_category( 'press') )
               return freiland_get_press_content($post_content);
       return $post_content;
}


  function freiland_get_event_content($content){
	global $post;
	$return = '<table class="event_meta">';
	$metadata = get_post_custom_keys();
	foreach( $metadata as $key ){
		$keyt = trim($key);
		if ( '_' == $keyt{0} )
			continue;
		$meta = get_post_meta($post->ID,$key,true);
		if ( $meta == null || is_array($meta)) continue;
		$return .= '<tr><td class="'.$key.'-label">'.$key.'</td>';
		$return .= '<td class="'.$key.'-content">'.$meta.'</td></tr>';
	}
	$return .= '</table>';
	$homepage = get_post_meta($post->ID,'homepage',true);
	$homepagelabel = get_post_meta($post->ID,'homepagelabel',true);
	if ( $homepage != null ){
		foreach( $homepage as $key => $link ){
			if ( isset($homepagelabel[$key]) )
				$return .= '<p>'.$homepagelabel[$key].'</p>';
			else $return .= '</br>';
			$return .= '[embed]'.$link.'[/embed]';
		}
		$return .= "\n";
	}

	$args = array(  'post_type' => 'attachment', 
			'numberposts' => -1, 
			'post_status' => null, 
			'post_parent' => $post->ID ); 
	$attachments = get_posts($args);
	if ($attachments) {
		foreach ( $attachments as $attachment ) {
			$return .= wp_get_attachment_image( $attachment->ID );
		}
	}else $return .= 'No images in this event:'.$post->ID;

	return $content . $return;
  }

  function freiland_get_news_content($content){
	global $post;
	$subtitle = get_post_meta($post->ID,'subtitle',true);
	$homepage = get_post_meta($post->ID,'homepage',true);
	$homepagelabel = get_post_meta($post->ID,'homepagelabel',true);
	$label = ($homepagelabel==null)?$homepage:$homepagelabel;

	$return = '<p>'. $subtitle .'</p>';
	$return .= $content;
	$return .= '<p><a href="'.$homepagelabel.'">'.$label.'</a></p>';
	return $return;
  }

  function freiland_get_press_content($content){
	return 'PRESS:'.$content;
  }

  /* load ec3 plugin if it exists
  */
  function freiland_load_ec3(){
	// check if ec3 plugin is activated
	$ret = false;
	$plugins = get_option('active_plugins');
	$ec3plugin = 'eventcalendar3.php';
	foreach( $plugins as $plugin ){
		if ( strpos($plugin,$ec3plugin) === false ) continue;
		$admin_php = str_replace($ec3plugin,'admin.php',$plugin);
		require_once(ABSPATH . "wp-content" . '/plugins/'.$admin_php);
		$ret = true;
		break;
	}
	return $ret;
  }

?>
