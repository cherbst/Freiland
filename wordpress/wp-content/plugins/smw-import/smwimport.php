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
// for the function wp_generate_attachment_metadata()
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(dirname(__FILE__) . '/smwaccess.php');
require_once(dirname(__FILE__) . '/favicon.inc.php');

class smwimport
{
  /* Supported data sources: 
	
    JSON: the JSON file must have at least following attribute:
        "items" : An array of all items to import
     Each item must have at least the following attribute:
        "type" : Name of the SMW category
     Any other attributes can be defined in the mapping for this 
     SMW category. Attributes that have no mapping are ignored.
  */

  /* Mapping SMW categories and attributes to wordpress types.
     The mapping must be an array of the form:

     array( <SMW category> => array(
	      'type' => <wordpress type>
	      <any required mappings for the wordpress type>
	      'attributes' => array(
	         'SMW attribute' => <attribute mapping>
	      )
	    )
     )
       
     Supported wordpress types:
     "post" : imports element into an wordpress post
	required mappings:
	   "category"    : slug of top level category to import into ( must exist )
	   "primary_key" : attribute which holds the primary key for each item
        supported attribute mappings:
	   A single value or an array of the following types are supported:
	   "category"   : adds post to a subcategory with the name of the attribute value
		          and a parent category with the name of the attribute
	   "post_title" : attribute value becomes the post title
	   "post_excerpt" : attribute value becomes the post excerpt
	   "post_content" : attribute value becomes the post content
	   "post_date"    : attribute value becomes the post publish date
	   "meta"	  : attribute value becomes a post custom value with the attribute
			    name as the key
	   "favicon"	  : attribute value should be a URL. The favicon of this URL will be
			    saved as a post meta with the key "favicon"
	   "attachmentname" : attribute value becomes the name of all attachments for
			      this post
	   "attachment"   : attribute value becomes an attachment to the post
			    The attribute value must be either an array containing the
			    keys described in the attachment attribute mapping or a string
			    of the form '<prefix>:filename'. The latter will be downloaded
			    from the url given in the attachment_url option
	   "calendar_start" : ( requires ec3 plugin ) 
			 attribute value becomes the start date of this post
	   "calendar_end" : ( requires ec3 plugin ) 
			 attribute value becomes the end date of this post

     "attachment" : imports element into a wordpress attachment
	required mappings:
	   "page"    : slug of the page that attachments are attached to ( must exist )
	   "primary_key" : attribute which holds the primary key for each item
        supported attribute mappings:
	   "url" : attribute value holds the URL used to download the attachment
	   "file" : attribute value holds the filename of the attachment
	   "title": attribute value becomes the title of this attachment

     "link" : imports element into a wordpress link
	required mappings:  none
        supported attribute mappings:
	   "link_name':  attribute value will become the link name 
	   "link_url":   attribute value will become the link url
	   "link_description":   attribute value will become the link description

     "gallery" : creates a post and attachments for this post
	required mappings:
	   "primary_key" : attribute which holds the primary key for each item
	   "category"    : slug of top level category to import into ( must exist )
        supported attribute mappings:
	   "name':  attribute value will become the title of the gallery 
	   "description":   attribute value will become the description of the gallery
	   "featured_image":   attribute value holds the filename of the featured image 
			       for the gallery
	   "gallery_folder":   attribute value is a folder which contains all images for
			       this gallery
  */
  static $smw_mapping = array(
	'Veranstaltung' => array(
		'type' => 'post',
		'category' => 'events',
		'primary_key' => 'label',
		'attributes' => array(
			'genre' => array('category','meta'),
			'title' => 'post_title',
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
			'image_name' => 'attachmentname',
			'homepage' => 'meta',
			'homepagelabel' => 'meta'
		)	
	),
	'News' => array(
		'type' => 'post',
		'category' => 'news',
		'primary_key' => 'label',
		'attributes' => array(
			'title' => 'post_title',
			'short_description' => 'post_excerpt',
			'long_description' => 'post_content',
			'subtitle' => 'meta',
			'image' => 'attachment',
			'homepage' => 'meta',
			'homepagelabel' => 'meta'
		)	
	),
	'Pressebericht' => array(
		'type' => 'post',
		'category' => 'press',
		'primary_key' => 'label',
		'attributes' => array(
			'title' => 'post_title',
			'description' => 'post_content',
			'subtitle' => array('post_excerpt','meta'),
			'homepage' => array('meta','favicon'),
			'homepagelabel' => 'meta',
			'source' => 'meta',
			'date' => array('meta','post_date')
		)	
	),
	'Bild' => array(
		'type' => 'attachment',
		'page' => 'images',
		'primary_key' => 'label',
		'attributes' => array(
			'label' => 'title',
			'file' => 'file',
			'url' => 'url'
		)	
	),
	'Link' => array(
		'type' => 'link',
		'attributes' => array(
			'name' => 'link_name',
			'website' => 'link_url',
			'short_description' => 'link_description',
		)	
	),
	'Gallery' => array(
		'type' => 'gallery',
		'primary_key' => 'name',
		'category' => 'images',
		'attributes' => array(
			'name' => 'name',
			'description' => 'description',
			'gallery_folder' => 'gallery_folder',
		)
	)
  );

