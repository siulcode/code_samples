<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/polls/get/query.byuser.php
 * Created: 03/19/08 by Luis Lopez
 * Extended: 03/25/2008 by Ivan Tumanov
 */
require_once(FRAMEWORK_ROOT . 'include.mypolls.php');


class KBQuery_polls_get_byuser extends KBQuery {
	function KBQuery_polls_get_byuser() {
		parent::KBQuery('polls-get-byuser');
	}

	function Execute($params, $filter) {
		global $cnMS;

		if ($sUsername = $params->pop()) {
			$iUID = getUIDfromUsername($sUsername);
			if ($iUID) {
				
				$factory = new KBPollFactory();
				
				if ($params->hasAtLeast(1)) {
					$iQuantity = $params->pop_numeric();
					$sql = $factory->GetSQL_PollSummaries(
						"MpqStatus=1 AND MpqUID={$iUID}",
						"MpqModified DESC",
						$iQuantity);
				} else {
					$sql = $factory->GetSQL_PollSummaries(
						"MpqStatus=1 AND MpqUID={$iUID}");
				}

				if ($cnMS->query($sql)) {
					while ($r = $cnMS->next_array()) {
						$filter->FilterRow($r);
					}
					return true;
				}
			}
		}
		return false;
	}
}

?>
