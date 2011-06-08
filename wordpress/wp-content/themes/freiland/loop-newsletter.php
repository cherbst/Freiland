<?php
/**
 * The loop that displays the newsletter page
 *
 *
 * @package WordPress
 * @subpackage Freiland
 * @since Freiland 0.1
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" <?php post_class('newsletter'); ?>>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php the_content(); ?>
		</div><!-- .entry-content -->
		<div id="input">
			<form action="#" method="post">
				<table border="0" cellpadding="0" cellspacing="0">
				    <tr>
      					<td class="label">NAME</td>
					<td><input name="name" type="text" size="30" maxlength="30"></td>
				    </tr>
				    <tr>
					<td class="label">EMAIL</td>
					<td><input name="zuname" type="text" size="30" maxlength="40"></td>
				    </tr>
				    <tr>
					<td></td><td align="right"><input id="submit" type="submit" value=""></td>
				    </tr>
				</table>
			</form>
		</div>
	</div><!-- #post-## -->

<?php endwhile; // end of the loop. ?>
