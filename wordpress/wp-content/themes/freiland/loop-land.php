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
				<span id="land_lager">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				<span id="land_aquarium">
					<img class="tooltip" src="<?php echo $url; ?>broken_fingaz.jpg" /> 
				</span>
				</div>
				<img class="alignleft" usemap="#map" src="<?php echo $url; ?>lageplan.png" alt="" />
				<map name="map">

				<area onmouseover="TagToTip('land_bba')" onmouseout="UnTip()" shape="rectangle" 
					coords="30,240,200,320" href="#" alt="<?php echo $bba; ?>" />
				<area onmouseover="TagToTip('land_cafe')" onmouseout="UnTip()" shape="rectangle" 
					coords="210,230,230,260" href="#" alt="<?php echo $cafe; ?>" />
				<area onmouseover="TagToTip('land_spartacus')" onmouseout="UnTip()" shape="rectangle" 
					coords="80,350,110,480" href="#" alt="<?php echo $spartacus; ?>" />
				<area onmouseover="TagToTip('land_jfb')" onmouseout="UnTip()" shape="rectangle" 
					coords="130,350,160,450" href="#" alt="<?php echo $jfb; ?>" />
				<area onmouseover="TagToTip('land_wk')" onmouseout="UnTip()" shape="rectangle" 
					coords="190,370,220,450" href="#" alt="<?php echo $wk; ?>" />
				<area onmouseover="TagToTip('land_lager')" onmouseout="UnTip()" shape="rectangle" 
					coords="100,470,170,500" href="#" alt="<?php echo $lager; ?>" />
				<area onmouseover="TagToTip('land_aquarium')" onmouseout="UnTip()" shape="rectangle" 
					coords="175,480,220,500" href="#" alt="<?php echo $aquarium; ?>" />
				</map>

				<div id="legende_box">
					<img src="<?php echo $url; ?>lageplan_beschreibung.png" alt="" />
				</div>
			</div>
		</div><!-- .entry-content -->
	</div><!-- #post-## -->


<?php endwhile; // end of the loop. ?>
