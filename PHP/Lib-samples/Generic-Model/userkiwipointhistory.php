<?
/*
	Copyright (c) 2007 Kiwibox Media, Inc.
	File: mypolls.php
	Created: 01/22/08 by Luis Lopez
*/
require_once('include.config.php');
include_once('framework/include.userkiwipointhistory.php');

startKiwiboxUI('My Polls');
startLeftColumn(true, true);

endLeftColumn();

startMiddleColumn();
	$currentSkin->startColorBox('','Your 20-day Kiwipoint History');
		?>
		<P>
		Here is your Kiwipoint history.  Adding up these points will
		not produce your current KiwiPoint balance.
		</P>
		<?
		displayUserKiwiPointHistory();
	$currentSkin->endColorBox();
endMiddleColumn();

startRightColumn();
	$currentSkin->startColorBox('','Questions?');
		?>
		<P>
		This Kiwipoint history catalogs most of the KiwiPoint activity 
		associated with your account.  If you have
		questions about something, take a look at the <a href="faq.asp">F.A.Q.</a>.
		</P>
		<p>
		* The daily login points have been successfully given out to you every
		day that you have logged in, however, the points history only started to 
		record it on 3/15/00.  Please don't worry if you don't see daily login
		history for any days before 3/15/00.
		</p>
		<?
	$currentSkin->endColorBox();

	$currentSkin->startColorBox('','How do I get KiwiPoints?');
		?>
		<P>
		For information about how you can earn and redeem KiwiPoints, take a look at
		<a href="http://www.kiwibox.com/kiwipoints.asp">KiwiPoints Information</a>.
		</P>
		<?
	$currentSkin->endColorBox();
endRightColumn();
endKiwiboxUI();

function displayUserKiwiPointHistory() {
	global $currentUser;
	if ($currentUser->isLoggedIn()) {
		$username = $currentUser->getUsername();
		$history = fetchUserKiwiPointHistory($username);
		
		?>	
		<table border="0" cellspacing="0" cellpadding="3">
		<?
		if (count($history) < 1) {
			?>
			<tr><td>You have nothing in your KiwiPoint History!</td></tr>
			<?
		} else {
			?>
			<tr>
				<td align="center">Date</td>
				<td align="center">Points</td>
				<td align="center">Description</td>
			</tr>
			<?
			foreach($history as $h) {
				?>
				<tr><td>
				<?=prettyPrintCompactDate(strtotime($h['KhDate']))?>
				</td><td align="center">
				<?=$h["KhPoints"]?>
				</td><td>
				<?
				if (is_null($h['KpName'])) {
					$iType = $h['KhType'];
					if ($iType < 0) {
						echo("Redeemed for KiwiReward (" . (0 - $iType) . ")");
					} else {
						echo('Unspecified');
					}
				} else {
					echo($h['KpName']);
				}
			
				echo('</td></tr>');
			}
		}
		echo('</table>');
	} else {
		echo('<P>You need to be a member of Kiwibox and logged in if you want to check your Kiwipoint History!</P>');
	}
}
?>