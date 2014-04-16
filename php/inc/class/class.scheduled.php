<?php
/********************************************************************************
 *
 * FILE:				class.scheduled.php
 * DATE:				1-16-14
 * AUTHOR:				@author J.Breindel
 *
 * DESCRIPTION:
 *
 * 	Class file for a shceduled ticket. 
 *
 ********************************************************************************/

class Scheduled {

	/******************************************* INSTANCE VARS *******************************************/

	/** @var $scheduled_id primary key */
	private $scheduled_id;
	/** @var $priority_id of the ticket */
	private $priority_id;
	/** @var $topic_id of the ticket */
	private $topic_id;
	/** @var $staff of the ticket */
	private $staff_id;
	/** @var $team of the ticket */
	private $team_id;
	/** @var $name on the ticket */
	private $username;
	/** @var $source of the ticket */
	private $source;
	/** @var $url of the ticket */
	private $url;
	/** @var $subject of the ticket */
	private $subject;
	/** @var $body of the first ticket response */
	private $body;
	/** @var $startdate of the ticket */
	private $startdate;
	/** @var $startdate of the ticket */
	private $starttime;
	/** @var $duedate of the ticket */
	private $duedate;
	/** @var $duedate of the ticket */
	private $duetime;
	/** @var $notifications staff of ticket */
	private $notifications;
	/** @var $isrecurring ticket */
	private $isrecurring;
	/** @var $isrecurring ticket */
	private $interval;
	/** @var $isrecurring ticket */
	private $time_interval;
	/** @var $updated timestamp */
	private $updated;
	/** @var $created timestamp */
	private $created;

	/******************************************* CONSTRUCTORS *******************************************/

	/**
	 * @param 	$scheduled_id		primary key of the Scheduled
	 *
	 */
	public function Scheduled($scheduled_id = null) {

		// IF an ID was passed in
		if($scheduled_id != null){
		
			// zero the id
			$this -> scheduled_id = 0;
			// load the scheduled
			$this -> load($scheduled_id);
			
		}
		
		return $this;
	}
	
	/**
	 * @param 	$assocArray		associative array representation of a scheduled
	 *
	 */
	public static function ScheduledFromArray($assocArray){
		
		// make a new scheduled
		$sched = new Scheduled();
		// load the data from the array
		$sched->loadFromArray($assocArray);
		
		return $sched;
	}

	/******************************************* MEMBER METHODS *******************************************/

	/**
	 * @param 	$id		primary key id
	 *
	 * 	loads the scheduled ticket
	 */
	private function load($id) {

		// IF we don't have an id
		if (!$id && !($id = $this->getScheduledId()))
			return false;

		$loadQuery = "
			SELECT 
				*
			FROM
				".SCHEDULED_TABLE."
			WHERE
				scheduled_id=".db_input($id);
		
		// IF the query fails
		if (!($res = db_query($loadQuery)) || !db_num_rows($res))
			return false;
		
		$array = db_assoc_array($res);
		
		// get the associative array
		$this->loadFromArray($array[0]);

		return true;
	
	}

	/**
	 * @param	$assocArray		loads the ticket from its corresponding associative array
	 */
	private function loadFromArray($assocArray){
		
		// assign the instance variables
		$this->scheduled_id = $assocArray['scheduled_id'];
		$this->priority_id = $assocArray['priority_id'];
		$this->topic_id = $assocArray['topic_id'];
		$this->staff_id = $assocArray['staff_id'];
		$this->team_id = $assocArray['team_id'];
		$this->username = $assocArray['username'];
		$this->source = $assocArray['source'];
		$this->url = $assocArray['url'];
		$this->subject = $assocArray['subject'];
		$this->body = $assocArray['body'];
		$this->startdate = $assocArray['startdate'];
		$this->starttime = Format::userdate('G:i', Misc::db2gmtime($assocArray['startdate']));
		$this->duedate = $assocArray['duedate'];
		$this->duetime = Format::userdate('G:i', Misc::db2gmtime($assocArray['duedate']));
		$this->notifications = $assocArray['notifications'];
		$this->isrecurring = $assocArray['isrecurring'];
		$this->interval = $assocArray['granularity_interval'];
		$this->time_interval = $assocArray['time_interval'];
		$this->updated = $assocArray['updated'];
		$this->created = $assocArray['created'];
		
	}

