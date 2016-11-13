<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboardslatestresponses.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_MypostboardsLatestResponses extends KBResultsetQuery {
	function KBQuery_mypostboardslatestresponses() {
		parent::KBResultsetQuery('mypostboardslatestresponses',
			"SELECT TOP @@numitems@@ PbID, PmSubject, PbName, PtID, PtTitle, PtMessages, PmDate, ULegalName, UIcon " .
			"FROM	((Postboards INNER JOIN PbTopics ON PtPbID=PbID) " .
			"INNER	JOIN PbMessages ON PmPtID=PtID) " .
			"INNER	JOIN Users ON PmUID=UID " .
			"WHERE	PbSecurity=1 ORDER BY PmDate DESC",
			array('numitems' => 'int'));
	}
}

?>
