<?php
/*
	Copyright (c) 2007-2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getfriendslist.php
	Created: 01/21/08 by Luis Lopez
*/

class KBQuery_getfriendslist extends KBResultsetQuery {
	function KBQuery_getfriendslist() {
		parent::KBResultsetQuery('getfriendslist',
			"SELECT FlID, FlKnUID, FlNickname, FlFirstName, FlMiddleName, FlLastName, " .
			"		FlEmail1, ULegalName, UIcon, FlICQ, FlYahoo, FlMSN, FlAOL " .
			"FROM	FriendList LEFT JOIN Users ON FriendList.FlKnUID=Users.UID " .
			"WHERE	FlUID=@@uid@@ " .
			"ORDER	BY FlNickname ASC" ,
			array('uid' => 'usernametouid'));
	}
}

?>
