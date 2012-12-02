<?php


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