<?php
/*
Template Name: Welcome
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="title" content="<?php wp_title( '|', true, 'right' ); ?>" />
<meta name="description" content="<?php echo get_bloginfo( 'description', 'display' ); ?>" />
<link rel="image_src" href="http://localhost/freiland/wp-content/uploads/2011/09/logo_digital.png" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php echo dirname(get_bloginfo( 'stylesheet_url' )); ?>/welcome.css" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php echo dirname(get_bloginfo('stylesheet_url')).'/images'; ?>/favicon.ico" type="image/x-icon" />
</head>

<body <?php body_class(); ?>>

<div id="content">

 <?php if (have_posts()) : while (have_posts()) : the_post();?>
 <div class="post">
  <div class="entrytext">
   <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
  </div>
 <?php endwhile; endif; ?>

</div>

</body>
