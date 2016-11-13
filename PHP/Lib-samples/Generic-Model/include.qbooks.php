<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: framework/include.qbooks.php
 * Created: 01/22/08 by Luis Lopez
 */

function fetchMyQbooks($sUsername, $iUIDHint = false) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('qbooks-get-byuser', $sUsername);
	} else {
		$iUID = ($iUIDHint ? $iUIDHint : getUIDfromUsername($sUsername));
		$sKey = sprintf('qbooks_by_%u', $iUID);
		return getCachedQueryResults($sKey,
			"	SELECT TOP 2	TbID, TbName, TbCreated, ULegalName,
					(	SELECT	COUNT(DISTINCT TaUID)
						FROM	TagAnswers INNER JOIN TagQuestions ON TaTqID=TqID
						WHERE	TqTbID=TbID) As AnswerCount
				FROM	TagBooks INNER JOIN Users ON TbUID=UID
				WHERE	TbUID={$iUID}
				ORDER	BY TbCreated DESC",
				true, 3600);
	}
}

function fetchQbooksNewest($iQuantity = 5) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('qbooks-get-newest', $iQuantity);
	} else { 
		$sKey = sprintf('qbooksnewest_%u', $iQuantity);
		return getCachedQueryResults($sKey,		
			"	SELECT  TOP {$iQuantity} TbID, TbName, TbCreated, ULegalName,
					(	SELECT	COUNT(DISTINCT TaUID)
						FROM	TagAnswers INNER JOIN TagQuestions ON TaTqID=TqID
						WHERE	TqTbID=TbID) As AnswerCount
				FROM	TagBooks INNER JOIN Users ON TbUID=UID
				WHERE	TbStatus=1
				ORDER	BY TbCreated DESC",
				true, 3600);

	}
}

?>
