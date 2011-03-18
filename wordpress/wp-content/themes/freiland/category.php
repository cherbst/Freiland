<?php
/**
 * The template for displaying Category Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

function freiland_category_header($cat_id){

	$filtercats = get_categories( "hide_empty=0&parent=$cat_id" );

?>
<form name="categoryfilterform" method="post" action="">
<input type="hidden" name="hiddencategoryfilter" value="Y">

<?php
	global $query_string;
	parse_str( $query_string, $query_args );

	$filtered = false;
	foreach( $filtercats as $filtercat ){
		$name = 'filter_'.$filtercat->slug; 
    		if( isset($_POST[ 'hiddencategoryfilter' ]) && $_POST['hiddencategoryfilter'] == 'Y' ) {
			if ( $_POST[$name] ){
				$query_args['category__and'][] = (int)$_POST[$name];
				$filtered = true;
			}
		}

		$args = array('hide_empty' => 0, 
			'name' => $name, 
			'orderby' => 'name',
			'selected' => $_POST[$name],
			'show_option_all'    => "Show all $filtercat->name"."s" ,
			'hierarchical' => true,
			'parent' => $filtercat->term_id );
	?>
		<div class="<?php echo $name ?>"><p><?php _e("Filter by $filtercat->name:", 'freiland-category' ); 
			wp_dropdown_categories($args); ?>
		</p></div>
  <?php }
	if ( $filtered )
		query_posts($query_args);

	if ( ! empty($filtercats) ){
 ?>
<p class="submit">
<input type="submit" name="filter" class="button-primary" value="<?php esc_attr_e('Filter posts') ?>" />
</p>
</form>
	
<?php 	}
  }

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<h1 class="page-title"><?php
					$cat = get_query_var('cat');
					$catObj = get_category ($cat);
					freiland_category_header($catObj->cat_ID);
				?></h1>
				<?php
					$category_description = category_description();
					if ( ! empty( $category_description ) )
						echo '<div class="archive-meta">' . $category_description . '</div>';

				/* Run the loop for the category page to output the posts.
				 * If you want to overload this in a child theme then include a file
				 * called loop-category.php and that will be used instead.
				 */
				get_template_part( 'loop', 'category' );
				?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
