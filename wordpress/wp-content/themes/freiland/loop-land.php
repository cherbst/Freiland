<?php
/**
 * This is the template file for the land page
 *
 *
 * @package WordPress
 * @subpackage Freiland
 * @since Freiland 0.1
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content">
			<?php 
				$bba="Büro-, Beratungs-, Atelierräume";
				$cafe="Cafe";
				$spartacus="Spartacus";
				$jfb="Jugendclub, freiRaum, Bandhaus";
				$wk="Werkstätten, Künstlerunterkunft";
				$lager="Lager";
				$aquarium="Aquarium";
				$url = dirname(get_bloginfo('stylesheet_url')).'/images/';
			?>
			<div id="land_box">
				<div id="tooltips">
				<span id="land_bba">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" />
				</span>
				<span id="land_cafe">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" />
				</span>
				<span id="land_spartacus">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				<span id="land_jfb">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				<span id="land_wk">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				<span id="land_aquarium">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				</div>
				<img class="alignleft" usemap="#map" src="<?php echo $url; ?>lageplan.png" alt="" />
				<map name="map">

				<area href="haus1" onmouseover="TagToTip('land_bba')" onmouseout="UnTip()" shape="poly" 
					coords="60,280,200,210,220,250,80,320" href="#" alt="<?php echo $bba; ?>" />
				<area href="haus2" onmouseover="TagToTip('land_cafe')" onmouseout="UnTip()" shape="rectangle" 
					coords="200,410,230,490" href="#" alt="<?php echo $cafe; ?>" />
				<area href="haus3" onmouseover="TagToTip('land_spartacus')" onmouseout="UnTip()" shape="rectangle" 
					coords="135,380,170,485" href="#" alt="<?php echo $spartacus; ?>" />
				<area href="haus4" onmouseover="TagToTip('land_jfb')" onmouseout="UnTip()" shape="rectangle" 
					coords="80,375,100,510" href="#" alt="<?php echo $jfb; ?>" />
				<area href="haus5" onmouseover="TagToTip('land_wk')" onmouseout="UnTip()" shape="rectangle" 
					coords="175,515,225,550" href="#" alt="<?php echo $wk; ?>" />
				<area href="haus6" onmouseover="TagToTip('land_aquarium')" onmouseout="UnTip()" shape="rectangle" 
					coords="220,215,240,225" href="#" alt="<?php echo $aquarium; ?>" />
				</map>

				<div id="legende_box">
					<img src="<?php echo $url; ?>lageplan_beschreibung.png" alt="" />
				</div>
			</div>
		</div><!-- .entry-content -->
	</div><!-- #post-## -->


<?php endwhile; // end of the loop. ?>
