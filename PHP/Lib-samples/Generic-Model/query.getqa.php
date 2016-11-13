<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getqa.php
	Created: 03/04/08 by Luis Lopez
*/

class KBQuery_getqa extends KBResultsetQuery {
	function KBQuery_getqa() {
		parent::KBResultsetQuery('getqa',
			"SELECT TOP 10 QaqID, QaqName, QaqLocation, QaqDateSubmitted " .
			"FROM QAQuestions WHERE QaqStatus=10 AND QaqCatID=@@iCID@@ " .
			"ORDER BY QaqDateSubmitted DESC" ,
			array('iCID' => 'int'));
	}
}

?>
