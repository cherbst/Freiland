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

class smwimport
{

  static $smw_mapping = array(
	'Veranstaltung' => array(
		'type' => 'post',
		'category' => 'events',
		'primary_key' => 'label',
		'attributes' => array(
			'genre' => array('category','meta'),
			'label' => 'post_title',
			'short_description' => 'post_excerpt',
			'long_description' => 'post_content',
			'genre' => array('category','meta'),
			'eventtype' => array('category','meta'),
			'location' => array('category','meta'),
			'house' => array('category','meta'),
			'room' => array('category','meta'),
			'age' => array('category','meta'),
			'date_begin' => 'calendar_start',
			'date_end' => 'calendar_end',
			'image_small' => 'attachment',
			'image_big' => 'attachment',
			'homepage' => 'meta'
		)	
	),
	'News' => array(
		'type' => 'post',
		'category' => 'news',
		'primary_key' => 'label',
		'attributes' => array(
			'label' => 'post_title',
			'short_description' => 'post_excerpt',
			'long_description' => 'post_content',
			'image' => 'attachment',
			'link' => 'meta'
		)	
	),
	'Presse' => array(
		'type' => 'post',
		'category' => 'press',
		'primary_key' => 'label',
		'attributes' => array(
			'label' => 'post_title',
			'short_description' => 'post_excerpt',
			'long_description' => 'post_content',
			'image' => 'attachment',
			'link' => 'meta'
		)	
	),
	'Bild' => array(
		'type' => 'attachment',
		'page' => 'images',
		'primary_key' => 'label',
		'attributes' => array(
			'label' => 'title',
			'file' => 'file'
		)	
	),
	'Link' => array(
		'type' => 'link',
		'attributes' => array(
			'name' => 'link_name',
			'website' => 'link_url',
			'short_description' => 'link_description',
		)	
	)
  );

  static function get_imported_sub_categories(){
	$subcats = array();
	foreach(self::$smw_mapping as $mapping){
		if ( $mapping['type'] != 'post' ) continue;
		if ( !isset($mapping['category']) ) continue;
		// top level category
		$topcat = get_category_by_slug($mapping['category']);
		if ( !$topcat ) continue;
		foreach( $mapping['attributes'] as $attr => $type ){
			if ( is_array($type) ){
				if ( ! in_array('category',$type) ) continue;
			}else if ( $type != 'category' ) continue;
			// get parent cat
			$parentcat = self::get_category_by_slug_and_parent($attr,$topcat->term_id);
			if ( $parentcat == -1 ) continue;
			// get sub categories
			$cats = get_categories( "hide_empty=0&parent=".$parentcat );
			foreach( $cats as $cat ){
				$subcats[] = (int)$cat->term_id;
			}
		}
	}
	return $subcats;
  }

  static function get_links(){
	$data = array( array(
		'type'  => 'Link',
		'name' => 'SMW Test Link', 
		'short_description' => 'This is a link automtically added by smwimport.',
		'website' => 'http://www.smwimport.org')
	);
	return $data;
  }

