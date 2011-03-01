<?php
/*
Plugin Name: SMW-Import
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Imports informations from a SMW into wordpress
Version: 0.1
Author: Christoph Herbst
Author URI: http://URI_Of_The_Plugin_Author
License: GPL2
*/
/*  Copyright 2011  Christoph Herbst  (email : chris.p.herbst@googlemail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $events_option_name;
global $press_option_name;
global $news_option_name;
global $images_page_option_name;
$events_option_name = 'smwimport_category_events';
$news_option_name = 'smwimport_category_news';
$press_option_name = 'smwimport_category_press';
$images_page_option_name = 'smwimport_page_images';

// Hook for adding admin menus
add_action('admin_menu', 'smwimport_add_pages');

// action function for above hook
function smwimport_add_pages() {
    $title = 'SMW Import';
    $slug = 'smwimport';
    // Add a new submenu under Tools:
    add_management_page( __($title,'menu-smwimport'), __($title,'menu-smwimport'), 'manage_options',$slug, 'smwimport_tools_page');

  // Add a new submenu under Settings:
    add_options_page(__($title,'menu-smwimport'), __($title,'menu-smwimport'), 'manage_options', $slug, 'smwimport_settings_page');

}


// mt_tools_page() displays the page content for the Test Tools submenu
function smwimport_tools_page() {
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    echo "<h2>" . __( 'SMW Import', 'menu-smwimport' ) . "</h2>";
    $hidden_field_name = 'smwimport_submit_hidden';

// See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

	$ret = smwimport_import_all();

	if ( is_wp_error($ret) )
		$message = $ret->get_error_message();
	else $message = 'successfully imported.';
        // Put an import done  message on the screen

?>
<div class="imported"><p><strong><?php _e($message, 'menu-smwimport' ); ?></strong></p></div>
<?php

    }


    // tools form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Import from SMW') ?>" />
</p>

</form>
</div>

<?php

}


// smw_import_page() displays the page content for the Test tools submenu
function smwimport_settings_page() {
    global $events_option_name, $news_option_name, $press_option_name, $images_page_option_name;
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $options['host']['name'] = 'smwimport_smw_host';
    $options['events']['name'] = $events_option_name;
    $options['news']['name'] = $news_option_name;
    $options['press']['name'] = $press_option_name;
    $options['images']['name'] = $images_page_option_name;
    $hidden_field_name = 'smwimport_submit_hidden';

    $categories_opt = array(
	'Events' => &$options['events'],
	'News' => &$options['news'],
	'Press' => &$options['press']);
    // Read in existing option value from database
    $host_opt['val'] = get_option( $host_opt['name'] );
    foreach ( $options as $key => $opt )
    	$options[$key]['val'] = get_option( $opt['name'] );


    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value

        foreach ( $options as $key => $opt )
    	    $options[$key]['val'] = $_POST[ $opt['name'] ];

        // Save the posted value in the database
        foreach ( $options as $key => $opt )
    	    update_option( $opt['name'], $opt['val'] );

        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-smwimport' ); ?></strong></p></div>
<?php

    }

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'SMW Import Settings', 'menu-smwimport' ) . "</h2>";

    // settings form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("SMW Host name:", 'menu-smwimport' ); ?> 
<input type="text" name="<?php echo $options['host']['name']; ?>" value="<?php echo $options['host']['val']; ?>" size="20">
</p>

<?php foreach ( $categories_opt as $key => $opt ){ ?>
<p><?php _e("Category to import $key:", 'menu-smwimport' ); ?> 
<?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => $opt['name'], 'orderby' => 'name', 'selected' => $opt['val'], 'hierarchical' => true)); ?>
</p>
<?php } ?>
<p><?php _e("Page to import Images:", 'menu-smwimport' ); ?> 
<?php wp_dropdown_pages(array('hide_empty' => 0, 'name' => $options['images']['name'], 'orderby' => 'name', 'selected' => $options['images']['val'], 'hierarchical' => true)); ?>
</p>
<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
 
}


function smwimport_import_all() {
	$importers = array( smwimport_import_links,
		smwimport_import_events,
		smwimport_import_news,
		smwimport_import_press,
		smwimport_import_image);
	foreach( $importers as $importer ){
		$ret = $importer();
		if ( is_wp_error($ret) )
			break;
	}
	return $ret;
}

function smwimport_get_link_category() {
	$link_categories = get_terms('link_category', 'fields=ids&slug=smwimport&hide_empty=0');
	if (empty($link_categories)) 
		return new WP_Error('no_link_category', __("Link category 'smwimport' does not exist!"));
	return $link_categories[0];
}

function smwimport_delete_links() {
	$cat = smwimport_get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$args = array( 'category' => (string)$cat );
	$links = get_bookmarks($args);
	foreach($links as $link)
		wp_delete_link($link->link_id);
}

function smwimport_import_links() {
	smwimport_delete_links();
	$linkdata['link_name'] = 'smwimport link';
	$linkdata['link_url'] = 'http://www.smwimport.org';
	$linkdata['link_description'] = 'This is a link automtically added by smwimport.';
	$cat = smwimport_get_link_category();
	if ( is_wp_error($cat) )
		return $cat;
	$linkdata['link_category'] = (string)$cat;
	return wp_insert_link($linkdata,true);
}

function smwimport_get_post($prim_key, $category_option){
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

function smwimport_get_post_content($category){

}

function smwimport_import_post($postarr, $category_option ) {
	$postarr['post_category'] = array( get_option( $category_option ));
	$posts = smwimport_get_post($postarr['post_title'],$category_option);
	if ( !empty($posts) )
		$postarr['ID'] = $posts[0]->ID;

	$ID = wp_insert_post($postarr,true);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"_prim_key",$postarr['post_title'],true);
}

function smwimport_import_events() {
	global $events_option_name;
	
	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = 'SMW Post';
	$postarr['post_excerpt'] = 'A new event';
	$postarr['post_content'] = '<strong>Newer imported event content</strong>';
	$ID = smwimport_import_post($postarr,$events_option_name);
	if ( is_wp_error($ID) ) return $ID;
	add_post_meta($ID,"age",18,true);
	add_post_meta($ID,"place","freiland",true);
	add_post_meta($ID,"room","Big room",true);
	add_post_meta($ID,"house","Big house",true);
	add_post_meta($ID,"genre","Rock",true);
	add_post_meta($ID,"type","concert",true);
	return $ID;
}

function smwimport_import_news() {
	global $news_option_name;

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = 'SMW News';
	$postarr['post_excerpt'] = 'A new news entry';
	$postarr['post_content'] = '<strong>Imported news content</strong>';
	$ID = smwimport_import_post($postarr,$news_option_name);
	return $ID;
}

function smwimport_import_press() {
	global $press_option_name;

	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = 'SMW Press';
	$postarr['post_excerpt'] = 'A new press entry';
	$postarr['post_content'] = '<strong>Imported press content</strong>';
	$ID = smwimport_import_post($postarr,$press_option_name);
	return $ID;
}

function smwimport_import_image() {
	global $images_page_option_name;
	$remotefile = 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png';
	$title = 'New imported image3';
	$localfile = basename($remotefile);

	$posts = smwimport_get_post($title,'image');
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
	$attach_id = wp_insert_attachment( $attachment, $filename, get_option( $images_page_option_name ) );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id,  $attach_data );
	add_post_meta($attach_id,"_prim_key",$title,true);
}

?>
