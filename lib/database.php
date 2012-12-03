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


