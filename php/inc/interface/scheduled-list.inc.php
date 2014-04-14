<?
	$sortOptions=array('date'=>'startdate','username'=>'username','subject'=>'subject','recurring'=>'recurring');
	$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');

	if($_REQUEST['sort']) {
        $order_by =$sortOptions[$_REQUEST['sort']];
	}
	if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
	}
	$order_by =$order_by?$order_by:'startdate';
	$order=$order?$order:'ASC';
	$negorder=$order=='DESC'?'ASC':'DESC';
	
	$total=db_count('SELECT count(*) FROM '.SCHEDULED_TABLE.' scheduled ');
	$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
	$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
	$pageNav->setURL('scheduled.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
	//Ok..lets roll...create the actual query
	$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
	
	$query="
			SELECT
				*
			FROM 
				".SCHEDULED_TABLE." 
			ORDER BY
				$order_by 
				$order 
			LIMIT 
				".$pageNav->getStart()."
				,".$pageNav->getLimit();
	
	$res=db_query($query);
	if($res && ($num=db_num_rows($res)))
    	$showing=$pageNav->showing().' scheduled tickets';
	else
    	$showing='No scheduled tickets found!';
	
	$results = db_query($query);
	$rows = db_num_rows($results);
	
?>

	<!------------------------>
	<!-- HEADER				-->
	<!------------------------>
	<div style="width:700;padding-top:5px; float:left;">
 	<h2>Scheduled Tickets</h2>
 	</div>
	<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
    	<b><a href="scheduled.php?a=add" class="Icon newHelpTopic">Add New Scheduled Ticket</a></b>
	</div>
	<div class="clear">
	</div>
	<form action="scheduled.php" method="POST" name="scheduled" onSubmit="return checkbox_checker(document.forms['staff'],1,0);">
	<?php csrf_token(); ?>
	<input type=hidden name='a' value='delete'>
	<input type=hidden name='do' value='mass_process'>
	
	<!------------------------>
	<!-- TABLE				-->
	<!------------------------>
	<table class="list" border="0" cellspacing="1" cellpadding="2" width="940">
		<caption>&nbsp;Scheduled Tickets</caption>
		
		<!------------------------>
		<!-- HEADER				-->
		<!------------------------>
     	<thead>
        	<tr>
           	<th>&nbsp;</th>
	        <th width="70"><a href="scheduled.php?sort=username&order=<?=$negorder?>" title="<?="Sort by username " . $negorder?>">Username</a></th>
	        <th width="170"><a href="scheduled.php?sort=subject&order=<?=$negorder?>" title="<?="Sort by subject " . $negorder?>">Subject</a></th>
	        <th width="60"><a href="scheduled.php?sort=recurring&order=<?=$negorder?>" title="<?="Sort by recurring " . $negorder?>">Recurring</a></th>
	        <th width="60"><a href="scheduled.php?sort=created&order=<?=$negorder?>" title="<?="Sort by created " . $negorder?>">Created</a></th>
        	</tr>
     	</thead>
     	
		<!------------------------>
		<!-- ROWS				-->
		<!------------------------>
     	<tbody>
        <?
        $class = "row1";
        $total=0;
        if($res && ($num=db_num_rows($res))){
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['scheduled_id'],$ids)){
					$sel=true;	
				}
                ?>
            <tr class="<?=$class?>" id="<?=$row['scheduled_id']?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?=$row['scheduled_id']?>" <?=$sel?'checked':''?>  onClick="highLight(this.value,this.checked);">
                	<td><?=$row['username']?>&nbsp;</td>
                	<td><a href="scheduled.php?a=update&id=<?=$row['scheduled_id']?>" style="text-decoration: underline; color: blue;"><?=$row['subject']?></a>&nbsp;</td>
                	<td><?=($row['isrecurring'] == 1 ? "Yes" : "No")?>&nbsp;</td>
                	<td><?=Format::db_datetime($row['created'])?>&nbsp;</td>          
            	</tr>
            	<?
            	$class = ($class =='row2') ?'row1':'row2';
        	} //end of while.
        }
		else{?>
			<tr><th colspan="6"><b>No Scheduled Tickets Found</b></th></tr>
		<?}?>
		</tbody>
		
    	<?if($rows > 0){?>
    	
    		<!------------------------>
			<!-- OPTIONS			-->
			<!------------------------>
    		<tfoot>
     		<tr>
        		<td colspan="7">
            		Select:&nbsp;
            		<a id="selectAll" href="#ckb">All</a>&nbsp;&nbsp;
            		<a id="selectNone" href="#ckb">None</a>&nbsp;&nbsp;
            		<a id="selectToggle" href="#ckb">Toggle</a>&nbsp;&nbsp;
        		</td>
     		</tr>
    		</tfoot>
    		
    	<?}?>
    	
    	</table>
    	<?php
		if($res && $num){ //Show options..
    		echo '<div>&nbsp;Page:'.$pageNav->getPageLinks().'&nbsp;</div>';
		?>
		<p class="centered" id="actions">
    		<input class="button" type="submit" name="delete" value="Delete">
		</p>
		<?php
		}
		?>
	</form>
    	
    <!------------------------>
	<!-- DIALOGS			-->
	<!------------------------>
	<div style="display:none;" class="dialog" id="confirm-action">
    	<h3>Please Confirm</h3>
    	<a class="close" href="">&times;</a>
    	<hr/>
    	<p class="confirm-action" style="display:none;" id="enable-confirm">
        	Are you sure want to <b>enable</b> selected Schedule tickets?
    	</p>
    	<p class="confirm-action" style="display:none;" id="disable-confirm">
        	Are you sure want to <b>disable</b>  selected Schedule tickets?
    	</p>
    	<p class="confirm-action" style="display:none;" id="delete-confirm">
        	<font color="red"><strong>Are you sure you want to DELETE selected Schedule tickets?</strong></font>
        	<br><br>Deleted Schedule tickets CANNOT be recovered.
    	</p>
    	<div>Please confirm to continue.</div>
    	<hr style="margin-top:1em"/>
    	<p class="full-width">
        	<span class="buttons" style="float:left">
            	<input type="button" value="No, Cancel" class="close">
        	</span>
        	<span class="buttons" style="float:right">
            	<input type="button" value="Yes, Do it!" class="confirm">
        	</span>
     	</p>
    	<div class="clear"></div>
	</div>
