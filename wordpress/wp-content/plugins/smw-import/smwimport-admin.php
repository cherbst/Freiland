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
    $hidden_field_name = 'smwimport_submit_hidden';

    $num_sources = (int)get_option('smwimport_num_data_sources');
    // Read in existing option value from database
    for ( $source = 0; $source < $num_sources; $source++)
	$datasources_opt[$source] = get_option('smwimport_data_source'.$source);

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value

	if ( $_POST['Submit'] ){
		foreach( $datasources_opt as $key => $opt )
    		    $datasources_opt[$key] = $_POST[ 'smwimport_data_source'.$key ];

		// Save the posted value in the database
		foreach( $datasources_opt as $key => $opt )
			update_option( 'smwimport_data_source'.$key, $opt );
		$message = __('settings saved.', 'menu-smwimport' );
	}else if ( $_POST['NewSource'] ){
		// add new data source
		$num_sources += 1;
		$datasources_opt[] = '';
		update_option( 'smwimport_num_data_sources', $num_sources );	
		$message = __('New data source added.', 'menu-smwimport' );
	}else if ( $_POST['RemoveSource'] ){
		// remove last data source
		$num_sources -= 1;
		unset($datasources_opt[$num_sources]);
		delete_option('smwimport_data_source'.$num_sources);
		update_option( 'smwimport_num_data_sources', $num_sources );
		$message = __('Data source removed.', 'menu-smwimport' );
	}
        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php echo($message);  ?></strong></p></div>
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
<p><?php echo(($key+1).'.'); _e("Data source:", 'menu-smwimport' ); ?> 
<input type="text" name="smwimport_data_source<?php echo $key; ?>" value="<?php echo $opt; ?>" size="80">
</p>
<?php } ?>

<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
<input type="submit" name="NewSource" class="button-secondary" value="<?php esc_attr_e('Add new data source') ?>" />
<?php if ( $num_sources > 0 ){ ?>
<input type="submit" name="RemoveSource" class="button-secondary" value="<?php esc_attr_e('Remove last data source') ?>" />
<?php } ?>
</p>

</form>
</div>

<?php
 
}


?>
