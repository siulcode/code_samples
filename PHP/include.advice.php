<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: include.advice.php
 * Created: 03/04/08 by Luis Lopez
 */

function displayAdviceCategories() {
	global $sKiwiboxRoot;

	$arAdvice = fetchAdviceCategories();
	$i = 13;
	foreach ($arAdvice as $advices => $ad) {
	$cssTitle = $ad['QacName'];	
		if ($i == $ad['QacID']) {
			?>
			<a href="<?=$sKiwiboxRoot?>qa/<?=($ad['QacID'])?>"><div 
				class="link item"><div class="thick"><?=($cssTitle)?></div></div>
			<?
		} else {
			?>
			<a href="<?=$sKiwiboxRoot?>qa/<?=($ad['QacID'])?>"><div 
				class="link item"><?=($cssTitle)?></a></div>
			<?
		}
	}
}


function displayGetQuestionsMessage() {
		?> 
		<div class="section item">
		Ask us questions, we are here to help. Our peer advisors are Kiwibox Users just like you, and understand how you feel. We may not have all the right answers, but we truely care. Unlike other magazines or web sites, we answer almost ALL of the questions we receive. So let your fellow teens help you!

Our advisors are not trained, and are unable to give advice about suicide, rape, abuse, substance abuse, or medical questions. If you are seeking advice dealing with one of these topics, please call one of the numbers or visit the sites listed <a href="http://www.kiwibox.com/advice.asp?item=numbers">here.</a> 
		</div>
		<?
	}
			
function displayAdviceQnA($iQAid) {
	$arQA = fetchAdviceQnA($iQAid);
	foreach ($arQA as $questions => $qa) {
		?>
		<div class="link item">
			<a href="<?=($qa['QaqID'])?>"><?=$qa['QaqName']?> from <?=$qa['QaqLocation']?> on <?=$qa['QaqDateSubmitted']?></a>
		</div>
		<?
	}
}

function fetchAdviceGetSelected($iQID) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('advice-get-selected', $iQID);
	} else {
		$sKey = sprintf('advice-get-selected-%u',$iQID);
		return getCachedQueryResults($sKey,
			"	SELECT	QaqName, QaqLocation, QaqDateSubmitted, QaqQuestion,
						QaaTitle, QaaText, QaaDateCreated, ULegalName
				FROM	QAQuestions
						LEFT JOIN QAAnswers ON QAQuestions.QaqID=QAAnswers.QaaQID
						LEFT JOIN Users ON QAAnswers.QaaUID=Users.UID
				WHERE	QaaStatus=1 AND QaqID={$iQID}
				ORDER	BY QaaDateCreated",
				true, 3600);
	}
}

function fetchAdviceQnA($iQAid) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getqa', $iQAid);
	} else {
		$sKey = sprintf('getqa_%u', $iQAid);
		return getCachedQueryResults($sKey,
			"SELECT	TOP 10 QaqID, QaqName, QaqLocation, QaqDateSubmitted " .
			"FROM	QAQuestions WHERE QaqStatus=10 AND QaqCatID= " . $iQAid . " " .
			"ORDER	BY QaqDateSubmitted DESC",
			true, 3600);
	}
}

function fetchAdviceCategories() {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getadvicecategories');
	} else {
		$sKey = ('getadvicecategories');
		return getCachedQueryResults($sKey, 
			"SELECT	QacID, QacName FROM QACat " . 
			"ORDER	BY QacDirID, QacName",
			true, 3600);
	}
}

function getShort($desc, $iDescLength = 10) {
	if (strlen($desc) > $iDescLength) {
		return substr($desc, 0, $iDescLength) . "...";
	} else {
		return $desc;
	}
}

?>
