<?php

/**
* @Package:	Crowdio
*/

class CrowdioComment extends Crowdio {
	
	function __construct()
	{
		global $wpdb;
	}

	function display_comment_form()
	{
	$action_url = $GLOBALS['post']->guid;
	$user_name = $_POST['name'];
	$user_email = $_POST['email'];
	$user_company = $_POST['company'];
	$user_website = $_POST['user_website'];
	$user_comment = $_POST['comment'];
	
	print <<<END
		<section class="crowdio_form">
		    <form method="post" action="$action_url">
		    	<input type="hidden" name="crowdio_id" value="$rfi_id" />
		    	
			    <div class="crowdio_row"> <span id="crowdio_comment_form">COMMENTS</span> </div>
			    
			    <fieldset>
			    	<legend>Personalia:</legend>
					<label for="crowdio_name">Name:</label> <input type="text" id="crowdio_name" />
	    		</fieldset>
	
		        <div class="crowdio_row"> <field>Name: </field> <input type="text" name="name" id="$user_name"> </div>
		        <div class="crowdio_row"> <field>Email: </field><input type="email" name="email" id="$user_email"> </div>
		        <div class="crowdio_row"> <field>Company: </field> <input type="text" name="company" id="$user_company"> </div>
		        <div class="crowdio_row"> <field>Website: </field> <input type="text" name="website" id="$user_website"> </div>
		        <div class="crowdio_row"> <field>Comment: </field><textarea name="comment" id="$user_comment"></textarea> </div>
		        <div class="crowdio_row"> <field>&nbsp; </field> <input type="submit" value="SUBMIT!!" id="submit"></div>
		    </form>
		</section>
END;
	}

	function crowdio_add_comment()
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
			    session_id => session_id()
			    )
			);

	}


	function display_comments()
	{
		// read database comment $wpdb->query('query'); ORDER BY / LIMIT
		$result = $wpdb->query('SELECT * FROM table');
		// print comments 
		foreach ($result as $row) 
		{
	    echo "
		<section class=\"form\">
			<field> &nbsp; </feild> 	<div> $row->date</div>
			<field> Name </field> 		<div> $row->name</div>
			<field> Email </field> 		<div> $row->email</div>
			<field> Comment </field>	<div> $row->comment</div>
		</section>
		";
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






