<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: include.mypolls.php
 * Created: 01/22/08 by Luis Lopez
 * Extended: 03/25/2008 by Ivan Tumanov
 */

class KBPollFactory extends KBFactory {
	function KBPollFactory() {
		parent::KBFactory('KBPollSummary');
	}

	function GetSQL_PollSummaries(	$sWHEREClause = 'MpqStatus=1 ',
									$sORDERClause = 'MpqModified DESC',
									$iQuantity = false, $iOffset = false) {
		$sTOPClause = ($iQuantity ? "TOP {$iQuantity} " : '');
		return "SELECT	{$sTOPClause} MpqID, MpqVotes, MpqQuestion, 
						MpqModified, ULegalName
				FROM	MyPollQ INNER JOIN Users ON MpqUID=UID
				WHERE	{$sWHEREClause}
				ORDER	BY {$sORDERClause}";
	}

	/*
	 * Example
	function FetchPoll_ByID($iPollID) {
		if (LOCAL_DEV) {
			$arResultset = $this->RetrieveFromQueryServer('polls-get-byid', $iPollID);
		} else {
			$arResultset = $this->RetrieveFromDB(
				"SELECT * FROM MyPollQ INNER JOIN MyPollA ON MpqID=MpaMpqID WHERE MpqID={$iPollID}");
		}
		return $this->ManufactureFromResultset($arResultset, 'KBPoll');
	}
	*/

	/*
	 * FetchPollSummarries_Newest returns the specified quantity of newest
	 * KBPollSummary objects
	 */
	function FetchPollSummaries_Newest($iQuantity = 20) {
		if (LOCAL_DEV) {
			$arResultset = $this->RetrieveFromQueryServer('polls-get-newest', $iQuantity);
		} else {
			$arResultset = $this->RetrieveFromDB(FROM_MSSQL,
				$this->GetSQL_PollSummaries(
					'MpqStatus=1', 'MpqModified DESC', $iQuantity),
				sprintf('polls-get-newest-%u', $iQuantity),
				3600);
		}
		return $this->ManufactureFromResultset($arResultset);
	}

	/*
	 * FetchPollSummariesByUser takes a KBUser or a KBProfileUser object
	 * and an optional quantity parameter.  Polls that are owned byuser
	 * the specified user are retrieved from the MyKiwibox Polls tables,
	 * and if a quantity is not specified then all polls owned by the
	 * user are returned.
	 */
	function FetchPollSummaries_ByUser($oUser, $iQuantity = false) {
		if (LOCAL_DEV) {
			$arResultset = $this->RetrieveFromQueryServer('polls-get-byuser', 
				$oUser->getUsername() . ($iQuantity ? '/' . $iQuantity : ''));
		} else {
			$iUID = $oUser->getUID();
			$arResultset = $this->RetrieveFromDB(FROM_MSSQL,
				$this->GetSQL_PollSummaries(
					"MpqStatus=1 AND MpqUID={$iUID}", 
					'MpqModified DESC', $iQuantity),
				sprintf('polls-by-user-%u-%u', $iUID, $iQuantity),
				3600);
		}
		return $this->ManufactureFromResultset($arResultset);
	}

	/*
	 * FetchPollSummaries_MostVotes returns poll summary objects representing
	 * polls created in the last {$iDateRange} days which got the most votes
	 */
	function FetchPollSummaries_MostVotes($iQuantity = 20, $iDateRange = 3) {
		if (LOCAL_DEV) {
			$arResultset = $this->RetrieveFromQueryServer('polls-get-mostvotes',
				$iQuantity . '/' . $iDateRange);
		} else {
			$arResultset = $this->RetrieveFromDB(FROM_MSSQL,
				$this->GetSQL_PollSummaries(
					"MpqStatus=1 AND MpqModified > DATEADD(day, -{$iDateRange}, GETDATE())",
					'MpqVotes DESC', $iQuantity),
				sprintf('polls-most-votes-%u-%u', $iQuantity, $iDateRange),
				3600);
		}
	}
}

/*
 * This class is set up to loosely resemble CCSItem-derived classes so
 * that going forward it'll be easier to transition Polls to be hosted
 * in MySQL and run through DCF/CCS.
 */
class KBPollSummary extends KBRowBase {
	function KBPollSummary(&$row) {
		parent::KBRowBase($row, 'MpqQuestion', 'MpqID');
	}

	function GetPermalink() {
		global $sMyAspDev;

		return  $sMyAspDev . 'polls/poll.asp?id=' . $this->ID;
	}

	function GetTitle($iMaxLength = false) {
		if ($iMaxLength) {
			return getShort($this->Name, $iMaxLength);
		} else {
			return $this->Name;
		}
	}

	function GetByLine() {
		$sUsername = $this->Row['ULegalName'];
		return '<span class="normal">by</span> <a href="' . getProfileURL($sUsername) . '">' . $sUsername . '</a>';
	}

	function ListDisplay($bShowByLine = false) {
	
		?>
			<div class="title_link">
				<a href="<?=$this->GetPermalink()?>"><?=htmlspecialchars(fixBadWords($this->GetTitle()))?></a>
			</div>
			<div class="title_link">
				<? if ($bShowByLine) echo($this->GetByLine()); ?>
				<span class="small_options"><?=showQuantity($this->Row['MpqVotes'], 'Vote')?></span>
		</div>
		<?
	}
	
	function ListUserDisplay($bShowByLine = false) {
		global $currentUser;
		$factory = new KBPollFactory();
		?>
			<div class="title_link">
				<a href="<?=$this->GetPermalink()?>"><?=htmlspecialchars(fixBadWords($this->GetTitle()))?></a>
			</div>
			<div class="details small_options">
				<? if ($bShowByLine) echo($this->GetByLine()); ?>
				<?=showQuantity($this->Row['MpqVotes'], 'Vote')?>
			</div>
		<?
	}
}


?>