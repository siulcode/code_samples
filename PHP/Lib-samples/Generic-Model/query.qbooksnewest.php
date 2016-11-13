<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.qbooksnewest.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_qbooksnewest extends KBResultsetQuery {
	function KBQuery_qbooksnewest() {
		parent::KBResultsetQuery('qbooksnewest',
			"SELECT	TOP @@numitems@@ TbID, TbUID, TbName, TbCreated, ULegalName, UIcon " .
			"FROM	TagBooks INNER JOIN Users ON TbUID=UID " .
			"WHERE	TbStatus=1 " .
			"ORDER	BY TbCreated DESC",
			array('numitems' => 'int'));
	}
}

?>
