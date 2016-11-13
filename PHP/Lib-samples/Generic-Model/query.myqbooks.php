<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.myqbooks.php
	Created: 02/06/08 by Luis Lopez
*/

class KBQuery_myqbooks extends KBResultsetQuery {
	function KBQuery_myqbooks() {
		parent::KBResultsetQuery('myqbooks',
			"SELECT TbID, TbName, TbStatus, TbCreated, " .
			"		(SELECT Count (DISTINCT TaUID) " . 
			"		FROM TagAnswers INNER JOIN TagQuestions ON TaTqID=TqID " .
			"		WHERE TqTbID=TbID) As AnswerCount " .
			"FROM	TagBooks " .
			"WHERE	TbUID=@@uid@@ " .
			"ORDER	BY TbCreated DESC",
			array('uid' => 'usernametouid'));
	}
}

?>
