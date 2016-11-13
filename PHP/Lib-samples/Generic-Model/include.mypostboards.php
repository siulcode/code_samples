<?
/*
	Copyright (c) 2008, Kiwibox Media, Inc.
	File: include.mypostboards.php
	Created: 01/22/08 by Luis Lopez
*/

function displayMyPostboardsNewestTopics($iQuantity = 20) {
	$items = fetchPostboardsNewestTopics($iQuantity);
	?><div class="section"><?
	foreach($items as $pb) {
		?>
		<div class="mypb">
			<div class="title_link"><?=htmlspecialchars($pb['PtTitle'])?></div>
			<div class="details">on <?=prettyPrintDate(strtotime($pb['PtDate']))?> by <?=htmlspecialchars($pb['ULegalName'])?> in <?=htmlspecialchars($pb['PbName'])?></div>
		</div>
		<?
	}
	?></div><?
}

function fetchPostboardsNewestTopics($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('postboardsnewesttopics',$iQuantity);
	} else {
		return mssqlQueryToArray("SELECT TOP " . $iQuantity . 
								 " PbID, PbName, PtID, PtTitle, PtDate, PtMessages, ULegalName, UIcon " .
								 "FROM (Postboards INNER JOIN PbTopics ON PtPbID=PbID) INNER JOIN Users ON PtUID=UID " .
								 "WHERE PbSecurity=1 ORDER BY PtDate DESC;");
	}
}

function fetchMyPostboardsNewestTopics($profileUser, $iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('mypostboardsnewesttopics', $profileUser->getUsername() . '/' . $iQuantity);
	} else { 
		$iUID = $profileUser->getUID();
		
		$sKey = sprintf('mypostboardsnewesttopics_%u', $iUID);
		return getCachedQueryResults($sKey, 
			"	SELECT TOP " . $iQuantity . " PbID, PbName, Postboards.PbTopics AS NumberOfTopics, 
					(SELECT SUM(PtMessages) FROM PbTopics WHERE PtPbID=PbID) AS NumberOfMessages
				FROM Postboards
				WHERE PbSecurity < 3 AND PbUID=" . $iUID . "
				ORDER BY PbCreated DESC",
				true, 3600);
	}
}

function displayMyPostboardsLatestResponses($iQuantity = 20) {
	$items = fetchMyPostboardsLatestResponses($iQuantity);
	?><div class="section"><?
	foreach($items as $pb) {
		?>
		<div class="mypb">
			<div class="title_link"><?=htmlspecialchars($pb['PtTitle'])?></div>
			<div class="details">on <?=prettyPrintDate(strtotime($pb['PmDate']))?> by <?=htmlspecialchars($pb['ULegalName'])?> in <?=htmlspecialchars($pb['PbName'])?></div>
		</div>
		<?
	}
	?></div><?
}

function fetchMyPostboardsLatestResponses($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('mypostboardslatestresponses',$iQuantity);
	} else {
		return mssqlQueryToArray("SELECT TOP " . $iQuantity . 
								 " PbID, PmSubject, PbName, PtID, PtTitle, PtMessages, PmDate, ULegalName, UIcon " .
								 "FROM ((Postboards INNER JOIN PbTopics ON PtPbID=PbID) " .
								 "INNER JOIN PbMessages ON PmPtID=PtID) " .
								 "INNER JOIN Users ON PmUID=UID " .
								 "WHERE PbSecurity=1 ORDER BY PmDate DESC");
	}
}

function displayMyPostboardsNewestPostboards($iQuantity = 20) {
	$items = fetchMyPostboardsNewestPostboards($iQuantity);
	?><div class="section"><?
	foreach($items as $pb) {
		?>
		<div class="mypb">
			<div class="title_link"><?=htmlspecialchars($pb['PbName'])?></div>
			<div class="details">by <?=htmlspecialchars($pb['ULegalName'])?> on <?=htmlspecialchars($pb['PbCreated'])?></div>
		</div>
		<?
	}
	?></div><?
}

function fetchMyPostboardsNewestPostboards($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('mypostboardsnewestpostboards',$iQuantity);
	} else {
		return mssqlQueryToArray("SELECT TOP 20 PbID, PbName, PbCreated, PbTopics, ULegalName, UIcon " .
						"FROM Postboards INNER JOIN Users ON PbUID=UID " .
						"WHERE PbSecurity=1 ORDER BY PbCreated DESC");
	}
}

function displayMyPostboardsActiveTopics($iQuantity = 20) {
	$items = fetchMyPostboardsActiveTopics($iQuantity);
	?><div class="section"><?
	foreach($items as $pb) {
		?>
		<div class="mypb">
			<div class="title_link"><?=htmlspecialchars($pb['PtTitle'])?></div>
			<div class="details">on <?=prettyPrintDate(strtotime($pb['PtDate']))?> by <?=htmlspecialchars($pb['ULegalName'])?> in <?=htmlspecialchars($pb['PbName'])?></div>
		</div>
		<?
	}
	?></div><?
}

function fetchMyPostboardsActiveTopics($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('mypostboardsactivetopics', $iQuantity);
	} else { 
		return mssqlQueryToArray("SELECT TOP 20 PbID, PbName, PtID, PtTitle, PtDate, PtMessages, ULegalName, UIcon " .
						"FROM (Postboards INNER JOIN PbTopics ON PtPbID=PbID) INNER JOIN Users ON PtUID=UID " .
						"WHERE PbSecurity=1 AND PtDate > DATEADD(day,-3,GETDATE()) " .
						"ORDER BY PtMessages DESC");
	}
}

function displayMyPostboardsActivePostboards($iQuantity = 20) {
	$items = fetchMyPostboardsActivePostboards($iQuantity);
	?><div class="section"><?
	foreach($items as $pb) {
		?>
		<div class="mypb">
			<div class="title_link"><?=htmlspecialchars($pb['PbName'])?></div>
			<div class="details">by <?=htmlspecialchars($pb['ULegalName'])?> on <?=htmlspecialchars($pb['PbCreated'])?></div>
		</div>
		<?
	}
	?></div><?
}

function fetchMyPostboardsActivePostboards($iQuantity = 20) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('mypostboardsactivepostboards', $iQuantity);
	} else {
		return mssqlQueryToArray("SELECT TOP 20 PbID, PbName, PbCreated, PbTopics, ULegalName, UIcon " .
								 "FROM Postboards INNER JOIN Users ON PbUID=UID " .
								 "WHERE PbSecurity=1 AND PbCreated > DATEADD(day,-7,GETDATE()) " .
								 "ORDER BY PbTopics DESC");
	}
}

?>
