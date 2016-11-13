<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.articlesbyauthor.php
	Created: 04/08/08 by Luis Lopez
*/

class KBQuery_articlesbyauthor extends KBResultsetQuery {
	function KBQuery_articlesbyauthor() {
		parent::KBResultsetQuery('articlesbyauthor',
			"	SELECT ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode, ArIssueID
				FROM Articles
				WHERE  ArAuthorID= (SELECT EdID FROM Articles INNER JOIN Editors ON Articles.ArAuthorID=Editors.EdID WHERE ArID=@@iArID@@) AND ArShow=1 AND ArStatus=4 
				ORDER BY ArDatePosted DESC",
			array('iArID' => 'int')); 
	}
}

?>