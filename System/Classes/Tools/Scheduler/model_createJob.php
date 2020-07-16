<?php

	global $commonDB;

// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
  global $commonDB;
  switch ($errno) {
  case E_USER_ERROR:

   if( $errno == 256 )
   {
        $commonDB->query("drop table if exists ScheduledJobs");
        $commonDB->query("create table ScheduledJobs
                           (sj_id       int(11) NOT NULL default '0',
                            sj_cwd      varchar(50),
                            sj_script   varchar(100),
                            sj_output_from varchar(100),
                            sj_output_to varchar(100),
                            sj_scheduled_date   datetime,
                            sj_executed_date    datetime,
                            sj_return_code  tinyint,
                            primary key (sj_id)) type=InnoDB" );
   }
   else
   {
       echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
       echo "  Fatal error in line $errline of file $errfile";
       echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
       echo "Aborting...<br />\n";
       exit(1);
   }
   break;
  case E_USER_WARNING:
   echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
   break;
  case E_USER_NOTICE:
   echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
   break;
  default:
   echo "Unkown error type: [$errno] $errstr<br />\n";
   break;
  }
}

	ss_RestrictPermission('CanAdministerAtLeastOneAsset');

	// create a job
	$this->param('Script');
	$this->param('OutputTo');
	$this->param('OutputFrom');

//    error_reporting (0);
//    restore_error_handler();
	startTransaction();
    set_error_handler("myErrorHandler");
	$id = newPrimaryKey('ScheduledJobs','sj_id',1,'commonDB');

    //briar changed this.. might always be contentmanager - or am I breaking this for Acme?
    $Q_Insert = $commonDB->query("
		INSERT INTO ScheduledJobs
			(sj_id, sj_cwd, sj_script, sj_output_from, sj_output_to, sj_scheduled_date)
		VALUES
			($id, '/www/htdocs/contentmanager','".escape($this->ATTRIBUTES['Script'])."', '"
            .escape($this->ATTRIBUTES['OutputTo'])."', '"
            .escape($this->ATTRIBUTES['OutputFrom'])."', NOW())"
	    );

    if( !$Q_Insert )
    {

    }

	commit();
?>
