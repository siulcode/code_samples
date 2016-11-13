<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: www/query/query/friends/get/query.list.php
	Created: 03/07/08 by Luis Lopez
*/
class KBQuery_friends_get_list extends KBResultsetQuery {
	function KBQuery_friends_get_list() {
		parent::KBResultsetQuery('friends_get_list',
		"	SELECT	TOP @@iRowCount@@ ULegalName AS Username
			FROM 	FriendList INNER JOIN Users ON FlKnUID=UID
			WHERE	FlUID=@@iUID@@
			  AND	FlID NOT IN (
			  			SELECT	TOP @@iOffset@@ FlID
						FROM	FriendList INNER JOIN Users ON FlKnUID=UID
						WHERE	FlUID=@@iUID@@
						ORDER	BY FlModified DESC)
			AND FlStatus = 2
			ORDER	BY FlModified DESC",
			array(	'iUID' => 'usernametouid',
					'iRowCount' => 'int',										
					'iOffset' => 'int'));
	}
}

?>
