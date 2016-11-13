<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getadvicecategories.php
	Created: 03/04/08 by Luis Lopez
*/

class KBQuery_getadvicecategories extends KBResultsetQuery {
	function KBQuery_getadvicecategories() {
		parent::KBResultsetQuery('getadvicecategories',
			"SELECT QacID, QacName FROM QACat ORDER BY QacDirID, QacName");
	}
}

?>