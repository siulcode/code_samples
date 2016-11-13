<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.journalsgetsummary.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_journalsgetsummary extends KBResultsetQuery {
	function KBQuery_journalsgetsummary() {
		parent::KBResultsetQuery('journalsgetsummary',
			"SELECT Journals.*, ULegalName, UIcon " .
			"FROM Journals INNER JOIN Users ON Journals.JoUID=UID " .
			"WHERE JoID=@@itemid@@",
			array('itemid' => 'int');
	}
}

?>