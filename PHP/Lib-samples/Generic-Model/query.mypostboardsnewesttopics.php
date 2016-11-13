<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypostboardsnewesttopics.php
	Created: 03/14/08 by Luis Lopez
*/

class KBQuery_MyPostboardsNewestTopics extends KBResultsetQuery {
	function KBQuery_MyPostboardsNewestTopics() {
		parent::KBResultsetQuery('mypostboardsnewesttopics',
			"	SELECT PbID, PbName, Postboards.PbTopics AS NumberOfTopics, 
					(SELECT SUM(PtMessages) FROM PbTopics WHERE PtPbID=PbID) AS NumberOfMessages
				FROM Postboards
				WHERE PbSecurity < 3 AND PbUID=@@uid@@
				ORDER BY PbCreated DESC",
			array('uid' => 'usernametouid'));
	}
}

?>
