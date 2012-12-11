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
			    	<input type="hidden" name="crowdio_id" value="$rfi_id" />
			    	
				    <div class="crowdio_row"> <span id="crowdio_comment_form">Add your best idea (one per person):</span> </div>
				    
				    <fieldset> <label for="crowdio_comment_name">Name:</label> <input type="text" id="crowdio_comment_name" value="$user_name" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_email">Email:</label> <input type="text" id="crowdio_comment_email" value="$user_email" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_organization">Organization:</label> <input type="text" id="crowdio_comment_organization" value="$user_company" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_website">Website:</label> <input type="text" id="crowdio_comment_website" value="$user_website" /> </fieldset>
				    <fieldset> <label for="crowdio_comment_content">Comment:</label> <textarea rows="4" cols="20" id="crowdio_comment_content" value="$user_comment"></textarea> </fieldset>
		
			        <div class="crowdio_row"> <input type="submit" value="SUBMIT" id="submit" /> </div>
			    </form>
			</section>
END;
	}

	function add_comment()
	{
		
		// write data to SQL $wpdb->insert( $table, $data, $format );
		$wpdb->insert($crowdio_comment_table_name,
			array(
				name => $_POST['name'],
				email => $_POST['email'],
				company => $_POST['company'],
			    comment => $_POST['comment'],
			    user_ip => $_SERVER['REMOTE_ADDR'],
			    user_id => wp_get_current_user(),
			    website => $_POST['website'], 
			    session_id => session_id(),
			    rfi_id => $_POST['crowdio_id']
			    )
			);
		print "Idea submitted, go F yourself. :P";

	}


	function display_comments()
	{
		// read database comment $wpdb->query('query'); ORDER BY / LIMIT
		$result = $wpdb->query('SELECT * FROM table');
		// print comments 
		foreach ($result as $row) 
		{
		    echo <<<END
			<section class="form">
				<field> &nbsp; </feild> 	<div> $row->date</div>
				<field> Name </field> 		<div> $row->name</div>
				<field> Email </field> 		<div> $row->email</div>
				<field> Comment </field>	<div> $row->comment</div>
			</section>
END;
		}
	}

	public function check_submission() {
		if (!empty($_POST['crowdio_comment_name']) &&
		!empty($_POST['crowdio_comment_email']) &&
		!empty($_POST['crowdio_comment_id']) &&
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
			//$content .= $this->display_comments();
			$content .= $this->display_comment_form();
		}
				
		return $content;
	}
}