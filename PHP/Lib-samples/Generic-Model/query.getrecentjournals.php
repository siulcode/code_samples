<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getrecentjournals.php
	Created: 02/05/08 by Luis Lopez

*/

class KBQuery_getRecentJournals extends KBResultsetQuery {
	function KBQuery_getRecentJournals() {
		parent::KBResultsetQuery('getrecentjournals',
		"	SELECT	TOP 2 JoID, JoSecurity, JoUpdated, JeText, JoSubs, JoName, JeResponses, JeTitle
			FROM	Journals INNER JOIN JoEntries ON JeJoID=JoID
			WHERE	JoUID=@@uid@@ AND JoSecurity < 3 ORDER BY JoCreated DESC",
			array('uid' => 'usernametouid'));
	}
}

?>
