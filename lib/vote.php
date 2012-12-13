<?php
/**
* @Package Crowdio
*/
class CrowdioVote extends Crowdio
{
	function __construct()
	{
		
	}

	function update_vote_totals() {
		parent::debug("Updating vote totals.");
		global $wpdb;

		$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : NULL;

		$new_upvotes_total = $wpdb->get_var("SELECT COUNT(*) FROM " . CROWDIO_VOTE_TABLE_NAME . " WHERE comment_id = '$comment_id' AND positive = '1'");
		$new_downvotes_total = $wpdb->get_var("SELECT COUNT(*) FROM " . CROWDIO_VOTE_TABLE_NAME . " WHERE comment_id = '$comment_id' AND negative = '1'");

		$wpdb->update( 
			CROWDIO_COMMENT_TABLE_NAME, 
			array( 
				'upvotes' => $new_upvotes_total, // Integer
				'downvotes' => $new_downvotes_total // Integer
			), 
			array( 'ID' => $comment_id )
		);
	}


	function add_vote() {
		parent::debug("Adding vote.");
		global $wpdb, $current_user;
		get_currentuserinfo();

			$name = $current_user->display_name;
			$email = $current_user->user_email;
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$user_id = $current_user->ID;
			$session_id = session_id();
			$parent_id = isset($_POST['crowdio_comment_parent_id']) ? $_POST['crowdio_comment_parent_id'] : NULL;
			$comment_id = $_GET['comment_id'];
			$rfi_id = isset($_GET['rfi_id']) ? $_GET['rfi_id'] : NULL;

			$vote_up = "0";
			$vote_down = "0";
			if ($_GET['crowdio_vote'] == "up") $vote_up = "1";
			if ($_GET['crowdio_vote'] == "down") $vote_down = "1";

		$wpdb->insert(CROWDIO_VOTE_TABLE_NAME, 
			array(
				'user_id' => $user_id,
				'user_ip' => $user_ip,
				'session_id' => $session_id,
				'positive' => $vote_up,
				'negative' => $vote_down,
				'comment_id' => $comment_id,
				'parent_id' => $parent_id,
				'rfi_id' => $rfi_id
				));
	}

	function remove_vote($vote_id) {
		parent::debug("Removing vote.");
		global $wpdb;
		$crowdio_vote_table = CROWDIO_VOTE_TABLE_NAME;

		$wpdb->query( 
			$wpdb->prepare("DELETE FROM $crowdio_vote_table WHERE ID = '%s'", $vote_id)
		);
	}

	function handle_vote_submission() {
		parent::debug("Handling vote submission.");
		global $current_user, $wpdb;
		get_currentuserinfo();

		$name = $current_user->display_name;
		$email = $current_user->user_email;
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$user_id = $current_user->ID;
		$session_id = session_id();
		$parent_id = isset($_POST['crowdio_comment_parent_id']) ? $_POST['crowdio_comment_parent_id'] : NULL;
		$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : NULL;
		$rfi_id = isset($_GET['rfi_id']) ? $_GET['rfi_id'] : NULL;

		$the_comment = $wpdb->get_row("SELECT * FROM " . CROWDIO_COMMENT_TABLE_NAME . " WHERE ID = '$comment_id'");
		if (!$the_comment)
		{
			// Trying to vote on nonexisting comment, stopping here.
 			return false;
		}

		$crowdio_vote_up = "0";
		$crowdio_vote_down = "0";
		$crowdio_unvote_up = "0";
		$crowdio_unvote_down = "0";
		if (isset($_GET['crowdio_vote']) && $_GET['crowdio_vote'] == "up") $crowdio_vote_up = "1";
		if (isset($_GET['crowdio_vote']) && $_GET['crowdio_vote'] == "down") $crowdio_vote_down = "1";
		if (isset($_GET['crowdio_unvote']) && $_GET['crowdio_unvote'] == "up") $crowdio_unvote_up = "1";
		if (isset($_GET['crowdio_unvote']) && $_GET['crowdio_unvote'] == "down") $crowdio_unvote_down = "1";

		$existing_upvote = $wpdb->get_row("SELECT * FROM " . CROWDIO_VOTE_TABLE_NAME . " WHERE user_id = '$user_id' AND comment_id = '$comment_id' && positive = '1'");
		$existing_downvote = $wpdb->get_row("SELECT * FROM " . CROWDIO_VOTE_TABLE_NAME . " WHERE user_id = '$user_id' AND comment_id = '$comment_id' && negative = '1'");

		if (!$crowdio_vote_up && !$crowdio_vote_down)
		{	// Processing an unvote:
			if ($crowdio_unvote_up && !empty($existing_upvote))
			{	// Clicked upvote when user already had existing upvote,
				// Let's remove their existing upvote.
				$this->remove_vote($existing_upvote->ID);
			} elseif ($crowdio_unvote_down) {
				// Clicked downvote when user already had existing downvote,
				// let's remove their existing downvote:
				$this->remove_vote($existing_downvote->ID);
			}
		} elseif ($crowdio_vote_up && $existing_downvote)
		{	// User clicked upvote, and they already have a downvote,
			// Let's remove their downvote and make it an upvote.
			$this->remove_vote($existing_downvote->ID);
			$this->add_vote();
		} elseif ($crowdio_vote_down && $existing_upvote)
		{	// User cicked downvote, and they already have an upvote,
			// let's remove their upvote and make it a downvote.
			$this->remove_vote($existing_upvote->ID);
			$this->add_vote();
		} elseif (($crowdio_vote_up || $crowdio_vote_down) && (!$existing_upvote && !$existing_downvote)) {
			// User wants to vote and hasn't voted at all yet...
			// We'll just add their vote in.
			$this->add_vote();
		}
		
		// Update the vote totals stored in the crowdio_comments table.
		$this->update_vote_totals();
		
	}
}