<?php
/**
*/
class CrowdioDatabase extends Crowdio
{
	
	function __construct()
	{
		global $wpdb, $table_prefix;
		$crowdio_vote_table_name = $table_prefix . "_crowdio_vote";
		$crowdio_comment_table_name = $table_prefix . "_crowdio_comment";
	}

	function create_tables() {
		$vote_table_create_query = "
			CREATE TABLE $crowdio_vote_table_name (
				id BIGINT(20) NOT NULL,
				user_id,
				user_ip,
				session_id,
				vote_direction,

				)
		"
		$comment_table_create_query = "
			CREATE TABLE $crowdio_comment_table_name (
				id BIGINT(20) NOT NULL, 
				user_id,
				user_ip,
				session_id,
				comment_text,
				timestamp)
			ENGINE = myisam;";
	}

	   $sql = 'CREATE TABLE `' . $table_name . '` ('
      . ' `crowdio_comment_id` BIGINT(20) NOT NULL, '
      . ' `crowdio_ips` BLOB NOT NULL, '
      . ' `crowdio_rating_up` INT,'
      . ' `crowdio_rating_down` INT'
      . ' )'
      . ' ENGINE = myisam;';


	function get_ranked_votes($type)
	{
		switch ($type) {
			case 'question':
				$comment_id = "";
				$table = "";
				break;
			
			case 'comment':
				$comment_id = "";
				$table = "";
				break;

			case 'reply':
				$comment_id = "";
				$table = "";
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