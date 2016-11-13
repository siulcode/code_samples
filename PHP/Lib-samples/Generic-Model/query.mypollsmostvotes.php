<?php
/*
	Copyright (c) 2008 Kiwibox Media, Inc.
	File: query.kiwibox.com/query/query.mypollsmostvotes.php
	Created: 01/22/08 by Luis Lopez
*/

class KBQuery_mypollsmostvotes extends KBResultsetQuery {
	function KBQuery_mypollsmostvotes() {
		parent::KBResultsetQuery('mypollsmostvotes',
			"SELECT TOP @@numitems@@ MpqID, MpqUID, MpqVotes, MpqQuestion, MpqModified, ULegalName, UIcon " .
			"FROM	MyPollQ INNER JOIN Users ON MpqUID=UID " .
			"WHERE	MpqStatus=1 AND MpqModified > DATEADD(day,-3,GETDATE()) " .
			"ORDER	BY MpqVotes DESC",
			array('numitems' => 'int'));
	}
}

?>
