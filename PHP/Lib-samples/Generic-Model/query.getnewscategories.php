<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getnewscategories.php
	Created: 02/01/08 by Luis Lopez
*/

class KBQuery_getnewscategories extends KBResultsetQuery {
	function KBQuery_getnewscategories() {
		parent::KBResultsetQuery('getnewscategories',
			"SELECT TcID, TcName FROM TodayCats WHERE TcShow=1");
	}
}

?>
