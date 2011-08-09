<?php
/*
	The template to output only the event listing,
	withpout header, footer and sidebar.
	To be used in ajax calls or to be included
	from full template files
*/


/* prevent pagination */
global $wp_query;
$args = array_merge( $wp_query->query, array('posts_per_page' => -1 ));
query_posts($args);
?>

<div id="event-listing">
<?php while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="entry-date">
			<?php freiland_the_event_date(); ?>
		</div><!-- .entry-date -->

		<div class="entry-content">
			<div class="event-type">
				<?php freiland_the_event_meta('eventtype','</br>'); ?>
			</div>
			<div class="event-title">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
				<div class="event-genre">
					<?php freiland_the_event_meta('subtitle'); ?>
				</div>
			</div>
		</div><!-- .entry-content -->
	</div><!-- #post-## -->

<?php endwhile; // End the loop. Whew. ?>
</div><!-- #event-listing -->
