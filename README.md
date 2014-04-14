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
