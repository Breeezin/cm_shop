<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES) and array_key_exists("Submit",$this->ATTRIBUTES)) {
		if ($this->ATTRIBUTES['Submit'] == "Create Thread") {
			if (!strlen(trim($this->ATTRIBUTES['Content']))) {
				$errors['Content'] = array('Message is a required field.');
			}
			if (!strlen(trim($this->ATTRIBUTES['Subject']))) {
				$errors['Subject'] = array('Subject is a required field.');
			}
			if (count($errors) == 0) {
				startTransaction();
				$thr_id = newPrimaryKey('forum_threads','thr_id',1);
				
				// Get the poster's details
				$userDetails = ss_getUser();
				$posterUserLink = $userDetails['us_id'];
				$posterFirstName = escape($userDetails['us_first_name']);
				$posterLastName = escape($userDetails['us_last_name']);
				$posterEmail = escape($userDetails['us_email']);
				
				// Insert the thread
				$Q_InsertThread = query("
					INSERT INTO forum_threads
						(thr_id, thr_subject, thr_views, thr_locked, 
							thr_created, thr_as_id, thr_last_thr_id)
					VALUES
						($thr_id, '".escape($this->ATTRIBUTES['Subject'])."', 0, 0,
							Now(), ".$asset->getID().", 1)
				");
				
				// Insert the message
				$Q_InsertMessage = query("
					INSERT INTO forum_messages
						(fm_id, fm_thr_id, fm_content, fm_poster_firstname,
							fm_poster_lastname, fm_poster_email, fm_poster_us_id, fm_timestamp)
					VALUES
						(1, $thr_id, '".escape($this->ATTRIBUTES['Content'])."', '$posterFirstName',
							'$posterLastName', '$posterEmail', $posterUserLink, Now())
				");
				
				// Subscribe the poster to the thread? - not yet
				
				// Send notifications to ppl
				$this->notify($thr_id,1);

				commit();	
				
				locationRelative(ss_withoutPreceedingSlash($asset->getPath())."?Service=ViewThread&thr_id=$thr_id");
			}
			
			
		}
		if ($this->ATTRIBUTES['Submit'] == "Cancel") {
			locationRelative(ss_withoutPreceedingSlash($asset->getPath()));
		}
	}
?>