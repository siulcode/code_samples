<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/query.getarticle.php
 * Created: 01/23/08 by Luis Lopez
 */

class KBQuery_getarticle extends KBResultsetQuery {
	function KBQuery_getarticle() {
		parent::KBResultsetQuery('getarticle',
		"	SELECT ArID, ArDirID, ArDateSubmitted, ArText, ArSummary, ArTitle, ArIssueID,
					EdNickname, EdID, EdUID, ULegalName
			FROM 	Articles LEFT JOIN Editors ON ArAuthorID=EdID
					LEFT JOIN Users ON EdUID=UID
			WHERE	ArID=@@arid@@ AND ArShow=1 AND ArStatus=4",
			array('arid' => 'int'));
	}
}

?>
