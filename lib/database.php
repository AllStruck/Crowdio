<?php
/**
* @Package:	Crowdio
*/
class CrowdioDatabase extends Crowdio
{
	
	function __construct()
	{
		global $wpdb, $table_prefix;
	}


	function create_tables() {
		global $wpdb, $table_prefix;
		$crowdio_comment_table_name = CROWDIO_COMMENT_TABLE_NAME;
		$crowdio_vote_table_name = CROWDIO_VOTE_TABLE_NAME;
		
		$comment_table_create_query = "
			CREATE TABLE IF NOT EXISTS $crowdio_comment_table_name (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
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
				PRIMARY KEY (id)
				)
			ENGINE = myisam DEFAULT CHARACTER SET = utf8;";
		
		$vote_table_create_query = "
			CREATE TABLE IF NOT EXISTS $crowdio_vote_table_name (
				id BIGINT(20) NOT NULL,
				user_id BIGINT(20),
				user_ip VARCHAR(100),
				session_id VARCHAR(250),
				positive INT(2),
				negative INT(2),
				comment_id BIGINT(20),
				parent_id BIGINT(20) )
			ENGINE = myisam DEFAULT CHARACTER SET = utf8";
		
		$wpdb->query($comment_table_create_query);
		$wpdb->query($vote_table_create_query);
	}

	function insert_comment($name, 
							$email, 
							$comment_text, 
							$user_ip, 
							$user_ID, 
							$user_url, 
							$session_id, 
							$rfi_id,
							$parent_id) {
		// write data to SQL $wpdb->insert( $table, $data, $format );
		$wpdb->insert(CROWDIO_COMMENT_TABLE_NAME,
			array(
				'name' => $name,
				'email' => $email,
			    'comment_text' => $comment_text,
			    'user_ip' => $user_ip,
			    'user_id' => $user_ID,
			    'user_url' => $user_url,
			    'session_id' => $session_id,
			    'rfi_id' => $rfi_id,
			    'parent_id' => $parent_id
			    )
			);
	}

	function get_ranked_votes($type)
	{
		switch ($type) {	
			case 'comment':
				$comment_id = "comment_id";
				$table = "$crowdio_vote_table_name";
				break;

			case 'reply':
				$comment_id = "comment_id , parent_id";
				$table = "$crowdio_vote_table_name";
				break;
		}

		$ranking_query = "
				SELECT '$comment_id', 
					(
						(positive + 1.9208) / 
						(pos + negative) - 
						1.96 * SQRT(
							(positive * negative) / 
							(positive + negative) + 0.9604) / 
				   			(positive + negative)
					) /
					(1 + 3.8416 / (positive + negative)) 
					AS ci_lower_bound 
					FROM '$table' 
					WHERE positive + negative > 0 
					ORDER BY ci_lower_bound DESC;";

		return $wpdb->get_result($ranking_query);
	}
}

