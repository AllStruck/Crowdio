<?php

/* 		--- Comments and Rating Database Schema ---
				== Comments ==
	- User ID - IP - Session - Comment -  Timestamp - 
	
				== Votes ==
	- User ID - IP - Direction - Browser - Session ID - Timestamp - 
*/

add_action('comment_post', 'crowdio_comment_posted');
add_action('admin_menu', 'crowdio_options_page');
add_action('wp_head', 'crowdio_add_highlight_style');
// late enough to avoid most conflicts, early enough to avoid conflicting
// with WP Threaded Comment
add_filter('comment_text', 'crowdio_display_filter', 9000); 
add_filter('comment_class', 'crowdio_comment_class', 10 , 4 );
add_action('init', 'crowdio_add_javascript');  // add javascript in the footer


	global $table_prefix, $wpdb;
   // caching database query per comment
   $crowdio_cache = array('crowdio_ips'=>"", 'crowdio_comment_id'=>0, 'crowdio_rating_up'=>0, 'crowdio_rating_down'=>0); 
		
	$table_name = $table_prefix . "crowdio_comment_rating";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		crowdio_install();
	}
   // Use the last new option added.  Reset all option to defaults
   // for all upgrades.
   if (!get_option('crowdio_style_comment_box')) crowdio_reset_default();

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

