<?php
/**
 * The template for displaying Category Archive pages.
 *
 * @package WordPress
 * @subpackage Freiland
 * @since Twenty Ten 1.0
 */

get_header(); ?>

<div id="main_left">

</div>

<div id="main_middle">

		<div id="container">
			<div id="content" role="main">

				<?php

				/* Run the loop for the category page to output the posts.
				 * If you want to overload this in a child theme then include a file
				 * called loop-category.php and that will be used instead.
				 */
				if ( in_category('events') )
					get_template_part( 'loop', 'events' );
				else
					get_template_part( 'loop', 'category' );
				?>

			</div><!-- #content -->
		</div><!-- #container -->

</div>

<div id="main_right">
	<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
