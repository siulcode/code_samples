<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: include.archives.php
 * Created: 02/25/08 by Luis Lopez
 */

function displayArchivesSectionList() {
	global $sKiwiboxRoot;

	$archiveItems = fetchArchivesSectionList();
	foreach ($archiveItems as $index => $arSh){
		?>
		<div class="title_link item">
			<a href="<?=$sKiwiboxRoot?>section-archives/<?=($arSh['DirID'])?>"><?=htmlspecialchars($arSh['DName'])?></a>
			<div class="meta">(<?=$arSh['NumArticles']?> articles)</div>
		</div>
		<?
	}
}

function displayArchivesArticlesInSection($iSection, $sURLBase, $iPage = 1, $iPerPage = 10, $iColumns = 2) {
	global $sectionSkin, $sKiwiboxRoot;

	$arSectionItems = fetchArchivesArticlesInSection($iSection);
	$iTotal = count($arSectionItems);

	$iPageCount = ceil($iTotal / $iPerPage);

	if ($iPage > $iPageCount) $iPage = $iPageCount;
	if ($iPage < 1) $iPage = 1;

	$iStartAt = ($iPage - 1) * $iPerPage;
	$iEndBefore = min($iStartAt + $iPerPage, $iTotal);

	$iShown = 0;
	$iInRow = 0;

	//Starting photo breadcrumbs.
	$archiveName = fetchArchivesDirName($iSection);
				?>
				<div class="breadcrumbs small_options dg_bottom">
				<a href="<?=$sKiwiboxRoot . 'section-archives/'?>">Kiwibox Archives</a> &raquo; <?=$archiveName?>
				</div>
				<div class="item"></div>
				<?
			//Ending photo breadcrumbs.
	for($i = $iStartAt; $i < $iEndBefore; $i++) {
		$iShown++;
		$iInRow++;

		displayArticleSummaryFromRow($arSectionItems[$i],
			'item fillout_box_m', $sectionSkin, true);

		if ($iInRow == $iColumns) {
			?><div class="<?=$sectionSkin->Color?>_bottom short_item"></div><?
			$iInRow = 0;
		}
	}

	if ($iShown > 0 && $iInRow > 0) {
		?><div class="<?=$sectionSkin->Color?>_bottom short_item"></div><?
	}

	?>&nbsp; &nbsp;<br /><?

	$pageControl = new KBPageControl($iPage, $sURLBase, $iPageCount);
	$pageControl->showPageControls();

	?>&nbsp; &nbsp;<br /> &nbsp; &nbsp;<br /> &nbsp; &nbsp;<br /><?
}

function displayArticlesByAuthor($iArID, $sURLBase, $iPage = 0, $iPerPage = 10, $iColumns = 2) {
	global $sectionSkin, $sKiwiboxRoot;

	$arSectionItems = fetchArticlesByAuthor($iArID);
	$iTotal = count($arSectionItems);

	$iPageCount = ceil($iTotal / $iPerPage);
	if ($iPage > $iPageCount) $iPage = $iPageCount;
	if ($iPage < 1) $iPage = 1;

	$iStartAt = ($iPage - 1) * $iPerPage;
	$iEndBefore = min($iStartAt + $iPerPage, $iTotal);

	$iShown = 0;
	$iInRow = 0;
	?><div class="section item"><?
	$baserURL = $sKiwiboxRoot . 'more-by-author-of/' . $iArID . '/';
	for($i = $iStartAt; $i < $iEndBefore; $i++) {
		$iShown++;
		$iInRow++;

		displayArticleSummaryFromRow($arSectionItems[$i],
			'item fillout_box_m',
			$sectionSkin, true);

		if ($iInRow == $iColumns) {
			?>
			<div class="<?=$sectionSkin->Color?>_bottom short_item"></div>
			<?
			$iInRow = 0;
		}
	}
	if ($iShown > 0 && $iInRow > 0) {
		?>
		<div class="<?=$sectionSkin->Color?>_bottom short_item"></div>
		<?
	}

	echo"&nbsp &nbsp<br />";


	$pageControl = new KBPageControl($iPage, $baserURL, $iPageCount,2,1, $sectionSkin->Color);
	$pageControl->showPageControls();
	echo"&nbsp &nbsp<br /> &nbsp &nbsp<br /> &nbsp &nbsp<br />";
	?></div><?
}

function fetchArchivesArticlesInSection($iSection) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('archivesarticlesinsection', $iSection);
	} else {
		$sKey = sprintf('archivesarticlesinsection_%u', $iSection);
		return getCachedQueryResults($sKey,
				"SELECT	ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode, ArIssueID " .
				"FROM	Articles " .
				"WHERE	ArDirID=" . $iSection . " AND ArShow=1 AND ArStatus=4 " .
				"ORDER	BY ArID DESC",
				true, 3600);
	}
}

function fetchArticlesByAuthor($iArID) {

		$sKey = sprintf('articlesbyauthor_%u', $iArID);
		$sql="	SELECT ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode, ArIssueID
				FROM Articles
				WHERE  ArAuthorID= (SELECT EdID FROM Articles INNER JOIN Editors ON Articles.ArAuthorID=Editors.EdID WHERE ArID={$iArID}) AND ArShow=1 AND ArStatus=4
				ORDER BY ArDatePosted DESC";

		return getCachedQueryResults($sKey,$sql,true, 3600);

}

function fetchArchivesSectionList() {
		$sKey = 'archivessectionlist';
		return getCachedQueryResults($sKey,
				"SELECT DirID, Count(*) AS NumArticles, " .
				"		(SELECT DirName FROM Dir As N WHERE N.DirID=D.DirID) As DName " .
				"FROM	Dir AS D INNER JOIN Articles ON D.DirID=Articles.ArDirID " .
				"WHERE	ArShow=1 AND ArStatus=4 " .
				"GROUP	BY DirID " .
				"ORDER	BY DName",
				true, 3600);
}


function fetchArchivesDirName($iID) {
		$sKey='DirID_'.$iID;
		$sql = "SELECT DirName
		        FROM Dir
		        WHERE DirID ={$iID}";
        return getCachedSingleResult($sKey,$sql,'DirName','',true,3600);
}

function fetchArchivesIssueList() {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('archivesissuelist');
	} else {
		$sKey = 'archivesissuelist';
		return getCachedQueryResults($sKey,
			"SELECT	IsID, IsName, IsDateFrom
			FROM Issues
			WHERE IsDateFrom < GETDATE()
			ORDER BY IsDateFrom DESC",
			true, 3600);
	}

}

function displayArchivesIssueList() {
	global $sKiwiboxRoot;
	$issues = fetchArchivesIssueList();
	foreach($issues as $issue) {
		//echo $issue['IsDateFrom'];
		$iIsID = $issue['IsID'];
		?>
		<div class="title_link item">
			<a href="<?=$sKiwiboxRoot?>in-this-issue/<?=$iIsID?>">Issue #<?=$iIsID?> <?=htmlspecialchars($issue['IsName'])?></a>
			<div class="meta">
				(<?=getShortDate($issue['IsDateFrom'])?>)
			</div>
		</div>
		<?
	}
}

function getShortDate($tm, $iDateLength = 11) {
	if (strlen($tm) > $iDateLength) {
		return substr($tm, 0, $iDateLength);
	} else {
		return $tm;
	}
}

?>
