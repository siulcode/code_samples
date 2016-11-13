<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getrecentmypostboards.php
	Created: 02/06/08 by Luis Lopez
*/

class KBQuery_getRecentMyPostboards extends KBResultsetQuery {
	function KBQuery_getRecentMyPostboards() {
		parent::KBResultsetQuery('getrecentmypostboards',
			"SELECT PbName, PbID " .
			"FROM	Postboards " .
			"WHERE	PbUID=@@iUID@@ AND PbSecurity < 3 " .
			"ORDER	BY PbCreated DESC",
			array('iUID' => 'int'));
	}
}

?>