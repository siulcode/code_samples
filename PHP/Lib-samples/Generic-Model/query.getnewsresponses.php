<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/query.getnewsresponses.php
 * Created: 02/01/2008 by Luis Lopez
 */

// TODO: implement paging for news comments by using the iOffset parameter

class KBQuery_getnewsresponses extends KBResultsetQuery {
	function KBQuery_getnewsresponses() {
		$sql = "SELECT TOP @@iQuantity@@ TmID, TmTitle, TmDateCreated, TmText, UID, ULegalName
                FROM    TodayMsgs LEFT JOIN Users ON TmUID=UID
                WHERE   TmShow=1
                AND TnID=@@iTnID@@
                AND TmID NOT IN (
                        SELECT TOP @@iOffset@@ TmID
                        FROM    TodayMsgs LEFT JOIN Users ON TmUID=UID
                        WHERE   TmShow=1
                        AND TnID=@@iTnID@@
                        ORDER   BY TmDateCreated DESC)
                ORDER   BY TmDateCreated DESC";
			parent::KBResultsetQuery('getnewsresponses',$sql,
			array('iTnID' => 'int', 'iQuantity' => 'int', 'iOffset' => 'int'));
	}
}

?>
