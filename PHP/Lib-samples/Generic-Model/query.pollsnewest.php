<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.pollsnewest.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_pollsnewest extends KBResultsetQuery {
	function KBQuery_pollsnewest() {
		parent::KBResultsetQuery('pollsnewest',
			"SELECT	TOP @@numitems@@ MpqID, MpqUID, MpqQuestion, MpqModified, ULegalName, UIcon " .
			"FROM	MyPollQ INNER JOIN Users ON MpqUID=UID " .
			"WHERE	MpqStatus=1 ORDER BY MpqModified DESC",
			array('numitems' => 'int'));
	}
}

?>