	/**
	 * @return	$array 		an array representation of this scheduled ticket
	 */
	public function getInfo(){
			
		// make a new array
		$assocArray = array();
		
		// set the variables
		$assocArray['scheduled_id'] = $this->scheduled_id;
		$assocArray['priority_id'] = $this->priority_id;
		$assocArray['topic_id'] = $this->topic_id;
		$assocArray['staff_id'] = $this->staff_id;
		$assocArray['team_id'] = $this->team_id;
		$assocArray['username'] = $this->username;
		$assocArray['source'] = $this->source;
		$assocArray['url'] = $this->url;
		$assocArray['subject'] = $this->subject;
		$assocArray['body'] = $this->body;
		$assocArray['startdate'] = $this->startdate?(Format::userdate('m/d/Y', Misc::db2gmtime($this->startdate))):'';
		$assocArray['starttime'] = $this->startdate?(Format::userdate('G:i', Misc::db2gmtime($this->startdate))):'';
		$assocArray['duedate'] = $this->duedate?(Format::userdate('m/d/Y', Misc::db2gmtime($this->duedate))):'';
        $assocArray['duetime'] = $this->duedate?(Format::userdate('G:i', Misc::db2gmtime($this->duedate))):'';
		$assocArray['notifications'] = $this->notifications;
		$assocArray['isrecurring'] = $this->isrecurring;
		$assocArray['interval'] = $this->interval;
		$assocArray['time_interval'] = $this->time_interval;
		$assocArray['updated'] = $this->updated;
		$assocArray['created'] = $this->created;
		
		return $assocArray;
	}

	/**
	 * reloads the object
	 */
	public function reload(){
		$this->load($this->scheduled_id);
	}

	/**
	 * @param 	$id			primary key id
	 * @param 	$vars		variables for saving the organization
	 * @param 	&$errors	error reporting
	 * 
	 * @return  boolean
	 *
	 * 	Saves an organization to the database
	 */
	private function save($id, $vars, &$errors) {

		// IF there isn't an id
		if ($id && $id != $vars['scheduled_id'])
			$errors['err'] = 'Internal error. Try again';
		
		// IF one of the parameters is not there
		if(!$vars['source']){ $errors['source'] = 'Invalid Source'; }
		if(!$vars['priority_id']){ $errors['priority_id'] = 'Invalid priority'; }
		if(!$vars['topic_id']){ $errors['topic_id'] = 'Invalid site'; }
		if(!$vars['username']){ $errors['username'] = 'Invalid username'; }
		if(!$vars['url']){ $errors['url'] = 'Invalid url'; }
		if(!$vars['subject']){ $errors['subject'] = 'Invalid subject'; }
		if(!$vars['body']){ $errors['body'] = 'Invalid issue'; }
		if(!$vars['startdate'] || !$vars['starttime']){ $errors['startdate'] = 'Invalid start date'; }
		if(!$vars['duedate'] || !$vars['time']){ $errors['duedate'] = 'Invalid due date'; }
		if(!$vars['notifications']){ $errors['notifications'] = 'Invalid notification type'; }
		if(!($vars['isrecurring'] == 0 
		  || $vars['isrecurring'] == 1)){ $errors['recurring'] = 'Invalid recurring value'; }
		
		// IF this is a non recurring ticket
		if($vars['isrecurring'] == 0){
			
			// set defaults for non-recurring tickets
			$vars['interval'] = -1;
			$vars['time_interval'] = -1;
			
		}
		// ELSE IF this is a recurring ticket and we don't have an interval
		elseif($vars['isrecurring'] == 1 && 
			!$vars['interval']){
			
			$errors['interval'] = 'Invalid Interval type';
			
		}		
		// ELSE IF this is a recurring ticket and we don't have a time
		elseif($vars['isrecurring'] == 1 && 
			!$vars['time_interval']){
			
			$errors['time_interval'] = 'Invalid time interval';
			
		}
		
		// IF there is no assigned but we should notify them?
		if(!$vars['assignId'] && ($vars['notifications'] == "assigned")){
			$errors['notifications'] = 'Cannot have notify assigned if none assigned';
		}

		// IF there is errors
		if ($errors)
			return false;
		
		$rv=0;
        $assignId=preg_replace("/[^0-9]/", "", $vars['assignId']);
        if($vars['assignId'][0]=='t') {
            $vars['team_id'] = $assignId;
        } elseif($vars['assignId'][0]=='s' || is_numeric($vars['assignId'])) {
            //We don't care if a team is already assigned to the ticket - staff assignment takes precedence
            $vars['staff_id'] = $assignId;
        }
		
		// set the attributes
		$attributes = "
				priority_id = " . db_input($vars['priority_id']) . "
				,topic_id = " . db_input($vars['topic_id']) . "
				,staff_id = " . db_input($vars['staff_id']) . "
				,team_id = " . db_input($vars['team_id']) . "
				,username = " . db_input($vars['username']) . "
				,source = " . db_input($vars['source']) . "
				,url = " . db_input($vars['url']) . "
				,subject = " . db_input($vars['subject']) . "
				,body = " . db_input($vars['body']) . "
				,startdate = " . ($vars['startdate']?db_input(date('Y-m-d G:i',Misc::dbtime($vars['startdate'].' '.$vars['starttime']))):'NULL') . "
				,duedate = " . ($vars['duedate']?db_input(date('Y-m-d G:i',Misc::dbtime($vars['duedate'].' '.$vars['time']))):'NULL') . "
				,notifications = " . db_input($vars['notifications']) . "
				,isrecurring = " . db_input($vars['isrecurring']) . "
				,granularity_interval = " . db_input($vars['interval']) . "
				,time_interval = " . db_input($vars['time_interval']) . "
				,updated = NOW()
		";

		// IF this is an existing scheduled
		if ($id) {
			
			$sql = "UPDATE 
						" . SCHEDULED_TABLE . "
					SET
						". $attributes ."
					WHERE
						" . SCHEDULED_TABLE . ".scheduled_id = " . db_input($id);
			
			// IF we're able to execute the query
			if($res = db_query($sql) && db_affected_rows($res) == 1){
				return true;
			}
			// ELSE there was an error
			else{
				$errors['err'] = 'Unable to update scheduled ticket. Internal error';
			}

		}
		// ELSE we're creating a new scheduled
		else {
			
			$sql = "INSERT INTO 
						" . SCHEDULED_TABLE . "
					SET 
						" . $attributes . "
						,created = NOW()";

			// IF we're able to execute the query
			if(db_query($sql)){
				return true;
			}
			// ELSE there was an error
			else{
				$errors['err'] = 'Unable to create scheduled ticket. Internal error';
			}
			
		}

		return false;
	}

