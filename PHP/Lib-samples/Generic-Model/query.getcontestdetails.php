<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getcontestdetails.php
	Created: 02/28/08 by Luis Lopez
*/

class KBQuery_getcontestdetails extends KBResultsetQuery {
	function KBQuery_getcontestdetails() {
		parent::KBResultsetQuery('getcontestdetails',
			"SELECT TOP 3 * FROM Contests WHERE CoID=@@coid@@",
			array('coid' => 'int'));
	}
}

?>