<?php
use App\Classes\AppOption;
use App\Classes\API;

new AppOption();

/*==========================*/
/*  Define Functions (A-Z)  */
/*==========================*/

/** Allow uploads of SVGs to the media library */
function allow_svg_upload($mimes) {
  $mimes['svg'] = 'image/svg+xml';

  return $mimes;
}

/** Remove non-essential items from the WP Admin bar  */
function clean_admin_bar() {
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wp-logo');
    // $wp_admin_bar->remove_menu('customize');
  $wp_admin_bar->remove_menu('updates');
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_menu('itsec_admin_bar_menu');
  $wp_admin_bar->remove_menu('wpseo-menu');
}

/** fixes improper display of svg thumbnails in media library */
function fix_svg_thumb_display() {
  echo '<style>
    td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail { 
        width: 100% !important; 
        height: auto !important; 
    }
    </style>';
}

/** Global custom stylesheet for WP back-end. */
function get_sage_admin_styles() {
  wp_register_style('sage-admin-styles', get_theme_file_uri() . '/resources/sage-admin.css');
  wp_enqueue_style('sage-admin-styles');
}

/** modify rest responses from app/Classes/API.php */
function init_rest_api_extensions() {
  if (class_exists('\App\Classes\API')) {
    new API();
  }
}

/** Browser detection function for Last 3 Versions of IE */
function is_ie() {
  return boolval(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/') !== false);
}

/**
 * Change all image src from editor to be compatible with blazy lazy-loader
 */ 
function lazy_load_editor_images($content) {
	//-- Change src/srcset to data attributes.
	$content = preg_replace("/<img(.*?)(src=|srcset=)(.*?)>/i", '<img$1data-$2$3>', $content);

	//-- Add .lazy-load class to each image that already has a class.
	$content = preg_replace('/<img(.*?)class=\"(.*?)\"(.*?)>/i', '<img$1class="$2 lazy"$3>', $content);

	//-- Add .lazy-load class to each image that doesn't already have a class.
	$content = preg_replace('/<img((.(?!class=))*)\/?>/i', '<img class="lazy"$1>', $content);
	
  return $content;
}

/** Hide pages for CPTUI and ACF if the user isn't privileged. */
function remove_menu_items_from_admin() {
  remove_menu_page('cptui_main_menu');
  remove_menu_page('edit.php?post_type=acf-field-group');
}




/*============================*/
/*      Admin Functions       */
/*============================*/
if (is_admin()) {
  $current_user = wp_get_current_user();
  add_action('admin_head', 'get_sage_admin_styles');

  // User is not an admin
  if (!in_array('administrator', $current_user->roles)) {
    add_action('admin_init', 'remove_menu_items_from_admin');
  }
}

/*===========================*/
/*          Actions          */
/*===========================*/

add_action('wp_before_admin_bar_render', 'clean_admin_bar');
add_action('admin_head', 'fix_svg_thumb_display');
add_action('rest_api_init', 'init_rest_api_extensions');


/*===========================*/
/*          Filters          */
/*===========================*/

add_filter('upload_mimes', 'allow_svg_upload');
add_filter('the_content' , 'lazy_load_editor_images');