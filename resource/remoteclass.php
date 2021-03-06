<?php
	#remoteproject.php is the interface for adding remote projects to the deployment. 
	#Helena F Deus (helenadeus@gmail.com)
	ini_set('display_errors',0);
	if($_REQUEST['su3d']) {
		ini_set('display_errors',1);
	}
	if($_SERVER['HTTP_X_FORWARDED_HOST']!='') {
		$def = $_SERVER['HTTP_X_FORWARDED_HOST'];
	} else {
		$def = $_SERVER['HTTP_HOST'];
	}	
	if(file_exists('../config.inc.php')) {
		include('../config.inc.php');
	} else {
		Header('Location: http://'.$def.'/s3db/');
		exit;
	}
	$key = $_GET['key'];
	#Get the key, send it to check validity
	include_once('../core.header.php');
	
	if($key) {
		$user_id = get_entry('access_keys', 'account_id', 'key_id', $key, $db);
	} else { 
		$user_id = $_SESSION['user']['account_id'];
	}

	$args = '?key='.$_REQUEST['key'];
	$remoteelement = 'collection';
	$remoteelement_id = $GLOBALS['COREids'][$remoteelement];
	include('../webActions.php');

	$project_id=$_REQUEST['project_id'];

	if($project_id!='') {
		$project_info = URIinfo('P'.$project_id, $user_id, $key, $db);
	} else {
		echo "Please specify a project_id";
		exit;
	}

	#CREATE THE HEADER AND SET THE TPL FILE
	if(!$project_info['add_data']) {
		echo "User cannot create projects in this Deployment";
		exit;
	}
	
	if($_POST['submit']) {
		$s3ql=compact('user_id','db');
		$s3ql['insert']=$remoteelement;
		$s3ql['where'] = array($remoteelement_id=>$_REQUEST[$remoteelement_id], 'project_id'=>$project_id);
		//$s3ql['where']['permission_level']='200';
		$s3ql['format']='html';
		$done = S3QLaction($s3ql);
		$msg=html2cell($done);$msg = $msg[2];
		
		##ereg('<error>(.*)</error>(.*)<('.$remoteelement_id.'|message)>(.*)</('.$remoteelement_id.'|message)>', $done, $s3qlout);
		if ($msg['error_code']=='0') {
			#preg_match('[0-9]', $done, $inserted_user_id);
			$inserted_id = $msg['collection_id'];
			header('Location:'.$action['project']);
			exit;
			#insert the user in the specified groups
		} else {
			$message = $msg['message'];
		}
	}
	#pass the variables to the form
	$remote_user_id= $_POST['remote_user_id'];
	$view=$remote_user_info['view'];
	$change=$remote_user_info['change'];
	$add=$remote_user_info['add'];
	
	include '../S3DBjavascript.php';
	
	$content_width='70%';
	$account_status='Active';
	$account_type='User';
	$checked='checked';
	$loginid_required='*';
	$uname_required='*';
	$password_required='*';
	$password2_required='*';
	$default_message='* required';
	$email_warn = '*';
	
	if($message=='') {
		$message = $default_message;
	}
?>

<form method="POST" action="<?php echo $action['remote'.$remoteelement]; ?>">
	<!-- BEGIN top -->
	<table class="top" align="center">
		<tr>
			<td>
				<table class="insidecontents" align="center" width="60%">
					<tr>
						<td class="message"><br /><?php echo $message; ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<!-- END top -->
	<!-- BEGIN user_info_edit -->
	<table class="middle" width="100%"  align="center">
		<tr>
			<td>
				<table class="insidecontents" width="<?php echo $content_width ?>"  align="center" border="0">
					<tr bgcolor="#80BBFF">
						<td colspan="4" align="center"><?php echo $message ?></td>
					</tr>
					<tr>
						<td colspan="4" align="left">Remote <?php echo $GLOBALS['plurals'][$remoteelement]; ?> are <?php echo $GLOBALS['plurals'][$remoteelement] ?> that exist in other deployments of S3DB. Specify a remote <?php echo $remoteelement; ?> either by concatenating deployment_id and <?php echo $remoteelement; ?>_id (for example, D45/P33) or by concatenating URL with <?php echo $remoteelement; ?>_id (http://s3db.org/P4). For <?php echo $remoteelement; ?>s in this deployment simply indicate the ID of that Class (you must have been given permission on it beforehand)</td>
					</tr>
					<tr class="odd">
						<td class="info"><?php echo $remoteelement; ?> ID<sup class="required">*</sup></td>
						<td class="info"><input name="<?php echo $remoteelement_id; ?>" value="" size="60">&nbsp;</td>	
					</tr>	
				</table>
			</td>
		</tr>
	</table>
	<!-- END user_info_edit -->
	<!-- BEGIN bottom -->
	<table class="bottom" width="100%"  align="center">
		<tr>
			<td>
				<table class="insidecontents" width="<?php echo $content_width ?>"  align="center">
					<tr>
						<td align="left"><input type="submit" name="submit" value="Create <?php echo $remoteelement; ?>"><br /><br /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<!-- END bottom -->
</form>
<?php
	include '../footer.php';
?>