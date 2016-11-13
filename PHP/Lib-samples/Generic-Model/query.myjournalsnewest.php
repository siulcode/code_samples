<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.myjournalsnewest.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_myjournalsnewest extends KBResultsetQuery {
	function KBQuery_myjournalsnewest() {
		parent::KBResultsetQuery('myjournalsnewest',
			"SELECT TOP @@numitems@@ JoID, JeTitle, JeText, JeUpdated, ULegalName, UIcon " .
            "FROM Journals INNER JOIN JoEntries ON JeJoID=JoID INNER JOIN Users ON JoUID=UID " .
            "WHERE JoSecurity=1 ORDER BY JeUpdated DESC",
            array('numitems' => 'int'));
	}
}

?>