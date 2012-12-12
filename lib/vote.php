<?php
/**
* @Package Crowdio
*/
class CrowdioVote extends Crowdio
{
	function __construct()
	{
		
	}

	function check_vote_submission()
	{
		//global $wpdb;

		//$comment_id = $_POST['comment_id'];

		// Pull the record for the comment being voted on:
		//$wpdb->get_row("SELECT * FROM " . CROWDIO_COMMENTS_TABLE_NAME . " WHERE ID = '$comment_id'");
		// Check for an existing vote on this comment from this user:
		//$wpdb->get_row("SELECT * FROM " . CROWDIO_VOTES_TABLE_NAME . " WHERE comment_id = '$comment_id' ");

		// 
	}

	function save_vote() {
		// write data to SQL $wpdb->insert( $table, $data, $format );
		global $current_user, $wpdb;
		get_currentuserinfo();

			$name = $current_user->display_name;
			$email = $current_user->user_email;
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$user_id = $current_user->ID;
			$session_id = session_id();
			$parent_id = $_POST['crowdio_comment_parent_id'];
			$comment_id = $_GET['comment_id'];
			$rfi_id = $_GET['rfi_id'];
			if ($_GET['crowdio_vote'] == "up") $crowdio_vote_up = "1";
			if ($_GET['crowdio_vote'] == "down") $crowdio_vote_down = "1";


	$existingvote = $wpdb->get_row("SELECT * FROM " . CROWDIO_VOTE_TABLE_NAME . " WHERE user_id = $user_id AND comment_id = '$comment_id'");
	IF (!$existingvote)
	{
			$wpdb->insert(CROWDIO_VOTE_TABLE_NAME, 
			array(
				'user_id' => $user_id,
				'user_ip' => $user_ip,
				'session_id' => $session_id,
				'positive' => $crowdio_vote_up,
				'negative' => $crowdio_vote_down,
				'comment_id' => $comment_id,
				'parent_id' => $parent_id,
				'rfi_id' => $rfi_id
				));
		}
	// check if different vote
		// elseif existing vote = up=1 and current vote = down then update row
		// elseif existing vote = down=1 and current vote = up then update row
		

	}
}