<?
/*
	Copyright (c) 2004-2007, Datacup LLP <license@datacup.com> http://datacup.com
	Customization Copyright (c) 2008, Kiwibox Media, Inc.
	File: include.madlibs.php
	Customized: 02/11/08
	Created by: Luis Lopez
*/

function displayMadLibs() {
	$item = fetchMadLibs();
	$itemcount = count($item);
	$n = 1;
	?>
	<div class="section">
	Here's a list of available KiwiMadlibs. Have fun and check back every week, because we are adding many more!
	</div>
	<?
	foreach ($item as $ar) {
		$cssClass = ($n == $itemcount ? 'item lg_bottom' : 'item dashed_bottom');
			?>
			<div class="title_link item">
				<a href="http://www.kiwibox.com/kiwimadlibs.asp?id=<?=($ar['MlName'])?>"><?=(htmlspecialchars($ar['MlName']))?></a>
			</div>
			<?

	}
}

function fetchMadLibs() {
	if (LOCAL_DEV) {
		return serializedLocalDevQuery('madlibs');
	} else {
		return getCachedQueryResults('madlibs',
			"SELECT * FROM Madlibs ORDER BY MlLastUpdated DESC",
			true, 3600);
	}
}

/*
	- id in displayMadLibs is $ar['MlName'] instead of $ar['MlID'], print_r to see if MlID is the right column name
	- convert fetchMadLibs and associated query file to only grab MlID, MlName
	- create fetchMadLibDetails function that returns SELECT * FROM Madlibs WHERE MlID=##mlid## and caches it in sprintf('madlib_%u',$iMlId)
	- create madlib.php and associate /madlib/##### with it
	- create displayMadLib($iMlID) to output details of that particular madlib like kiwimadlibs.asp does now
	- ask ivan to help port ASP code
*/

?>
