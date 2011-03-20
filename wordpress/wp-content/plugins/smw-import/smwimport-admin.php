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

require_once('smwimport.php');
// Hook for adding admin menus
add_action('admin_menu', 'smwimport_add_pages');
add_filter( 'the_content', 'smwimport_filter_the_content' );
register_activation_hook( __FILE__, 'smwimport_activate_cron' );
register_deactivation_hook(__FILE__, 'smwimport_deactivate_cron');
add_action('smwimport_import_all_event', 'smwimport_import_all' );


function smwimport_deactivate_cron() {
	wp_clear_scheduled_hook('smwimport_import_all_event');
}

function smwimport_activate_cron() {
	wp_schedule_event(time(), 'hourly','smwimport_import_all_event');
}

function smwimport_import_all(){
	smwimport::import_all();
	// reschedule event
	wp_clear_scheduled_hook('smwimport_import_all_event');
	wp_schedule_event(time(), 'hourly','smwimport_import_all_event');
}

function smwimport_filter_the_content( $post_content ) {
	if ( in_category( get_option('smwimport_category_events') ) )
		return smwimport::get_event_content($post_content);
	else if ( in_category( get_option('smwimport_category_news') ) )
		return smwimport::get_news_content($post_content);
	else if ( in_category( get_option('smwimport_category_press') ) )
		return smwimport::get_press_content($post_content);
	return $post_content;
}

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
	if ( $_POST['Import'] ){
		$ret = smwimport::import_all();

		if ( is_wp_error($ret) )
			$message = $ret->get_error_message();
		else $message = 'successfully imported.';
	}else if ( $_POST['Delete'] ){
		$ret = smwimport::delete_all_imported();
		if ( is_wp_error($ret) )
			$message = $ret->get_error_message();
		else $message = 'successfully deleted all imported posts.';
	}
        // Put the result  message on the screen
?>
<div class="imported"><p><strong><?php _e($message, 'menu-smwimport' ); ?></strong></p></div>
<?php

    }


    // tools form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p class="submit">
<input type="submit" name="Import" class="button-primary" value="<?php esc_attr_e('Import from SMW') ?>" />
</p>
<p class="submit">
<input type="submit" name="Delete" class="button-primary" value="<?php esc_attr_e('Delete all imported') ?>" />
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
    $options['events_data']['name'] = 'smwimport_events_data';
    $options['news_data']['name'] = 'smwimport_news_data';
    $options['press_data']['name'] = 'smwimport_press_data';
    $options['links_data']['name'] = 'smwimport_links_data';
    $options['images_data']['name'] = 'smwimport_images_data';
    $hidden_field_name = 'smwimport_submit_hidden';

    $datasources_opt = array(
	'Events' => &$options['events_data'],
	'News' => &$options['news_data'],
	'Press' => &$options['press_data'],
	'Links' => &$options['links_data'],
	'Images' => &$options['images_data']);

    // Read in existing option value from database
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

<?php foreach ( $datasources_opt as $key => $opt ){ ?>
<p><?php _e("Data source for $key:", 'menu-smwimport' ); ?> 
<input type="text" name="<?php echo $opt['name']; ?>" value="<?php echo $opt['val']; ?>" size="80">
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


?>
