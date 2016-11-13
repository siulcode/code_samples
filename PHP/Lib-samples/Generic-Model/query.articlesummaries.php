<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.articlesummaries.php
	Created: 01/23/08 by Luis Lopez
*/

class KBQuery_articlesummaries extends KBResultsetQuery {
	function KBQuery_articlesummaries() {
		parent::KBResultsetQuery('articlesummaries',
			"SELECT	ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode " .
			"FROM	Articles " .
			"WHERE	ArDirID=@@directory@@ AND ArShow=1 AND ArArchived=0 AND ArStatus=4 " .
			"ORDER	BY ArDateModified DESC",
			array('directory' => 'int'));
	}
}

?>
