<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: include.myjournals.php
 * Created: 01/22/08 by Luis Lopez
 */

function fetchJournalsNewest($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('myjournalsnewest', $iQuantity);
	} else { 
		$sKey = sprintf('myjournalsnewest_%u', $iQuantity);
		return getCachedQueryResults($sKey,
			"	SELECT	TOP " . $iQuantity . " JoID, JeTitle, JeText, JeUpdated, ULegalName, UIcon 
				FROM	Journals INNER JOIN JoEntries ON JeJoID=JoID INNER JOIN Users ON JoUID=UID 
				WHERE	JoSecurity=1 ORDER BY JeUpdated DESC",
				true, 3600); 
	}
}

function fetchUserJournals($profileUser, $iQuantity = 2, $iOffset=0) {
	if (LOCAL_DEV) {				
		return serializedLocalDevQuery('journals-get-recent', 
			$profileUser->getUsername() . '/' . $iQuantity . '/' . $iOffset);
	} else { 
		$iUID = $profileUser->getUID();	
		$sKey = sprintf('journals-get-recent%u', $iUID."_".$iQuantity."_".$iOffset);
 		return getCachedQueryResults($sKey,
		"	SELECT	TOP {$iQuantity} JoID, JoUpdated, JeText, JoSubs, JoName, JeResponses, JeTitle,JeUpdated
			FROM	Journals INNER JOIN JoEntries ON JeJoID=JoID
			WHERE	JoID NOT IN (SELECT	TOP {$iOffset} JoID
								 FROM	Journals INNER JOIN JoEntries ON JeJoID=JoID
								 WHERE	JoSecurity < 3 AND JoUID={$iUID} ORDER BY JeUpdated DESC)
			  AND	JoSecurity < 3 AND JoUID= {$iUID} ORDER BY JeUpdated DESC",
			true, 3600);
	}
}

?>
