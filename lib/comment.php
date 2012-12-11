<?php

/*
* @Package:	Crowdio
*
*/

class CrowdioComment extends Crowdio {
	
	function __construct()
	{
		global $wpdb;
	}

	function display_comment_form()
	{
		$action_url = $GLOBALS['post']->guid;
		$user_name = $_POST['crowdio_comment_name'];
		$user_email = $_POST['crowdio_comment_email'];
		$user_company = $_POST['crowdio_comment_organization'];
		$user_website = $_POST['crowdio_comment_website'];
		$user_comment = $_POST['crowdio_comment_content'];
		$rfi_id = $GLOBALS['post']->ID;

		
		print <<<END
			<section class="crowdio_form">
			    <form method="post" action="$action_url">
			    	<input type="hidden" name="crowdio_rfi_id" value="$rfi_id" />
			    	
				    <div class="crowdio_row"> <span id="crowdio_comment_form">Add your best idea (one per person):</span> </div>
				    
				    <fieldset> <label for="crowdio_comment_name">Name:</label> <input type="text" id="crowdio_comment_name" value="$user_name" name="crowdio_comment_name" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_email">Email:</label> <input type="text" id="crowdio_comment_email" value="$user_email" name="crowdio_comment_email" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_organization">Organization:</label> <input type="text" id="crowdio_comment_organization" name="crowdio_comment_organization" value="$user_company" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_website">Website:</label> <input type="text" id="crowdio_comment_website" name="crowdio_comment_website" value="$user_website" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_content">Comment:</label> <textarea rows="4" cols="20" id="crowdio_comment_content" name="crowdio_comment_content">$user_comment</textarea> </fieldset>
		
			        <div class="crowdio_row"> <input type="submit" value="SUBMIT" id="submit" name="submit"  /> </div>
			    </form>
			</section>
END;
	}

	function add_comment()
	{
		global $wpdb;
		$sid = session_id();
		//wp_get_current_user();user_id => $current_user->ID,
		// write data to SQL $wpdb->insert( $table, $data, $format );
		$wpdb->insert(CROWDIO_COMMENT_TABLE_NAME,
			array(
				'name' => $_POST['crowdio_comment_name'],
				'email' => $_POST['crowdio_comment_email'],
				'company' => $_POST['crowdio_comment_organization'],
			    'comment_text' => $_POST['crowdio_comment_content'],
			    'user_ip' => $_SERVER['REMOTE_ADDR'],
			     
			    'website' => $_POST['crowdio_comment_website'], 
			    'session_id' => $sid,
			    'rfi_id' => $_POST['crowdio_rfi_id']
			    )
			);
		print "*";

	}

	function display_comment($comment_row, $levelclass) {
		$date = $comment_row->date;
		$name = $comment_row->name;
		$email = $comment_row->email;
		$comment = $comment_row->comment_text;
	    print <<<END
			<div class="answer $levelclass">
				<div>Date: $date</div>
				<div>Name: $name</div>
				<div>Email: $email</div>
				<div>Comment: $comment</div>
			</div>
END;
	}

	function display_comments()
	{
		global $wpdb;
		// read database comment $wpdb->query('query'); ORDER BY / LIMIT
		$comment_table = CROWDIO_COMMENT_TABLE_NAME;
		$rfi_id = $GLOBALS['post']->ID;
		$firstlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id = '$rfi_id'");
		// print comments 
		foreach ($firstlevel as $row) 
		{
			$this->display_comment($row, "firstlevel");

			$secondlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id = '$rfi_id' AND parent_id = '$row->ID'");
			if ($wpdb->num_rows > 0)
			{
				foreach ($secondlevel as $row)
				{
					$this->display_comment($row, "secondlevel");

					$thirdlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id = '$rfi_id' AND parent_id = '$row->ID'");
					if ($wpdb->num_rows > 0)
					{
						foreach ($thirdlevel as $row)
						{
							$this->display_comment($row, "thirdlevel");
						}
					}
				}
			}
		}
	}

	public function check_submission() {
		if (!empty($_POST['crowdio_comment_name']) &&
		!empty($_POST['crowdio_comment_email']) &&
		!empty($_POST['crowdio_rfi_id']) &&
		!empty($_POST['crowdio_comment_organization']) &&
		!empty($_POST['crowdio_comment_website']) &&
		!empty($_POST['crowdio_comment_content'])) {
			$this->add_comment();
		} else {
			print("Error saving comment");
		}
	}
	
	public function modify_page_content($content)
	{
		if (is_single() && $GLOBALS['post']->post_type == 'crowdios')
		{
			$content .= $this->display_comments();
			$content .= $this->display_comment_form();
		}
				
		return $content;
	}
}