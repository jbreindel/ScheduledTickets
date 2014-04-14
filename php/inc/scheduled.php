<?php
/*********************************************************************
 *
 * FILE:				scheduled.php
 * AUTHOR:				Jake Breindel
 * DATE:				1-15-2014
 *
 * DESRIPTION:
 *  performs operations on scheduled tickets.
 *
 **********************************************************************/

require('admin.inc.php');
require(INCLUDE_DIR . 'class.scheduled.php');

$scheduled=null;
if($_REQUEST['id'] && !($scheduled = new Scheduled($_REQUEST['id'])))
    $errors['err']='Unknown or invalid Scheduled ID.';

if($_POST){
	
    switch(strtolower($_POST['do'])){
		
		case 'update':
			
			// IF there isn't a client
            if(!$scheduled){
                $errors['err']='Unknown or invalid scheduled ticket.';
            }
            // ELSE IF the post new site is set
            elseif($scheduled->update($_POST,$errors)){
            		
				// modified successfully
				$msg = 'Successfully modified Scheduled ticket';
				
            }else{
            	// report an error
				$errors['err']='Error updating Scheduled ticket. Try again!';
            	return false;
            }

		break;
			
        case 'create':
            if(Scheduled::create($_POST,$errors)){
                $msg='Scheduled added successfully';
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']='Unable to add Scheduled. Correct error(s) below and try again.';
            }
            break;
			
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = 'You must select at least one Scheduled';
            } else {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'delete':
						
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t= new Scheduled($v)) && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = 'Selected Scheduled deleted successfully';
                        elseif($i>0)
                            $warn = "$i of $count selected Scheduled deleted";
                        elseif(!$errors['err'])
                            $errors['err']  = 'Unable to delete selected Scheduled ticket';

                        break;
                    default:
                        $errors['err']='Unknown action - get technical help.';
                }
            }
            break;
			
        default:
            $errors['err']='Unknown command/action';
            break;
    }
}

$page='scheduled-list.inc.php';
if($scheduled || ($_REQUEST['a'] && $_REQUEST['a'] == 'add'))
    $page='scheduled.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');

?>
