<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.getcontestrecentwinnersresults.php
	Created: 02/28/08 by Luis Lopez
*/

class KBQuery_getcontestrecentwinnersresults extends KBResultsetQuery {
	function KBQuery_getcontestrecentwinnersresults() {
		parent::KBResultsetQuery('getcontestrecentwinnersresults',
			"SELECT	CoID, CoName, CoWinnersList, CoStatus, CoType FROM Contests " .
			"WHERE	CoID=@@iCoID@@ AND CoStatus> 3 ",
			array('iCoID' => 'int'));
	}
}

?>