	/**
	 * @param 	$vars		the orgainzations's information
	 * @param 	&$errors	error reporting
	 *
	 * @return 	boolean
	 * 
	 * returns true or false based on success or failure
	 */
	public function update($vars, &$errors) {

		// IF the update fails
		if (!$this->save($this->getScheduledId(), $vars, $errors))
			return false;

		$this->reload();
		return true;
	}

	/**
	 * @param 	$vars		the vars that contain the client informationg
	 * @param 	&$errors	error reporting
	 *
	 * @return boolean
	 *
	 * returns true or false based on success or failure
	 */
	public static function create($vars, &$errors) {

		return self::save(NULL, $vars, &$errors);

	}
	
	/**
	 * deletes the scheduled ticket
	 */
	public function delete() {

		$delQuery = "DELETE FROM 
						" . SCHEDULED_TABLE . " 
					WHERE 
						" . SCHEDULED_TABLE . ".scheduled_id = " . $this->getScheduledId();
		
		return (db_query($delQuery));
	}
	
	/**
	 * @return	$interval		the recurrance interval expressed as DateInterval
	 */
	public function recurranceInterval(){
		
		// make a new date interval
		$interval = new DateInterval('PT1S');
		$interval->s = 0;
			
		// SWITCH on the interval
		switch($this->getInterval()){
				
			case "hour":
				// set the interval in hours
				$interval->h = $this->getTimeInterval();
			break;
				
			case "day":
				// set the interval in days
				$interval->d = $this->getTimeInterval();
			break;
				
			case "week":
				// set the interval in weeks
				$interval->d = $this->getTimeInterval() * 7;
			break;
				
			case "month":
				// set the interval in months
				$interval->m = $this->getTimeInterval();
			break;
				
			case "year":
				// set the interval in years
				$interval->y = $this->getTimeInterval();
			break;
				
		}
		
		return $interval;
	}
	
