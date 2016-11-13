<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.postboardsnewesttopics.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_PostboardsNewestTopics extends KBResultsetQuery {
	function KBQuery_PostboardsNewestTopics() {
		parent::KBResultsetQuery('postboardsnewesttopics',
			"SELECT TOP @@numitems@@ PbID, PbName, PtID, PtTitle, PtDate, PtMessages, ULegalName, UIcon " .
			"FROM (Postboards INNER JOIN PbTopics ON PtPbID=PbID) INNER JOIN Users ON PtUID=UID " .
			"WHERE PbSecurity=1 ORDER BY PtDate DESC",
			array('numitems' => 'int'));
	}
}

?>
