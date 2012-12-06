<?php

/* 
Crowdio Comment Function
Version 1.0
*/

/*
IF isset($_POST['submit'])
 >check no empty values (name,email,comment)
 >goto submit
	else >goto form
*/
class Crowdio_Comments extends Crowdio
{
	
	function __construct(argument)
	{
	
	}

	function draw_comment_form()
	{
	print <<<END

	<section class="crowdio_form">
        <form method="post" action="htmlentities($_SERVER['PHP_SELF'])">
            <div class="crowdio_row"> <field>Name: </field> <input type="text" name="name" id="$_POST['name']">
                <div class="crowdio_row"> <field>Email: </field><input type="email" name="email" id="$_POST['email']"> </div>
                <div class="crowdio_row"> <field>Company: </field> <input type="text" name="company" id="$_POST['company']"> </div>
                <div class="crowdio_row"> <field>Website: </field> <input type="text" name="website" id="$_POST['website']"> </div>
                <div class="crowdio_row"> <field>Comment: </field><textarea name="comment" id="$_POST['comment']"></textarea> </div>
                <div class="crowdio_row"> <field>&nbsp; </field> <input type="submit" value="SUBMIT!!" id="submit"></div>
        </form>
</section>
END
	}

	function crowdio_add_comment()
	{
		
		// write data to SQL $wpdb->insert( $table, $data, $format );
		$wpd->insert($crowdio_comment_table_name,
			array(
				name => $_POST['name'] ,
				email => $_POST['email'] ,
				company => $_POST['company'] ,
			    comment => $_POST['comment'] ,
			    user_ip => $_SERVER['REMOTE_ADDR'] ,
			    user_id => wp_get_current_user() ,
			    website => $_POST['website'] ,
			    session_id => session_id() 
			    )

	}

	function crowdio_view_comments($per_page)
	{
		// read database comment

		// print comments 
		print <<<END 

END
	}
}






