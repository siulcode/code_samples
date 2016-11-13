<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/polls/get/query.singlepoll.php
 * Created: 04/07/08 by Luis Lopez
 */

class KBQuery_polls-get-singlepoll extends KBResultsetQuery {
	function KBQuery_polls-get-singlepoll() {
		parent::KBResultsetQuery('polls-get-singlepoll',
		"	SELECT * FROM MyPollQ INNER JOIN MyPollA ON MpaQID=MpqID 
			WHERE MpqID=@@iID@@ AND MpqStatus=1" ,
			array('iID' => 'int'));
	}
}

?>