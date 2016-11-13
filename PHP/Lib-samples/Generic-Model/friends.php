<?php
/*
 * Copyright (c) 2007-2008 Kiwibox Media, Inc.
 * File: friends.php
 * Created: 02/22/2008 by Ivan Tumanov
 * Modified: Luis Lopez, Christian Serna
 */
require_once('include.config.php');
require_once('include.subsite.php');
require_once('include.friends.php');
require_once('include.kiwinotes.php');
require_once(FRAMEWORK_ROOT . '4info.php');

initRuntime();
if ($profileUsername == '') {
	if ($currentUser->isLoggedIn()) {
		$profileUsername = $currentUser->getUsername();
	} else {
		header('Location: ' . $sKiwiboxRoot);
        exit();
	}
}


if(isset($_POST['currentUser'])){
   friendRankForm :: ProcessPOST();
}

if((isset($_POST['delete']))&&($_POST['delete'] = 1)){
   friendDeleteForm :: ProcessPOST();
}


startKiwiboxUI($profileUsername . "'s Profile");

ob_start();
	startLargeRightColumn();
	switch ($urlContext){
		case 'addfriend':
			$friend = fetchURLParameter_text(0, 'friend', '');
			displayFriendRequest($friend);
			break;
		case 'membersonline':
			$friend = fetchURLParameter_text(0, 'friend', '');
			displayFriendRequest($friend);
			break;
		case 'acceptfriend':
			$friend = fetchURLParameter_text(0, 'friend', '');
			$hash = fetchURLParameter_text(1, 'hash', '');
			displayAcceptFriend($friend, $hash);
			break;
		case 'ignorefriend':
			$friend = fetchURLParameter_text(0, 'friend', '');
			$hash = fetchURLParameter_text(1, 'hash', '');
			displayIgnoreFriend($friend, $hash);
			break;
		case 'editfriends':
			editFriendsList($profileUsername);
			break;
		default:
			$page = fetchURLParameter_numeric(0, 'page', 1);
			$offset = (($page - 1) * 30);
			displayFriendsList($profileUsername, $page, $offset,'',TRUE);
	}
	$outputRight = ob_get_clean();
	ob_start();
	startProfileLeftColumn();
		moduleDisplayPersonalInfo($profileUsername);
		moduleDisplayAboutMe($profileUsername);
	endLeftColumn();
$outputleft = ob_get_clean();
echo $outputleft;
echo $outputRight;
endKiwiboxUI();
?>
