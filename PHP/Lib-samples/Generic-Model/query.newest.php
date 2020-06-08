<?php
/*
 * Created: 03/19/08 by Luis Lopez
 * Extended: 03/26/2008 by Ivan Tumanov
 */
require_once(FRAMEWORK_ROOT . 'include.mypolls.php');


class KBQuery_polls_get_newest extends KBQuery {
	function KBQuery_polls_get_newest() {
		parent::KBQuery('polls-get-newest');
	}

	function Execute($params, $filter) {
		global $cnMS;

		$factory = new KBPollFactory();
		if ($iQuantity = $params->pop_numeric(false)) {
			$sql = $factory->GetSQL_PollSummaries(
						"MpqStatus=1",
						"MpqModified DESC",
						$iQuantity);

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
