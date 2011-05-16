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
add_filter( 'the_excerpt', 'freiland_filter_the_excerpt',7 );
add_filter( 'excerpt_more', 'freiland_filter_excerpt_more');
add_filter( 'excerpt_length', 'freiland_filter_excerpt_length',999);
add_filter( 'the_title', 'freiland_filter_the_title',7,2 );
add_filter( 'walker_nav_menu_start_el', 'freiland_filter_start_el',11,2 );
add_filter( 'nav_menu_css_class', 'freiland_filter_menu_classes',11,2 );
add_filter( 'post_class', 'freiland_post_class_filter');
add_action( 'init', 'freiland_init_method');
add_action( 'wp_enqueue_scripts', 'freiland_enqueue_scripts');


function freiland_init_method() {
	wp_register_script('event-listing',
	dirname(get_bloginfo('stylesheet_url')) . '/js/event-listing.js',
	array('jquery'));
}

function freiland_enqueue_scripts() {
	if ( in_category('events') )
		wp_enqueue_script('event-listing');
}

function freiland_post_class_filter($classes) {
	if ( !in_category('events') ) return $classes;
	global $post;
	foreach((get_the_category($post->ID)) as $category)
		$classes[] = 'cat-id-' . $category->term_id;
	return $classes;
}

function freiland_filter_menu_classes( $classes, $item ){
	$cat = get_category_by_slug('events');
	if ( $cat == null || $cat->term_id != $item->object_id )
		return $classes;
	return array_merge( $classes, array( 'cat-item-'.$cat->term_id) );
}

function freiland_filter_start_el( $item_output, $item ){
	$cat = get_category_by_slug('events');
	if ( $cat == null || $cat->term_id != $item->object_id || !in_category('events'))
		return $item_output;
	$item_output .= '<ul class="children">';
	$item_output .= freiland_get_event_locations(0);
	$item_output .= '</ul>';
	return $item_output;
}

function freiland_filter_the_content( $post_content ) {
       if ( in_category( 'events' ) )
               return freiland_get_event_content($post_content);
       else if ( in_category( 'news') )
               return freiland_get_news_content($post_content);
       else if ( in_category( 'press') )
               return freiland_get_press_content($post_content);
	else if ( is_page( 'links') )
               return freiland_get_links_content($post_content);
       return $post_content;
}

function freiland_filter_the_excerpt( $post_excerpt ) {
       if ( in_category( 'news') )
               return freiland_get_news_excerpt($post_excerpt);
       return $post_excerpt;
}

