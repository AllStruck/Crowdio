<?php

global $wpdb, $table_prefix;
$crowdioVoteTableName = $table_prefix . "_crowdio_vote";
$crowdioCommentTableName = $table_prefix . "_crowdio_comment";

$ranking = "
			SELECT comment_id, 
				(
					(positive + 1.9208) / 
					(pos + negative) - 
					1.96 * SQRT(
						(positive * negative) / 
						(positive + negative) + 0.9604) / 
			   			(positive + negative)
			   	) / t
				(1 + 3.8416 / (positive + negative)) 
			   	AS ci_lower_bound 
			   	FROM '$crowdioVoteTableName' 
			   	WHERE positive + negative > 0 
			   	ORDER BY ci_lower_bound DESC;
			";