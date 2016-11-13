<?
/*
	Copyright (c) 2008, Kiwibox Media, Inc.
	File: include.contests.php
	Created: 01/21/08 by Ivan Tumanov
	Modified: 02/28/08 by Luis Lopez
	Modified: 06/2/08 by Mike Howard
*/

function displayCurrentContests($iQuantity = 5, $iSummary = 2) {
	global $sImageRoot, $sKiwiboxRoot, $sKiwiboxAspDev, $sMyAspDev;
	$contestItems = fetchOpenContestItems();
	$i = 1;

	if (is_array($contestItems)) {
		$iTotalItems = count($contestItems);
		$iDisplayWithSummary = min($iSummary, $iTotalItems);

		// display contests with full summary
		for ($i = 1; $i <= $iDisplayWithSummary; $i++) {
			$ci = array_shift($contestItems);
			if (!is_null($ci)) {
				$cssClass = ($iDisplayWithSummary == $iTotalItems ? 'item lg_bottom' : 'item dashed_bottom');
				$contestTitle = ($ci['CoShortLinkName'] == '' ? $ci['CoName'] : $ci['CoShortLinkName']);

				?>
				<div class="<?=$cssClass?>">					
					<div class="tiny_item_nc fillout_box_7f">
						
						<a href="<?=$sKiwiboxAspDev?>contest.asp?con=<?=$ci['CoID']?>"><img class="thumb" src="<?=$ci['CoMicroImageSRC']?>" /></a>
					
					</div>
					<div class="left_short_item_nc fillout_box_20">
						<div class="title_link">
							<a href="<?=$sKiwiboxAspDev?>contest.asp?con=<?=$ci['CoID']?>"><?=htmlspecialchars(fixCharacterSetDifferences(getShortSummary($contestTitle, 24)))?></a>
						</div>
						<div class="details">
							<?=getShortSummary(htmlspecialchars(fixCharacterSetDifferences($ci['CoShortDesc'])))?>
							<div class="tiny_vspace"></div><div class="tiny_vspace"></div><div class="tiny_vspace"></div>
							<a href="<?=$sKiwiboxAspDev?>contest.asp?con=<?=$ci['CoID']?>"><img class="enter_now" src="<?=$sImageRoot?>lg_enter_now.gif"><span class="enter_now">Enter Now</span></a>
		
						
						</div>	
						
					</div>
					<div class="clr"></div>
				</div>
				<?
			}
		}
		
		// now display $iSummary - $iQuantity title-only contests, in random order out of the remaining ones	
		$iRemaining = count($contestItems);
		$iDisplayWithoutSummary = min($iQuantity - $iSummary, $iRemaining);
		if ($iDisplayWithoutSummary > 0) {
			$random_keys = array_rand($contestItems, $iDisplayWithoutSummary);
			for ($i = 1; $i <= $iDisplayWithoutSummary; $i++) {
				if (is_array($random_keys)) {
					$ci = $contestItems[array_shift($random_keys)];
				} else {
					$ci = $contestItems[$random_keys];
					$i = $iDisplayWithoutSummary;
				}

				if (!is_null($ci)) {
					$cssClass = ($i == $iDisplayWithoutSummary ? 'item lg_bottom' : 'item dashed_bottom');
					$contestTitle = ($ci['CoShortLinkName'] == '' ? $ci['CoName'] : $ci['CoShortLinkName']);

					?>
					<div class="<?=$cssClass?>">
						<div class="title_link"><a 
							href="<?=$sKiwiboxAspDev?>contest.asp?con=<?=$ci['CoID']?>"><?=fixCharacterSetDifferences(htmlspecialchars($contestTitle))?></a></div>
					</div>
					<?
				}
			}
		}
	}
}

function fetchContestItems() {
	global $cnMS;

	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getallcontests');
	} else { 		
		$skey= "getallcontest";
		$sQuery="SELECT CoID, CoName, CoShortLinkName, CoMicroImageSRC, CoShortDesc
				  FROM Contests WHERE CoStatus=2 OR CoStatus=3 OR CoStatus=4
				  ORDER BY CoDateStart DESC";
		 $arContestItems = getCachedQueryResults($skey,$sQuery,True,3600);
			return $arContestItems;
	}
	return array();
}

function fetchOpenContestItems() {
	global $cnMS;

	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getopencontests');
	} else { 		
		$skey= "getopencontest";
		$sQuery="SELECT CoID, CoName, CoShortLinkName, CoMicroImageSRC, CoShortDesc, CoStatus
				  FROM Contests WHERE CoStatus=2
				  ORDER BY CoDateStart DESC";
		 $arContestItems = getCachedQueryResults($skey,$sQuery,True,3600);
			return $arContestItems;
	}
	return array();
	
}

function displayContestDefault() {
	global $sKiwiboxRoot;
?>
	<div class="section">
			<div class="item">
				<div class="article_body">
					 Welcome to the Contest section of Kiwibox. This section can handle all your contest desires and questions. You can see what new contest are running, who won previous contests, how to win Backstage Passes and Concert Tickets, and other general things about KiwiContests.
					<br />
					<br />
				If you click on a link the details will appear where this message is now, so have fun and win stuff, cause that is what it's all about, having fun and winning free stuff, right?
				<br />
				<br />
				To see a list of winners from recent contests, <a href="<?=$sKiwiboxRoot?>winners/">Click here!</a>
				</div>
			</div>
	</div>
<?
	}

