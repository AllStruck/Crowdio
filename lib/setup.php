<?php

		require_once($lib_path . 'database.php');
		//require_once($lib_path . 'comment.php');
		require_once($lib_path . 'vote.php');


/**
* @Package Crowdio
*/
class Crowdio
{
	public static $plugin_path = CROWDIO_PLUGIN_DIR_PATH;
	public static $plugin_dir_url = CROWDIO_PLUGIN_DIR_URL;
	public static $comment_table_name = CROWDIO_COMMENT_TABLE_NAME;
	public static $vote_table_name = CROWDIO_VOTE_TABLE_NAME;

	public function __construct()
	{
		global $wpdb, $table_prefix;

		define('CROWDIO_PLUGIN_DIR_PATH', plugin_dir_path(CROWDIO_MAIN_PLUGIN_FILE));
		define('CROWDIO_PLUGIN_DIR_URL', plugin_dir_url(CROWDIO_MAIN_PLUGIN_FILE));
		define('CROWDIO_COMMENT_TABLE_NAME', $table_prefix . 'crowdio_comments');
		define('CROWDIO_VOTE_TABLE_NAME', $table_prefix . 'crowdio_votes');
	
			add_action( 'init', array( $this, 'add_rfi_post_type' ) );
		
		$lib_path = $plugin_path . 'lib/';		
	}


	public function add_actions() 
	{

	}
	
	 function add_form_css() {
		wp_register_style('cloudio_form_css');
		wp_register_style('cloudio_form_css', $plugin_path . 'style/form.css');
		wp_enqueue_style('cloudio_form_css');
	}

	public function add_rfi_post_type() {
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
			'menu_icon' => CROWDIO_PLUGIN_DIR_URL . 'image/Checkmark.png'
		);
		register_post_type('crowdios', $args);
		remove_post_type_support( 'crowdios', 'comments' );
	}
}
// Plugin initialization:

// Classes:
$crowdio_main = new Crowdio();
$crowdio_db = new CrowdioDatabase();

// Custom post type:
$crowdio_main->add_actions();


// Plugin installation:
// Create new tables if they do not exist:
if(($wpdb->get_var("SHOW TABLES LIKE '$comment_table_name'") != $comment_table_name) ||
	$wpdb->get_var("SHOW TABLES LIKE '$vote_table_name'") != $vote_table_name) {
	$crowdio_db->create_tables();
}