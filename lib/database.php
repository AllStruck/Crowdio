<?php
/**
* @Package:	Crowdio
*/
class CrowdioDatabase extends Crowdio
{
	function __construct() {
		
	}

	// Create custom tables for storing votes and comments:
	function create_tables() {
		global $wpdb, $table_prefix;
		$crowdio_comment_table_name = CROWDIO_COMMENT_TABLE_NAME;
		$crowdio_vote_table_name = CROWDIO_VOTE_TABLE_NAME;
		
		$comment_table_create_query = "
			CREATE TABLE IF NOT EXISTS $crowdio_comment_table_name (
				ID BIGINT(20) NOT NULL AUTO_INCREMENT,
				created_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				user_id BIGINT(20),
				user_ip VARCHAR(100),
				name VARCHAR(250),
				email VARCHAR(100),
				user_url VARCHAR(100),
				organization VARCHAR(250),
				session_id VARCHAR(250),
				comment_text TEXT,
				parent_id BIGINT(20),
				rfi_id BIGINT(20),
				upvotes INT(20),
				downvotes INT(20),
				PRIMARY KEY (ID)
				)
			ENGINE = myisam DEFAULT CHARACTER SET = utf8;";
		
		$vote_table_create_query = "
			CREATE TABLE IF NOT EXISTS $crowdio_vote_table_name (
				ID BIGINT(20) NOT NULL AUTO_INCREMENT,
				vote_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				user_id BIGINT(20),
				user_ip VARCHAR(100),
				session_id VARCHAR(250),
				positive INT(2),
				negative INT(2),
				comment_id BIGINT(20),
				parent_id BIGINT(20),
				rfi_id BIGINT(20), 
				PRIMARY KEY (ID) )
			ENGINE = myisam DEFAULT CHARACTER SET = utf8";
		
		$wpdb->query($comment_table_create_query);
		$wpdb->query($vote_table_create_query);
	}

	// Add comment to database:
	function insert_comment($name, 
							$email, 
							$comment_text, 
							$user_ip, 
							$user_id, 
							$user_url, 
							$session_id, 
							$rfi_id,
							$parent_id) {
		global $wpdb;

		$parent_id = empty($parent_id) ? NULL : $parent_id;

		// write data to SQL $wpdb->insert( $table, $data, $format );
		$wpdb->insert(CROWDIO_COMMENT_TABLE_NAME,
			array(
				'name' => $name,
				'email' => $email,
			    'comment_text' => $comment_text,
			    'user_ip' => $user_ip,
			    'user_id' => $user_id,
			    'user_url' => $user_url,
			    'session_id' => $session_id,
			    'rfi_id' => $rfi_id,
			    'parent_id' => $parent_id,
			    'upvotes' => '1',
			    'downvotes' => '0'
			    )
			);
		if ($wpdb->insert_id)
		{
			// Add one vote up for new comment:
			$wpdb->insert(CROWDIO_VOTE_TABLE_NAME,
				array(
					'comment_id' => $wpdb->insert_id,
					'positive' => '1',
					'negative' => '0',
					'rfi_id' => $rfi_id,
					'user_id' => $user_id
					)
			);
			if ($wpdb->insert_id) {
				return True;
			} else
			{
				print 'Comment was inserted but default upvote was not.'; $wpdb->print_error();
			}
		} else
		{
			$wpdb->show_errors();
			print 'Error with $wpdb->insert(): '; $wpdb->print_error();
		}

	
	}

	// Pull comments using ranking algorithm.
	function get_ranked_votes($type, $rfi_id, $parent_id='0')
	{
		global $wpdb;

		switch ($type) {
			case 'comment':
				$comment_id_field = "ID";
				$table = CROWDIO_COMMENT_TABLE_NAME;
				break;

			case 'reply':
				$comment_id_field = "comment_id , parent_id";
				$table = CROWDIO_COMMENT_TABLE_NAME;
				break;
		}

			$rfi_id = $GLOBALS['post']->ID;

		$ranking_query = "SELECT $comment_id_field, 
					upvotes, downvotes, created_timestamp, comment_text, user_id, 
					(
						(upvotes + 1.9208) / 
						(upvotes + downvotes) - 
						1.96 * SQRT(
							(upvotes * downvotes) / 
							(upvotes + downvotes) + 0.9604) / 
				   			(upvotes + downvotes)
					) /
					(1 + 3.8416 / (upvotes + downvotes)) 
					AS ci_lower_bound 
					FROM $table 
					WHERE upvotes + downvotes > -1 
					AND rfi_id = '$rfi_id' 
					AND parent_id = $parent_id 
					ORDER BY ci_lower_bound DESC;";

		$results = $wpdb->get_results($ranking_query);

		//usort($results, array($this, 'sort_ranked_votes'));

		//return $results;

		return $results;

	}

}