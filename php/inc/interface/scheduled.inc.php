<?
require_once(INCLUDE_DIR . 'class.scheduled.php');
	
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($scheduled && $_REQUEST['a']!='add') {
    $title='Update Scheduled Ticket';
    $action='update';
    $submit_text='Save Changes';
	$info = $scheduled->getInfo();
    $qstr.='&id='.$scheduled->getScheduledId();
} else {
    $title='Add New Scheduled Ticket';
    $action='create';
    $submit_text='Add Scheduled Ticket';
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>

<form action="scheduled.php?<?php echo $qstr; ?>" method="post" id="save">
 	<?php csrf_token(); ?>
 	<input type="hidden" name="do" value="<?php echo $action; ?>">
 	<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 	<input type="hidden" name="scheduled_id" value="<?php echo $info['scheduled_id']; ?>">
    <h2><?=($scheduled ? $scheduled->getSubject() : 'New Scheduled Ticket')?></h2>
	<table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
		
		<!------------------------>
		<!-- HEADER				-->
		<!------------------------>
    	<thead>
        	<tr>
            	<th colspan="2">
                	<h4>Scheduled Ticket</h4>
                	<em><strong>User Information</strong>:</em>
            	</th>
        	</tr>
    	</thead>
    	
    	<!------------------------>
		<!-- INFO				-->
		<!------------------------>
		<tbody>
          <tr>
            <td width="200" class="required">
                Username
            </td>
            <td>
            	<input type="text" name="username" size=60 value="<?=$info['username']?>">
              	&nbsp;<font class="error">*&nbsp;
              	<?=$errors['username'] ?>
              	</font>
             </td>
          </tr>
          <tr>
            <th colspan="2">
                <em><strong>Ticket Information &amp; Options</strong>:</em>
            </th>
          </tr>
          <tr>
            <td width="160" class="required">
                Ticket Source:
            </td>
            <td>
                <select name="source">
                    <option value="" selected >&mdash; Select Source &mdash;</option>
                    <option value="Phone" <?php echo ($info['source']=='Phone')?'selected="selected"':''; ?>>Phone</option>
                    <option value="Email" <?php echo ($info['source']=='Email')?'selected="selected"':''; ?>>Email</option>
                    <option value="Other" <?php echo ($info['source']=='Other')?'selected="selected"':''; ?>>Direct</option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
          </tr>
        <tr>
            <td width="160" class="required">
                Site:
            </td>
            <td>
                <select name="topic_id" id="siteSelect">
                    <option value="">&mdash; Select Site &mdash;</option>
                    <?php
                    if($topics=Topic::getHelpTopics()) {
                        foreach($topics as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['topic_id']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['topic_id']; ?></font>
            </td>
        </tr> 
        <tr>
            <td width="160" class="required">
                URL:
            </td>
            <td>
                <input type="text" size="100" name="url" id="url" value="<?php echo $info['url']; ?>"
                    autocomplete="off" autocorrect="off" autocapitalize="off">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['url']; ?></span>
            </td>
        </tr>
         <tr>
            <td width="160" class="required">
                Creation Date:
            </td>
            <td>
                <input class="dp" id="startdate" name="startdate" value="<?php echo Format::htmlchars($info['startdate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['starttime'])
                    list($hr, $min)=explode(':', $info['starttime']);
				
                echo Misc::createDateDropdown($hr, $min, 'starttime');
                ?>
                <font class="error"><b>*</b>&nbsp;
                	<?php echo $errors['startdate']; ?> &nbsp; <?php echo $errors['starttime']; ?></font>
            </td>
         </tr>
        <tr>
            <td width="160" class="required">
                Priority:
            </td>
            <td>
                <select name="priority_id" id="complete">
                    <?php
                    if($priorities=Priority::getPriorities()) {
                        foreach($priorities as $id =>$name) {
                        	 echo ($name != "Low") ? sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['priority_id']==$id)?'selected="selected"':'',$name) : "";
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['priority_id']; ?></font>
            </td>
         </tr>
         <tr id="dueDateRow">
            <td width="160" class="required">
                Due Date:&nbsp;
            </td>
            <td>
                <input class="dp" id="date" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['duetime'])
                    list($hr, $min)=explode(':', $info['duetime']);
				
                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                <div id="fourOclock" style="display: inline">4:00 PM&nbsp;</div>
                	<div id="recurringDueDates" style="display: inline">
                		<font class="warn"><i>(Due dates will be relative for recurring tickets)</i>&nbsp;</font>
                	</div>
                	<font class="error">
                		<b>*</b>&nbsp;
                		<?php echo $errors['duedate']; ?> &nbsp; <?php echo $errors['duetime']; ?>
                	</font>
            </td>
        </tr>
		<tr>
            <td width="160">Assign To:</td>
            <td>
                <select id="assignId" name="assignId">
                    <option value="0" selected="selected">&mdash; Select Staff Member OR a Team &mdash;</option>
                    <?php
                    if(($users=Staff::getAvailableStaffMembers())) {
                        echo '<OPTGROUP label="Staff Members ('.count($users).')">';
                        foreach($users as $id => $name) {
                            $k=$id;
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['staff_id']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }

                    if(($teams=Team::getActiveTeams())) {
                        echo '<OPTGROUP label="Teams ('.count($teams).')">';
                        foreach($teams as $id => $name) {
                            $k=$id;
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['team_id']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>&nbsp;<span class='error'>&nbsp;<?php echo $errors['assignId']; ?></span>
            </td>
            
		<!------------------------>
		<!-- ISSUE		 		-->
		<!------------------------>
		<tr>
            <th colspan="2">
                <em><strong>Issue</strong>: The user will be able to see the issue summary below and any associated responses. <a class="tip" href="scheduledTicketVariables.txt">Supported Variables</a></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <div>
                    <em><strong>Subject</strong>: Issue summary </em> &nbsp;<font class="error">*&nbsp;<?php echo $errors['subject']; ?></font><br>
                    <input type="text" name="subject" size="60" value="<?php echo $info['subject']; ?>">
                </div>
                <div><em><strong>Issue</strong>: Details on the reason(s) for opening the ticket.</em> <font class="error">*&nbsp;<?php echo $errors['body']; ?></font></div>
                <textarea name="body" cols="21" rows="8" style="width:80%;"><?php echo $info['body']; ?></textarea>
            </td>
        </tr>
        </tr>
        
		<!------------------------>
		<!-- EMAIL NOTIFICATONS -->
		<!------------------------>
        <tr>
            <th colspan="2">
                <em><strong>Email Notifications</strong>:</em>
            </th>
        </tr>
        <tr>
           <td width="160" class="required">
                Notifications:
           </td>
           <td>
				<input type="radio" name="notifications" value="all" <?=(($info['notifications'] == "all") ? "checked" : "")?>>Notfiy All&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="notifications" value="assigned" <?=(($info['notifications'] == "assigned") ? "checked" : "")?>>Notify Assigned&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="notifications" value="none" <?=(($info['notifications'] == "none") ? "checked" : "")?>>No Notifications
				<font class="error"><b>*</b>&nbsp;
                	<?php echo $errors['notifications']; ?></font>
           </td>
        </tr>
        
		<!------------------------>
		<!-- SCHEDULING 		-->
		<!------------------------>
        <tr>
            <th colspan="2">
                <em><strong>Scheduling</strong>: Defines how the ticket recurs</em>
            </th>
        </tr>
        <tr>
           <td width="160" class="required">
                Is Reccuring:
           </td>
           <td>
				<input type="radio" name="isrecurring" value="0" <?=(($info['isrecurring'] == 0) ? "checked" : "")?>>One Time Ticket&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="isrecurring" value="1" <?=(($info['isrecurring'] == 1) ? "checked" : "")?>>Recurring Ticket
				<font class="error"><b>*</b>&nbsp;
                	<?php echo $errors['recurring']; ?></font>
           </td>
        </tr>
        <tr id="intervalRow">
           <td width="160" class="required">
                Interval:
           </td>
           <td>
                <select id="intervalSelect" name="interval">
                	<option value="" selected="selected">Select a time interval</option>
					<option value="hour" <?php echo ($info['interval']=='hour')?'selected="selected"':''; ?>>Hour</option>
					<option value="day" <?php echo ($info['interval']=='day')?'selected="selected"':''; ?>>Day</option>
                    <option value="week" <?php echo ($info['interval']=='week')?'selected="selected"':''; ?>>Week</option>
                    <option value="month" <?php echo ($info['interval']=='month')?'selected="selected"':''; ?>>Month</option>
                    <option value="year" <?php echo ($info['interval']=='year')?'selected="selected"':''; ?>>Year</option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['interval']; ?></font>
           </td>
        </tr>
        <tr id="timeIntervalRow">
           <td width="160" class="required">
                Recur Every:
           </td>
           <td>
                <select id="timeIntervalSelect" name="time_interval">
					<option value="" selected="selected">Select Time</option>
					<?
					// IF this is an update page
					if($_REQUEST['a'] == "update"){
							
						// SWITCH on options
						switch($info['interval']){
			
							case "hour":
								// FOR all hours
								for ($i = 1; $i < 24; $i++){?>
									<option value="<?=$i?>" <?=($info['time_interval'] == $i ? 'selected="selected"' : '')?>><?=$i . " hours"?></option>
								<?}
							break;
			
							case "day":
								// FOR all days
								for ($i = 1; $i < 7; $i++){?>
									<option value="<?=$i?>" <?=($info['time_interval'] == $i ? 'selected="selected"' : '')?>><?=$i . " days"?></option>
								<?}
							break;
			
							case "week":
								// FOR all days
								for ($i = 1; $i < 5; $i++){?>
									<option value="<?=$i?>" <?=($info['time_interval'] == $i ? 'selected="selected"' : '')?>><?=$i . " weeks"?></option>
								<?}
							break;
			
							case "month":
								// FOR all days
								for ($i = 1; $i < 12; $i++){?>
									<option value="<?=$i?>" <?=($info['time_interval'] == $i ? 'selected="selected"' : '')?>><?=$i . " months"?></option>
								<?}
							break;
			
							case "year":
								// FOR all days
								for ($i = 1; $i < 6; $i++){?>
									<option value="<?=$i?>" <?=($info['time_interval'] == $i ? 'selected="selected"' : '')?>><?=$i . " years"?></option>
								<?}
							break;
			
						}
						
					}
					?>
                </select><span id="timeIntervalLabel"></span>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['time_interval']; ?></font>
           </td>
        </tr>
    	</tbody>
        </table>
        
		<!------------------------>
		<!-- BUTTONS			-->
		<!------------------------>
		<p style="padding-left:225px;">
    		<input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    		<input type="reset"  name="reset"  value="Reset">
    		<input type="button" name="cancel" value="Cancel" onclick='window.location.href="scheduled.php"'>
		</p>
  </form>
</table>
