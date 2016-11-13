<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.archivesarticlesinsection.php
	Created: 02/26/08 by Luis Lopez
*/

class KBQuery_archivesarticlesinsection extends KBResultsetQuery {
	function KBQuery_archivesarticlesinsection() {
		parent::KBResultsetQuery('archivesarticlesinsection',
			"SELECT ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode " .
			"FROM Articles " .
			"WHERE ArDirID=@@section@@ AND ArShow=1 AND ArStatus=4 " .
			"ORDER BY ArID DESC",
			array('section' => 'int')); 
	}
}
?>
