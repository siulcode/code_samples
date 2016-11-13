<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getrecentmyqbooks.php
	Created: 02/06/08 by Luis Lopez
*/

class KBQuery_getRecentMyQbooks extends KBResultsetQuery {
	function KBQuery_getRecentMyQbooks() {
		parent::KBResultsetQuery('getrecentmyqbooks',
			"SELECT TbName, TbID " .
			"FROM	TagBooks " . 
			"WHERE	TbUID=@@iUID@@ AND TbStatus < 10 " .
			"ORDER	BY TbCreated DESC",
			array('iUID' => 'int'));
	}
}

?>