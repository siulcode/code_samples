<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/query.mypollsnewest.php
 * Created: 03/19/08 by Luis Lopez
 */

class KBQuery_mypollsnewest extends KBResultsetQuery {
	function KBQuery_mypollsnewest() {
		parent::KBResultsetQuery('mypollsnewest',
			"	SELECT	TOP @@quantity@@ MpqID, MpqUID, MpqQuestion, MpqModified
				FROM	MyPollQ
				WHERE	MpqStatus=1 AND MpqUID=@@uid@@
				ORDER	BY MpqModified DESC",
			array('uid' => 'usernametouid', 'quantity' => 'int'));
	}
}

?>
