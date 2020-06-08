<?php
/*
 * File: framework/include.userkiwipointhistory.php
 * Created: 02/11/08 by Luis Lopez
 */

function fetchUserKiwiPointHistory($sUsername) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('userkiwipointhistory');
	} else {
		$iUID = getUIDfromUsername($sUsername);
		$arResults = getQueryResults("SELECT KhDate, KhPoints, KhType, KpName " .
									 "FROM KPHistory LEFT JOIN KpHistoryTypes ON KhType=KpID " .
									 "WHERE KhUID=" . $iUID . "ORDER BY KhDate DESC");
		if ($arResults !== false) return $arResults;
	}
	return array();
}