	/**
	 * @return 	$interval		returns the time between original start date
	 * 							and due date as a DateInterval
	 */
	public function dueDateInterval(){
		
		// make a new date interval
		$start = new DateTime($this->getStartDate());
		$due = new DateTime($this->getDueDate());
		
		return $start->diff($due);
	}
	
	/**
	 * @return	boolean		returns true or false whether the scheduled ticket should run now
	 */
	public function runNow(){
		
		// reference now
		$now = new DateTime();
		// get the start time
		$start = new DateTime($this->getStartDate());
		
		// IF this is a non recurring ticket
		if($this->isRecurring() == 0){
		
			// determine the interval between the two times
			$difference = $now->diff($start);
		
			// return if the time is within the minute
			return $difference->y == 0 && 
		       	$difference->m == 0 && 
		       	$difference->d == 0 &&
		       	$difference->h == 0 &&
		       	$difference->i == 0 &&
		       	$difference->s <= 59;
			
		}
		// ELSE this is a recurring ticket
		else{
			
			// determine the interval between the two times
			$difference = $now->diff($start);
			
			// IF we're before the start date
			if($difference->invert == 0){
				
				// too early
				return false;
				
			}
			// IF we're on the start date
			elseif($difference->y == 0 && 
		       $difference->m == 0 && 
		       $difference->d == 0 &&
		       $difference->h == 0 &&
		       $difference->i == 0 &&
		       $difference->s <= 59){
				
				// we're on
				return true;
				
			}
			// ELSE we're past the start date
			else{
				
				// clone the start time
				$recurrance = clone $start;
				// get the recurrance interval
				$interval = $this->recurranceInterval();
			
				// furture flag
				$future = false;

				// WHILE we're not past a date it should run
				while(!$future){
				
					// add the interval to the date time
					$recurrance->add($interval);
					
					// difference between now and recurrance
					$difference = $now->diff($recurrance);
					
					echo "<pre>";
					print_r($difference);
					echo "</pre>";
					
					// IF we past the date
					if($difference->invert == 1){
						
						// return if the time is within the minute
						return $difference->y == 0 && 
		       		   		   $difference->m == 0 && 
		       		   		   $difference->d == 0 &&
		       		   		   $difference->h == 0 &&
		       		   		   $difference->i == 0 &&
		       		   		   $difference->s <= 59;
						
					}
				}	
			}
		}
	}

	/**
	 * @return 	$output		puts the template variables in the input accordingly
	 */
	private function replaceVariables($input, $variables){
		
		// replace the variables
		$input = str_replace('%{assigned}', ($variables['assigned'] ? $variables['assigned'] : ''), $input);
		$input = str_replace('%{recipient}', ($variables['recipient'] ? $variables['recipient'] : ''), $input);
		$input = str_replace('%{date}', ($variables['date'] ? $variables['date'] : ''), $input);
		$input = str_replace('%{due}', ($variables['due'] ? $variables['due'] : ''), $input);
		
		return $input;
	}
	