function freiland_filter_the_title( $post_title, $id ) {
	global $post;
	if ( in_category( 'press') && in_the_loop() && $id == $post->ID )
		return freiland_get_press_title($post_title,$id);
	return $post_title;
}


  function freiland_get_event_content($content){
	global $post;
	$args = array(  'post_type' => 'attachment', 
			'numberposts' => -1, 
			'post_status' => null, 
			'post_parent' => $post->ID ); 

	$return  = freiland_get_post_image($post->ID,'image_big');
	$return .= freiland_get_post_image($post->ID,'sponsor');
	$return .= $content ;
	$homepage = get_post_meta($post->ID,'homepage',true);
	$homepagelabel = get_post_meta($post->ID,'homepagelabel',true);
	if ( $homepage != null ){
		if ( !is_array($homepage ) ){
			$homepage = array($homepage);
			$homepagelabel = array($homepagelabel);
		}
		foreach( $homepage as $key => $link ){
			if ( isset($homepagelabel[$key]) )
				$return .= '<p>'.$homepagelabel[$key].'</p>';
			else $return .= '</br>';
			$return .= '[embed]'.$link.'[/embed]';
		}
		$return .= "\n";
	}

	// append link to the eventtype category
	$eventtypes = get_post_meta($post->ID,'eventtype',true);
	if ( $eventtypes ){ 
		if ( !is_array($eventtypes) )
			$eventtypes = array($eventtypes);
		foreach ( $eventtypes as $eventtype ){
			$eventcat = get_term_by('name', $eventtype, 'category');
			if ( $eventcat )
				$cats[] = $eventcat->term_id;
		}
		if ( $cats ){
			$cats = implode(',',$cats);
			$return .= '<ul class="event-subcat">';
			$return .=  wp_list_categories ("echo=0&include=$cats&title_li=");
			$return .= "</ul>";
		}
	}
	return $return;
  }

  function freiland_get_news_excerpt($excerpt){
	global $post;
	$return = freiland_get_post_image($post->ID,'image');
	return $return . $excerpt;
  }

  function freiland_get_news_content($content){
	global $post;
	$subtitle = get_post_meta($post->ID,'subtitle',true);
	$homepage = get_post_meta($post->ID,'homepage',true);
	$homepagelabel = get_post_meta($post->ID,'homepagelabel',true);
	$label = ($homepagelabel==null)?$homepage:$homepagelabel;

	$return = freiland_get_post_image($post->ID,'image');
	$return .= '<p>'. $subtitle .'</p>';
	$return .= $content;
	$return .= '<p><a href="'.$homepage.'">'.$label.'</a></p>';
	return $return;
  }

  function freiland_filter_excerpt_more($more){
	return __('weiter lesen','freiland');
  }

  function freiland_filter_excerpt_length($length){
	return 90;
  }

  function freiland_get_press_content($content){
	global $post;
	$return = '';
	$source = get_post_meta($post->ID,'source',true);
	$date = get_post_meta($post->ID,'date',true);
	$date =  new DateTime($date);	
	$subtitle = get_post_meta($post->ID,'subtitle',true);
	$homepage = get_post_meta($post->ID,'homepage',true);
	$homepagelabel = get_post_meta($post->ID,'homepagelabel',true);
	$label = ($homepagelabel==null)?$homepage:$homepagelabel;

	$return .= '<p>'. $subtitle .'</p>';
	$return .= $content .'<p/>';
	$return .= '<div class="source">'. $source .' '.$date->format('d.m.Y').'</div>';
	$return .= '<div class="press_homepage"><a href="'.$homepage.'">'.$label.'</a></div>';
	return $return;
  }

  function freiland_get_links_content($content){
	$link_categories = get_terms('link_category');
	$content = '';
	$exclude = array('blogroll','smwimport');
	foreach ( $link_categories as $cat ){
		if ( in_array($cat->slug,$exclude) ) continue;
		$content .= '<h1 class="link-category">'.$cat->name.'</h1>';
		$content .= '[blogroll-links categoryslug="'.$cat->slug.'"]'."\n";
	}
	return $content;
  }

  function freiland_get_press_title($title,$id){
	$img = freiland_get_favicon_img($id);
	return $img.$title;
  }

  function freiland_get_post_image($ID,$meta){
	$attach_ids = get_post_meta($ID,$meta,true);
	if ($attach_ids != "") {
		if ( !is_array($attach_ids))
			$attach_ids = array($attach_ids);

		$return = '<div class="'.$meta.'">';
		foreach( $attach_ids as $attach_id){
			$attach_id = intval($attach_id);
			$return .= '<a rel="lightbox" href="'.wp_get_attachment_url($attach_id).'">';
			$return .= wp_get_attachment_image( $attach_id,
				'thumbnail',false,array('class' => 'alignright' ) );
			$return .= '</a>';
		}
		$return .= '</div>';
	}
	return $return;
  }

  function freiland_get_favicon_img($id){
	$favicon = get_post_meta($id,'favicon',true);

	if ( $favicon != null )
		$return = '<img src="'.$favicon.'" class="alignleft"/>';

	return $return;
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
		$template_php = str_replace($ec3plugin,'template-functions.php',$plugin);
		require_once(ABSPATH . "wp-content" . '/plugins/'.$admin_php);
		require_once(ABSPATH . "wp-content" . '/plugins/'.$template_php);
		$ret = true;
		break;
	}
	return $ret;
  }