function crowdio_install() //Install the needed SQl entries.
{
   global $table_prefix, $wpdb;

   $table_name = $table_prefix . "crowdio_comment_rating";

   $sql = 'DROP TABLE `' . $table_name . '`';  // drop the existing table
   mysql_query($sql);
   $sql = 'CREATE TABLE `' . $table_name . '` (' //Add table
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

function crowdio_comment_posted($crowdio_comment_id) //When comment posted this executes
{
   global $table_prefix, $wpdb;
   $ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
   $table_name = $table_prefix . "crowdio_comment_rating";
   mysql_query("INSERT INTO $table_name (crowdio_comment_id, crowdio_ips, crowdio_rating_up, crowdio_rating_down) VALUES ('" . $crowdio_comment_id . "', '" . $ip . "', 0, 0)"); //Adds the new comment ID into our made table, with the users IP
}

// cache DB results to prevent multiple access to DB
function crowdio_get_rating($comment_id)
{
   global $crowdio_cache, $table_prefix, $wpdb;

   // return it if the value is in the cache
   if ($comment_id == $crowdio_cache['crowdio_comment_id']) return;

   $table_name = $table_prefix . "crowdio_comment_rating";
   $crowdio_sql = "SELECT crowdio_ips, crowdio_rating_up, crowdio_rating_down FROM `$table_name` WHERE crowdio_comment_id = $comment_id";
   $crowdio_result = mysql_query($crowdio_sql);
   
   $crowdio_cache['crowdio_comment_id'] = $comment_id;
   if(!$crowdio_result) { 
      $crowdio_cache['crowdio_ips'] = '';
      $crowdio_cache['crowdio_rating_up'] = 0;
      $crowdio_cache['crowdio_rating_down'] = 0;
      mysql_query("INSERT INTO $table_name (crowdio_comment_id, crowdio_ips, crowdio_rating_up, crowdio_rating_down) VALUES ('" . $comment_id . "', '', 0, 0)");
   }
   else if(!$crowdio_row = mysql_fetch_array($crowdio_result, MYSQL_ASSOC)) {
      $crowdio_cache['crowdio_ips'] = '';
      $crowdio_cache['crowdio_rating_up'] = 0;
      $crowdio_cache['crowdio_rating_down'] = 0;
      mysql_query("INSERT INTO $table_name (crowdio_comment_id, crowdio_ips, crowdio_rating_up, crowdio_rating_down) VALUES ('" . $comment_id . "', '', 0, 0)");
   }
   else {
      $crowdio_cache['crowdio_ips'] = $crowdio_row['crowdio_ips'];
      $crowdio_cache['crowdio_rating_up'] = $crowdio_row['crowdio_rating_up'];
      $crowdio_cache['crowdio_rating_down'] = $crowdio_row['crowdio_rating_down'];
   }
}

// Display images and rating in comments
function crowdio_display_content()
{
   global $crowdio_cache;
   $plugin_path = get_bloginfo('wpurl').'/wp-content/plugins/crowdio-comments';
   $crowdio_link = str_replace('http://', '', get_bloginfo('wpurl'));
   $crowdio_comment_ID = get_comment_ID();
   $content = '';
   crowdio_get_rating($crowdio_comment_ID);

   $imgIndex = get_option('crowdio_image_index') . '_' . get_option('crowdio_image_size') . '_';
   $ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
   if(strstr($crowdio_cache['crowdio_ips'], $ip)) {
      $imgUp = $imgIndex . "gray_up.png";
      $imgDown = $imgIndex . "gray_down.png";
      $imgStyle = 'style="padding: 0px; margin: 0px; border: none;"';
      $onclicrowdio_add = '';
      $onclicrowdio_sub = '';
   }
   else {
      $imgUp = $imgIndex . "up.png";
      $imgDown = $imgIndex . "down.png";
      if (get_option('crowdio_mouseover') == 1)
         // no effect
         $imgStyle = 'style="padding: 0px; border: none; cursor: pointer;"';
      else
         // enlarge
         $imgStyle = 'style="padding: 0px; border: none; cursor: pointer;" onmouseover="this.width=this.width*1.3" onmouseout="this.width=this.width/1.2"';
//      $onclicrowdio_add = "onclick=\"javascript:crowdioRatingKarma('$crowdio_comment_ID', 'add', '{$crowdio_link}/wp-content/plugins/crowdio-comments/', '$imgIndex');\" title=\"". __('Vote up','crowdioRating'). "\"";
//      $onclicrowdio_sub = "onclick=\"javascript:crowdioRatingKarma('$crowdio_comment_ID', 'subtract', '{$crowdio_link}/wp-content/plugins/crowdio-comments/', '$imgIndex')\" title=\"". __('Vote down', 'crowdioRating') ."\"";
//EP-12-31-2009 Replaced two lines above with line below for Tooltip Text option.  I think __() is the localization. We shouldn't need that for these strings now. 
      $onclicrowdio_add = "onclick=\"javascript:crowdioRatingKarma('$crowdio_comment_ID', 'add', '{$crowdio_link}/wp-content/plugins/crowdio-comments/', '$imgIndex');\" title=\"". get_option('crowdio_up_alt_text')."\"";
      $onclicrowdio_sub = "onclick=\"javascript:crowdioRatingKarma('$crowdio_comment_ID', 'subtract', '{$crowdio_link}/wp-content/plugins/crowdio-comments/', '$imgIndex')\" title=\"".get_option('crowdio_down_alt_text')."\"";
   }

   $total = $crowdio_cache['crowdio_rating_up'] - $crowdio_cache['crowdio_rating_down'];
   if ($total > 0) $total = "+$total";
   //Use onClick for the image instead, fixes the style link underline problem as well.
   if ( ((int)$crowdio_cache['crowdio_rating_up'] - (int)$crowdio_cache['crowdio_rating_down'])
           >= (int)get_option('crowdio_goodRate')) {
      $content .= get_option('crowdio_words_good');
   }
   else if ( ((int)$crowdio_cache['crowdio_rating_down'] - (int)$crowdio_cache['crowdio_rating_up'])
            >= (int)get_option('crowdio_negative')) {
      $content .= get_option('crowdio_words_poor');
   }
   else if ( ((int)$crowdio_cache['crowdio_rating_down'] + (int)$crowdio_cache['crowdio_rating_up'])
            >= (int)get_option('crowdio_debated')) {
      $content .= get_option('crowdio_words_debated');
   }
   else
      $content .= get_option('crowdio_words');

   $likesStyle = 'style="' . get_option('crowdio_likes_style') .  ';"';
   $dislikesStyle = 'style="' . get_option('crowdio_dislikes_style') .  ';"';
   // apply crowdio_vote_type
   if ( get_option('crowdio_vote_type') != 'dislikes' )
   {
      $content .= " <span $imgStyle id=\"up-$crowdio_comment_ID\" src=\"{$plugin_path}/images/$imgUp\" alt=\"".__('Vote up', 'crowdioRating') ."\" $onclicrowdio_add>&and;</span>";
      if ( get_option('crowdio_value_display') != 'one' )
         $content .= " <span id=\"karma-{$crowdio_comment_ID}-up\" $likesStyle>{$crowdio_cache['crowdio_rating_up']}</span>";
   }
   if ( get_option('crowdio_vote_type') != 'likes' )
   {
      $content .= " <span $imgStyle id=\"down-$crowdio_comment_ID\" src=\"{$plugin_path}/images/$imgDown\" alt=\"". __('Vote down', 'crowdioRating')."\" $onclicrowdio_sub>&or;</span>"; //Phew
      if ( get_option('crowdio_value_display') != 'one' )
         $content .= " <span id=\"karma-{$crowdio_comment_ID}-down\" $dislikesStyle>{$crowdio_cache['crowdio_rating_down']}</span>";
   }

   $totalStyle = '';
   if ($total > 0) $totalStyle = $likesStyle;
   else if ($total < 0) $totalStyle = $dislikesStyle;
   if ( get_option('crowdio_value_display') == 'one' )
      $content .= " <span id=\"karma-{$crowdio_comment_ID}-total\" $totalStyle>{$total}</span>";
   if ( get_option('crowdio_value_display') == 'three' )
      $content .= " (<span id=\"karma-{$crowdio_comment_ID}-total\" $totalStyle>{$total}</span>)";

   return array($content, $crowdio_cache['crowdio_rating_up'], $crowdio_cache['crowdio_rating_down']);
}

// Display images and rating for widget on sidebar
function crowdio_display_sidebar($crowdio_comment_ID)
{
   global $crowdio_cache;
   $plugin_path = get_bloginfo('wpurl').'/wp-content/plugins/crowdio-comments';
   $crowdio_link = str_replace('http://', '', get_bloginfo('wpurl'));
   $content = '';
   crowdio_get_rating($crowdio_comment_ID);

   $imgIndex = get_option('crowdio_image_index') . '_' . get_option('crowdio_image_size') . '_';
   $imgUp = $imgIndex . "up.png";
   $imgDown = $imgIndex . "down.png";
   $imgStyle = 'style="padding: 0px; border: none;"';
   $onclicrowdio_add = '';
   $onclicrowdio_sub = '';

   $total = $crowdio_cache['crowdio_rating_up'] - $crowdio_cache['crowdio_rating_down'];
   if ($total > 0) $total = "+$total";
   //Use onClick for the image instead, fixes the style link underline problem as well.

   $likesStyle = 'style="' . get_option('crowdio_likes_style') .  ';"';
   $dislikesStyle = 'style="' . get_option('crowdio_dislikes_style') .  ';"';
   // Use crowdio_karma_type to determine the image shape
   if ( get_option('crowdio_karma_type') != 'dislikes' )
   {
      $content .= "&nbsp;<img $imgStyle src=\"{$plugin_path}/images/$imgUp\" alt=\"".__('Vote up', 'crowdioRating') ."\" $onclicrowdio_add />";
      if ( get_option('crowdio_value_display') != 'one' )
         $content .= "&nbsp;<span $likesStyle>{$crowdio_cache['crowdio_rating_up']}</span>";
   }
   if ( get_option('crowdio_karma_type') != 'likes' )
   {
      $content .= "&nbsp;<img $imgStyle src=\"{$plugin_path}/images/$imgDown\" alt=\"". __('Vote down', 'crowdioRating')."\" $onclicrowdio_sub />"; //Phew
      if ( get_option('crowdio_value_display') != 'one' )
         $content .= "&nbsp;<span $dislikesStyle>{$crowdio_cache['crowdio_rating_down']}</span>";
   }

   $totalStyle = '';
   if ($total > 0) $totalStyle = $likesStyle;
   else if ($total < 0) $totalStyle = $dislikesStyle;
   if ( get_option('crowdio_value_display') == 'one' )
      $content .= "&nbsp;<span id=\"karma-{$crowdio_comment_ID}-total\" $totalStyle>{$total}</span>";
   if ( get_option('crowdio_value_display') == 'three' )
      $content .= "&nbsp;(<span id=\"karma-{$crowdio_comment_ID}-total\" $totalStyle>{$total}</span>)";

   return $content;
}

function crowdio_display_filter($text)
{
   $crowdio_comment_ID = get_comment_ID();
   $crowdio_comment = get_comment($crowdio_comment_ID); 
   $crowdio_comment_author = $crowdio_comment->comment_author;
   $crowdio_author_name = get_the_author();
   
   if (get_option('crowdio_admin_off') == 'yes' && 
       ($crowdio_author_name == $crowdio_comment_author || $crowdio_comment_author == 'admin')
      )
      return $text;

   $arr = crowdio_display_content();

   // $content is the modifed comment text.
   $content = $text;

   if (((int)$arr[1] - (int)$arr[2]) >= (int)get_option('crowdio_goodRate')) {
      $content = '<div style="' . get_option('crowdio_styleComment') . '">' .
               $text .  '</div>';
   }
   else if ( ((int)$arr[2] - (int)$arr[1])>= (int)get_option('crowdio_negative') &&
             ! ($crowdio_author_name == $crowdio_comment_author || $crowdio_comment_author == 'admin')
           )
   {
      $content = '<p>'.__('Hidden due to','crowdioRating').' '.__('low','crowdioRating');
      if ( (get_option('crowdio_inline_style_off') == 'yes') &&
           (get_option('crowdio_javascript_off') == 'yes')) {
         $content .= ' '. __('comment rating','crowdioRating');
      } else {
         $content .= ' <a href="http://wealthynetizen.com/wordpress-plugin-comment-rating/" title="'
               .__('Rated by other readers', 'crowdioRating').'">'
               .__('comment rating','crowdioRating').'</a>.';
      }
      $content .= " <a href=\"javascript:crSwitchDisplay('ckhide-$crowdio_comment_ID');\" title=\"".__('Click to see comment','crowdioRating')."\">".__('Click here to see', 'crowdioRating')."</a>.</p>" .
              "<div id='ckhide-$crowdio_comment_ID' style=\"display:none; ".get_option('crowdio_hide_style').';">' .
              $text .
              "</div>";
   }
   else if (((int)$arr[1] + (int)$arr[2]) >= (int)get_option('crowdio_debated')) {
      $content = '<div style="' . get_option('crowdio_style_debated') . '">' .
               $text .  '</div>';
   }

   // No auto insertion of images and ratings
   if (get_option('crowdio_auto_insert') != 'yes')
      return $content;

   // Add the images and ratings
   if (get_option('crowdio_position') == 'below')
      return $content. '<p>' . $arr[0] . '</p>';
   else
      return '<p>' . $arr[0] . '</p>' . $content;
}

function crowdio_display_karma()
{
   $arr = crowdio_display_content();
   print $arr[0];
}

function crowdio_add_javascript() {
   if (get_option('crowdio_javascript_off') == 'yes') return;

   wp_enqueue_script('crowdio', plugins_url('crowdio-comments/comment-rating.js'), array(), false, true);
}

function crowdio_add_highlight_style() {
   if (get_option('crowdio_inline_style_off') == 'yes') return;

   echo '
<!-- Comment Rating plugin Version: '.COMMENTRATING_VERSION. ' by Bob King, http://wealthynetizen.com/, dynamic comment voting & styling. --> 
<style type="text/css" media="screen">
   .crowdio_highly_rated {'. get_option('crowdio_styleComment') . ';}
   .crowdio_poorly_rated {'. get_option('crowdio_hide_style') . ';}
   .crowdio_hotly_debated {'. get_option('crowdio_style_debated') . ';}
</style>

';
}

function crowdio_comment_class (  $classes, $class, $comment_id, $page_id) {
   // Don't style the comment box
   if (get_option('crowdio_style_comment_box') == 'no') return $classes;

   global $crowdio_cache;
   //get the comment object, in case $comment_id is not passed.
   $crowdio_comment_ID = get_comment_ID();
   crowdio_get_rating($crowdio_comment_ID);
   
   if ( ((int)$crowdio_cache['crowdio_rating_up'] - (int)$crowdio_cache['crowdio_rating_down'])
              >= (int)get_option('crowdio_goodRate')) {
      //add comment highlighting class
      $classes[] = "crowdio_highly_rated";
   }
   else if ( ((int)$crowdio_cache['crowdio_rating_down'] - (int)$crowdio_cache['crowdio_rating_up'])
            >= (int)get_option('crowdio_negative')) {
      //add hiding comment class
      $classes[] = "crowdio_poorly_rated";
   }
   else if ( ((int)$crowdio_cache['crowdio_rating_down'] + (int)$crowdio_cache['crowdio_rating_up'])
            >= (int)get_option('crowdio_debated')) {
      $classes[] = "crowdio_hotly_debated";
   }
    
   //send the array back
   return $classes;
}