<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/polls/get/query.mostvotes.php
 * Created: 03/19/08 by Luis Lopez
 * Extended: 03/26/2008 by Ivan Tumanov
 */
require_once(FRAMEWORK_ROOT . 'include.mypolls.php');


class KBQuery_polls_get_mostvotes extends KBQuery {
	function KBQuery_polls_get_mostvotes() {
		parent::KBQuery('polls-get-mostvotes');
	}

	function Execute($params, $filter) {
		global $cnMS;	

		$factory = new KBPollFactory();
				
		if ($iQuantity = $params->pop_numeric(false)) {
			$iDateRange = $params->pop_numeric(3);

			$sql = $factory->GetSQL_PollSummaries(
					"MpqStatus=1 AND MpqModified > DATEADD(day, -{$iDateRange}, GETDATE())",
					'MpqVotes DESC', $iQuantity);
			if ($cnMS->query($sql)) {
				while ($r = $cnMS->next_array()) {
					$filter->FilterRow($r);
				}
				return true;
			}
		}
		return false;
	}
}

?>
