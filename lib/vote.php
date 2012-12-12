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
		global $wpdb;

		$comment_id = $_POST[''];

		// Pull the record for the comment being voted on:
		$wpdb->get_row("SELECT * FROM " . CROWDIO_COMMENTS_TABLE_NAME . " WHERE ID = '$comment_id'");
		// Check for an existing vote on this comment from this user:
		$wpdb->get_row("SELECT * FROM " . CROWDIO_VOTES_TABLE_NAME . " WHERE comment_id = '$comment_id' ");

		// 
	}

	function save_vote() {
		
	}
}