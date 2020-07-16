<?
    require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
    //die($Q_NewsletterRecipients->numRows());
    if( php_sapi_name() == 'cli')
    // are we being called via the command line ?
    {
        $this->sendNewsletter($Newsletter,$Q_NewsletterRecipients,$this->ATTRIBUTES['ArchiveNeID'],'index.php?act=NewslettersAdministration.List');
    // or is there less than 10 to send
    } else if ($Q_NewsletterRecipients->numRows() < 2) {
        $this->sendOldNewsletter($Newsletter,$Q_NewsletterRecipients,$this->ATTRIBUTES['ArchiveNeID'],'index.php?act=NewslettersAdministration.List');

?>
<script language="Javascript">
    alert( "Your newsletters have been sent" );
    document.location = 'index.php?act=NewslettersAdministration.List';
</script>
<?
    }
    else  //schedule the job
    {
        global $commonDB;
        $id = newPrimaryKey('ScheduledJobs','sj_id',1,'commonDB');

        $currentSite = str_replace('http://', '', $GLOBALS['cfg']['currentSite']);
        $currentSite = str_replace('/', '', $currentSite);
        // insert the job in _Shared:ScheduledJobs
        /*$res = new Request("Scheduler.CreateJob",array(
                                'Script'    =>      'SendNewsletter.sh '.$this->ATTRIBUTES['nl_id'].' '.$this->ATTRIBUTES['ArchiveNeID'] .' '.$currentSite,
                                'OutputTo'          =>      $_SESSION['User']['us_email'],
                                'OutputFrom'        =>    $Newsletter['nl_sender_email']
                        ));  */
        //briar changed this..
        $cwd        = getcwd();
        $script     = 'SendNewsletter.sh '.$this->ATTRIBUTES['nl_id'].' '.$this->ATTRIBUTES['ArchiveNeID'] .' '.$currentSite;
        $outputTo   = $_SESSION['User']['us_email'];
        $outputFrom = $Newsletter['nl_sender_email'];

        $Q_Insert = $commonDB->query("
    		INSERT INTO ScheduledJobs
    			(sj_id, sj_cwd, sj_script, sj_output_from, sj_output_to, sj_scheduled_date)
    		VALUES
    			($id,'".escape($cwd)."','".escape($script)."', '"
                .escape($outputTo)."', '"
                .escape($outputFrom)."', NOW())"
    	    );
?>
<script language="Javascript">
    alert( "Your request has been scheduled to run within the next hour" );
    document.location = 'index.php?act=NewslettersAdministration.List';
</script>

<?php

        $mailer = new htmlMimeMail();
		$mailer->setFrom($Newsletter['nl_sender_email']);
		$mailer->setSubject('New newsletter scheduling - ' .$currentSite .' - '. $Newsletter['nl_subject'] .' - recipient count = '.$Q_NewsletterRecipients->numRows());
        $mailer->send(array('im@admin.com'));

    }
?>
