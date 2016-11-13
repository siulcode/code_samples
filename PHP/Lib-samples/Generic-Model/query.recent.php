<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getrecentjournals.php
	Created: 02/05/08 by Luis Lopez

*/

class KBQuery_journals_get_recent extends KBResultsetQuery {
	function KBQuery_journals_get_recent() {
		parent::KBResultsetQuery('journals-get-recent',
		"	SELECT	TOP @@iQuantity@@ JoID, JoSecurity, JoUpdated, JeText, JoSubs, JoName, JeResponses, JeTitle, JeUpdated
			FROM	Journals INNER JOIN JoEntries ON JeJoID=JoID
			WHERE	
			JoID NOT IN (SELECT TOP @@iOffset@@ JoID
							FROM Journals INNER JOIN JoEntries ON JeJoID=JoID
							where JoSecurity < 3 AND JoUID= @@iUID@@  ORDER BY JeUpdated DESC
							)
			AND
			JoSecurity < 3 AND JoUID= @@iUID@@ ORDER BY JeUpdated DESC"	,
			array(	'iUID' => 'usernametouid',
					'iQuantity' => 'int',										
					'iOffset' => 'int'));
	}
}

?>