	/**
	 * puts a copy of the ticket into the database
	 */
	public function putTicketIntoDatabse(){
		
		// reference the email address
		$email = $this->getUsername()."@buffalo.edu";
		
		// IF we can get the staff ID by username
		if(Staff::isCreated($email)){
				
			// lookup the opening staff by email
			$staff = Staff::lookupStaffByEmail($email);
			// get the staff info
			$name = $staff->getName();
			
		}
		// ELSE IF we can get the client's ID
		else if(Client::isCreated($email)){
			
			// lookup the client by email
			$client = Client::lookupClientByEmail($email);
			// get the client info
			$name = $client->getName();
			$phone = $client->getPhone();
			
		}
		
		// get the due date
		$now = new DateTime();
		$due = clone $now;
		$due->add($this->dueDateInterval());
		
		// put the varaiables
		$variables['recipient'] = $name;
		$variables['email'] = $email;
		$variables['date'] = $now->format(DATE_FORMAT_STRING);
		$variables['due'] = $due->format(DATE_FORMAT_STRING);
		
		// IF this ticket should be assigned to a staff memeber
		if($this->getStaffId() > 0){
			
			// get the staff member
			$staff = new Staff($this->getStaffId());
			// assigned variable
			$variables['assigned'] = $staff->getName();
			
		}
		// ELSEIF this ticket should be assigned to a team
		elseif($this->getTeamId() > 0){
			
			// get the team
			$team = new Team($this->getTeamId());
			// assigned variable
			$variables['assigned'] = $team->getName();
			
		}
		
		// replace the variables
		$body = $this->replaceVariables($this->getBody(), $variables);
		$subject = $this->replaceVariables($this->getSubject(), $variables);
		
		// write the query to put into the ticket table
		$ticketquery = "
				INSERT INTO
					".TICKET_TABLE."
				SET
					 ticketID = ".db_input(self::genExtRandID())."
					,dept_id = ".db_input(WEB_SERVICES_DEPT_ID)."
					,sla_id = ".db_input(SLA_ID)."
					,priority_id = ".db_input($this->getPriorityId())."
					,topic_id = ".db_input($this->getTopicId())."
					,staff_id = ".db_input($this->getStaffId())."
					,team_id = ".db_input($this->getTeamId())."
					,email = ".db_input($email)."
					,name = ".db_input($name)."
					,subject = ".db_input($subject)."
					,status = ".db_input('open')."
					,source = ".db_input($this->getSource())."
					,isoverdue = ".db_input(0)."
					,isanswered = ".db_input(0)."
					,duedate = ".db_input($due->format('Y-m-d H:i:s'))."
					,created = NOW()
					,updated = NOW()
					,url = ".db_input($this->getUrl());

		// IF the query was successful
		if(($res = db_query($ticketquery) && db_affected_rows() == 1)){
			
			// get the Id inserted
			$id = db_insert_id();
			
			// put the first entry in
			$threadquery = "
				INSERT INTO
					".TICKET_THREAD_TABLE."
				SET
					 ticket_id = ".db_input(db_insert_id())."
					,staff_id = ".db_input(0)."
					,thread_type = ".db_input('M')."
					,poster = ".db_input($name)."
					,title = ".db_input($subject)."
					,body = ".db_input($body)."
					,created = NOW()
					,updated = NOW()
			";
			
			// put the autocreated event in
			$evenquery = "
				INSERT INTO 
					".TICKET_EVENT_TABLE."
				SET
					 ticket_id = ".db_input($id)."
					,staff_id = ".db_input($this->getStaffId())."
					,team_id = ".db_input($this->getTeamId())."
					,dept_id = ".db_input(WEB_SERVICES_DEPT_ID)."
					,topic_id = ".db_input($this->getTopicId())."
					,state = 'created'
					,staff = 'SYSTEM'
					,annulled = 0
					,timestamp = NOW()
			";
		
			// IF the query wasn't successful
			if(!($res = db_query($threadquery) && db_affected_rows() == 1)){
				throw new Exception('Unable to put thread entry into databse');
			}
			
			// IF the query wasn't successful
			if(!($res = db_query($evenquery) && db_affected_rows() == 1)){
				throw new Exception('Unable to put event into databse');
			}
			
		}
		// ELSE something went wrong
		else{
			throw new Exception('Unable to put ticket into databse');
		}

		// get the ID that was inserted
		$this->sendEmails($id, $subject, $body);

        return $id;
	}

