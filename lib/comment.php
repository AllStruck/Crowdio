<?php

/**
*/
class ClassName extends AnotherClass
{
	
	function __construct(argument)
	{
		# code...
	}

	function draw_comment_form() {
		$comment_id;
		print <<<END
		
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
Name: <input type="text" name="name" id="<?php $_POST['name']; ?>"> <BR>
Email: <input type="text" name="email" id="<?php $_POST['email']; ?>"> <BR>
Company: <input type="text" name="company" id="<?php $_POST['company']; ?>"> <BR>
Comment: <textarea name="comment" id="<?php $_POST['comment']; ?>"></textarea>
</form>

END
	}
}






