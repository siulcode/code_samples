<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.madlibs.php
	Created: 01/31/08 by Luis Lopez
*/

class KBQuery_madlibs extends KBResultsetQuery {
	function KBQuery_madlibs() {
		parent::KBResultsetQuery('madlibs',
			"SELECT * FROM Madlibs ORDER BY MlLastUpdated DESC");
	}
}

?>