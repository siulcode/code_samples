<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getrecentmypolls.php
	Created: 02/06/08 by Luis Lopez
*/

class KBQuery_getrecentmypolls extends KBResultsetQuery {
	function KBQuery_getrecentmypolls() {
		parent::KBResultsetQuery('getrecentmypolls',
			"SELECT MpqQuestion, MpqID " .
			"FROM	MyPollQ " .
			"WHERE	MpqUID=@@iUID@@ AND MpqStatus = 1 ORDER BY MpqModified DESC",
			array('iUID' => 'int'));
	}
}

?>