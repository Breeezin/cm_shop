<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES) and array_key_exists("Submit",$this->ATTRIBUTES)) {
		if ($this->ATTRIBUTES['Submit'] == "Post Reply") {
			if (!strlen(trim($this->ATTRIBUTES['Content']))) {
				$errors['Content'] = array('Message is a required field.');
			}
			if (count($errors) == 0) {
				startTransaction();
				$fm_id = newPrimaryKey('forum_messages','fm_id',1,'sql','fm_thr_id = '.safe($this->ATTRIBUTES['thr_id']));
				
				// Get the poster's details
				$userDetails = ss_getUser();
				$posterUserLink = $userDetails['us_id'];
				$posterFirstName = escape($userDetails['us_first_name']);
				$posterLastName = escape($userDetails['us_last_name']);
				$posterEmail = escape($userDetails['us_email']);
				
				// Insert the message
				$Q_InsertMessage = query("
					INSERT INTO forum_messages
						(fm_id, fm_thr_id, fm_content, fm_poster_firstname,
							fm_poster_lastname, fm_poster_email, fm_poster_us_id, fm_timestamp)
					VALUES
						($fm_id, ".safe($this->ATTRIBUTES['thr_id']).", '".escape($this->ATTRIBUTES['Content'])."', '$posterFirstName',
							'$posterLastName', '$posterEmail', $posterUserLink, Now())
				");
				
				// Update the thread
				$Q_InsertThread = query("
					UPDATE forum_threads
					SET thr_last_thr_id = $fm_id
					WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
						AND thr_as_id = ".$asset->getID()."
				");
				
				// Count how many messages in the thread
				$Messages = getRow("
					SELECT COUNT(*) AS TheCount FROM forum_messages
					WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
				");
				
				// Subscribe the poster to the thread?
				
				// Send notifications to ppl
				$this->notify($this->ATTRIBUTES['thr_id'],$fm_id);
				
				commit();	
				
				locationRelative(ss_withoutPreceedingSlash($asset->getPath())."?Service=ViewThread&thr_id={$this->ATTRIBUTES['thr_id']}&CurrentPage=".ceil($Messages['TheCount']/$this->messagesPerPage)."#Message{$fm_id}");
			}
			
			
		}
		if ($this->ATTRIBUTES['Submit'] == "Cancel") {
			locationRelative(ss_withoutPreceedingSlash($asset->getPath())."?Service=ViewThread&thr_id={$this->ATTRIBUTES['thr_id']}");
		}
	}
?>