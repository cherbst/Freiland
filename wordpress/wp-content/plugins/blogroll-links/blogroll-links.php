<?php
  /*
   Plugin Name: Blogroll Links
   Plugin URI: http://www.rajiv.com/blog/2008/02/10/blogroll-links/
   Description: Displays blogroll links on a Page or Post. Insert <code>[blogroll-links categoryslug="blogroll"]</code> to a Page or Post and it will display your blogroll links there.
   Author: Rajiv Pant
   Version: 2.2
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
   
   New WordPress shortcode format:
   
   [blogroll-links categoryslug="rajiv-web" sortby="link_title"]
   [blogroll-links categoryslug="people" sortby="link_title" sortorder="desc"]

   Old format (now deprecated) from version 1.0 and 1.1:
   
   <!--blogroll-links category-slug="rajiv-web" sort-by="link_title"><!--/blogroll-links-->
   <!--blogroll-links category-slug="people" sort-by="link_title" sort-order="desc"--><!--/blogroll-links-->
   
   */



  function blogroll_links_handler($atts) {
  
//    $attributes = ''; // for debugging
//    foreach ($atts as $key=>$value) { // for debugging
//        $attributes .= "$key: $value\n"; // for debugging
//   } // for debugging
//    return "Attributes: $attributes<br/>Content: $content"; // for debugging

  
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

          /*
           
           Sample SQL Query:
           SELECT *
           FROM wp_links, wp_term_relationships
           WHERE link_id = object_id
           AND link_visible = 'Y'
           AND term_taxonomy_id = (
           SELECT wp_term_taxonomy.term_taxonomy_id
           FROM wp_term_taxonomy
           WHERE term_id = (SELECT DISTINCT term_id
           FROM wp_terms, wp_term_relationships
           WHERE slug = 'charity'
           AND taxonomy = 'link_category'))
           ORDER BY link_name
           
           */
          
          $sort_by_str = (strlen($sort_by) > 0) ? "ORDER BY " . $sort_by : '';
          
          $sql = "SELECT * " . "FROM $wpdb->links, " . $table_prefix . "term_relationships " .
          "WHERE link_id = object_id " . "AND link_visible='Y' " . // Skip links marked as not to be visible
          "AND term_taxonomy_id = ( " . "SELECT " . $table_prefix . "term_taxonomy.term_taxonomy_id " .
          "FROM " . $table_prefix . "term_taxonomy " . "WHERE term_id = (SELECT DISTINCT term_id " .
          "FROM " . $table_prefix . "terms, " . $table_prefix . "term_relationships " .
          "WHERE slug = '" . $category_slug . "' " . "AND taxonomy = 'link_category')) " .
          $sort_by_str;

          if ($debug == 1) {
			  $links .= "category-slug=". $category_slug . "\n<br />";
			  $links .= "sort-order=". $sort_order . "\n<br />";
			  $links .= "sort-by=". $sort_by . "\n<br />";
			  $links .= "sql=". $sql . "\n<br />";
          }
          
          $alllinks = $wpdb->get_results($sql);
          
          $links .= '<ul class="Linklist">';
          
          foreach ($alllinks as $link) {
              $url = $link->link_url;
              $name = $link->link_name;
              $description = (strlen($link->link_description) > 0) ? '<span class="link-description"> - ' . $link->link_description . '</span>' : '';
              $rel = (strlen($link->link_rel) > 0) ? ' rel="' . $link->link_rel . '"' : '';
              $image = (strlen($link->link_image) > 0) ? '<img src="' . $link->link_image . '" border="0" class="LinkImage"/>' : '';
              $target = (strlen($link->link_target) > 0) ? ' target="' . $link->link_target . '"' : '';
              $links .= '<li>'.$image.'<a href="' . $url . '"' . $rel . $target . '>' . $name . '</a>' . $description . '</li>';
          }
          
          $links .= '</ul>';
                
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
          
          
          /*
           
           Sample SQL Query:
           SELECT *
           FROM wp_links, wp_term_relationships
           WHERE link_id = object_id
           AND link_visible = 'Y'
           AND term_taxonomy_id = (
           SELECT wp_term_taxonomy.term_taxonomy_id
           FROM wp_term_taxonomy
           WHERE term_id = (SELECT DISTINCT term_id
           FROM wp_terms, wp_term_relationships
           WHERE slug = 'charity'
           AND taxonomy = 'link_category'))
           ORDER BY link_name
           
           */
          
          $sql = "SELECT * " . "FROM $wpdb->links, " . $table_prefix . "term_relationships " . "WHERE link_id = object_id " . "AND link_visible='Y' " . // Skip links marked as not to be visible
          "AND term_taxonomy_id = ( " . "SELECT " . $table_prefix . "term_taxonomy.term_taxonomy_id " . "FROM " . $table_prefix . "term_taxonomy " . "WHERE term_id = (SELECT DISTINCT term_id " . "FROM " . $table_prefix . "terms, " . $table_prefix . "term_relationships " . "WHERE slug = '" . $category_slug . "' " . "AND taxonomy = 'link_category')) " . "ORDER BY " . $sort_by;
          
          //$links .= "attributes=". $attributes . "\n<br />"; // for debugging
          //$links .= "category_slug=". $category_slug . "\n<br />"; // for debugging
          //$links .= "sql=". $sql . "\n<br />"; // for debugging
          
          
          $alllinks = $wpdb->get_results($sql);
          
          $links .= '<ul>' . "\n";
          
          foreach ($alllinks as $link) {
              $url = $link->link_url;
              $name = $link->link_name;
              $description = (strlen($link->link_description) > 0) ? ' - ' . $link->link_description : '';
              $rel = (strlen($link->link_rel) > 0) ? ' rel="' . $link->link_rel . '"' : '';
              $image = (strlen($link->link_image) > 0) ? '<img src="' . $link->link_image . '" border="0"/>' : '';
              $target = (strlen($link->link_target) > 0) ? ' target="' . $link->link_target . '"' : '';
              $links .= '<li><a href="' . $url . '"' . $rel . $target . '>' . $name . '</a>' . $description . '<br />' . $image . '</li>' . "\n";
          }
          
          $links .= '</ul>' . "\n";
          
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
