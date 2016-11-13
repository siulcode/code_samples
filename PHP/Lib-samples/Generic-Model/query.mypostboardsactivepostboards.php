<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboardsactivepostboards.php
	Created: 01/23/08 by Luis Lopez
*/

class KBQuery_MyPostboardsActivePostboards extends KBResultsetQuery {
	function KBQuery_MyPostboardsActivePostboards() {
		parent::KBResultsetQuery('myPostboardsactivepostboards',
			"SELECT TOP 20 PbID, PbName, PbCreated, PbTopics, ULegalName, UIcon " .
			"FROM	Postboards INNER JOIN Users ON PbUID=UID " .
			"WHERE	PbSecurity=1 AND PbCreated > DATEADD(day,-7,GETDATE()) " .
			"ORDER	BY PbTopics DESC",
			array('numitems' => 'int'));
	}
}

?>
