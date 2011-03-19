<?php
/*
Copyright (c) 2011, Christoph Herbst.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once(ABSPATH . "wp-admin" . '/includes/bookmark.php');
require_once(ABSPATH . "wp-admin" . '/includes/taxonomy.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-content" . '/plugins/event-calendar-3-for-php-53/admin.php');
require_once(dirname(__FILE__) . '/ArrayXML.php');
require_once(dirname(__FILE__) . '/json.php');

class smwimport
{
	static $all_categories = array( 
	  array('cat_name' => 'Events',
	  'category_description' => 'Events',
	  'category_nicename' => 'events',
	  'option_name' => 'smwimport_category_events',
	  'subcategories' => array( 
		array('cat_name' => 'Age',
		'category_nicename' => 'age'),
		array('cat_name' => 'Location',
		'category_nicename' => 'location'),
		array('cat_name' => 'Room',
		'category_nicename' => 'room'),
		array('cat_name' => 'House',
		'category_nicename' => 'house'),
		array('cat_name' => 'Genre',
		'category_nicename' => 'genre'),
		array('cat_name' => 'Type',
		'category_nicename' => 'type')
	  	)
	  ),
	  array('cat_name' => 'Press',
	  'category_description' => 'Press',
	  'category_nicename' => 'press',
	  'option_name' => 'smwimport_category_press'
	  ),
	  array('cat_name' => 'News',
	  'category_description' => 'News',
	  'category_nicename' => 'news',
	  'option_name' => 'smwimport_category_news'
	  )
	);

  static function get_event_subcategories(){
	$subcats = array();
	foreach(self::$all_categories as $cat){
		if ( $cat['category_nicename'] == 'events' ){
			foreach($cat['subcategories'] as $subcat)
				$subcats[] = $subcat['category_nicename'];
		}
	}
	return $subcats;
  }

  static function get_links(){
	$data = array( 'SMW Test Link' => array(
		'short_description' => 'This is a link automtically added by smwimport.',
		'website' => 'http://www.smwimport.org')
	);
	return $data;
  }

  static function get_events(){
	$data = array( 'SMW Test Event' => array(
		'title' => 'SMW Event',
		'type'  => 'concert',
		'date_begin' => '2011-03-02 10:00',
		'date_end' => '2011-03-06 10:00',
		'short_description' => 'SMW imported event',
		'long_description' => '<strong>Newer imported event content</strong>',
		'genre' => 'rock',
		'homepage' => array( 'www.test1.de','www.test2.de','www.test3.de'),
		'location' => 'Werkstatt',
		'house' => 'big house',
		'room' => '203',
		'age' => '18',
		'image_big' => array(
			'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
			'title' => 'Big image title'),
		'image_small' => array(
			'file' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'Small image title')
		),
	'SMW Zweites Event' => array(
		'title' => 'SMW Event 2',
		'type'  => 'festival',
		'date_begin' => '2011-04-02 12:00',
		'date_end' => '2011-04-03 15:00',
		'short_description' => 'SMW new imported event',
		'long_description' => '<strong>Newer imported event content</strong>',
		'genre' => 'pop',
		'homepage' => array( 'www.test1.de','www.test2.de','www.test3.de'),
		'location' => 'Spartakus',
		'house' => 'small house',
		'room' => '210',
		'age' => '16')
	);
	return $data;
  }

  static function get_news(){
	$data = array( 'SMW Test News' => array(
		'topic' => 'SMW News',
		'short_description' => 'SMW imported news',
		'long_description' => '<strong>New imported news content</strong>',
		'link' => 'www.test1.de',
		'image' => array(
			'file' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'News image title')
		)
	);
	return $data;
  }

  static function get_press(){
	$data = array( 'SMW Test Press' => array(
		'topic' => 'SMW Press',
		'date'  => '1.1.2011',
		'media' => 'Bild am Sonntag',
		'short_description' => 'SMW imported press',
		'long_description' => '<strong>New imported press content</strong>',
		'link' => 'www.test1.de',
		'image' => array(
			'file' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'Press image title')
		)
	);
	return $data;
  }

  static function get_images(){
	$data = array( 
		'SMW Test Image' => array(
		'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'title' => 'New imported image3'),
		'SMW New Image' => array(
		'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'title' => 'New imported image3')
	);
	return $data;
  }

  static function test_write_data_as_xml($source_root_map) {
	$url = get_option( 'smwimport_xml_data_source' );
	$fh = fopen($url, 'w');
	if ( $fh == null ) 
		return new WP_Error('data_source_error', __("Could not open data source:").$url);

	foreach( $source_root_map as $source => $root )
		foreach( self::$source() as $key => $data)
			$array[$root][$key] = $data;

	$content = ArrayXml::arrayToXml($array,'smwimportdata');
	$dom = new DomDocument();
	$dom->loadXML($content);
	$dom->formatOutput = true;
	$formatedXML = $dom->saveXML();
	fwrite($fh,$formatedXML);
	fclose($fh);

	return 0;
  }

  static function test_read_data_from_xml(){
	$url = get_option( 'smwimport_xml_data_source' );
	$content = file_get_contents($url);
	if ($content === false) 
		return new WP_Error('data_source_error', __("Could not get data source:").$url);
	$xml = simplexml_load_string($content);
	return ArrayXML::XMLToArray($xml);
  }

  static function test_read_events_from_csv(){
	$url = get_option( 'smwimport_events_data' );
	$content = file_get_contents($url);
	if ($content === false) 
		return new WP_Error('data_source_error', __("Could not get event data source:").$url);
	$data = str_getcsv($content);
	$start = 9; // skip garbage
	$events[] = array( $data[$start] => array(
		'title' => $data[$start],
		'genre' => $data[$start+1],
		'date_begin' => $data[$start+2],
		'date_end'   => $data[$start+2],
		'age' => $data[$start+3],
		'short_description' => $data[$start+4],
		'long_description' => $data[$start+5],
		'link1' => $data[$start+6],
		'location' => $data[$start+7],
		'room' => $data[$start+8],
		'house' => $data[$start+9],
		'type'  => $data[$start+11]));

//	error_log(print_r($events,true));
	return $data;
  }

  static function test_read_events_from_json(){
	$url = get_option( 'smwimport_events_data' );
	$content = file_get_contents($url);
	if ($content === false) 
		return new WP_Error('data_source_error', __("Could not get event data source:").$url);

	$content = str_replace(array("\r", "\r\n", "\n"),' ',$content);
	$data = json_decode($content,true);
	if ( !$data )
		return new WP_Error('data_source_error', __("Could not decode json file:").$url);

	foreach( $data['items'] as $item ){
		$item['title'] = $item['label'];
		$events[$item['label']] = $item;
	}
	return $events;
  }

  static function test_write_data_as_json($data){
  	$url = get_option( 'smwimport_xml_data_source' );
	$url = str_replace('.xml','.json',$url);
	$fh = fopen($url, 'w');
	if ( $fh == null ) 
		return new WP_Error('data_source_error', __("Could not open json file:").$url);

	$json = new json();
	$json_str = $json->indent(json_encode($data));
	fwrite($fh,$json_str);
	fclose($fh);
  }

  static function get_event_content($post_content){
	global $post;
	$metadata = self::get_event_subcategories();
	$return = '<table class="event_meta">';
	foreach( $metadata as $key ){
		$meta = get_post_meta($post->ID,$key,true);
		if ( $meta == null ) continue;
		$return .= '<tr><td class="'.$key.'-label">'.$key.'</td>';
		$return .= '<td class="'.$key.'-content">'.$meta.'</td></tr>';
	}
	$homepage = get_post_meta($post->ID,'homepage',true);
	if ( $homepage != null ){
		foreach( $homepage as $key => $link ){
			$return .= '<tr><td class=homepage"'.$key.'-label">homapage'.$key.'</td>';
			$return .= '<td class=homepage"'.$key.'-content">';
			$return .= '<a href="'.$link.'">homepage'.$key.'</a></td></tr>';
		}
	}
	$return .= '</table>';
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

	return $post_content . $return;
  }

  static function get_news_content($post_content){
	return 'NEWS:'.$post_content;
  }

  static function get_press_content($post_content){
	return 'PRESS:'.$post_content;
  }

  static function create_categories(){

	foreach( self::$all_categories as $catarr ){
		// create category
		$id = smwimport::create_category( $catarr );
		if ( is_wp_error($id) ){
			error_log( $id->get_error_message());
			continue;
		}
    	    	update_option( $catarr['option_name'], $id );
		if ( isset( $catarr['subcategories'] ) ){
			foreach( $catarr['subcategories'] as $subcategory ){
				$subcategory['category_parent'] = $id;
				$subid = smwimport::create_category( $subcategory );
				if ( is_wp_error($subid) ){
					error_log( $subid->get_error_message());
					continue;
				}
			}
		}
	}
  }

  static function create_category($category){
	$cat_id = -1;
	if ( !isset($category['category_parent']) ){
		$cat = get_category_by_slug($category['category_nicename']);
		if ( $cat )
			$cat_id = $cat->term_id;
	}else{
		//XXX: again needed because of a bug in wordpress term cache
		wp_cache_flush();
		$cats = get_categories( "hide_empty=0&parent=".$category['category_parent'] );
		foreach( $cats as $cat ){
			if ( $cat->slug == $category['category_nicename'] )
				$cat_id = (int)$cat->term_id;
		}
	}

	if ( $cat_id == -1 )
		$cat_id = wp_insert_category($category, true);

	if ( is_wp_error( $cat_id ) ) {
		if ( 'term_exists' == $cat_id->get_error_code() )
			return (int) $cat_id->get_error_data();
	} elseif ( ! $cat_id ) {
		return(new WP_Error('category_failed', __("Sorry, the new category failed.")));
	}

	return($cat_id);
  }

  static function delete_empty_subcategories(){

	foreach( self::$all_categories as $category ){
		if ( !isset( $category['subcategories'] ) )
			continue;
		foreach($category['subcategories'] as $subcat ){
			$parent =  get_category_by_slug($subcat['category_nicename']);
			$childs = get_categories( "hide_empty=0&parent=$parent->term_id" );
			foreach( $childs as $child ){
				// XXX: the following should work, but does not!
				//if ($child->category_count == 0){
				$objects = get_objects_in_term($child->term_id,'category');
				if ( empty($objects) ){
					error_log('Deleting empty subcategory:'.$child->slug);
					wp_delete_category( $child->term_id );
				}
			}
		}
	}
	return true;
  }

  static function delete_all_imported(){
	self::delete_links();
	$posts = self::get_smwimport_posts();

	foreach($posts as $post)
		wp_delete_post($post->ID,true);
	self::delete_empty_subcategories();
  }

  static function import_all() {
	self::delete_links();

	$source_root_map = array(
		get_links => 'links',
		get_events => 'events',
		get_news => 'news',
		get_press => 'press',
		get_images => 'images'
	);

	$ret = self::test_write_data_as_xml($source_root_map);
	if ( is_wp_error($ret) ) return $ret;
	$ret = self::test_read_data_from_xml();
	if ( is_wp_error($ret) ) return $ret;

	self::test_write_data_as_json($ret);

//	self::test_read_events_from_csv();
	$testret = self::test_read_events_from_json();
	if ( is_wp_error($testret) ){
		error_log($testret->get_error_message());
	}else{
		foreach( $testret as $key => $event )
			self::import_event($key,$event);
	}

	$root_importer_map = array(
		'links' => import_link,
		'events' => import_event,
		'news' => import_news,
		'press' => import_press,
		'images' => import_image
	);

	foreach( $ret as $root => $entities )
		foreach( $entities as $key => $data ){
			$importer = $root_importer_map[$root];
			//XXX: quick fix for homepages on xml import data
			// xml import will probably be discarded anyway
			if ( isset($data['homepage']) && isset($data['homepage']['homepage']) )
				$data['homepage'] = $data['homepage']['homepage'];
			$ret = self::$importer($key,$data);
			if ( is_wp_error($ret) ){
				error_log($ret->get_error_message());
				return $ret;
			}
		}

	// XXX: this is needed due to a bug in wordpress category
//	delete_option('category_children'); 
	wp_cache_flush();
	return self::delete_empty_subcategories();
  }

  static function get_link_category() {
	$link_categories = get_terms('link_category', 'fields=ids&slug=smwimport&hide_empty=0');
	if (empty($link_categories)) 
		return new WP_Error('no_link_category', __("Link category 'smwimport' does not exist!"));
	return $link_categories[0];
  }

  static function delete_links() {
	$cat = self::get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$args = array( 'category' => (string)$cat );
	$links = get_bookmarks($args);
	foreach($links as $link)
		wp_delete_link($link->link_id);
  }

  static function import_link($key,$data) {
	$linkdata['link_name'] = $key;
	$linkdata['link_url'] = $data['website'];
	$linkdata['link_description'] = $data['short_description'];
	$cat = self::get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$linkdata['link_category'] = (string)$cat;
	return wp_insert_link($linkdata,true);
  }

  static function get_smwimport_posts(){
	$args = array(
		'meta_key' => '_post_type',
		'meta_value' => 'smwimport',
		'numberposts' => -1
	);
	$posts = get_posts($args);
	$args['post_type'] = 'attachment';
	$args['post_status'] = null;
	$attachments = get_posts($args);
	return array_merge($posts,$attachments);	
  }

  static function get_post($prim_key, $category_option){
	if ( $category_option=='image'){
		$cat = null;
		$type = 'attachment';
	}else{
		$cat = get_option( $category_option );
		$type = 'post';
	}

	$args = array(
		'category' => $cat,
		'post_type' => $type,
		'numberposts' => 1,
		'meta_key' => '_prim_key',
		'meta_value' => $prim_key
	);
	return get_posts($args);
  }

  static function import_post($prim_key,&$postarr, $category_option ) {
	$postarr['post_category'] = array( get_option( $category_option ));
	$posts = self::get_post($prim_key,$category_option);
	if ( !empty($posts) )
		$postarr['ID'] = $posts[0]->ID;

	$ID = wp_insert_post($postarr,true);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"_prim_key",$prim_key,true);
	add_post_meta($ID,"_post_type",'smwimport',true);
	return $ID;
  }

  static function import_event_dates($post_id,$action,$start,$end){
	if ( $start == null )
		$start = date("Y-m-d H:i");
	if ( $end == null )
		$end = $start;
	$sched_entry = array(
		'action' => $action,
		'start'  => $start,
		'end'  => $end,
		'allday' => 0
	);

	$ec3_admin=new ec3_Admin();
	if ( $action == 'update' ){
		error_log("Updating date:".$post_id);
		$schedule = $ec3_admin->get_schedule($post_id);
		$sched_entries = array( $schedule[0]->sched_id => $sched_entry );
	}else{
		error_log("Creating date:".$post_id);
		$sched_entries = array( $sched_entry );
	}
	$ec3_admin->ec3_save_schedule($post_id,$sched_entries);
  }

  static function import_event_meta($post_ID,$data){
        $ret = 0;
	if ( isset($data['homepage'] ) ){
		add_post_meta($post_ID,'homepage',$data['homepage'],true);
	}
	$metadata = self::get_event_subcategories();
	foreach( $metadata as $key ){
		if ( !isset($data[$key]) ) continue;
		add_post_meta($post_ID,$key,$data[$key],true);
		// get parent category
		$idObj =  get_category_by_slug($key);
		if ( is_array($data[$key]) )
			$subcats = $data[$key];
		else
			$subcats = array( $data[$key] );
		foreach($subcats as $subcat){ 
			$category['cat_name'] = $subcat;
			$category['category_nicename'] = $subcat;
			$category['category_parent'] = $idObj->term_id;
			$cat_id = self::create_category($category);
			if ( is_wp_error($cat_id) ){
				error_log('smwimport: could not create sub category:'.$subcat);
				error_log($cat_id->get_error_message());
				continue;
			}
			$categories[] = $cat_id;
		}
	}
	return wp_set_post_terms($post_ID,$categories,'category',true);
  }

  static function import_event($prim_key,$data) {
	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['title'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = self::import_post($prim_key,$postarr,'smwimport_category_events');
	if ( is_wp_error($ID) ){
		error_log('smwimport: could not import:'.$prim_key);
		return $ID;
	}
	$images = array('image_big','image_small');
	foreach( $images as $image ){
		if ( isset($data[$image]) ){
			$ret = self::import_image_for_post($prim_key.$image,$data[$image],$ID);
			if ( is_wp_error($ret) ) return $ret;
		}
	}
	$action = 'create';
	if ( isset($postarr['ID']) )
		$action = 'update';
	self::import_event_dates($ID,$action,$data['date_begin'], $data['date_end']);
	return self::import_event_meta($ID,$data);
  }

  static function import_news($prim_key,$data) {

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['topic'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = self::import_post($prim_key,$postarr,'smwimport_category_news');
	if ( is_wp_error($ID) ) return $ID;
	if ( isset($data['image']) ){
		$ret = self::import_image_for_post($prim_key.'image',$data['image'],$ID);
		if ( is_wp_error($ret) ) return $ret;
	}
	return $ID;
  }

  static function import_press($prim_key,$data) {

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['topic'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = self::import_post($prim_key,$postarr,'smwimport_category_press');
	if ( is_wp_error($ID) ) return $ID;
	if ( isset($data['image']) ){
		$ret = self::import_image_for_post($prim_key.'image',$data['image'],$ID);
		if ( is_wp_error($ret) ) return $ret;
	}
	return $ID;
  }

  static function import_image($prim_key,$data) {
	return self::import_image_for_post($prim_key,$data,get_option( 'smwimport_page_images' ));
  }

  static function import_image_for_post($prim_key,$data,$post_id) {
	$remotefile = $data['file'];
	$title = $data['title'];
	$localfile = basename($remotefile);

	$posts = self::get_post($prim_key,'image');
	if ( empty($posts) ){
		$contents = file_get_contents($remotefile);
		if ( $contents == FALSE )
			return new WP_Error('download_failed', __("Could not get file:".$remotefile));
		$upload = wp_upload_bits($localfile,null,$contents);
		if ( $upload['error'] != false )
			return new WP_Error('upload_failed', $upload['error']);
		$filename = $upload['file'];
		$wp_filetype = wp_check_filetype(basename($filename), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $title,
			'guid' => $filename,
			'post_excerpt' => $title,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id,  $attach_data );
	}else{
		//XXX: update the image? then we need a hash or something
		error_log('Image already exists:'. $posts[0]->ID);
		// only update title
		$post = $posts[0];
		$post->title = $title;
		$post->post_excerpt = $title;
		$attach_id = wp_update_post($post);	
	}
	add_post_meta($attach_id,"_prim_key",$prim_key,true);
	add_post_meta($attach_id,"_post_type",'smwimport',true);
  }

}
