<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.archivesissuelist.php
	Created: 02/27/08 by Luis Lopez
*/

class KBQuery_archivesissuelist extends KBResultsetQuery {
	function KBQuery_archivesissuelist() {
		parent::KBResultsetQuery('archivesissuelist',
			"SELECT	IsID, IsName, IsDateFrom " .
			"FROM	Issues ORDER BY IsDateFrom DESC");
	}
}
?>