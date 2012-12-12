<?php
/**
* @Package:	Crowdio
*/
class CrowdioDatabase extends Crowdio
{
	
	function __construct()
	{

	}


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
			    'parent_id' => $parent_id
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
					'rfi_id' => $rfi_id
					)
			);
			if ($wpdb->insert_id) {
				return True;
			} else
			{
				print 'Comment was inserted but default upvote was not.';
			}
		} else
		{
			$wpdb->show_errors();
			print 'Error with $wpdb->insert(): '; $wpdb->print_error();
		}

	
	}

	function get_ranked_votes($type, $rfi_id)
	{
		global $wpdb;

		switch ($type) {
			case 'comment':
				$comment_id_field = "comment_id";
				$table = CROWDIO_VOTE_TABLE_NAME;
				break;

			case 'reply':
				$comment_id_field = "comment_id , parent_id";
				$table = CROWDIO_VOTE_TABLE_NAME;
				break;
		}

		$ranking_query = "SELECT DISTINCT $comment_id_field, 
					(
						(positive + 1.9208) / 
						(positive + negative) - 
						1.96 * SQRT(
							(positive * negative) / 
							(positive + negative) + 0.9604) / 
				   			(positive + negative)
					) /
					(1 + 3.8416 / (positive + negative)) 
					AS ci_lower_bound 
					FROM $table 
					WHERE positive + negative > 0 
					AND rfi_id = '$rfi_id' 
					ORDER BY ci_lower_bound DESC;";

		$results = $wpdb->get_results($ranking_query, 'ARRAY_A');

		//usort($results, array($this, 'sort_ranked_votes'));

		//return $results;

		$score = array();
		foreach ($results as $key => $row)
		{
		    $score[$key] = $row['ci_lower_bound'];
		}
		array_multisort($score, SORT_DESC, $results);

		return $results;

	}
	function sort_ranked_votes($a, $b) {
		if ($a->value == $b->value) {
			return 0;
		} else {
			return $a->value < $b->value ? 1 : -1;
		}
	}
}