function freiland_subcategory_dropdown($cat_id){
	$filtercats = get_categories( "hide_empty=0&parent=$cat_id" );
	if ( empty($filtercats) ) return;
?>
	<form name="categoryfilterform" method="post" action="">
	<input type="hidden" name="hiddencategoryfilter" value="Y">

<?php
	global $query_string;
	parse_str( $query_string, $query_args );

	$filtered = false;
	foreach( $filtercats as $filtercat ){
		$name = 'filter_'.$filtercat->slug; 
    		if( isset($_POST[ 'hiddencategoryfilter' ]) && $_POST['hiddencategoryfilter'] == 'Y' ) {
			if ( $_POST[$name] ){
				$query_args['category__and'][] = (int)$_POST[$name];
				$filtered = true;
			}
		}

		$args = array('hide_empty' => 0, 
			'name' => $name, 
			'orderby' => 'name',
			'selected' => $_POST[$name],
			'show_option_all'    => "Show all $filtercat->name"."s" ,
			'hierarchical' => true,
			'parent' => $filtercat->term_id );  ?>

		<div class="<?php echo $name ?>"><p><?php _e("Filter by $filtercat->name:", 'freiland-category' ); 
			wp_dropdown_categories($args); ?>
		</p></div>
  <?php }
	if ( $filtered )
		query_posts($query_args);

	if ( ! empty($filtercats) ){ ?>
		<p class="submit">
			<input type="submit" name="filter" class="button-primary" 
				value="<?php esc_attr_e('Filter posts') ?>" />
		</p>
  <?php	} ?>
	</form>
<?php 
  }

  function freiland_query_only_events(){
	if ( freiland_load_ec3() ){
		// if ec3 is installed we only show posts from its event category
		global $ec3;
		global $wp_query;
		$args = array_merge( $wp_query->query, array('category__and' => array( $ec3->event_category ) ));
		query_posts($args);
	}
  }

  function freiland_location_submenu(){
	if(!is_single()){
		if ( in_category( 'events' ) ){ ?>
			<div id="submenu" class="menu-header">
				<ul class="menu"><?php
					$cat = get_category_by_slug('location');
					if ( $cat != null )
						wp_list_categories ('child_of='.$cat->term_id.'&title_li=');
				?></ul>
			</div>
		<?php }
	}
  }

  function freiland_the_event_date(){
	global $post;
	$begin_date = get_post_meta($post->ID,'date_begin',true);
	if ( $begin_date == "" ) return;
	$begin_date = strtotime($begin_date);
	echo '<div class="begin_date" id="';
	echo date("Y_n",$begin_date);
	echo '">';
	echo '<div class="event_day">';
	echo strftime("%A",$begin_date);
	echo '</div>';
	echo '<div class="event_date">';
	echo strftime("%d.%m.",$begin_date);
	echo '</div>';
	echo '<div class="event_time">';
	echo strftime("%H:%M",$begin_date) . " UHR";
	echo '</div>';
	echo '</div>';

	$end_date = get_post_meta($post->ID,'date_end',true);
	if ( $end_date == "" ) return;
	$end_date = strtotime($end_date);
	if ( $end_date == $begin_date ) return;
	echo '<div class="end_date">';
	echo '<div class="event_day">';
	echo strftime("%A",$end_date);
	echo '</div>';
	echo '<div class="event_date">';
	echo strftime("%d.%m.",$end_date);
	echo '</div>';
	echo '<div class="event_time">';
	echo strftime("%H:%M",$end_date) . " UHR";
	echo '</div>';
	echo '</div>';
  }

  function freiland_get_exclude_subcats($cat){
	// exclude categories which have no active posts
	if ( !freiland_load_ec3() ) return;
	$cats = get_categories( "parent=".$cat->term_id );
	foreach( $cats as $c ){
		if ( ec3_util_get_active_event_count($c->term_id) == 0 )
			$exclude[] = $c->term_id;
	}

	if ( !empty($exclude) )
		return implode(',',$exclude);
  }

  function freiland_get_event_locations($echo=1){
	$cat = get_category_by_slug('location');
	if ( $cat != null )
		return wp_list_categories ("echo=$echo&child_of=$cat->term_id&title_li=");
  }

  function freiland_get_eventtypes($echo=1){
	$cat = get_category_by_slug('eventtype');
	if ( $cat == null ) return;
	$args = array('echo' => 0,
		'child_of' => $cat->term_id,
		'title_li' => '');
	$return = wp_list_categories($args);
	// add link to 'event' category
	$cat = get_category_by_slug('events');
	if ( $cat != null ){
		$title =  __('Zeige alle','freiland');
		$link = get_category_link( $cat->term_id );
		$show_all  = '<li class="cat-item cat-item-'.$cat->term_id.'">';
		$show_all .= '<a href="'.$link.'" title="'.$title.'">';
		$show_all .=  $title.'</a></li>';
		$return = $show_all . $return;
	}
	if ( $echo == 1 )
		echo $return;
	return $return;
  }

  function freiland_the_event_type(){
	global $post;
	$eventtype = get_post_meta($post->ID,'eventtype',true);
	if ( is_array($eventtype) )
		$eventtype = implode(" ",$eventtype);
	echo $eventtype;
  }

  function freiland_the_event_genre(){
	global $post;
	$genre = get_post_meta($post->ID,'genre',true);
	if ( is_array($genre) )
		$genre = implode(" ",$genre);
	echo $genre;
  }

  function freiland_get_mainevent_banner(){
	$id = get_option('banner');
	if ( $id == false ) return;

	return wp_get_attachment_image( $id,
		'full',false,array('class' => 'aligncenter' ) );
  }

  function freiland_get_news_banner(){
	global $post;
	$id = get_post_meta($post->ID,'image',true);
	if ( $id == "" ) return;

	return wp_get_attachment_image( $id,
		'full',false,array('class' => 'aligncenter' ) );
  }

?>
