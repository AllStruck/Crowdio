<?php

/**
*/
class ClassName extends AnotherClass
{
	
	function __construct(argument)
	{
	
	}

	function draw_comment_form()
	{
	print <<<END

	<section class="crowdio_form">
        <form method="post" action="$_SERVER['PHP_SELF']">
            <div class="crowdio_row"> <field>Name:</field>  <input type="text" name="name" id="$_POST['name']">
                <div class="crowdio_row"> <field> Email: </field><input type="email" name="email" id="$_POST['email']"> </div>
                <div class="crowdio_row"> <field>Company:</field> <input type="text" name="company" id="$_POST['company']"> </div>
                <div class="crowdio_row"> <field>Comment: </field><textarea name="comment" id="$_POST['comment']"></textarea> </div>
                
                <div class="crowdio_row"> <field>&nbsp;</field> <input type="submit" value="SUBMIT!!" id="submit"></div>
        </form>
</section>
END
	}
}






