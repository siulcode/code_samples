<?php
/*
 * Copyright (c) 2008 Kiwibox Media, Inc.
 * File: query.kiwibox.com/query/query.articles.php
 * Created: 03/07/2008 by Luis Lopez
 * Extended: 03/26/2008 by Ivan Tumanov
 */
require_once(FRAMEWORK_ROOT . 'include.articles.php');

class KBQuery_get_articles extends KBQuery {
	function KBQuery_get_articles() {
		parent::KBQuery('get-articles');
	}

	function Execute($params, $filter) {
		global $cnMS, $cn,$urlParameters ;
		
		$sDir= $params->pop('all');
		$iQuantity = $params->pop_numeric(25);
		$CaTitle=fetchURLParameter_text(0,'cat','all');
		

		switch (strtolower($sDir)){
			case ('music'):
			$sArticleCats="(59,60,61)";
			break;
			case ('entertainment'):
			$sArticleCats="(18,22)";
            break;
			case ('games'):
			$sArticleCats="(75,28)";
            break;
			case ('style'):
			$sArticleCats="(15,16)";
            break;
			case ('life'):
			$sArticleCats="(14,20,62,70,74)";
       		break;
			default:
			$sArticleCats="(14,15,16,18,20,22,28,59,60,61,62,70,74,75)";
		}

		if ($cnMS->query(
			"	SELECT	TOP {$iQuantity}
						ArID AS ArticleID,
						ArTitle AS Title, 
						EdNickname AS Author, 
						ArDatePosted AS DatePosted,
						ArText AS FullText,
						ArDirID AS Section,
						ArSummary AS ShortDescription,  
						ArThumbMode, ArDateSubmitted
				FROM	Articles LEFT JOIN Editors ON ArAuthorID=EdID 
				WHERE   ArShow=1 AND ArStatus=4
				AND		ArDirID in " .$sArticleCats."
				ORDER	BY ArDatePosted DESC")) {
			while ($a = $cnMS->next_array()) {
				$iThumbMode = $a['ArThumbMode'];
				$tsDateSubmitted = strtotime($a['ArDateSubmitted']);
				$iArID = $a['ArticleID'];

				unset($a['ArThumbMode']);
				unset($a['ArDateSubmitted']);

				$a['ThumbnailImageURL'] = articleThumbURL($iArID, $iThumbMode, $tsDateSubmitted);
				$a['FullText'] = implode('<hr>',replaceArticleTags($iArID, fixCharacterSetDifferences($a['FullText']), $tsDateSubmitted));
				$a['Section'] = $cn->getSingleValue(
					'SELECT CaTitle FROM ContentAreas WHERE CaDirID=' . $a['Section'], 'CaTitle', ''); 
				$filter->FilterRow($a);
			}
			
			return true;
		}

		return false;
	}
}

?>
