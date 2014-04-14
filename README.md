ScheduledTickets
================

Scripts to have Tickets be scheduled for sometime in the future or recur every so often. To have tickets recur run the script at <code>php/inc/scheduled.cron.php</code> every 30 minutes using cron with the following syntax:

<code>0,30 * * * * php {PATH TO CRON} > /tmp/phpjob.txt 2>&1</code>

This will run through all the scheduled tickets and determine if they should be ran and then handle them accordingly.  

add these functions to your class.client.php:

	static function lookupClientByEmail($email){
		
		$clientQuery = 'SELECT client_id FROM ' . CLIENT_TABLE . ' WHERE email='.db_input($email);
		
		// IF we can find the ID
        if(($res=db_query($clientQuery)) && db_num_rows($res)) {
            
			// there should only be one row	
            list($id)=db_fetch_row($res);
			
			// construct the client
			return new Client($id);
			
        } 
        // ELSE we can't find the client's ticket
        else {
        	return FALSE;
        }
	}
	
	static function getAllClients($isActive=true){
		
		// make the query
		$sql = 'SELECT client_id, CONCAT_WS(", ",lastName,firstName) as name 
				FROM '. CLIENT_TABLE . 
				(($isActive) ? ' WHERE is_active=1' : '') . ' 
				ORDER BY name';
		
		$clients = db_query($sql);
		$returnArray = new ArrayObject();
		
		// WHILE we still have more clients
		while (list($client_id,$client_name) = db_fetch_row($clients)){
			$returnArray->append(array('client_id' => $client_id, 'name' => $client_name));
		}
		
		return $returnArray;
	}
	
</code>

Here are supported variables for both the subject line of the ticket and Body part of the ticket:

<div style="width:300px;">
    <h2>Scheduled Ticket Variables</h2>
    Listed are variables used in Scheduled Tickets
    <br/>
    <table width="100%" border="0" cellspacing=1 cellpadding=2>
        <tr><td valign="top"><b>Base Variables</b></td></tr>
        <tr>
            <td width="55%" valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="130">%{assigned}</td><td>Staff or team name</td></tr>
                    <tr><td>%{recipient}</td><td>Full name on ticket</td></tr>
                    <tr><td>%{date}</td><td>Date created</td></tr>
                    <tr><td>%{due}</td><td>Due date</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

p.s. you can have ajax.content.php return the above HTML code to have a pop over in the scheduled.inc.php Supported Variables link.

<h2>File Placement</h2>

Here is where all the files in this project should be placed in your osTicket installation:

<code>/php/scheduled.php				->	/scp/scheduled.php</code><br />
<code>/php/inc/class/class.scheduled.php		->	/include/class.scheduled.php</code><br />
<code>/php/inc/interface/scheduled-list.inc.php	->	/include/staff/scheduled-list.inc.php</code><br />
<code>/php/inc/interface/scheduled.inc.php		->	/include/staff/scheduled.inc.php</code><br />
<code>/php/inc/interface/scripts.inc.php		->	/include/staff/footer.inc.php	(PUT AT BOTTOM)</code><br />
<code>/php/inc/scheduled.cron.php			->	/api/scheduled.cron.php</code>

this line needs to be placed in nav to make a link in your nav bar:

<code>$subnav[]=array('desc'=>'Scheduled Tickets','href'=>'scheduled.php','iconclass'=>'scheduled');</code>

<h2>Table Structure</h2>

Here is the table structure for the scheduled tickets table:

<pre>
CREATE TABLE IF NOT EXISTS `ost_scheduled_tickets` (
  `scheduled_id` int(11) NOT NULL AUTO_INCREMENT,
  `priority_id` int(10) NOT NULL,
  `topic_id` int(10) NOT NULL,
  `staff_id` int(10) NOT NULL,
  `team_id` int(10) NOT NULL,
  `username` varchar(128) NOT NULL,
  `source` varchar(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  `subject` varchar(64) NOT NULL,
  `body` text NOT NULL,
  `notifications` enum('all','assigned','none') NOT NULL,
  `startdate` datetime NOT NULL,
  `duedate` datetime NOT NULL,
  `isrecurring` int(1) NOT NULL DEFAULT '0',
  `granularity_interval` enum('hour','day','week','month','year') NOT NULL,
  `time_interval` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`scheduled_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;</pre>
