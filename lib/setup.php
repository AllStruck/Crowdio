<?php
// Globals and constants:
global $wpdb, $table_prefix;

define('CROWDIO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CROWDIO_COMMENT_TABLE_NAME', $table_prefix . 'crowdio_comments');
define('CROWDIO_VOTE_TABLE_NAME', $table_prefix . 'crowdio_votes');

// Variables:
$crowdio_db = new CrowdioDatabase();

// Plugin installation:
// Create new tables if they do not exist:
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	$crowdio_db->create_tables();
}

// Add custom post type for Requests for Ideas:
function add_crowdios_custom_post_type_rfis() {
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
add_action( 'init', 'add_crowdios_custom_post_type_rfis' );

// 