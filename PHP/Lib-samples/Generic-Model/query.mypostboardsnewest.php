<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboards.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_mypostboardsnewesttopics extends KBResultsetQuery {
	function KBQuery_mypostboardsnewesttopics() {
		parent::KBResultsetQuery('mypostboardsnewesttopics',
			"SELECT PbID, PbName, Postboards.PbTopics AS NumberOfTopics, 
					(SELECT SUM(PtMessages) FROM PbTopics WHERE PtPbID=PbID) AS NumberOfMessages
				FROM Postboards
				WHERE PbSecurity < 3 AND PbUID={$iUID}
				ORDER BY PbCreated DESC",
			array('numitems' => 'int'));
	}
}

?>
