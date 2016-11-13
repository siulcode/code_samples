<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboardsnewestpostboards.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_MyPostboardsNewestPostboards extends KBResultsetQuery {
	function KBQuery_MyPostboardsNewestPostboards() {
		parent::KBResultsetQuery('mypostboardsnewestpostboards',
			"SELECT TOP @@numitems@@ PbID, PbName, PbCreated, PbTopics, ULegalName, UIcon " .
			"FROM Postboards INNER JOIN Users ON PbUID=UID " .
			"WHERE PbSecurity=1 ORDER BY PbCreated DESC",
			array('numitems' => 'int'));
	}
}

?>
