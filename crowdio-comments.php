<?php
/*
Plugin Name: Crowdio Comments
Plugin URI: http://crowdio.com/
Description: Get your idea vetted with this WordPress comment engine.
Author: Crowdio <team@crowdio.com>
Version: 1.0
Author URI: http://crowdio.com/
*/

require plugin_dir_path( __FILE__ ) . 'comment-voting.php';

function add_crowdios_type() {
  $labels = array(
    'name' => _x('Request for Ideas', 'post type general name', 'crowdio_rfis'),
    'singular_name' => _x('Request for Ideas', 'post type singular name', 'crowdio_rfis'),
    'add_new' => _x('Add New', 'crowdio_rfis', 'crowdio_rfis'),
    'add_new_item' => __('Add New Request for Ideas', 'crowdio_rfis'),
    'edit_item' => __('Edit Request for Ideas', 'crowdio_rfis'),
    'new_item' => __('New Request for Ideas', 'crowdio_rfis'),
    'all_items' => __('All Requests for Ideas', 'crowdio_rfis'),
    'view_item' => __('View Request for Ideas', 'crowdio_rfis'),
    'search_items' => __('Search Requests for Ideas', 'crowdio_rfis'),
    'not_found' =>  __('No Requests for Ideas found', 'crowdio_rfis'),
    'not_found_in_trash' => __('No Requests for Ideas found in Trash', 'crowdio_rfis'), 
    'parent_item_colon' => '',
    'menu_name' => __('Requests for Ideas', 'crowdio_rfis')

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => _x( 'request-for-ideas', 'URL slug', 'crowdio_rfis' ) ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'author', 'avatar'),
    'menu_icon' => plugins_url( 'Checkmark.png', __FILE__ )
  ); 
  register_post_type('crowdios', $args);
  remove_post_type_support( 'crowsdios', 'comments' );
}
add_action( 'init', 'add_crowdios_type' );

function remove_menus () {
global $menu;
	$restricted = array(''); //__('Comments'));
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}
add_action('admin_menu', 'remove_menus');