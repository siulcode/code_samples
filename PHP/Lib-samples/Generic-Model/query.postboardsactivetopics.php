<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboardsactivetopics.php
	Created: 01/23/08 by Luis Lopez
*/

class KBQuery_MyPostboardsActiveTopics extends KBResultsetQuery {
	function KBQuery_MyPostboardsActiveTopics() {
		parent::KBResultsetQuery('mypostboardsactivetopics',
			"SELECT TOP 20 PbID, PbName, PtID, PtTitle, PtDate, PtMessages, ULegalName, UIcon " .
			"FROM	(Postboards INNER JOIN PbTopics ON PtPbID=PbID) INNER JOIN Users ON PtUID=UID " .
			"WHERE	PbSecurity=1 AND PtDate > DATEADD(day,-3,GETDATE()) " .
			"ORDER	BY PtMessages DESC",
			array('numitems' => 'int'));
	}
}

?>
