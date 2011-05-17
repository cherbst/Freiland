<?php
/*
Template Name: Welcome
*/
?>

<div id="content">

 <?php if (have_posts()) : while (have_posts()) : the_post();?>
 <div class="post">
  <div class="entrytext">
   <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
  </div>
 <?php endwhile; endif; ?>

</div>
