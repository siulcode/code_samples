<?php
/*
	Created: 03/17/08 by Luis Lopez
*/

class KBQuery_myqbooksnewest extends KBResultsetQuery {
	function KBQuery_myqbooksnewest() {
		parent::KBResultsetQuery('myqbooksnewest',
			"SELECT TbID, TbName, TbCreated, ULegalName,
					(
						SELECT	COUNT(DISTINCT TaUID)
						FROM	TagAnswers INNER JOIN TagQuestions ON TaTqID=TqID
						WHERE	TqTbID=TbID
					) As NumResponders " .
			"FROM	TagBooks INNER JOIN Users ON TbUID=UID " .
			"WHERE	TbStatus=1 " .
			"ORDER	BY TbCreated DESC");
	}
}

?>
