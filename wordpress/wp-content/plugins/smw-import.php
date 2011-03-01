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
$events_option_name = 'smwimport_category_events';
$news_option_name = 'smwimport_category_news';
$press_option_name = 'smwimport_category_press';

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

	if ( $ret == 0 ) $message = 'successfully imported.';
	else $message = 'not yet implemented.';
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
    global $events_option_name, $news_option_name, $press_option_name;
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $host_opt['name'] = 'smwimport_smw_host';
    $categories_opt['events']['name'] = $events_option_name;
    $categories_opt['news']['name'] = $news_option_name;
    $categories_opt['press']['name'] = $press_option_name;
    $hidden_field_name = 'smwimport_submit_hidden';

    // Read in existing option value from database
    $host_opt['val'] = get_option( $host_opt['name'] );
    foreach ( $categories_opt as $key => $opt )
    	$categories_opt[$key]['val'] = get_option( $opt['name'] );


    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $host_opt['val'] = $_POST[ $host_opt['name'] ];

        foreach ( $categories_opt as $key => $opt )
    	    $categories_opt[$key]['val'] = $_POST[ $opt['name'] ];

        // Save the posted value in the database
        update_option( $host_opt['name'], $host_opt['val'] );

        foreach ( $categories_opt as $key => $opt )
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
<input type="text" name="<?php echo $host_opt['name']; ?>" value="<?php echo $host_opt['val']; ?>" size="20">
</p>

<?php foreach ( $categories_opt as $key => $opt ){ ?>
<p><?php _e("Category to import $key:", 'menu-smwimport' ); ?> 
<?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => $opt['name'], 'orderby' => 'name', 'selected' => $opt['val'], 'hierarchical' => true)); ?>
</p>
<?php } ?>
<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
 
}


function smwimport_import_all() {
	$ret = smwimport_import_events();
	$ret += smwimport_import_links();
	return $ret;
}

function smwimport_import_links() {
	$linkdata['link_name'] = 'smwimport link';
	$linkdata['link_url'] = 'http://www.smwimport.org';
	$linkdata['link_description'] = 'This is a link automtically added by smwimport.';
	wp_insert_link($linkdata);
	return 0;
}

function smwimport_get_event($prim_key){
	global $events_option_name;
	$args = array(
		'category' => get_option( $events_option_name ),
		'numberposts' => 1,
		'meta_key' => '_prim_key',
		'meta_value' => $prim_key
	);
	return get_posts($args);
}

function smwimport_import_events() {
	global $events_option_name;
	$ret = 0;
	
	$postarr['post_status'] = 'publish';
	$postarr['post_title'] = 'SMW Post';
	$postarr['post_excerpt'] = 'A new event';
	$postarr['post_content'] = '<strong>Newer imported event content</strong>';
	$postarr['post_category'] = array( get_option( $events_option_name ));
	$posts = smwimport_get_event($postarr['post_title']);
	if ( !empty($posts) ){
		$postarr['ID'] = $posts[0]->ID;
		$ID = wp_update_post($postarr);
	}else
		$ID = wp_insert_post($postarr);
	if ( $ID == 0 ) return 1;
	add_post_meta($ID,"age",18);
	add_post_meta($ID,"place","freiland");
	add_post_meta($ID,"room","Big room");
	add_post_meta($ID,"house","Big house");
	add_post_meta($ID,"genre","Rock");
	add_post_meta($ID,"type","concert");
	add_post_meta($ID,"_prim_key",$postarr['post_title']);
	return $ret;
}
?>
