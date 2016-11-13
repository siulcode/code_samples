<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: www/query/query/advice/get/query.selected.php
	Created: 03/07/08 by Luis Lopez
*/

class KBQuery_advice_get_selected extends KBResultsetQuery {
	function KBQuery_advice_get_selected() {
		parent::KBResultsetQuery('advice_get_selected',
		"	SELECT	QaqName, QaqLocation, QaqDateSubmitted, QaqQuestion,
					QaaTitle, QaaText, QaaDateCreated, ULegalName
			FROM	QAQuestions
					LEFT JOIN QAAnswers ON QAQuestions.QaqID=QAAnswers.QaaQID
					LEFT JOIN Users ON QAAnswers.QaaUID=Users.UID
			WHERE	QaaStatus=1 AND QaqID=@@iQID@@
			ORDER	BY QaaDateCreated",
			array('iQID' => 'int'));
	}
}

?>