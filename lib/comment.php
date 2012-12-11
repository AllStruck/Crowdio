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
		global $current_user, $wpdb;
		get_currentuserinfo();
		$user_url = $current_user->user_url;
		$display_name = $current_user->display_name;
		$user_email = $current_user->user_email;
		$user_ID = $current_user->ID;

		$action_url = $_SERVER["REQUEST_URI"]; //$GLOBALS['post']->guid;
		$user_comment = $_POST['crowdio_comment_content'];
		$rfi_id = $GLOBALS['post']->ID;

		$existingComment = $wpdb->get_row("SELECT * FROM " . CROWDIO_COMMENT_TABLE_NAME . " WHERE user_id = $user_ID");
		
		if (is_user_logged_in())
		{
			if (empty($existingComment) || !empty($_GET['replyto']))
			{
				$errors = $GLOBALS['crowdio_comment_submit_error'];
					print <<<END
						<div class="crowdio_form_error">
							<span class="error">$errors</span>
						</div>
END;

				$addCommentInstructionWebsite = !empty($user_url) ? 
					"Your website address on your profile is $user_url" : 
					'Your website address is blank on your profile.';
				$addCommentInstructionWebsite .= ' You can <a href="/wp-admin/profile.php">edit your profile</a> at any time.';

				$replyOrIdeaPrompt = empty($_GET['replyto']) ?
					'Write your idea here:' :
					'Write your reply here';

				$commentParent = !empty($_GET['replyto']) ?
					$_GET['replyto'] :
					'';

				$commentContentClass = in_array('crowdio_comment_content', $GLOBALS['crowdio_comment_blank_fields']) ? 
					"submittedBlank" :
					"";

				print <<<END
					<a name="replyform">&nbsp</a>
					<div class="crowdio_form">
					    <form method="post" action="$action_url">
					    	<input type="hidden" name="crowdio_rfi_id" value="$rfi_id" />
					    	<input type="hidden" name="crowdio_comment_name" value="$display_name" />
					    	<input type="hidden" name="crowdio_comment_email" value="$user_email" />
					    	<input type="hidden" name="crowdio_comment_url" value="$user_url" />
					    	<input type="hidden" name="crowdio_comment_user_id" value="$user_ID" />
					    	<input type="hidden" name="crowdio_comment_parent_id" value="$commentParent" />
					    	<input type="hidden" name="crowdio_comment_submit" value="verify" />

						    <div id="crowdioAddCommentInstructions">
						    	<div id="crowdioAddCommentInstructionOne">Add your best idea (one per person):</div>
						    	<div id="crowdioAddCommentInstructionUser">Your idea will be left as $display_name.</div>
						    	<div id="crowdioAddCommentInstructionWebsite">$addCommentInstructionWebsite</div>
						    </div>
						    
						    <fieldset>
						    	<label for="crowdio_comment_content">$replyOrIdeaPrompt</label>
						    	<textarea class="$commentContentClass" rows="4" cols="20" id="crowdio_comment_content" name="crowdio_comment_content">$user_comment</textarea>
						    </fieldset>
				
					        <div class="crowdio_row"> <input type="submit" value="Save" id="submit" name="submit"  /> </div>
					    </form>
					</div>
END;
			} else // User has already submitted an idea.
			{
				print <<<END
					<div class="crowdioFormNotice">
						<span class="noticeText">You have already submitted an idea here. You can still reply to other ideas though!</span>
					</div>
END;
			}
		} else
		{
			print <<<END
				<div class="crowdioFormNotice">
					<span class="noticeText">Please <a href="/wp-login.php">Log In</a> or <a href="/wp-login.php?action=register">Register</a> to add your idea.</span>
				</div>
END;
		}
	}

	function add_comment()
	{
		global $wpdb, $current_user;
		$sid = session_id();
		get_currentuserinfo();

		if (is_user_logged_in()) {
			$name = $_POST['crowdio_comment_name'];
			$email = $_POST['crowdio_comment_email'];
			$comment_text = $_POST['crowdio_comment_content'];
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$user_ID = $_POST['crowdio_comment_user_ID'];
			$user_url = $_SERVER['crowdio_comment_user_url'];
			$session_id = session_id();
			$rfi_id = $_POST['crowdio_rfi_id'];
			//$crowdio_db->insert_comment($name, $email, $comment_text, $user_ip, $user_id, $user_url, $session_id, $rfi_id, $parent_id);
			
			// write data to SQL $wpdb->insert( $table, $data, $format );
			$wpdb->insert(CROWDIO_COMMENT_TABLE_NAME,
				array(
					'name' => $_POST['crowdio_comment_name'],
					'email' => $_POST['crowdio_comment_email'],
					//'organization' => $_POST['crowdio_comment_organization'],
				    'comment_text' => $_POST['crowdio_comment_content'],
				    'user_ip' => $_SERVER['REMOTE_ADDR'],
				    'user_id' => $_POST['crowdio_comment_user_id'],
				    'website' => $_POST['crowdio_comment_url'],
				    'session_id' => $sid,
				    'rfi_id' => $_POST['crowdio_rfi_id'],
				    'parent_id' => $_POST['crowdio_comment_parent_id']
				    )
				);
		} else {
			print("Can't save comment, user not logged in.");
		}
		if ($wpdb->insert_id)
		{
			print "Success!";
		} else
		{
			print "SQL Insert Error: "; $wpdb->print_error();
		}
	}

	function display_comment($comment_row, $levelclass, $comment_id)
	{
		global $wpdb;
		$commentUser = get_userdata($comment_row->user_id);
		
		if ($commentUser)
		{
			$created = $comment_row->created_timestamp;
			$name = $commentUser->display_name;
			$url = $commentUser->user_url;
			$comment = $comment_row->comment_text;
			$comment_id = $comment_row->id;
			$action_url = $_SERVER["REQUEST_URI"] . "/?replyto=$comment_id#replyform";
		    print <<<END
				<div class="idea">
					<div class="ideaVoteReplyButtons">
						<span class="ideaVoteButton up"><a href="">+</a> </span>
						<span class="ideaVoteButton down"><a href="">-</a> </span>
						<span class="ideaReplyButton"><a href="$action_url">Reply</a></span>
					</div>
					<div class="ideaInfo">
						<span class="ideaDate">$created</span>:
						<span class="ideaName"> <a href="$url">$name</a></span>
						<span> said:</span>
					</div>

					<div class="ideaContent">$comment</div>
				</div>
END;
		}
	}

	function display_comments()
	{
		global $wpdb;
		// read database comment $wpdb->query('query'); ORDER BY / LIMIT
		$comment_table = CROWDIO_COMMENT_TABLE_NAME;
		$rfi_id = $GLOBALS['post']->ID;
		$firstlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id='$rfi_id' AND parent_id IS NULL");
		
		if ($firstlevel)
		{
			print '<div class="crowdioComment firstlevel">';

			// print comments 
			foreach ($firstlevel as $row) 
			{
				$this->display_comment($row, "firstlevel");

				$secondlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id = '$rfi_id' AND parent_id = '$row->id'");
				if ($secondlevel)
				{
					print '<div class="crowdioComment secondlevel">';
					foreach ($secondlevel as $row)
					{
						$this->display_comment($row, "secondlevel");

						$thirdlevel = $wpdb->get_results("SELECT * FROM $comment_table WHERE rfi_id = '$rfi_id' AND parent_id = '$row->id'");
						if ($thirdlevel)
						{
							print '<div class="crowdioComment thirdlevel">';
							foreach ($thirdlevel as $row)
							{
								$this->display_comment($row, "thirdlevel");
							}
							print '</div><!-- End thirdlevel -->';
						}
					}
					print '</div><!-- End secondlevel -->';
				}
			}
			print '</div><!-- End firstlevel -->';
		} else
		{
			print '<div class="crowdioNoIdeas">No ideas yet!</div>';
		}
	}

	public function check_comment_submission()
	{
		if (!empty($_POST['crowdio_comment_user_id']) &&
		!empty($_POST['crowdio_comment_name']) &&
		!empty($_POST['crowdio_comment_email']) &&
		!empty($_POST['crowdio_rfi_id']) &&
		!empty($_POST['crowdio_comment_url']) &&
		!empty($_POST['crowdio_comment_content']))
		{
			$this->add_comment();
		} else
		{
			if (empty($_POST['crowdio_comment_content']))
			{
				$GLOBALS['crowdio_comment_submit_error'] = 'Required field was left blank.';
				$GLOBALS['crowdio_comment_blank_fields'] = array("crowdio_comment_content");
			}
			print("Can't save comment, missing _POST data.");
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