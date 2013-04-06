<?php
  /*
   Plugin Name: Blogroll Links
   Plugin URI: http://www.rajiv.com/blog/2008/02/10/blogroll-links/
   Description: Displays blogroll links on a Page or Post. Insert <code>[blogroll-links categoryslug="blogroll"]</code> to a Page or Post and it will display your blogroll links there.
   Author: Rajiv Pant
   Version: 2.3
   Author URI: http://www.rajiv.com/
   */
  
  
  /*
   Blogroll Links is a Wordpress Plugin that displays a list of blogroll links
   in a Post or Page on your Wordpress Blog.
   
   Version 1.1 includes modifications made to the admin panel layout to make it
   better compliant with the WordPress guidelines. Thanks to @federicobond.

   Version 2 switches over the tag format to WordPress shortcodes.
   The old format is still supported for backwards compatibility.

   Copyright (C) 2008-2010 Rajiv Pant
   Thanks to Dave Grega, Adam E. Falk (xenograg) for their contributions to this code.
      
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
      
   Examples of use:
   
   WordPress shortcode syntax:
   
   [blogroll-links categoryslug="rajiv-web" sortby="link_title"]
   [blogroll-links categoryslug="people" sortby="link_title" sortorder="desc"]
   
   */



function blogroll_links_html($category_id, $sort_by, $sort_order) 
{
	$bm = get_bookmarks( array(
            'orderby'        => $sort_by, 
            'order'          => $sort_order,
            'limit'          => -1, 
            'category'       => "$category_id",
            'category_name'  => null, 
            'hide_invisible' => 1,
            'show_updated'   => 0, 
            'include'        => null,
            'exclude'        => null,
            'search'         => '.'));

      $links .= '<ul>';
      foreach ($bm as $bookmark) {

		$rel_string = $bookmark->link_rel;
		$rel_tag_part = (strlen($rel_string) > 0) ? ' rel="' . $rel_string . '"' : '';

		$target_string = $bookmark->link_target;
		$target_tag_part = (strlen($target_string) > 0) ? ' target="' . $target_string . '"' : '';

		$description_string = $bookmark->link_description;
		$description_tag = (strlen($description_string) > 0) ? ' - ' . $description_string : '';

		$image_string = $bookmark->link_image;
		$image_tag = (strlen($image_string) > 0) ? '<br />' . '<img src="' . $bookmark->link_image . '" border="0"/>' : '';

	  	$links .= sprintf(
			'<li><a href="%s"%s%s>%s</a>%s%s</li>',
			$bookmark->link_url,
			$rel_tag_part,
			$target_tag_part,
			$bookmark->link_name,
			$description_tag,
			$image_tag
		);

      }
      $links .= '</ul>';
    
      return $links;
}

  function blogroll_links_handler($atts) {
  
    global $wpdb, $table_prefix;
      
    $attributes = shortcode_atts(array(
        'categoryslug' => get_option('blogroll_links_default_category_slug'),
        'sortby'       => get_option('blogroll_links_default_sort_by'),
        'sortorder'    => get_option('blogroll_links_default_sort_order'),
        'debug'        => '0',
    ), $atts);


	$category_slug = $attributes['categoryslug'];
    $sort_by       = $attributes['sortby'];
    $sort_order    = $attributes['sortorder'];
    $debug         = $attributes['debug'];

	$sql = sprintf("SELECT term_id FROM wp_terms WHERE slug = '%s' LIMIT 1", $category_slug);
    $results = $wpdb->get_results($sql);
	$category_id = $results[0]->term_id;

	// error_log(var_export($results, true));
      
    $links = blogroll_links_html($category_id, $sort_by, $sort_order);               
    return $links;
  }


  
  
  // Replaces the <!--blogroll-links--> tag and its contents with the blogroll links
  // This function supports a previous (now deprecated) syntax
  function blogroll_links_text($text)
  {
      global $wpdb, $table_prefix;
      
      // Only perform plugin functionality if post/page contains <!-- show-blogroll-links -->
      while (preg_match("{<!--blogroll-links\b(.*?)-->.*?<!--/blogroll-links-->}", $text, $matches)) {
          // to contain the XHTML code that contains the links returned
          $links = '';
          
          $tmp = get_option('blogroll_links_default_category_slug');
          $category_slug = (strlen($tmp) > 0) ? $tmp : 'blogroll';
          
          $tmp = get_option('blogroll_links_default_sort_by');
          $sort_by = (strlen($tmp) > 0) ? $tmp : 'link_name';
          
          $tmp = get_option('blogroll_links_default_sort_order');
          $sort_order = (strlen($tmp) > 0) ? $tmp : '';
          
          $attributes = $matches[1];
          
          if (preg_match("{\bcategory-slug\b=\"(.*?)\"}", $attributes, $matches)) {
              $category_slug = $matches[1];
          }
          
          if (preg_match("{\bsort-by\b=\"(.*?)\"}", $attributes, $matches)) {
              $sort_by = $matches[1];
          }
          
          if (preg_match("{\bsort-order\b=\"(.*?)\"}", $attributes, $matches)) {
              $sort_order = $matches[1];
          }
          

		$sql = sprintf("SELECT term_id FROM wp_terms WHERE slug = '%s' LIMIT 1", $category_slug);
	    $results = $wpdb->get_results($sql);
		$category_id = $results[0]->term_id;

		// error_log(var_export($results, true));

	    $links = blogroll_links_html($category_id, $sort_by, $sort_order);               

          
          // by default preg_replace replaces all, so the 4th paramter is set to 1, to only replace once.
          $text = preg_replace("{<!--blogroll-links\b.*?-->.*?<!--/blogroll-links-->}", $links, $text, 1);
      }
      // end while loop
      
      return $text;
  }
  // end function blogroll_links_text()
  
  
  
  
  
  // admin menu
  function blogroll_links_admin()
  {
      if (function_exists('add_options_page')) {
          add_options_page('Blogroll Links', 'Blogroll Links', 1, basename(__FILE__), 'blogroll_links_admin_panel');
      }
  }
  
  
  function blogroll_links_admin_panel()
  {
      // Add options if first time running
      add_option('blogroll_links_new_window', 'no', 'Blogroll Links - open links in new window');
      
      if (isset($_POST['info_update'])) {
          // update settings
          
          if ($_POST['new-window'] == 'on') {
              $new = 'yes';
          } else {
              $new = 'no';
          }
          
          update_option('blogroll_links_new_window', $new);
      } else {
          // load settings from database
          $new = get_option('blogroll_links_default_category_slug');
      }
?>
<div class=wrap>
  <form method="post">
    <h2>Blogroll Links Plugin Options</h2>
    <h3 class="title">Default Settings</h3>
    <table class="form-table">
      <tr valign="top">
        <th scope="row"><label for="blogroll_links_default_category_slug">Category Slug</label></th>
        <td><input class="regular-text" type="text" id="blogroll_links_default_category_slug" name="blogroll_links_default_category_slug" value="<?php
      checked('yes', $new);
?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="blogroll_links_default_sort_by">Sort-By</label></th>
        <td><input class="regular-text" type="text" id="blogroll_links_default_sort_by" name="blogroll_links_default_sort_by" value="<?php
      checked('yes', $new);
?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="blogroll_links_default_sort_order">Sort Order</label></th>
        <td><input class="regular-text" type="text" id="blogroll_links_default_sort_order" name="blogroll_links_default_sort_order" value="<?php
      checked('yes', $new);
?>" /></td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" class="button-primary" name="info_update" value="<?php
      _e('Save Changes')
?>" />
    </p>
  </form>
</div>
<?php
      } // end function blogroll_links_admin_panel()
      
      
      // hooks
      add_filter('the_content', 'blogroll_links_text', 2);
      add_shortcode('blogroll-links', 'blogroll_links_handler');
      add_action('admin_menu', 'blogroll_links_admin');
?>