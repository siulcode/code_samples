<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.qbooksgetsummary.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_qbooksgetsummary extends KBResultsetQuery {
	function KBQuery_qbooksgetsummary() {
		parent::KBResultsetQuery('qbooksgetsummary',
			"SELECT	TagBooks.*, ULegalName, UIcon " .
			"FROM	TagBooks INNER JOIN Users ON TagBooks.TbUID=UID " .
			"WHERE TbID=@@itemid@@",
			array('itemid' => 'int');
	}
}

?>