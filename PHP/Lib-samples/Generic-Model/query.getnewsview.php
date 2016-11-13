<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getnewsview.php
	Created: 01/31/08 by Luis Lopez
*/

class KBQuery_getnewsview extends KBResultsetQuery {
	function KBQuery_getnewsview() {
		parent::KBResultsetQuery('getnewsview',
		"	SELECT TnID, TnTime, TnURL, TnHeading, TnText, TnClickThroughs, TnResponses, ULegalName, TnCat 
			FROM TodayNews LEFT JOIN Users ON TnSubmittedUID=UID 
			WHERE TnID=@@newsview@@", 
			array('newsview' => 'int'));
	}
}

?>
