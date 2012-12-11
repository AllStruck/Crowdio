<?php

/**
* @Package Crowdio
*/
class Crowdio
{

	public function __construct()
	{
		global $wpdb, $table_prefix;
		session_start();

		define('CROWDIO_PLUGIN_DIR_PATH', plugin_dir_path(CROWDIO_MAIN_PLUGIN_FILE));
		define('CROWDIO_PLUGIN_DIR_URL', plugin_dir_url(CROWDIO_MAIN_PLUGIN_FILE));
		define('CROWDIO_COMMENT_TABLE_NAME', $table_prefix . 'crowdio_comments');
		define('CROWDIO_VOTE_TABLE_NAME', $table_prefix . 'crowdio_votes ');
	
		add_action( 'init', array( $this, 'add_rfi_post_type' ) );
		
		$plugin_path = CROWDIO_PLUGIN_DIR_PATH;
		$plugin_dir_url = CROWDIO_PLUGIN_DIR_URL;
		$comment_table_name = CROWDIO_COMMENT_TABLE_NAME;
		$vote_table_name = CROWDIO_VOTE_TABLE_NAME;
		
		$lib_path = $plugin_path . 'lib/';
		require_once($lib_path . 'database.php');
		require_once($lib_path . 'comment.php');
		require_once($lib_path . 'vote.php');

		$crowdio_db = new CrowdioDatabase();
		register_activation_hook(__FILE__, $crowdio_db->create_tables());
		
		$crowdio_comment = new CrowdioComment();
		add_filter('comments_template', array($crowdio_comment, 'modify_page_content'));

		if (!empty($_POST['submit'])) {
			$crowdio_comment->check_submission();
		}
		
		add_action('init', array($this, 'add_form_css'));
	}

	
	function add_form_css() {
		wp_deregister_style('cloudio_form_css');
		wp_register_style('cloudio_form_css', CROWDIO_PLUGIN_DIR_URL . 'style/form.css');
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

$crowdio_main = new Crowdio();
