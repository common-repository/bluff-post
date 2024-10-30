<?php
if ( have_posts() ) :
	the_post();
	$post         = get_post();
	$post_content = $post->post_content;
	echo $post_content;
endif;