  // time measure variables
  static $start_time;
  static $fetch_time;
  // post time for creating posts
  static $posttime;

  /* returns an array of the ids of all imported subcategories 
  */
  private static function get_imported_sub_categories(){
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

  private static function get_links(){
	$data = array( array(
		'type'  => 'Link',
		'name' => 'SMW Test Link', 
		'short_description' => 'This is a link automtically added by smwimport.',
		'website' => 'http://www.smwimport.org')
	);
	return $data;
  }

  private static function get_events(){
	$data = array( 
	array(
		'type'  => 'Veranstaltung',
		'label' => 'SMW Event',
		'title' => 'SMW Event',
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
			'url' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
			'title' => 'Big image title'),
		'image_small' => array(
			'url' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'Small image title')
		),
	array(
		'type'  => 'Veranstaltung',
		'label' => 'SMW Event 2',
		'title' => 'SMW Event 2',
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

  private static function get_news(){
	$data = array( array(
		'type'  => 'News',
		'label' => 'SMW News',
		'title' => 'SMW News title',
		'short_description' => 'SMW imported news',
		'long_description' => '<strong>New imported news content</strong>',
		'subtitle' => 'A news subtitle',
		'homepage' => 'www.test1.de',
		'homepagelabel' => 'A test link',
		'image' => array(
			'url' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'News image title')
		)
	);
	return $data;
  }

  private static function get_press(){
	$data = array( array(
		'type'  => 'Pressebericht',
		'label' => 'SMW Press',
		'title' => 'SMW Press',
		'date'  => '1.1.2011',
		'source' => 'Bild am Sonntag',
		'subtitle' => 'SMW imported press',
		'description' => '<strong>New imported press content</strong>',
		'homepage' => 'www.test1.de'
		)
	);
	return $data;
  }

  private static function get_images(){
	$data = array( array(
		'type'  => 'Bild',
		'url' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image1'),
		array(
		'type'  => 'Bild',
		'url' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image2')
	);
	return $data;
  }

  private static function get_galleries(){
	$data = array( array(
		'type'  => 'Gallery',
		'name' => 'Test gallery1',
		'description' => 'An imported test gallery',
		'gallery_folder' => '/home/chris/Bilder/Coanghai/'),
		array(
		'type'  => 'Gallery',
		'name' => 'Test gallery2',
		'description' => 'Another imported test gallery',
		'gallery_folder' => '/home/chris/Bilder/Coanghai/')
	);
	return $data;
  }

  /*  returns array of all defined data sources together with function to retrieve the data
      returns: array( 'url' => function ) or array( WP_Error )
  */
  private static function get_data_sources(){
	$num_sources = (int)get_option( 'smwimport_num_data_sources' );
	if ($num_sources == 0) 
		return array(new WP_Error('no_data_sources', __("No data sources defined.")));

	$data_sources = array();
	for( $i = 0; $i< $num_sources; $i++ )
		$data_sources[get_option( 'smwimport_data_source'.$i )] = get_data_from_source;

	if (empty($data_sources)) 
		return array(new WP_Error('no_data_sources', __("No data sources defined.")));
	return $data_sources;
  }

  /*  returns array of SMW items from datasource or a WP_Error
      $url: url of data source
  */
  private static function get_data_from_source($url){

	$ret = true;
	$start = time(); 
	$content = smwaccess::get_content($url);
	self::$fetch_time += time()-$start;
	if ($content === false)
		return new WP_Error('data_source_error', __("Could not retrieve data source:").$url);
	//XXX: assume that empty lines only occur in strings (may break import)
	$content = str_replace(array("\n\n"),'<p></p>',$content);
	$content = str_replace(array("\r", "\r\n", "\n"),' ',$content);
	$data = json_decode($content,true);

	if ( !$data )
		return new WP_Error('data_source_error', __("Could not decode source into json:").$url);	
	return $data['items'];
  }

  /* returns the id of the wordpess category if it exists, otherwise -1
     $slug: slug of the category
     $parent: $parent of the category
  */ 
  private static function get_category_by_slug_and_parent($slug,$parent = null){
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

  /* 	create a category if it does not exist 
  */
  private static function create_category($category){
	$cat_id = self::get_category_by_slug_and_parent(
		$category['category_nicename'],
		$category['category_parent']);

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

  /* 	delete imported subcategories that are no longer used 
	( have no posts attached )
  */
  private static function delete_empty_subcategories(){

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

  /*  converts an SMW file attribute value of the form
      '<prefix>:filename' into an array which can be used by
      self::import_attachment_for_post
  */
  private static function convert_smw_attachment_to_array($attachment){
	$array = explode(':',$attachment);
	if ( !isset($array[1]) ) return false;
	$file = $array[1];
	$attachment_url = get_option('smwimport_attachment_url');
	$attach_array = array( 'title' => $file,
			       'file' => $file,
			       'url' => $attachment_url.urlencode($file));
	return $attach_array;
  }

  /*  imports $data into a wordpress post according to $mapping
  */
  private static function import_post_type($mapping,$data){
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
				case 'post_date':
					$postarr[$key_map] = $value;
					break;
				case 'attachment':
					$attachments[] = $key;
					break;
				case 'attachmentname':
					$attachmentname = $value;
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
				case 'favicon':
					$favicon = $value;
					break;
				case 'category':
					$categories[$key] = $value;
					break;
				default:
					// ignore some keys
					if ( $key != 'uri' && $key != 'type' && $key != $mapping['primary_key'] )
						error_log('smwimport: no mapping defined for:'.$key);
			}
		}
	}
	$prim_key = $data[$mapping['primary_key']];

	// get top level category
	$cat = get_category_by_slug($mapping['category']);
	if ( !$cat )
		return new WP_Error('category_failed', __("Could not find top level category:").$mapping['category']);

	// create the post
	$ID = self::import_post($prim_key,$postarr,$cat->term_id);
	if ( is_wp_error($ID) )
		return $ID;
	
	// import attachments
	foreach( $attachments as $attachment ){
		$attach_arr = $data[$attachment];
		if ( !is_array($attach_arr) )
			$attach_arr = self::convert_smw_attachment_to_array($attach_arr);
		if ( $attach_arr === false ){
			error_log('Could not get smw attachment:'.$attachment);
			continue;
		}
		if ( $attachmentname != null )
			$attach_arr['title'] = $attachmentname;
		$ret = self::import_attachment_for_post($prim_key.$attachment,$attach_arr,$ID);
		if ( is_wp_error($ret) ) $g_ret = $ret;
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
		update_post_meta($ID,$meta,$data[$meta]);

	// import favicon url
	if ( $favicon != null )
		self::import_favicon_url($ID,$favicon);

	// create categories
	if ( $categories != null ){
		$ret = self::import_post_categories($ID,$categories,$cat->term_id);
		if ( is_wp_error($ret) ) $g_ret = $ret;
	}
	return $g_ret;
  }

  /* get the favicon url and cache it in an option */
  private function get_favicon_url($url){
	$favicon = new favicon($url, 0);
	$favicon_url = get_option('favicon-'.$favicon->get_site_url());
	if ( $favicon_url == null ){
		if ( $favicon->is_ico_exists() ){
			$favicon_url = $favicon->get_ico_url();
			update_option('favicon-'.$favicon->get_site_url(),$favicon_url);
		}
	}
	return $favicon_url;
  }

  /*  saves the favicon url of a given url into a post meta
  */
  private function import_favicon_url($post_id,$url){
	$favicon = self::get_favicon_url($url);

	if ( $favicon != null )
		update_post_meta($post_id,'favicon',$favicon);
  }

  /*  imports $data into a wordpress attachment according to $mapping
  */
  private static function import_attachment_type($mapping,$data){
	$prim_key = $data[$mapping['primary_key']];
	$attribute_mapping = $mapping['attributes'];

	$page = get_page_by_path( $mapping['page'] ); 
	
	if ( $page == null )
		return new WP_Error('no_page', __("could not find attachment page:").$mapping['page']);

	foreach( $data as $key => $value ){
		switch($attribute_mapping[$key]){
			case 'title':
			case 'url':
			case 'file':
				$attachment[$attribute_mapping[$key]] = $value;
				break;
		}
	}

	return self::import_attachment_for_post($prim_key,$attachment,$page->ID);
  }

  /*  creates a wordpress post together with attachments
      for all files contained in a public directory
  */
  private static function import_gallery_type($mapping,$data){
	$prim_key = $data[$mapping['primary_key']];
	$attribute_mapping = $mapping['attributes'];

	// get top level category
	$cat = get_category_by_slug($mapping['category']);
	if ( !$cat )
		return new WP_Error('category_failed', __("Could not find top level category:").$mapping['category']);

	foreach( $data as $key => $value ){
		switch($attribute_mapping[$key]){
			case 'description':
				$postarr['post_excerpt'] = $value;
				$postarr['post_content'] = $value;
				break;
			case 'name':
				$postarr['post_title'] = $value;
				break;
			case 'gallery_folder':
				$gallery_folder = $value;
				break;
			case 'featured_image':
				$featured_image = $value;
				break;
		}
	}

	$postarr['post_content'] .= '[gallery]';

	$uploads = wp_upload_dir();
	if (!is_dir($gallery_folder))
		return new WP_Error('no_directory', __("The given gallery folder is not a directory:").$gallery_folder);
	if (!($dh = opendir($gallery_folder)))
		return new WP_Error('open_error', __("Could not open the given gallery folder:").$gallery_folder);

	$ID = self::import_post($prim_key,&$postarr,$cat->term_id);
	if ( is_wp_error($ID) ) return $ID;

	// set gallery format
	set_post_format($ID,'gallery');

	error_log("Importing gallery folder:".$gallery_folder);
	while (($file = readdir($dh)) !== false) {
		if ( filetype($gallery_folder . $file) != 'file' )
			continue;
		if ( $featured_image == null )
			$featured_image = $file;
		$data['url'] = $uploads['path'] .'/' . $file;
		$data['title'] = $file;
		// create a symlink in the upload folder
		symlink( $gallery_folder . $file, $data['url'] );

		$attach_id = self::import_attachment_for_post($prim_key.$file,$data,$ID,false);
		if ( $file == $featured_image )
			update_post_meta( $ID, '_thumbnail_id', $attach_id );
	}
	closedir($dh);

	return $ID;
  }

  /*  imports $data into a wordpress link according to $mapping
  */
  private static function import_link_type($mapping,$data){
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
	$favicon = self::get_favicon_url($link['link_url']);
	if ( $favicon != null )
		$link['link_image'] = $favicon;
	return self::import_link($link);
  }

  /*
	Check for wordpress type of data and call the right import function 
	returns WP_Error on error or boolean true on success
  */
  private static function import_data($data){
	if ( !isset($data['type']) )
		return new WP_Error('no_type', __("No SMW type set, cannot continue"));

	if ( !isset(self::$smw_mapping[$data['type']]) )
		return new WP_Error('no_mapping', __("No mapping defined for:").$data['type']);

	$mapping = self::$smw_mapping[$data['type']];
	$importer = array( 'post' => import_post_type,
			   'attachment' => import_attachment_type,
			   'link' => import_link_type,
			   'gallery' => import_gallery_type);

	if ( !isset( $importer[$mapping['type']]) )
		return new WP_Error('undefined_type',__('smwimport: Undefined wordpress import type:').$mapping['type']);

	return self::$importer[$mapping['type']]($mapping,$data);
  }

  /* load ec3 plugin if it exists
  */
  private static function load_ec3(){
	// check if ec3 plugin is activated
	$plugins = get_option('active_plugins');
	$ec3plugin = 'eventcalendar3.php';
	foreach( $plugins as $plugin ){
		if ( strpos($plugin,$ec3plugin) === false ) continue;
		$admin_php = str_replace($ec3plugin,'admin.php',$plugin);
		require_once(ABSPATH . "wp-content" . '/plugins/'.$admin_php);
		break;
	}
  }

  /* public function
     Deletes all imported data ( posts, attachments, links, categories )
  */
  public static function delete_all_imported(){
	self::delete_links();
	$posts = self::get_smwimport_posts();

	self::load_ec3();
	foreach($posts as $post){
		self::delete_post_dates($post->ID);
		wp_delete_post($post->ID,true);
	}
	self::delete_empty_subcategories();
  }

  /* public function
     Imports data from all defined data sources
  */
  public static function import_all() {
	global $wp_rewrite;
	self::$start_time = time();
	self::$fetch_time = 0;
	self::$posttime = time();
	self::delete_links();

	$sources = array(
		get_events,
		get_news,
		get_press,
		get_images,
		get_links,
		get_galleries
	);
	
	$sources = array_merge( $sources, self::get_data_sources());

	// login to data source server if auth details are given
	$login_url = get_option('smwimport_login_url');
	if ( $login_url != "" ){
		$username = get_option('smwimport_username');
		$password = get_option('smwimport_password');
		smwaccess::login($login_url,$username,$password);
	}

	self::load_ec3();
	$g_ret = true;
	foreach( $sources as $key => $source ){
		if ( is_wp_error($source) ){
			$g_ret = $source;
			continue;
		}
		$items = self::$source($key);
		if ( is_wp_error($items) ){
			error_log("smwimport: could not import from:".$source);
			$g_ret = $items;
			continue;
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
	// XXX: needed to make permalinks work (not documented)
	$wp_rewrite->flush_rules();
	$ret = self::delete_empty_subcategories();
	if ( is_wp_error($ret) ) $g_ret = $ret;
	if ( !is_wp_error($g_ret) ){
		$g_ret  = 'The import took '.(time() - self::$start_time).' seconds.'."\n";
		$g_ret .= 'Fetching data took '.self::$fetch_time.' seconds.';
	}
	return $g_ret;
  }

  /*  return the id of the link category into which links will be imported
  */
  private static function get_link_category() {
	$link_categories = get_terms('link_category', 'fields=ids&slug=smwimport&hide_empty=0');
	if (empty($link_categories)) 
		return new WP_Error('no_link_category', __("Link category 'smwimport' does not exist!"));
	return $link_categories[0];
  }

  /*  deletes all imported links
  */
  private static function delete_links() {
	$cat = self::get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$args = array( 'category' => (string)$cat );
	$links = get_bookmarks($args);
	foreach($links as $link)
		wp_delete_link($link->link_id);
  }

  /*  imports $link
      $link must be an array expected by wp_insert_link
  */
  private static function import_link($link) {
	$cat = self::get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$link['link_category'] = (string)$cat;
	return wp_insert_link($link,true);
  }

  /*  returns an array of all imported posts + attachments
  */
  private static function get_smwimport_posts(){
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

  /*  return a post with the specified $prim_key inside $category_id
      $category_id can be null
  */
  private static function get_post($prim_key, $category_id = null){
	if ( $category_id === null ){
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

  /*  import a post
      $postarr must be an array expected by wp_insert_post
  */
  private static function import_post($prim_key,&$postarr, $category_id ) {
	$postarr['post_category'] = array( $category_id );
	$postarr['post_status'] = 'publish';
	$posts = self::get_post($prim_key,$category_id);
	if ( !empty($posts) ){
		$ID = $posts[0]->ID;
		$postarr['ID'] = $ID;
		$post = get_post($ID,'ARRAY_A');
		$post['post_category'] = $postarr['post_category'];
		$diff = array_diff_assoc($postarr,$post);
		// the post did not change, so just return the ID
		if ( empty($diff) )
			return $ID;
	}
	// make sure each post has a different publish date
	// otherwise the 'next_post', 'previous_post' queries get confused
	self::$posttime -= 1;
	if ( !isset($postarr['post_date']) )
		$postarr['post_date'] = date("Y-m-d H:i:s",self::$posttime);

	$ID = wp_insert_post($postarr,true);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"_prim_key",$prim_key,true);
	add_post_meta($ID,"_post_type",'smwimport',true);
	return $ID;
  }

  /*  deletes all dates attached to $post_id
  */
  private static function delete_post_dates($post_id){
	// this requires the ec3 plugin
	if ( !class_exists(ec3_admin) ) return;
	$sched_entry = array(
		'action' => 'delete',
		'start'  => 'dummy',
		'end'  => 'dummy'
	);

	$ec3_admin=new ec3_Admin();
	$schedule = $ec3_admin->get_schedule($post_id);
	foreach( $schedule as $entry )
		$sched_entries[$entry->sched_id] = $sched_entry;
	if ( !empty($sched_entries) )
		$ec3_admin->ec3_save_schedule($post_id,$sched_entries);
  }

  /*  creates or updates a date for $post_id
  */
  private static function import_ec3_post_dates($post_id,$action,$start,$end){
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

  /*  creates or updates a date for $post_id
  */
  private static function import_post_dates($post_id,$action,$start,$end){
	$ret = true;
	// this requires the ec3 plugin
	if ( class_exists(ec3_admin) ) 
		$ret = self::import_ec3_post_dates($post_id,$action,$start,$end);

	// set meta data for the-events-calender
	update_post_meta($post_id,"_isEvent","yes");
	update_post_meta($post_id,"_EventStartDate",$start);
	$end = ( $end == null?$start:$end );
	update_post_meta($post_id,"_EventEndDate",$end);
	return $true;
  }

  /*  Attaches a post to categories. The categories are created if they do not
      exist.
      $post_id: id of the post
      $data: array with elements of the form:
	<parent_slug> => <category slug>
      $top_cat: id of the top category under which all categories will be created
  */
  private static function import_post_categories($post_ID,$data,$top_cat){
        $ret = 0;
	$categories[] = $top_cat;
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
	return wp_set_post_terms($post_ID,$categories,'category');
  }

  /*  import an attachment for $post_id
      The attachment is downloaded if $download is true and if it does not exist
  */
  private static function import_attachment_for_post($prim_key,$data,$post_id,$download = true) {
	$remotefile = $data['url'];
	$title = $data['title'];
	$localfile = (isset($data['file'])?$data['file']:basename($remotefile));

	$posts = self::get_post($prim_key);
	if ( empty($posts) ){
		if ( $download ){
			$contents = smwaccess::get_content($remotefile);
			if ( $contents == FALSE )
				return new WP_Error('download_failed', __("Could not get file:").$remotefile.'for post:'.$prim_key);
			$upload = wp_upload_bits($localfile,null,$contents);
			if ( $upload['error'] != false )
				return new WP_Error('upload_failed', $upload['error']);
			$filename = $upload['file'];
		}else $filename = $remotefile;

		$wp_filetype = wp_check_filetype(basename($filename), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $title,
			'guid' => $localfile,
			'post_excerpt' => $title,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id,  $attach_data );
		add_post_meta($attach_id,"_prim_key",$prim_key,true);
		add_post_meta($attach_id,"_post_type",'smwimport',true);
	}else{
		$post = $posts[0];
		if ( $post->guid != $localfile ){
			error_log('Attachment changed:'. $post->ID);
			// filename changed, delete this attachment and create a new one
			wp_delete_post($post->ID,true);
			$attach_id = self::import_attachment_for_post($prim_key,$data,$post_id);
		}else{
			//XXX: update the attachment? then we need a hash or something
			error_log('Attachment already exists:'. $post->ID);
			// only update title
			$post->post_title = $title;
			$post->post_excerpt = $title;
			$attach_id = wp_update_post($post);
		}
	}
	return $attach_id;
  }

}
