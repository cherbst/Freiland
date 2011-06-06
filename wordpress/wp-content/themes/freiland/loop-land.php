<?php
/**
 * The loop that displays a page.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop-page.php.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.2
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content">
			<div id="land_box">
				<img id="land_bba" src="http://localhost/freiland/wp-content/themes/freiland/images/broken_fingaz.jpg"> 
					<p>B&uuml;ro-, Beratungs-, Atelierr&auml;ume</p>
				</img>
				<img id="land_cafe" src="http://localhost/freiland/wp-content/themes/freiland/images/broken_fingaz.jpg"> 
					<p>Cafe</p>
				</img>
				<img id="land_spartacus" src="http://localhost/freiland/wp-content/themes/freiland/images/broken_fingaz.jpg"> 
					<p>Spartacus</p>
				</img>
				<img class="alignleft" usemap="#map" src="http://localhost/freiland/wp-content/themes/freiland/images/lageplan.png" alt="" />
				<map name="map">

				<area shape="rectangle" coords="30,240,200,320" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="210,230,230,260" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="80,350,110,480" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="130,350,160,450" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="190,370,220,450" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="100,470,170,500" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				<area shape="rectangle" coords="175,480,220,500" href="#" alt="B&uuml;ro-, Beratungs-, Atelierr&auml;ume" />

				</map>

				<div id="legende_box">
					<img src="http://localhost/freiland/wp-content/themes/freiland/images/lageplan_beschreibung.png" alt="" />
				</div>
			</div>
		</div><!-- .entry-content -->
	</div><!-- #post-## -->


<?php endwhile; // end of the loop. ?>
