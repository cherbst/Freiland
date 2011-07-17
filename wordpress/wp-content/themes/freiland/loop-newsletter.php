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
			<form action="http://groups.google.com/a/yourdomain.com/group/yourgroupname/boxsubscribe">
				<table border="0" cellpadding="0" cellspacing="0">
				    <tr>
      					<td align="right" class="label">NAME</td>
					<td align="right"><input name="name" type="text" size="30" maxlength="30"></td>
				    </tr>
				    <tr>
					<td align="right" class="label">EMAIL</td>
					<td align="right"><input name="email" type="text" size="30" maxlength="40"></td>
				    </tr>
				    <tr>
					<td></td><td align="right"><input disabled id="submit" type="submit" name="sub" value="send"></td>
				    </tr>
				</table>
			</form>
		</div>
		<p>Sorry, der Newsletter ist zur Zeit noch nicht verfügbar. </p>
	</div><!-- #post-## -->

<?php endwhile; // end of the loop. ?>
