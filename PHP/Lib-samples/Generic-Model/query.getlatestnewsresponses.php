<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getnewsresponses.php
	Created: 02/01/08 by Luis Lopez
*/

class KBQuery_getlatestnewsresponses extends KBResultsetQuery {
	function KBQuery_getlatestnewsresponses() {
		parent::KBResultsetQuery('getlateestnewsresponses',
			"SELECT TOP  @@quantity@@ " .  
			"		TmID, TmTitle, TmDateCreated, TmText, " .
			"		TodayNews.TnID, TodayNews.TnHeading, " .
			"		ULegalName, Users.UIcon " .
			"FROM	TodayMsgs INNER JOIN TodayNews ON TodayMsgs.TnID=TodayNews.TnID " .
			"		INNER JOIN Users ON TodayMsgs.TmUID=Users.UID " .
			"WHERE TmShow=1 ORDER BY TmDateCreated DESC", 
			array('quantity' => 'int'));
	}
}
?>