	/**
	 * sends out the appropriate emails if
	 */
	public function sendEmails($id, $ticketSubject, $ticketBody){
				
		// reference the time
		$now = new DateTime();
			
		// reference email components
		$subj = "Auto Ticket Generator";
		$body = "
		Auto Ticket Generator Cron-
			
		A new ticket has been created. See the following information below:
			
		DateTime:
		".$now->format(DATE_FORMAT_STRING)."
		Username:
		".$this->getUsername()."
		Subject:
		".$ticketSubject."
		Body:
		".$ticketBody."
			
			
		To view more about this ticket please click the following link:
			
		".HOME_URL."scp/tickets.php?id=".$id;
		$from = "AutoTicketGenerator@buffalo.edu";
				
		// SWITCH on the notifications
		switch($this->getNotifications()){
			
			// assigned staff members
			case "assigned":
				
				// IF this ticket should be assigned to a staff memeber
				if($this->getStaffId() > 0){
			
					// get the staff member
					$staff = new Staff($this->getStaffId());
					// send them an email
					Mailer::sendmail($staff->getEmail(), $subj, $body, $from);
			
				}
				// ELSEIF this ticket should be assigned to a team
				elseif($this->getTeamId() > 0){
			
					// get the team
					$team = new Team($this->getTeamId());
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
				
			break;
			
			// all staff members
			case "all":
			
				// get all of the staff members
				$allStaff = Staff::getAvailableStaffMembers();
				
				// FOREACH of the staff
				foreach($allStaff as $num => $id){
				
					// get the staff member
					$staff = new Staff($id);
					// send them an email
					Mailer::sendmail($staff->getEmail(), $subj, $body, $from);
				}
				
			break;
			
			// no staff members
			case "none":
				// don't send any emails
			break;
			
		}
		
	}

	/**
	 * @return	$array 		an array of all the scheduled objects
	 */
	public static function getAllScheduled(){
		
		// get all scheduled
		$query = "
			SELECT
				*
			FROM "
				.SCHEDULED_TABLE;

		// perform the query
		$array = db_assoc_array(db_query($query));
		// make a new array object
		$ret = array();
		
		// FOREACH of the scheduled
		foreach($array as $num => $assoc){
			
			// push the object onto the array
			array_push($ret, self::ScheduledFromArray($assoc));
			
		}
		
		return $ret;
	}
	
	/**
	 * generates a random ticket ID. See from Ticket::genExtRandID()
	 */
    private static function genExtRandID() {
        global $cfg;

        //We can allow collissions...extId and email must be unique ...so same id with diff emails is ok..
        // But for clarity...we are going to make sure it is unique.
        $id=Misc::randNumber(EXT_TICKET_ID_LEN);
        if(db_num_rows(db_query('SELECT ticket_id FROM '.TICKET_TABLE.' WHERE ticketID='.db_input($id))))
            return self::genExtRandID();

        return $id;
    }

	/******************************************* ACCESSORS AND MUTATORS *******************************************/
	
	public function getScheduledId(){
		return $this->scheduled_id;
	}
	
	public function setScheduledId($scheduled_id){
		$this->scheduled_id = $scheduled_id;
	}
	
	public function getPriorityId(){
		return $this->priority_id;
	}
	
	public function setPriorityId($priority_id){
		$this->priority_id = $priority_id;
	}
	
	public function getTopicId(){
		return $this->topic_id;
	}
	
	public function setTopicId($topic_id){
		$this->topic_id = $topic_id;
	}
	
	public function getStaffId(){
		return $this->staff_id;
	}
	
	public function setStaffId($staff_id){
		$this->staff_id = $staff_id;
	}
	
	public function getTeamId(){
		return $this->team_id;
	}
	
	public function setTeamId($team_id){
		$this->team_id = $team_id;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function setUsername($username){
		$this->username = $username;
	}
	
	public function getSource(){
		return $this->source;
	}
	
	public function setSource($source){
		$this->source = $source;
	}
	
	public function getUrl(){
		return $this->url;
	}
	
	public function setUrl($url){
		$this->url = $url;
	}
	
	public function getSubject(){
		return $this->subject;
	}
	
	public function setSubject($subject){
		$this->subject = $subject;
	}
	
	public function getBody(){
		return $this->body;
	}
	
	public function setBody($body){
		$this->body = $body;
	}
	
	public function getStartDate(){
		return $this->startdate;
	}
	
	public function setStartDate($startdate){
		$this->startdate = $startdate;
	}
	
	public function getStartTime(){
		return $this->starttime;
	}
	
	public function setStartTime($starttime){
		$this->starttime = $starttime;
	}
	
	public function getDueDate(){
		return $this->duedate;
	}
	
	public function setDueDate($duedate){
		$this->duedate = $duedate;
	}
	
	public function getDueTime(){
		return $this->duetime;
	}
	
	public function setDueTime($duetime){
		$this->duetime = $duetime;
	}
	
	public function getNotifications(){
		return $this->notifications;
	}
	
	public function setNotifications($notifications){
		$this->notifications = $notifications;
	}
	
	public function isRecurring(){
		return $this->isrecurring;
	}
	
	public function setReccuring($isrecurring){
		$this->isrecurring = $isrecurring;
	}
	
	public function getInterval(){
		return $this->interval;
	}
	
	public function setInterval($interval){
		$this->interval = $interval;
	}
	
	public function getTimeInterval(){
		return $this->time_interval;
	}
	
	public function setTimeInterval($time_interval){
		$this->time_interval = $time_interval;
	}
	
	public function getUpdated(){
		return $this->updated;
	}
	
	public function setUpdated($updated){
		$this->updated = $updated;
	}
	
	public function getCreated(){
		return $this->created;
	}
	
	public function setCreated($created){
		$this->created = $created;
	}
	
}
?>