function displayContestDetails($iCoID) {
	global $currentSkin, $sectionSkin;

	$arContest = fetchContestDetails($iCoID);
	foreach($arContest as $co) {		
		?>
		<div class="section">
			<div class="article_top">
				<div class="article_header thick short_item"><span class="normal"><?=fixCharacterSetDifferences($co['CoShortLinkName']);?></span></div>
				<div class="small_section"></div><div class="small_section"></div>
				<div class="lg_bottom short_item"></div>
			</div>

			<div class="article_body">
				<?
				
				$sDetails = $currentSkin->replaceHeaderTags($co['CoContestDetails']);
				$sDetails = fixCharacterSetDifferences($sDetails);

				$search = array('<img ');
				$replace = array('<img class="item" ');

				$sDetails = str_replace($search, $replace, $sDetails);

				echo($sDetails);
				?>
			</div>
		</div>

		<div class="section clearfix">
			<? 
			$sectionSkin->tinyHeader('Official Rules');
			//$sectionSkin->miniHeader('Official Rules', 'officialrules.gif'); 
			?>
			<div class="article_body">
				<p>Please read the <a href="blob">Official Rules and Regulations</a>  for this contest.</p>
			</div>
		</div>

		<div class="section clearfix">
			<?// $sectionSkin->miniHeader('Tell Your Friends', 'tellyourfriends.gif'); ?>
			<div class="article_body">
				
				Tell your friends about this contest and you'll score <span class="thick">10 KiwiPoints</span> if 
				they check out the contest after you invite them. <span class="thick caps">And</span> this is one 
				of the contests where you actually get <span class="thick">EXTRA ENTRIES</span> for each friend 
				that clicks through the link you send them. Please tell us that we don't have to twist your arm 
				any more to tell your friends about this awesome contest. :) Go on. Do it :)
				
				<? // <p class="thick"><a href="blob">Click here to Invite Friends!</a></p> ?>
			</div>
		</div>
		<?
	}
}

function fetchContestDetails($iCoID) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getcontestdetails', $iCoID);
	} else {
		$sKey = sprintf('getcontestdetails_%u', $iCoID);
		return getCachedQueryResults($sKey, 
				"SELECT	* FROM Contests WHERE CoID= " . $iCoID .
				"ORDER	BY CoDateStart DESC",
				true, 3600);
	}
}

function displayContestWinnersDefault() {
	global $sKiwiboxRoot;
//	$arDefault = fetchContestRecentWinners();
//	foreach ($arDefault as $arD) {

		?>
			<div class="article_body item">
			Welcome to the Contest Winners section of Kiwibox. Here you can find out who won previous contests, how to win Backstage Passes and Concert Tickets, and other general things about KiwiContests.
			<br />
			<br />
			If you click on a link on the left, the details will appear where this message is now, so have fun and win stuff, cause that is what it's all about, having fun and winning free stuff, right?
			<br />
			<br />
			To see a list of current active contests you can enter, <a href="<?=$sKiwiboxRoot?>contest/">Click Here!</a>
			<br />
			<br />
			Kiwi Prizes usually take about 4 - 8 weeks to be shipped out. However, many Rewards and Prizes are shipped out directly from third party sponsors, and may take longer than usual. Kiwibox cannot guarantee the delivery time for items shipped by 3rd party companies. See disclaimer in Terms of Use. 
			</div>
		
		<?
//	}
}

function displayContestRecentWinners() {
	global $sKiwiboxRoot;
	$arRecent = fetchContestRecentWinners();
	?><div class='section'><?
	foreach ($arRecent as $arR) {
		?>
		<div class="article_top item">
		<a href="<?=$sKiwiboxRoot.'winner/'.$arR['CoID']?>"><?=$arR['CoName']?></a>
		</div>
		<?
	}
	?></div><?
}

function displayEndOfContests($iCoID) {
	global $currentSkin, $sectionSkin, $sKiwiboxAspDev;

	$arResult = fetchContestRecentWinnersResults($iCoID);
	foreach ($arResult as $Result) {
	?>
    <div class="section item">
        <a href="<?=$sKiwiboxAspDev . 'contest.asp?con=' . $Result['CoID']?>"><div class="title_link"><?=$Result['CoName'] ?></div></a>
        <?
        echo $currentSkin->replaceHeaderTags($Result['CoWinnersList']);
        ?>
        </div>
        <?
	}
}

function fetchContestRecentWinners() {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getcontestrecentwinners');
	} else {
		$sKey = ('getcontestrecentwinners');
		return getCachedQueryResults($sKey,
			"SELECT	CoID, CoName FROM Contests " . 
			"WHERE	CoStatus=4 ORDER BY CoDateEnd DESC",
			true, 3600);
	}
}

function fetchContestRecentWinnersResults($iCoID) {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('getcontestrecentwinnersresults', $iCoID);
	} else {
		$sKey = sprintf('getcontestrecentwinnersresults_%u', $iCoID);
		return getCachedQueryResults($sKey,
			"SELECT	CoID, CoName, CoWinnersList, CoStatus, CoType FROM Contests " .
			"WHERE	CoID= " . $iCoID . " AND CoStatus > 3 ",
			true, 3600);
	}
}

function getShortSummary($desc, $iDescLength = 106) {
	if (strlen($desc) > $iDescLength) {
		return substr($desc, 0, $iDescLength) . "...";
	} else {
		return $desc;
	}
}

?>
