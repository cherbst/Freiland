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

	<div id="post-<?php the_ID(); ?>" <?php post_class('land'); ?>>
		<div class="entry-content">
			<?php 
				$url = dirname(get_bloginfo('stylesheet_url')).'/images/';

				$coords = array( 'haus1' => array( array( 'poly','60,280,200,210,220,250,80,320'), 
								   array('0,0,200,10') ),
						 'haus2' => array( array( 'rectangle','200,410,230,490'), 
								   array('0,25,200,35') ),
						 'haus3' => array( array( 'rectangle','135,380,170,485'), 
								   array('0,50,200,60') ),
						 'haus4' => array( array( 'rectangle','80,375,100,510'), 
								   array('0,75,200,85') ),
						 'haus5' => array( array( 'rectangle','175,515,225,550'), 
								   array('0,100,200,110') ),
						 'haus6' => array( array( 'rectangle','220,215,240,225'),
								   array('0,140,200,150') )
						);

				$arr = array('post_parent' => $post->ID,
					     'post_type' => 'page',
					     'numberposts' => -1);
				$pages = get_posts($arr);
			?>
			<div id="land_box">
				<div id="tooltips">
				<?php 
				foreach ( $pages as $page){
					if ( !has_post_thumbnail($page->ID) )
						continue;
					$houses[$page->post_name] = $page;
				?>
					<span id="land_<?php echo $page->post_name; ?>">
						<?php echo get_the_post_thumbnail($page->ID,'thumbnail','class=tooltip'); ?>
					</span>
				<?php } ?>
				</div>
				<img class="alignleft" usemap="#map" src="<?php echo $url; ?>lageplan.png" alt="" />
				<map name="map">

				<?php 
				foreach ( $houses as $house => $page){
					if ( !isset($coords[$house]) ) continue;
				?>
				<area href="<?php echo $house; ?>" 
				      onmouseover="TagToTip('land_<?php echo $house; ?>')" onmouseout="UnTip()" 
				      shape="<?php echo $coords[$house][0][0]; ?>" 
				      coords="<?php echo $coords[$house][0][1]; ?>" 
				      alt="<?php echo $page->post_title; ?>" />
				<?php } ?>
				</map>

				<div id="legende_box">
					<img usemap="#map1" src="<?php echo $url; ?>lageplan_beschreibung.png" alt="" />
					<map name="map1">

					<?php 
						foreach ( $houses as $house => $page){
							if ( !isset($coords[$house]) ) continue;
					?>
						<area href="<?php echo $house; ?>"
				      		      shape="rectangle" 
						      coords="<?php echo $coords[$house][1][0]; ?>" 
						      alt="<?php echo $page->post_title; ?>" />
					<?php } ?>
					</map>
				</div>
			</div>
		</div><!-- .entry-content -->
	</div><!-- #post-## -->


<?php endwhile; // end of the loop. ?>
