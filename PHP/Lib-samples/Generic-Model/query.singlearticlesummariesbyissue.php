<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.articlesummariesbyissue.php
	Created: 02/25/08 by Luis Lopez
*/

class KBQuery_singlearticlesummariesbyissue extends KBResultsetQuery {
	function KBQuery_singlearticlesummariesbyissue() {
		parent::KBResultsetQuery('singlearticlesummariesbyissue',
			"SELECT	ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode " .
			"FROM	Articles " .
			"WHERE	ArDirID=@@directory@@ AND ArIssueID=@@issue@@ AND ArShow=1 AND ArArchived=0 AND ArStatus=4 " .
			"ORDER	BY ArDateModified DESC",
			array('directory' => 'int', 'issue' => 'int'));
	}
}

?>