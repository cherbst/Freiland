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

	foreach( $filtercats as $filtercat ){
		$args = array('hide_empty' => 0, 
			'name' => 'filter_'.$filtercat->slug, 
			'orderby' => 'name',
			'show_option_all'    => "Show all $filtercat->name"."s" ,
			'hierarchical' => true,
			'parent' => $filtercat->term_id );
	?>
		<p><?php _e("Filter by $filtercat->name:", 'freiland-category' ); 
			wp_dropdown_categories($args); ?>
		</p>
	<?php
	}
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
