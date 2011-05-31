<?php
/**
 * Template Name: Events404
 *
 * The template for displaying 404 pages for events (Not Found).
 *
 * @package WordPress
 * @subpackage Freiland
 * @since Freiland 0.1
 */

get_header(); ?>

	<div id="container">
		<div id="content" role="main">
 			<?php if (have_posts()) : while (have_posts()) : the_post();?>
			<div id="post-0" class="post error404 not-found">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<p><?php the_content(); ?></p>
					<?php 
						$cat = get_category_by_slug('events');
						// Get the URL of this category
						$category_link = get_category_link( $cat->term_id );
					 ?>
					<!-- Print a link to this category -->
					<a href="<?php echo $category_link; ?>" title="Zeige alle Veranstaltungen">Zeige alle Veranstaltungen</a>
				</div><!-- .entry-content -->
			</div><!-- #post-0 -->
 			<?php endwhile; endif; ?>
		</div><!-- #content -->
	</div><!-- #container -->

<?php get_footer(); ?>
