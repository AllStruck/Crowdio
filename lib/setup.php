<?php
// Globals and constants.
global $wpdb, $table_prefix;

define('CROWDIO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CROWDIO_COMMENT_TABLE_NAME', $table_prefix . 'crowdio_comments');
define('CROWDIO_VOTE_TABLE_NAME', $table_prefix . 'crowdio_votes');

