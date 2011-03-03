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


class smwimport
{
  function get_links(){
	$data = array( 'SMW Test Link' => array(
		'short_description' => 'This is a link automtically added by smwimport.',
		'website' => 'http://www.smwimport.org')
	);
	return $data;
  }

  function get_events(){
	$data = array( 'SMW Test Event' => array(
		'title' => 'SMW Post',
		'type'  => 'concert',
		'date_begin' => '2011-03-02 10:00',
		'date_end' => '2011-03-06 10:00',
		'short_description' => 'SMW imported event',
		'long_description' => '<strong>Newer imported event content</strong>',
		'genre' => 'rock',
		'link1' => 'www.test1.de',
		'link2' => 'www.test2.de',
		'link3' => 'www.test3.de',
		'location' => 'Potsdam',
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
		'title' => 'SMW Post 2',
		'type'  => 'concert',
		'date_begin' => '2011-04-02 12:00',
		'date_end' => '2011-04-03 15:00',
		'short_description' => 'SMW new imported event',
		'long_description' => '<strong>Newer imported event content</strong>',
		'genre' => 'rock',
		'link1' => 'www.test1.de',
		'link2' => 'www.test2.de',
		'link3' => 'www.test3.de',
		'location' => 'Potsdam',
		'house' => 'big house',
		'room' => '203',
		'age' => '18')
	);
	return $data;
  }

  function get_news(){
	$data = array( 'SMW Test News' => array(
		'topic' => 'SMW News',
		'short_description' => 'SMW imported news',
		'long_description' => '<strong>New imported news content</strong>',
		'link' => 'www.test1.de')
	);
	return $data;
  }

  function get_press(){
	$data = array( 'SMW Test Press' => array(
		'topic' => 'SMW Press',
		'date'  => '1.1.2011',
		'media' => 'Bild am Sonntag',
		'short_description' => 'SMW imported press',
		'long_description' => '<strong>New imported press content</strong>',
		'link' => 'www.test1.de')
	);
	return $data;
  }

  function get_images(){
	$data = array( 'SMW Test Image' => array(
		'file' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'title' => 'New imported image3')
	);
	return $data;
  }

  function import_all() {
	$this->delete_links();

	$source_importer_map = array(
		get_links => import_link,
		get_events => import_event,
		get_news => import_news,
		get_press => import_press,
		get_images => import_image
	);

	$ret = 0;
	foreach( $source_importer_map as $source => $importer )
		foreach( $this->$source() as $key => $data){
			$ret = $this->$importer($key,$data);
			if ( is_wp_error($ret) )
				return $ret;
		}

	return $ret;
  }

  function get_link_category() {
	$link_categories = get_terms('link_category', 'fields=ids&slug=smwimport&hide_empty=0');
	if (empty($link_categories)) 
		return new WP_Error('no_link_category', __("Link category 'smwimport' does not exist!"));
	return $link_categories[0];
  }

  function delete_links() {
	$cat = $this->get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$args = array( 'category' => (string)$cat );
	$links = get_bookmarks($args);
	foreach($links as $link)
		wp_delete_link($link->link_id);
  }

  function import_link($key,$data) {
	$linkdata['link_name'] = $key;
	$linkdata['link_url'] = $data['website'];
	$linkdata['link_description'] = $data['short_description'];
	$cat = $this->get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$linkdata['link_category'] = (string)$cat;
	return wp_insert_link($linkdata,true);
  }

  function get_post($prim_key, $category_option){
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

  function import_post($prim_key,&$postarr, $category_option ) {
	$postarr['post_category'] = array( get_option( $category_option ));
	$posts = $this->get_post($prim_key,$category_option);
	if ( !empty($posts) )
		$postarr['ID'] = $posts[0]->ID;

	$ID = wp_insert_post($postarr,true);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"_prim_key",$prim_key,true);
	return $ID;
  }

  function import_event_dates($post_id,$action,$start,$end){
	$sched_entry = array(
		'action' => $action,
		'start'  => $start,
		'end'  => $end,
		'allday' => 0
	);

	require_once(ABSPATH . "wp-content" . '/plugins/event-calendar-3-for-php-53/admin.php');
	$ec3_admin=new ec3_Admin();
	if ( $action == 'update' ){
		error_log("Updating:".$post_id);
		$schedule = $ec3_admin->get_schedule($post_id);
		$sched_entries = array( $schedule[0]->sched_id => $sched_entry );
	}else{
		error_log("Creating:".$post_id);
		$sched_entries = array( $sched_entry );
	}
	$ec3_admin->ec3_save_schedule($post_id,$sched_entries);
  }

  function import_event($prim_key,$data) {
	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['title'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = $this->import_post($prim_key,$postarr,'smwimport_category_events');
	if ( is_wp_error($ID) ) return $ID;
	$images = array('image_big','image_small');
	foreach( $images as $image ){
		if ( isset($data[$image]) ){
			$ret = $this->import_image_for_post($prim_key.$image,$data[$image],$ID);
			if ( is_wp_error($ret) ) return $ret;
		}
	}
	$action = 'create';
	if ( isset($postarr['ID']) )
		$action = 'update';
	$this->import_event_dates($ID,$action,$data['date_begin'], $data['date_end']);
	$metadata = array('age','location','room','house','genre','type');
	foreach( $metadata as $key )
		add_post_meta($ID,$key,$data[$key],true);
	return $ID;
  }

  function import_news($prim_key,$data) {

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['topic'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = $this->import_post($prim_key,$postarr,'smwimport_category_news');
	return $ID;
  }

  function import_press($prim_key,$data) {

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = $data['topic'];
	$postarr['post_excerpt'] = $data['short_description'];
	$postarr['post_content'] = $data['long_description'];
	$ID = $this->import_post($prim_key,$postarr,'smwimport_category_press');
	return $ID;
  }

  function import_image($prim_key,$data) {
	return $this->import_image_for_post($prim_key,$data,get_option( 'smwimport_page_images' ));
  }

  function import_image_for_post($prim_key,$data,$post_id) {
	$remotefile = $data['file'];
	$title = $data['title'];
	$localfile = basename($remotefile);

	$posts = $this->get_post($prim_key,'image');
	if ( !empty($posts) ){
		//XXX: update the image? then we need a hash or something
		error_log('Image already exists:'. $posts[0]->ID);
		return 0;
	}

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
		'post_content' => '',
		'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id,  $attach_data );
	add_post_meta($attach_id,"_prim_key",$prim_key,true);
  }

}
