<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.archivessectionlist.php
	Created: 02/25/08 by Luis Lopez
*/

class KBQuery_archivessectionlist extends KBResultsetQuery {
	function KBQuery_archivessectionlist() {
		parent::KBResultsetQuery('archivessectionlist',
			"SELECT	DirID, COUNT(*) AS NumArticles, " .
			"		(SELECT DirName FROM Dir As N WHERE N.DirID=D.DirID) As DName " .
			"FROM	Dir AS D INNER JOIN Articles ON D.DirID=Articles.ArDirID " .
			"WHERE	ArShow=1 AND ArStatus=4 " .
			"GROUP	BY DirID " .
			"ORDER	BY DName"); 
	}
}
?>
