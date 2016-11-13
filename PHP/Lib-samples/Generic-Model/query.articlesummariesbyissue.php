<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.articlesummariesbyissue.php
	Created: 02/25/08 by Ivan Tumanov
	Customized: 04/24/08 by Luis Lopez
*/

class KBQuery_articlesummariesbyissue extends KBResultsetQuery {
	function KBQuery_articlesummariesbyissue() {
		parent::KBResultsetQuery('articlesummariesbyissue',
		"	SELECT	ArID, ArDirID, ArIssueId, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode 
			FROM Articles 
			WHERE	ArDirID=@@iDirID@@ AND ArIssueId=@@iIssueId@@ AND ArShow=1 AND ArStatus=4 
			ORDER	BY ArDateModified DESC",
			array('iDirID' => 'int','iIssueId' => 'int'));
	}
}

?>