<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/*
 * @created For VirtueMart 1.1 created by Jan Pavelka
 * @info http://www.phoca.cz/
 * TEST FUNCTION OF EMAIL
 */
 
 
?>
<h3>Checking the e-mail function</h3>
<p>Add the e-mail address, where you want to send the test e-mail with test attachment (attachment 'test.pdf' should be saved in tmp folder):</p>
<form method="post" name="test-email" action="<?php echo $_SERVER['PHP_SELF'] . '?option=com_virtuemart&page=admin.test_email.php'; ?>">
	Email address: <input class="inputbox" type="text" name="to" id="to" style="width:300px" maxlength="30" value="" />	
	<input type="submit" class="button" value="Send" name="send" />
    <input type="hidden" name="vmtoken" value="<?php echo vmSpoofValue($sess->getSessionId()) ?>" />
	<input type="hidden" name="delivery_pane" value="1" />
    <input type="hidden" name="page" value="admin.test_email" />
    <input type="hidden" name="option" value="com_virtuemart" />
</form>
<?php 

$post		= array();
$post['to']	= JRequest::getVar( 'to', '', 'post', 'string', JREQUEST_ALLOWRAW );

if ($post['to'] !='') {

	$db 	= JFactory::getDBO();
	$query = 'SELECT name, email, sendEmail' .
					' FROM #__users' .
					' WHERE LOWER( usertype ) = "super administrator"';
	$db->setQuery( $query );
	$rows = $db->loadObjectList();
	$from = $rows[0]->email;

	
	// Change the time -------------------------------------
	$changedMaxExecTime		= 0;
	$standardMaxExecTime 	= ini_get('max_execution_time');
	if ($standardMaxExecTime < 120) {
		set_time_limit(120);
		$changedMaxExecTime	= 1;
	}
	// -----------------------------------------------------
	
	$sendMail  = JUtility::sendMail($from, $from, $post['to'], 'VM Email Test', 'This is a VirtueMart Email Test', true, array(), array(), JPATH_ROOT . DS .'tmp'.DS.'test.pdf', '', '');
	
	// Set back the time --------------------
	if ($changedMaxExecTime == 1) {
		set_time_limit($standardMaxExecTime);
	}
	// --------------------------------------
	$message = '';
	if (isset($sendMail->message)) {
			$message = '<p style="color:#fc0000">Error: '  . $sendMail->message . '</p>';
	} else if ($sendMail == 1) {
		$message = '<p style="color:#008040">Email was sent. Check the email box, whether the email with attachment was delivered or not.</p>';
	} else {
		$message = '<p style="color:#fc0000">Error: Email was not sent</p>';
	}
		
		echo $message;
}