  static function get_events(){
	$data = array( 
	array(
		'type'  => 'Veranstaltung',
		'label' => 'SMW Event',
		'eventtype'  => 'concert',
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
	array(
		'type'  => 'Veranstaltung',
		'label' => 'SMW Event 2',
		'eventtype'  => 'festival',
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
	$data = array( array(
		'type'  => 'News',
		'label' => 'SMW News',
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
	$data = array( array(
		'type'  => 'Presse',
		'label' => 'SMW Press',
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
	$data = array( array(
		'type'  => 'Bild',
		'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image1'),
		array(
		'type'  => 'Bild',
		'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image2')
	);
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

	return $data['items'];
  }

  static function get_category_by_slug_and_parent($slug,$parent = null){
	$cat_id = -1;
	if ( $parent != null ){
		$cat = get_category_by_slug($slug);
		if ( $cat )
			$cat_id = $cat->term_id;
	}else{
		//XXX: again needed because of a bug in wordpress term cache
		wp_cache_flush();
		//XXX: same bug, needed for wp_cron support
		delete_option('category_children');
		$cats = get_categories( "hide_empty=0&parent=".$parent );
		foreach( $cats as $cat ){
			if ( $cat->slug == $slug )
				$cat_id = (int)$cat->term_id;
		}
	}
	return $cat_id;
  }

  static function create_category($category){
	$cat_id = self::get_category_by_slug_and_parent($category['category_nicename'],$category['category_parent']);

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

	foreach( self::get_imported_sub_categories() as $category ){
		// XXX: the following should work, but does not!
		//if ($child->category_count == 0){
		$objects = get_objects_in_term($category,'category');
		if ( empty($objects) ){
			error_log('Deleting empty subcategory:'.$category);
			wp_delete_category( $category );
		}
	}
	return true;
  }

  static function import_post_type($mapping,$data){
	$attribute_mapping = $mapping['attributes'];
	$attachments = array();
	$calendar = null;
	$metas = array();
	$categories = null;
	$g_ret = true;

	foreach( $data as $key => $value ){
		if ( is_array($attribute_mapping[$key]) )
			$key_mapping = $attribute_mapping[$key];
		else
			$key_mapping = array($attribute_mapping[$key]);
		foreach( $key_mapping as $key_map ){
			switch($key_map){
				case 'post_title':
				case 'post_excerpt':
				case 'post_content':
					$postarr[$key_map] = $value;
					break;
				case 'attachment':
					$attachments[] = $key;
					break;
				case 'calendar_start':
					$calendar['start'] = $value;
					break;
				case 'calendar_end':
					$calendar['end'] = $value;
					break;
				case 'meta':
					$metas[] = $key;
					break;
				case 'category':
					$categories[$key] = $value;
					break;
				default:
					// ignore some keys
					if ( $key != 'uri' && $key != 'type' ) 
						error_log('smwimport: no mapping defined for:'.$key);
			}
		}
	}
	$prim_key = $data[$mapping['primary_key']];
	$postarr['post_status'] = 'publish';

	// get top level category
	$cat = get_category_by_slug($mapping['category']);
	if ( !$cat ){
		error_log('smwimport: could not find category:'.$mapping['category']);
		return new WP_Error('category_failed', __("Could not find parent category."));
	}
	// create the post
	$ID = self::import_post($prim_key,$postarr,$cat->term_id);
	if ( is_wp_error($ID) ){
		error_log('smwimport: could not import:'.$prim_key);
		return $ID;
	}
	
	// import attachments
	foreach( $attachments as $attachment ){
		$ret = self::import_image_for_post($prim_key.$attachment,$data[$attachment],$ID);
		if ( is_wp_error($ret) ){
			error_log('smwimport: could not import attachment:'.$attachment);
			$g_ret = $ret;
		}
	}

	// import dates
	if ( is_array($calendar) ){
		$action = 'create';
		if ( isset($postarr['ID']) )
			$action = 'update';
		self::import_post_dates($ID,$action,$calendar['start'], $calendar['end']);
	}

	// import meta data
	foreach( $metas as $meta )
		add_post_meta($ID,$meta,$data[$meta],true);

	// create categories
	if ( $categories != null ){
		$ret = self::import_post_categories($ID,$categories,$cat->term_id);
		if ( is_wp_error($ret) ){
			error_log('smwimport: could not import post categories:'.$prim_key);
			$g_ret = $ret;
		}
	}
	return $g_ret;
  }

  static function import_attachment_type($mapping,$data){
	$prim_key = $data[$mapping['primary_key']];
	$attribute_mapping = $mapping['attributes'];

	$page = get_page_by_path( $mapping['page'] ); 
	
	if ( $page == null ){
		error_log('smwimport: could not find attachment page:'.$mapping['page']);
		return new WP_Error('no_page', __("could not find attachment page:").$mapping['page']);
	}

	foreach( $data as $key => $value ){
		switch($attribute_mapping[$key]){
			case 'title':
			case 'file':
				$attachment[$attribute_mapping[$key]] = $value;
				break;
		}
	}

	return self::import_image_for_post($prim_key,$attachment,$page->ID);
  }

  static function import_link_type($mapping,$data){
	$attribute_mapping = $mapping['attributes'];
	foreach( $data as $key => $value ){
		switch($attribute_mapping[$key]){
			case 'link_name':
			case 'link_url':
			case 'link_description':
				$link[$attribute_mapping[$key]] = $value;
				break;
		}
	}
	return self::import_link($link);
  }

  static function import_data($data){
	if ( !isset($data['type']) )
		return new WP_Error('no_type', __("No SMW type set, cannot continue"));

	if ( !isset(self::$smw_mapping[$data['type']]) )
		return new WP_Error('no_mapping', __("No mapping defined for:").$data['type']);

	$mapping = self::$smw_mapping[$data['type']];
	
	switch($mapping['type']){
		case "post":
			$ret = self::import_post_type($mapping,$data);
			break;
		case "attachment":
			$ret = self::import_attachment_type($mapping,$data);
			break;
		case "link":
			$ret = self::import_link_type($mapping,$data);
			break;
		default:
			error_log('smwimport: Undefined wordpress import type:'.$mapping['type']);
	}
	return $ret;
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

	$sources = array(
		test_read_events_from_json,
		get_events,
		get_news,
		get_press,
		get_images,
		get_links
	);

	foreach( $sources as $source ){
		$items = self::$source();
		if ( is_wp_error($items) ){
			error_log("smwimport: could not import from:".$source);
			return $items;
		}
		foreach( $items as $item ){
			$ret = self::import_data($item);
			if ( is_wp_error($ret) ){
				error_log($ret->get_error_message());
				$g_ret = $ret;
			}
		}
	}
	// XXX: this is needed due to a bug in wordpress category cache
	wp_cache_flush();
	delete_option('category_children');
	$ret = self::delete_empty_subcategories();
	if ( is_wp_error($ret) ) $g_ret = $ret;
	return $g_ret;
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

  static function import_link($link) {
	$cat = self::get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$link['link_category'] = (string)$cat;
	return wp_insert_link($link,true);
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

  static function get_post($prim_key, $category_id = null){
	if ( $category_id == null ){
		$type = 'attachment';
	}else{
		$type = 'post';
	}

	$args = array(
		'category' => $category_id,
		'post_type' => $type,
		'numberposts' => 1,
		'meta_key' => '_prim_key',
		'meta_value' => $prim_key
	);
	return get_posts($args);
  }

  static function import_post($prim_key,&$postarr, $category_id ) {
	$postarr['post_category'] = array( $category_id );
	$posts = self::get_post($prim_key,$category_id);
	if ( !empty($posts) )
		$postarr['ID'] = $posts[0]->ID;

	$ID = wp_insert_post($postarr,true);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"_prim_key",$prim_key,true);
	add_post_meta($ID,"_post_type",'smwimport',true);
	return $ID;
  }

  static function import_post_dates($post_id,$action,$start,$end){
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

  static function import_post_categories($post_ID,$data,$top_cat){
        $ret = 0;
	foreach( $data as $parent_slug => $cat_slug ){
		// create parent category
		$parent_id = self::create_category(array(
			'cat_name' => $parent_slug,
			'category_nicename' => $parent_slug,
			'category_parent' => $top_cat));
		if ( is_wp_error($parent_id) ){
			error_log('smwimport: could not create parent category:'.$parent_slug);
			error_log($parent_id->get_error_message());
			continue;
		}

		if ( is_array($cat_slug) )
			$subcats = $cat_slug;
		else
			$subcats = array( $cat_slug );
		foreach($subcats as $subcat){ 
			$category['cat_name'] = $subcat;
			$category['category_nicename'] = $subcat;
			$category['category_parent'] = $parent_id;
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

  static function import_image_for_post($prim_key,$data,$post_id) {
	$remotefile = $data['file'];
	$title = $data['title'];
	$localfile = basename($remotefile);

	$posts = self::get_post($prim_key);
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
