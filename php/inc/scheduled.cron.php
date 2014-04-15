<?php
/*********************************************************************
 *
 * FILE:				scheduled.cron.php
 * AUTHOR:				Jake Breindel
 * DATE:				1-15-2014
 *
 * DESRIPTION:
 * 	checks to see if any scheduled tickets need to be inserted and
 * 	notifies the necessary users. 
 *
 **********************************************************************/


/*********************************************************** DEPENDENCIES ***********************************************************/

//Path separator
if(!defined('PATH_SEPARATOR')){
	if(strpos($_ENV['OS'],'Win')!==false || !strcasecmp(substr(PHP_OS, 0, 3),'WIN'))
		define('PATH_SEPARATOR', ';' ); //Windows
    else
        define('PATH_SEPARATOR',':'); //Linux
}

#Define the pear directory
define("INCLUDE_DIR", "/htdocs/live/osTicket/include/");
define("PEAR_DIR", INCLUDE_DIR."pear/");
#Mysql Login info
define('DBTYPE','mysql');
define('DBHOST','localhost'); 
define('DBNAME','osTicket');
define('DBUSER','xxxxx');
define('DBPASS','xxxx');
#Table names
define('SCHEDULED_TABLE', 'ost_scheduled_tickets');
define('CONFIG_TABLE', 'ost_config');
define('TIMEZONE_TABLE', 'ost_timezone');
define('EMAIL_TABLE', 'ost_email');
define('TICKET_TABLE', 'ost_ticket');
define('STAFF_TABLE', 'ost_staff');
define('CLIENT_TABLE', 'ost_clients');
define('GROUP_TABLE', 'ost_groups');
define('TICKET_THREAD_TABLE', 'ost_ticket_thread');
define('TICKET_EVENT_TABLE', 'ost_ticket_event');
#Ticket information
define('HOME_URL', 'http://example.com/tickets/');
define('DEPT_ID', 3);
define('SLA_ID', 1);
define('DATE_FORMAT_STRING', 'm/d/y g:ia');
define('EXT_TICKET_ID_LEN',6);

//Set include paths. Overwrite the default paths.
ini_set('include_path', '/htdocs/live/osTicket/include/'.PATH_SEPARATOR.INCLUDE_DIR.PATH_SEPARATOR.PEAR_DIR);

// Config class
require_once(INCLUDE_DIR.'class.timezone.php');
require_once(INCLUDE_DIR.'class.config.php');
// MYSQL functions
require_once(INCLUDE_DIR.'mysql.php');
// Text formatters
require_once (INCLUDE_DIR.'class.format.php');
require_once (INCLUDE_DIR.'class.misc.php');
require_once (INCLUDE_DIR.'class.variable.php');
// Mail Helper
require_once (INCLUDE_DIR.'class.mailer.php');
// Staff, Team, and Client
require_once (INCLUDE_DIR.'class.staff.php');
require_once (INCLUDE_DIR.'class.team.php');
require_once (INCLUDE_DIR.'class.client.php');
// Scheduled tickets class
require_once (INCLUDE_DIR.'class.scheduled.php');

// Error reporting
define('ERROR_REPORT', TRUE);

// IF error reporting is turned on
if(ERROR_REPORT){
	error_reporting(E_ALL); 
	ini_set('display_errors','On');
}

// IF we can't connect to the database
if (!db_connect(DBHOST,DBUSER,DBPASS) || !db_select_database(DBNAME)) {
   echo 'Unable to connect to database';
   exit;
}

// load the config object
$cfg = new Config(1);
 
/**
 * dumps the variable in a properly formated way and then exits.
 */
function dump($var){
		
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	exit;
}

/**
 * reports errors for inserting $scheduledObj into the database
 */
function scheduledError($scheduledObj){
	
	// reference the time
	$now = new DateTime();
			
	// reference email components
	$subj = "Auto Ticket Generator Failed";
	$body = "
	Auto Ticket Generator Cron-
			
	Unable to put the following ticket into the database:
			
	DateTime:
	".$now->format(DATE_FORMAT_STRING)."
	Username:
	".$scheduledObj->getUsername()."
	Subject:
	".$scheduledObj->getSubject()."
	Body:
	".$scheduledObj->getSubject()."
			
	Please check the scheduled ticket, and also check the auto cron script if necessary.
	";
	$from = "AutoTicketGenerator@example.com";
			
	// IF this ticket should be assigned to a staff memeber
	if($scheduledObj->getStaffId() > 0){
			
		// get the staff member
		$staff = new Staff($scheduledObj->getStaffId());
		// send them an email
		Mailer::sendmail($staff->getEmail(), $subj, $body, $from);
			
	}
	// ELSEIF this ticket should be assigned to a team
	elseif($scheduledObj->getTeamId() > 0){
			
		// get the team
		$team = new Team($scheduledObj->getTeamId());
		// get the team members
		$members = $team->getMembers();
			
		// FOREACH of the members
		foreach($members as $num => $id){
				
			// get the staff member
			$staff = new Staff($id);
			// send them an email
			Mailer::sendmail($staff->getEmail(), $subj, $body, $from);
		}
	}
	// ELSE we need to notify everyone
	else{
						
		// get all of the staff members		
		$allStaff = Staff::getAvailableStaffMembers();
				
		// FOREACH of the staff
		foreach($allStaff as $num => $id){
				
			// get the staff member
			$staff = new Staff($id);
			// send them an email
			Mailer::sendmail($staff->getEmail(), $subj, $body, $from);
		}
	}
}

/************************************************************************************************************************************/

// get all scheduled
$all = Scheduled::getAllScheduled();

// FOREACH of the scheduled tickets
foreach($all as $num => $scheduledObj){
	
	// IF we should run the scheduled ticket now
	if($scheduledObj->runNow()){
		
		try{
			
			// put a copy into the database
			$scheduledObj->putTicketIntoDatabse();
			
			// IF the scheduled ticket is non recurring
			if($scheduledObj->isRecurring() == 0){
				
				// delete the scheduled ticket
				$scheduledObj->delete();
				
			}
			
		}catch(Exception $e){
			
			// handle the error
			scheduledError($scheduledObj);
			
		}
	}
}

?>
