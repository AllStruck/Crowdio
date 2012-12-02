<?php
/*
Plugin Name: Crowdio Comments
Plugin URI: http://crowdio.com/
Description: Get your idea vetted with this WordPress comment engine.
Author: Crowdio <team@crowdio.com>
Version: 1.0
Author URI: http://crowdio.com/
*/

// Constants

function setup() {
	global $wpdb, $table_prefix;
	
	define('CROWDIO_VOTE_TABLE_NAME', 'crowdio_votes');
	define('CROWDIO_COMMENT_TABLE_NAME', '');
}
add_action('init', setup());

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


// Database Table Creation

/* 		--- Comments and Rating Database Schema ---
				== Comments ==
	- User ID - IP - Session - Comment -  Timestamp - 
	
				== Votes ==
	- User ID - IP - Direction - Browser - Session ID - Timestamp - 
*/

	global $table_prefix, $wpdb;
   // caching database query per comment
   $crowdio_cache = array('crowdio_ips'=>"", 'crowdio_comment_id'=>0, 'crowdio_rating_up'=>0, 'crowdio_rating_down'=>0); 
	
	// If our tables do not exist, create them.
	$table_name = $table_prefix . "crowdio_comment_rating";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		crowdio_install();
	}


//Install the needed SQl entries.
function crowdio_install()  {
   global $table_prefix, $wpdb;

   $table_name = $table_prefix . CROWDIO_VOTE_TABLE_NAME;

   // drop the existing table
   $sql = 'DROP TABLE `' . $table_name . '`';
   mysql_query($sql);
   // Add table
   $sql = 'CREATE TABLE `' . $table_name . '` ('
      . ' `crowdio_comment_id` BIGINT(20) NOT NULL, '
      . ' `crowdio_ips` BLOB NOT NULL, '
      . ' `crowdio_rating_up` INT,'
      . ' `crowdio_rating_down` INT'
      . ' )'
      . ' ENGINE = myisam;';
   mysql_query($sql);
   $sql = 'ALTER TABLE `' . $table_name . '` ADD INDEX (`crowdio_comment_id`);';  // add index
   mysql_query($sql);

   echo "crowdio_comment_rating tables created";
       
   $crowdio_result = mysql_query('SELECT comment_ID FROM ' . $table_prefix . 'comments'); //Put all IDs in our new table
   while($crowdio_row = mysql_fetch_array($crowdio_result, MYSQL_ASSOC)) //Wee loop
   {
      mysql_query("INSERT INTO $table_name (crowdio_comment_id, crowdio_ips, crowdio_rating_up, crowdio_rating_down) VALUES ('" . $crowdio_row['comment_ID'] . "', '', 0, 0)");
   }
}


add_action('comment_post', 'crowdio_comment_posted');
add_action('admin_menu', 'crowdio_options_page');
add_action('wp_head', 'crowdio_add_highlight_style');
// late enough to avoid most conflicts, early enough to avoid conflicting
// with WP Threaded Comment
add_filter('comment_text', 'crowdio_display_filter', 9000); 
add_filter('comment_class', 'crowdio_comment_class', 10 , 4 );
add_action('init', 'crowdio_add_javascript');  // add javascript in the footer


   // Use the last new option added.  Reset all option to defaults
   // for all upgrades.
   if (!get_option('crowdio_style_comment_box')) crowdio_reset_default();

// Add settings page and listen for responses:

function crowdio_options_page(){
   add_options_page('Comment Settings', 'Crowdio', 8, 'crowdioRating', 'crowdio_show_options_page');
}

