<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getcontestrecentwinners.php
	Created: 02/28/08 by Luis Lopez
*/

class KBQuery_getcontestrecentwinners extends KBResultsetQuery {
	function KBQuery_getcontestrecentwinners() {
		parent::KBResultsetQuery('getcontestrecentwinners',
			"SELECT CoID, CoName FROM Contests WHERE CoStatus=4 ORDER BY CoDateEnd DESC");
	}
}

?>