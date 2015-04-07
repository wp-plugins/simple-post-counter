<?php
/*
Plugin Name: Simple Post Counter
Version: 0.1.1
Plugin URI: http://www.beliefmedia.com/wp-plugins/simple-post-counter.php
Description: A simple post and page counter that will record page impressions then display it as an image or text with shortcode. Provides basic stats on the edit and posts page. Shortcode will render the count on your page as text or an image. Usage: [ipc] outputs text. [ipc img="1"] ouputs an image count. [ipc img="1" type="race"] ouputs an image count with 'race' images. [ipc img="1" type="sqblue" height="30"] ouputs an ('sqblue' library) image count scaled to 30px in height. <code>Images</code> directory must be writable. Full details on our <a href="http://www.beliefmedia.com/wp-plugins/simple-post-counter.php">website</a>.
Author: Marty Khoury
Author URI: http://www.internoetics.com/
*/

/* Incluide image scale library */
include( plugin_dir_path( __FILE__ ) . 'inc/mr-image-resize.php');

function internoetics_wordpress_post_count() {
 global $post;
 $id = $post->ID;
 if ( (get_post_meta($id, 'Internoetics_Post_Count', true )) != '') {
  $count = get_post_meta($id, 'Internoetics_Post_Count', true );
  $newcount = $count + 1;
  update_post_meta($id, 'Internoetics_Post_Count', $newcount);
   } else {
  $count = update_post_meta($id, 'Internoetics_Post_Count', '1');
 }
}
add_action('wp_footer', 'internoetics_wordpress_post_count');

/* Scale Images */
function internoetics_resize_image($url, $width, $height=0, $align='') {
  return mr_image_resize($url, $width, $height, true, $align, false);
}

/* Shortcode function to retrieve count. Will cache for 5 minutes. */
function internoetics_post_count($atts) {
  extract(shortcode_atts(array(
    'img' => 0,
    'type' => 'digi',
    'width' => '',
    'height' => '35',
    'cache' => '300'
  ), $atts));

 $totalpostcount = 'ipc_' . md5($img.$type.$width.$height.$cache);
 $cachedposts = get_transient($totalpostcount);
 if ($cachedposts !== false) {
 return $cachedposts;

  } else {

   $id = get_the_ID();
   $count = get_post_meta($id, 'Internoetics_Post_Count', true );

   if ($img) {
     $hit = str_split($count, 1);
      foreach ($hit as $num) {
	 $imgurl = plugins_url('images', __FILE__) . '/' . $type . '/' . $num . '.jpg';
	 $img_resized_url = internoetics_resize_image($imgurl, $w="$width", $h="$height");
	 $visits .= '<img src="' . $img_resized_url . '" height="' . $h . '">';
       }
      } else {
    $visits = number_format($count);
   }

   set_transient($totalpostcount, $visits, $cache);
  return $visits;
 }
}
add_shortcode('ipc','internoetics_post_count');


function internoetics_count_column($columns) {
 $columns['views'] = 'Views';
 return $columns;
}
add_filter('manage_posts_columns', 'internoetics_count_column', 10);
add_filter('manage_pages_columns', 'internoetics_count_column', 10);

function internoetics_viewcount_column($name) {
  global $post;
   switch ($name) {
   case 'views':
   $views = get_post_meta($post->ID, 'Internoetics_Post_Count', true);
    if (!$views) $views = 'NA';
  echo $views;
 }
}
add_action('manage_posts_custom_column',  'internoetics_viewcount_column');


/*
	Add Post View Count to Publish Menu
*/


function internoetics_admin_display_count() {
  global $post;
  if (get_post_type($post) == 'post') {

  echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
  $postcount = get_post_meta( $post->ID, 'Internoetics_Post_Count', true );
   if ($postcount == '') $postcount = 'NA';
  echo '<label>Article Views: ' . $postcount . '</label><br />';
  echo '</div>';
 }
}
add_action( 'post_submitbox_misc_actions', 'internoetics_admin_display_count' );