function crowdio_show_options_page() {
	global $table_prefix, $wpdb;
   if ($_POST[ 'crowdio_hidden' ] == 'Y') {
      if (isset($_POST['Reset'])) {
         crowdio_reset_default();
		   echo '<div id="message" class="updated fade"><p><strong>Comment Rating Options are set to default.</strong></p></div>';
      }
      else {
         update_option('crowdio_auto_insert', $_POST['crowdio_auto_insert']);
         update_option('crowdio_inline_style_off', $_POST['crowdio_inline_style_off']);
         update_option('crowdio_javascript_off', $_POST['crowdio_javascript_off']);
         update_option('crowdio_position', $_POST['crowdio_position']);
         update_option('crowdio_words', urldecode($_POST['crowdio_words']));
         update_option('crowdio_words_good', urldecode($_POST['crowdio_words_good']));
         update_option('crowdio_words_poor', urldecode($_POST['crowdio_words_poor']));
         update_option('crowdio_words_debated', urldecode($_POST['crowdio_words_debated']));
         update_option('crowdio_goodRate', $_POST['crowdio_goodRate']);
         update_option('crowdio_styleComment', urldecode($_POST['crowdio_styleComment']));
         update_option('crowdio_negative', $_POST['crowdio_negative']); 
         update_option('crowdio_hide_style', urldecode($_POST['crowdio_hide_style']));
         update_option('crowdio_admin_off', $_POST['crowdio_admin_off']);
         update_option('crowdio_style_comment_box', $_POST['crowdio_style_comment_box']);
         update_option('crowdio_value_display', $_POST['crowdio_value_display']);
         update_option('crowdio_likes_style', urldecode($_POST['crowdio_likes_style']));
         update_option('crowdio_dislikes_style', urldecode($_POST['crowdio_dislikes_style']));
         update_option('crowdio_image_index', $_POST['crowdio_image_index']);
         update_option('crowdio_image_size', $_POST['crowdio_image_size']);
//EP-12-31-2009 Added options for ToolTip text
         update_option('crowdio_up_alt_text', $_POST['crowdio_up_alt_text']);
         update_option('crowdio_down_alt_text', $_POST['crowdio_down_alt_text']);
//EP-12-31-2009 End of added options
         update_option('crowdio_style_debated', urldecode($_POST['crowdio_style_debated']));
         update_option('crowdio_debated', $_POST['crowdio_debated']);
         update_option('crowdio_mouseover', $_POST['crowdio_mouseover']);
         update_option('crowdio_vote_type', $_POST['crowdio_vote_type']);

         // Update comment_karma if the karma_type changes.
         if (get_option('crowdio_karma_type') != $_POST['crowdio_karma_type']) {
            update_option('crowdio_karma_type', $_POST['crowdio_karma_type']);
            $crowdio_result = mysql_query('SELECT crowdio_comment_id, crowdio_rating_up, crowdio_rating_down FROM ' . $table_prefix . 'crowdio_comment_rating'); 
            $comment_table_name = $table_prefix . 'comments';
            if(!$crowdio_result) { mysql_error(); }

            while($crowdio_row = mysql_fetch_array($crowdio_result, MYSQL_ASSOC)) //Wee loop
            {
               if (get_option('crowdio_karma_type') == 'likes') { $karma = $crowdio_row['crowdio_rating_up']; }
               else if (get_option('crowdio_karma_type') == 'dislikes') { $karma = $crowdio_row['crowdio_rating_down']; }
               else { $karma = $crowdio_row['crowdio_rating_up'] - $crowdio_row['crowdio_rating_down']; }
               $query = "UPDATE `$comment_table_name` SET comment_karma = '$karma' WHERE comment_ID = '" .  $crowdio_row['crowdio_comment_id'] . "'";
               $result = mysql_query($query); 
            }
         }
         echo '<div id="message" class="updated fade"><p><strong>Comment Rating Options updated.</strong></p></div>';
      }
   }
?>
   <div class="wrap">
   <div id="icon-options-general" class="icon32">
   <br/>
   </div>
   <h2>Crowdio Options</h2>
<?php 
   if (0 == get_option('crowdio_show_thankyou') % 4)
      print('
         <div style="width: 75%; background-color: yellow;">
         <em><b> Thank you for choosing Crowdio.  If you like the
         plugin, please help promoting its use. You can rate it at
         <a href="http://wordpress.org/extend/plugins/crowdio-comments/">WordPress.org Plugins</a>.
         </b>
         </em>
         </div>
         ');
   update_option('crowdio_show_thankyou', get_option('crowdio_show_thankyou')+1);
   
   include(plugin_dir_path(__FILE__) .'crowdio-settings.php');
}

// set the default values to options
function crowdio_reset_default() {
   update_option('crowdio_auto_insert', 'yes');
   update_option('crowdio_inline_style_off', 'no');
   update_option('crowdio_javascript_off', 'no');
   update_option('crowdio_position', 'below');
   update_option('crowdio_words', 'Like or Dislike:');
   update_option('crowdio_words_good', 'Well-loved. Like or Dislike:');
   update_option('crowdio_words_poor', 'Poorly-rated. Like or Dislike:');
   update_option('crowdio_words_debated', 'Hot debate. What do you think?');
   update_option('crowdio_negative', 3); 
   update_option('crowdio_goodRate', 4); 
   update_option('crowdio_debated', 8); 
   update_option('crowdio_styleComment', 'background-color:#FFFFCC !important');
   update_option('crowdio_hide_style', 'opacity:0.6;filter:alpha(opacity=60) !important');
   update_option('crowdio_style_debated', 'background-color:#FFF0F5 !important');
   update_option('crowdio_admin_off', 'no');
   update_option('crowdio_style_comment_box', 'yes');
   update_option('crowdio_value_display', 'two');
   update_option('crowdio_likes_style', 'font-size:12px; color:#009933');
   update_option('crowdio_dislikes_style', 'font-size:12px; color:#990033');
   update_option('crowdio_image_index', 1);
   update_option('crowdio_image_size', 14);
//EP-12-31-2009 Added options for ToolTip text.  Note, to BoB, should all the default strings be localized?
   update_option('crowdio_up_alt_text', __('Vote up', 'crowdioRating'));
   update_option('crowdio_down_alt_text', __('Vote down', 'crowdioRating'));
//EP-12-31-2009 End of added options
   update_option('crowdio_mouseover', 2);
   update_option('crowdio_vote_type', 'both');
   update_option('crowdio_karma_type', 'both');
}

