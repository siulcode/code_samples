<?php
/*
	Created: 03/07/08 by Luis Lopez
*/
class KBQuery_get_news extends KBResultsetQuery {
	function KBQuery_get_news() {
		parent::KBResultsetQuery('get-news',
		"	SELECT	TOP @@quantity@@
					TnID As NewsID,
					TnHeading AS Title,
					ULegalName AS SubmittedBy,
					TnTime AS DatePosted,
					TnText AS FullText,
					TcName as Section,
					'' AS ThumbnailImageURL
			FROM	TodayNews INNER JOIN TodayCats ON TnCat=TcId
					INNER JOIN Users ON TnSubmittedUID=UID
			ORDER	BY TnTime DESC",
			array('quantity' => 'int'));
	}
}

?>
