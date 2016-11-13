<?php
/*
 * Copyright (c) 2008, Kiwibox Media, Inc.
 * File: include.articles.php
 * Created: 01/23/2008 by Luis Lopez
 * Extended: 03/21/2008 by Ivan Tumanov
 *
 * This file contains functions and classes that are used to handle
 * articles and issues.
 */

require_once(FRAMEWORK_ROOT . 'library.forms.php');
include_once(FRAMEWORK_ROOT . 'include.archives.php');

/*
 * The KBIssue class encapsulates methods to retrieve information about Issues
 */
class KBIssue extends KBBase {
    var $IssueId;
    var $DateFrom;
    var $DateTo;
    var $Description;
    var $LetterFromTheEditor;
    var $CurrentIssue;
    var $CoverPic;
    var $LittleCoverPic;
    var $LogoCoverPic;

    function KBIssue($iIssueNumber) {
        parent::KBBase('Issue ' . $iIssueNumber);
        $this->fetchIssue($iIssueNumber);
    }

    function fetchIssue($iIssueId) {
        global $cnMS;

        $iCurrentIssue = getSafeInt(fetchSetting('Issue_Number'));

        if (!$iCurrentIssue) {
            $iCurrentIssue = $cnMS->getSingleValue(
                'SELECT TOP 1 IsID FROM Issues ' .
                'WHERE (GETDATE() BETWEEN IsDateFrom AND IsDateTo)',
                'IsID', false);
        }

        if ($iCurrentIssue) {
            if (!$iIssueId || ($iIssueId > $iCurrentIssue)) $iIssueId = $iCurrentIssue;

            $sKey  = sprintf('issuedetails_%u',$iIssueId);
            $issue = getFirstIf(getCachedQueryResults($sKey,
                "SELECT * FROM Issues WHERE IsID={$iIssueId}",
                true, 3600));
            $issue['CurrentIssueID'] = $iCurrentIssue;
        }

        if ($issue) {
            $this->IssueId             = $issue['IsID'];
            $this->Name                = $issue['IsName'];
            $this->DateFrom            = getIssueDates($issue['IsDateFrom']); //strtotime($issue['IsDateFrom']);
            $this->DateTo              = getIssueDates($issue['IsDateTo']); //strtotime($issue['IsDateTo']);
            $this->Description         = $issue['IsDesc'];
            $this->LetterFromTheEditor = $issue['IsLetterFromTheEditor'];
            $this->CoverPic            = $issue['IsCoverPic'];
            $this->LittleCoverPic      = $issue['IsLittleCoverPic'];
            $this->LogoCoverPic        = $issue['IsLogoCoverPic'];
            $this->CurrentIssue        = $issue['CurrentIssueID'];
            return true;
        }
        return false;
    }
}

class KBContentArea extends KBRowBase {
    function KBContentArea(&$row) {
        parent::KBRowBase($row, 'CaTitle', 'CaID');
    }

    function GetDirID() {
        return ($this->IsLoaded() ? $this->Row['CaDirID'] : false);
    }

    function GetName() {
        return ($this->IsLoaded() ? $this->Name : false);
    }

    function GetSkinColor() {
        return ($this->IsLoaded() ? $this->Row['CaSectionSkin'] : false);
    }

    function GetDirPrefix() {
        return ($this->IsLoaded() ? $this->Row['CaCatName'] : false);
    }

    function GetColorName() {
        return ($this->IsLoaded() ? $this->Row['CaColorName'] : false);
    }

    function GetDirURL() {
        if ($this->IsLoaded()) {
            $search = array(' ', '&');
            $replace = array('-', 'and');
            return str_replace($search, $replace, strtolower($this->Name));
        } else {
            return false;
        }
    }
}

function LoadKBArticle($iArID) {
    $row = fetchSingleArticle($iArID);
    return new KBArticle($row);
}

class KBArticle extends KBRowBase {
    var $ContentArea;

    function KBArticle(&$row) {
        parent::KBRowBase($row, 'ArTitle', 'ArID');
        if ($this->ContentArea = $this->FetchContentArea()) {
            // mark this article as not loaded if we can't load the content area
            if (!$this->ContentArea) $this->ID = false;
        }
    }

    function FetchContentArea($iOptionalDirID = false) {
        if ($this->IsLoaded()) {
            $iDirID = ($iOptionalDirID ? $iOptionalDirID : $this->Row['ArDirID']);
            if (LOCAL_DEV) {
                $ar = serializedLocalDevQuery('articles-get-contentarea', $iDirID);
            } else {
                $sKey = sprintf('content-area-%u', $iDirID);
                $ar = getCachedQueryResults($sKey,
                        "   SELECT  *
                            FROM    ContentAreas INNER JOIN Skins ON CaSectionSkin=SAbbr
                            WHERE   CaDirID={$iDirID}",
                            false, 3600);
            }
            return new KBContentArea(getFirstIf($ar));
        }
    }

    function GetTitle() {
        return ($this->IsLoaded() ? $this->Name : false);
    }




    function GetSkinColor() {
        $sColor= ($this->IsLoaded() ? $this->ContentArea->GetSkinColor() : false);
        if(strlen($sColor)<2){
            logDebug("Include.articles.php - 156.  No \$sColor found - hardcodeint to 'ly'");
            $sColor='ly';
            }
        return $sColor;

    }

    function GetDirPrefix() {
        return ($this->IsLoaded() ? $this->ContentArea->GetDirPrefix() : false);
    }

    function GetDirName() {
        return ($this->IsLoaded() ? $this->ContentArea->GetName() : false);
    }

    function GetDirID() {
        return ($this->IsLoaded() ? $this->ContentArea->GetDirID() : false);
    }

    function GetColorName() {
        $sColorName= ($this->IsLoaded() ? $this->ContentArea->GetColorName() : false);
        if(strlen($sColorName)<2){
            logDebug("Include.articles.php - 178.  No \$sColorName found - hardcodeint to 'ly'");
            $sColorName='ly';
            }
        return $sColorName;
    }

    function GetIssueID() {
        return ($this->IsLoaded() ? $this->Row['ArIssueID'] : false);
    }

    function GetDirURL() {
        return ($this->IsLoaded() ? $this->ContentArea->GetDirURL() : false);
    }

    function FullDisplay($iPage = 1) {
        global $sKiwiboxRoot, $currentUser, $sImageRoot, $sStaticRoot;;
        if ($this->IsLoaded()) {
            $sDirPrefix = $this->GetDirPrefix();
            $sTitle = htmlspecialchars($this->Name);
            $sDirURL = $this->GetDirURL();
            $sColor = $this->GetSkinColor();
            $sColorName = $this->GetColorName();

            ?>
            <div class="section clearfix" id="article">
                <div class="small_options">
                    <div class="small_hspace">
                        <div class="small_hspace">
                            <a class="small_options"
                                href="<?=$sKiwiboxRoot?>in-this-issue/<?=$this->GetIssueID()?>">In This Issue</a>
                            &raquo;
                            <a class="small_options" href="<?=$sKiwiboxRoot . $sDirURL?>"><?=htmlspecialchars($this->ContentArea->Name)?></a>
                            &raquo;
                            <?=htmlspecialchars($this->Name)?>
                            <div class="small_vspace"></div><div class="small_vspace"></div><div class="small_vspace"></div>
                        </div>
                    </div>

                    <div class="<?=$sColor?>_bottom"></div>

                    <div class="small_vspace"></div><div class="small_vspace"></div>

                    <div class="article_description">
                        <div class="article_header thick"><h1 class="article_header thick"><?=htmlspecialchars($this->Name)?></h1></div>
						
						<h2 class="fourteenpx normal "><?=htmlspecialchars($this->Row['ArSummary'])?></h2>
                        

							<div class="small_options">Written by:
                                <a class="thick small_options"
                                    href="<?=$sKiwiboxRoot?>more-by-author-of/<?=$this->ID?>"><?=htmlspecialchars($this->Row['EdNickname'])?></a>
                             &ndash; Posted: <?=prettyPrintCompactDate(strtotime($this->Row['ArDateSubmitted']))?>
                            </div>
                        </div>

                        <div class="<?=$sColor?>_bottom"></div>
                    </div>

                    <div id="article_body" class="item">
                        <div id="article_sidebar" class="bottomright_item_nc" >
                                <div class="fullBorder item">
                                    <div class="item <?=$sDirPrefix?>article_sidebar_header"><span
                                        class="thick caps bsarticle_sidebar_header italics">Author</span>
                                    </div>

                                    <table>
                                        <tr><td align="center">
                                        <? displayUserThumbCombo($this->Row['ULegalName']);?>
                                        </td></tr>
                                    </table>

                                </div>
                                <?
                                showArticleTools(null,$sDirPrefix);
                                //showArticleTags($ar);
                                showMoreBy($this->ID,$sDirPrefix);
                                //showMoreBy($ar);
                                ?>
                        </div>


                    </div>

                            <? $this->DisplayArticleBody($iPage); ?>

                            <div class="small_vspace"></div><div class="small_vspace"></div><div class="small_vspace"></div>

                            <?
                            $iNextArticle=nextArticle($this->GetDirName(),$this->ID);
                            ?>
                                <div class="article_footer align_right short_item">
                                    <span class="thick"><a href="<?=$sKiwiboxRoot?>article/<?=$iNextArticle?>">
                                        Next Article in <?=$this->ContentArea->Name;?> &raquo;</a></span>
                                </div>

                    </div>
                    <?if($currentUser->isLoggedIn()){
                        $this->displayCommentForm();
                    } else {
                    ?>
                    <div class=" clearfix">
                        <div class="item"></div>

                        <H6 class="graphic addcomment">Post Your Comment</H6>

                    <div class="section clearfix"><a href="#top_login">To leave a  comment, click here to login or sign up</a>
                    <div class="item"></div>
                    <div class="item"></div>
                    <textarea cols="80" rows="5" disabled="disabled" class="flat_text_input" value="" id="comment" name="comment"></textarea>
                    <div class="item"></div>
                    <div class="item"></div>
                    <input  class="lg_button" type="submit" disabled="disabled" name="post_comment" value="Post Comment" />
					&nbsp;&nbsp;
					<a href="#Earn 200 KP" rel="#TipArticleCommentPoints" class="ClueTip" onclick="return false;" ><img src="<?=$sStaticRoot;?>img/points/earn_200kp_arrow.gif" align="top" border="0"></a>
					<div id="TipArticleCommentPoints" style="display: none;">
						<b>Amount:</b> 200 KiwiPoints<br/>
						<b>KiwiPoints Awarded:</b> Tuesdays<br/>
						<br/>
						<b>How to earn KiwiPoints:</b> Post a constructive comment on any article from the current issue. On Mondays editors will choose ONE comment from every article and award the selected users 200 KiwiPoints. Yes you CAN earn points on multiple articles.
					</div>


                    </div></div><?
                    }

                    $arComments = fetchArticleComments($this->ID);
                    $iNumComments = count($arComments);

                    if ($iNumComments > 0 && is_array($arComments)) {
                        ?>
                        <div class="clr clearfix" id="view_comments">
                            <div class="item"></div>
                            <H6 class="graphic viewcomments">View Comments</H6>
                            <?
                            $iNumShown = 0;
                            foreach($arComments as $c) {
                                $iNumShown++;
                                ?>
                                <?if(($currentUser->hasPermision('W','helper'))or LOCAL_DEV or ($currentUser->getUsername()==$c['ULegalName'])) {
                                                    $own = $currentUser->getUsername()==$c['ULegalName']?TRUE:NULL;
                                                ?>
                                                <div class='right item_nc gray_border'>
                                                <a href="<?= $sKiwiboxRoot ?>delete/arComment/<?=$c['MsgID'] ?>/<?= $own ?>" onclick="window.open('<?= $sKiwiboxRoot  ?>delete/arComment/<?=$c['MsgID'] ?>/<?=$own ?>','delete','width=500,height=500,scrollbars=no,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0'); return false"> <img src="<?=$sImageRoot?>icon_trash.gif" class='thumb'></a>
                                                </div>
                                    <?} ?>

                                <div class="comment <?=($iNumComments == $iNumShown ? '' : 'dashed_bottom ')?> clearfix">
                                    <div class="comment_pic bottomless_item clearfix">
                                        <? displayUserThumbCombo($c['ULegalName']);?>
                                    </div>

                                    <div class=" item_nc">
                                        <?
                                        $sMsgText = $c['MsgText'];
                                        $WPtext = wordwrap($sMsgText, 66, "\n", true);
                                        $jeText =  "$WPtext\n";
                                        echo(nl2br(htmlspecialchars($jeText)));
                                        ?>

                                        <div class="comment_footer">
                                            <div class="small_options info"><span
                                                class="small_options">Posted
                                                <?=prettyPrintShortDate(strtotime($c['MsgDateCreated']))?>


                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?
        }
    }



    function displayCommentForm(){
        global $sKiwiboxRoot;
        $sColor = $this->GetSkinColor();
        $sColorName = $this->GetColorName();

        ?>
        <div class=" clearfix" id="add_comment">
            <div class="item"></div>
            <H6 class="graphic addcomment">Post Your Comment</H6>
            <?
            $form = new ArticleCommentForm($this->ID,$sColorName,$sColor);
            $form->execute();
            ?>
        </div>

    <?
    }

    function DisplayArticleBody($iPage = 1) {
        global $sKiwiboxRoot;
        if ($this->IsLoaded()) {
            $arPages = replaceArticleTags($this->ID, fixCharacterSetDifferences($this->Row['ArText']), strtotime($this->Row['ArDateSubmitted']));

            $iPageCount = count($arPages);
            if ($iPage < 1) $iPage = 1;
            if ($iPage > $iPageCount) $iPage = $iPageCount;

            $sBody = $arPages[$iPage - 1];

            // add the article_body class to paragraph tags
            $sBody = str_replace('<p>','<p class="article_body">', $sBody);
            ?>
            <div class="article_body">
                <?=$sBody?>
            </div>

            <div class="small_vspace"></div>
            <div class="article_footer align_right short_item">
            <?
            $sURLForPagination = $sKiwiboxRoot . 'article/' . $this->ID . '/';
            $pageControl = new KBPageControl($iPage,$sURLForPagination,$iPageCount,2,1,$this->GetSkinColor());
            $pageControl->showPageControls();


            ?></div><?
        }
    }
}

/*
 * replaceArticleTags() takes an article ID, a string containing the article body text,
 * as well as a timestamp of the article submission date.
 *
 * Please note that strtotime() needs to be applied to date values directly from the database.
 *
 * Returns an array of article pages
 */
function replaceArticleTags($iArID, $sBody, $tsDateSubmitted) {
    global $sKiwiboxRoot;

    $sSearches = array(
        '[heading]', '[/heading]',
        '[themeroot]',
        '[rbox]', '[/rbox]',
        '[lbox]', '[/lbox]',
        '[rimg]', '[/rimg]',
        '[limg]', '[/limg]',
        '[cimg]', '[/cimg]'
        );

    $sArImageRoot = 'http://arimages.kiwibox.com/ar/' . date('ym',$tsDateSubmitted) . '/' . $iArID . '.';

    $sReplacements = array(
        '<b>', '</b>',
        'http://images.kiwibox.com/theme/0',
        '<div style="width: 170px; float: right; padding: 5px; border: 1px solid #000;">', '</div>',
        '<div style="width: 170px; float: left; padding: 5px; border: 1px solid #000;">', '</div>',
        '<img style="margin: 0 0 5px 5px;" align="right" border="0" src="' . $sArImageRoot, '" />',
        '<img style="margin: 0 5px 5px 0;" align="left" border="0" src="' . $sArImageRoot, '" />',
        '<center><img border="0" src="' . $sArImageRoot, '" /></center>'
        );

    return explode('[page]', nl2br(str_replace($sSearches, $sReplacements, $sBody)));
}

function articleThumbURL($iArID, $iArThumbMode, $tsDateSubmitted, $sRootTagSansDate = 'http://arimages.kiwibox.com/ar/') {
    $sRootTag = $sRootTagSansDate . date('ym',$tsDateSubmitted) . '/';
    switch($iArThumbMode) {
        case 31: return $sRootTag . 'T32.' . $iArID . '.jpg';
        case 32: return $sRootTag . 'T32.' . $iArID . '.jpg';
        case 35: return $sRootTag . 'T32.' . $iArID . '.gif';
        case 36: return $sRootTag . 'T32.' . $iArID . '.gif';
        case 41: return $sRootTag . 'T48.' . $iArID . '.jpg';
        case 42: return $sRootTag . 'T48.' . $iArID . '.jpg';
        case 45: return $sRootTag . 'T48.' . $iArID . '.gif';
        case 46: return $sRootTag . 'T48.' . $iArID . '.gif';
        case 61: return $sRootTag . 'T64.' . $iArID . '.jpg';
        case 62: return $sRootTag . 'T64.' . $iArID . '.jpg';
        case 65: return $sRootTag . 'T64.' . $iArID . '.gif';
        case 66: return $sRootTag . 'T64.' . $iArID . '.gif';
        case 71: return $sRootTag . 'T75.' . $iArID . '.jpg';
        case 72: return $sRootTag . 'T75.' . $iArID . '.jpg';
        case 75: return $sRootTag . 'T75.' . $iArID . '.gif';
        case 76: return $sRootTag . 'T75.' . $iArID . '.gif';
    }
    return $sRootTag;
}

function displayRandomArticleRollovers() {
    global $sKiwiboxRoot, $sStaticRoot;

    ?>
    <script type="text/javascript" src="<?=$sStaticRoot?>js/boxover.js"></script>
    <?

    $arArticles = fetchRandomArticles(4);
    foreach($arArticles as $ar) {
        $iArID = $ar['ArID'];
        $h = str_replace(array('[',']'),'',htmlspecialchars($ar['ArTitle']));
        $b = htmlspecialchars('<div class="small_vspace small_hspace left_align">' .
                htmlspecialchars($ar['ArSummary']) . '</div><div class="small_vspace small_hspace left_align">' .
                '<a class="small_options" href=' . $sKiwiboxRoot . 'article/' . $iArID . '>click to read article</a></div>');
        $sThumbURL = articleThumbURL($iArID, $ar['ArThumbMode'], strtotime($ar['ArDateSubmitted']));
        ?>
        <span
            title="header=[<?=$h?>] body=[<?=$b?>] offsetx=[-260] offsety=[-40] windowlock=[off] cssheader=[box_over_header] cssbody=[box_over_body]"><a
            href="<?=$sKiwiboxRoot?>article/<?=$iArID?>"><img class="iti_cover_article_top" src="<?=$sThumbURL?>"></a></span>
        <?
    }
}

function displayArticleSummaryFromRow($ar, $sDivClass, $skin = false, $bShowDate = false, $bShowIssue = false) {
    global $sKiwiboxRoot, $sectionSkin;

    if (!$skin) $skin = $sectionSkin;

    $iArID = $ar['ArID'];
    $tsDateSubmitted = strtotime($ar['ArDateSubmitted']);
    $sThumbURL = articleThumbURL($iArID, $ar['ArThumbMode'], $tsDateSubmitted);
    $sArticleURL = $sKiwiboxRoot . 'article/' . $iArID;

    ?>
    <div class="<?=$sDivClass?>">
        <div class="tiny_item_nc fillout_box_7f">
            <a href="<?=$sArticleURL?>"><img class="thumb" src="<?=$sThumbURL?>" /></a>
        </div>
        <div class="left_short_item_nc fillout_box_20">
            <div class="lb_title_link"><a href="<?=$sArticleURL?>"><?=htmlspecialchars($ar['ArTitle'])?></a></div>
            <div class="details"><?=htmlspecialchars($ar['ArSummary'])?></div>
            <?
            $skin->readThisLink($sArticleURL);
            if ($bShowDate) {
                ?>
                <div class="meta" style="margin-top: 5px;"><?=prettyPrintCompactDate($tsDateSubmitted)?></div>
                <?
            }
            ?>
        </div>
        <div class="clr"></div>
    </div>
    <?
}


function skinNameToCSSName($sSkinName){

    switch ($sSkinName){
        Case ('Music Articles'):
            return 'header_music';
        break;
        Case ('Entertainment Articles'):
            return 'header_entertainment';
        break;
        Case ('Games Articles'):
            return 'header_gamestech';
        break;
        Case ('Life and Love Articles'):
            return 'header_lifelove';
        break;
        Case ('Bodystyle Articles'):
            return 'header_bodystyle';
        break;
        default:
            return FALSE;
    }
}

function displayArticleSummaries($iDirectory, $skin = false) {
    global $sectionSkin;


    if (!$skin) $skin = $sectionSkin;

        $articles = fetchArticleSummaries($iDirectory);

    $iTotal = count($articles);
    $iShown = 0;

    if($iTotal>0){
        $name =  $skin->Name;
        $class = skinNameToCSSName($name);
        ?><H6 class="iti graphic">In This Issue</H6><?
    }else{
    return false;
    }
    foreach($articles as $ar) {
        $iShown++;
        displayArticleSummaryFromRow($ar,
            'item ' . ($iShown >= $iTotal ? $skin->Color . '_bottom' : 'dashed_bottom'),
            $skin);
    }
}

function displayArticleSummariesCompact($iDirectory, $iIssueNumber = 0, $skin = false, $iColumns = 2) {
    global $sKiwiboxRoot, $sectionSkin;
    if (!$skin) { $skin = $sectionSkin; }

    $oIssue       = new KBIssue($iIssueNumber);
    $pubIssue     = $oIssue->CurrentIssue;

    if ($iIssueNumber < $pubIssue) {
        $articles = fetchArticleSummaries_ByIssue($iDirectory, $iIssueNumber);
        $iTotal   = count($articles);
        $iShown   = 0;
        $iInRow   = 0;
        foreach($articles as $ar) {
            $iShown++;
            $iInRow++;

            displayArticleSummaryFromRow($ar, 'item fillout_box_m', $skin);

            if ($iInRow == $iColumns) {
                ?><div class="<?=$skin->Color?>_bottom short_item"></div><?
                $iInRow = 0;
            }
        }
        if ($iShown > 0 && $iInRow > 0) {
            ?><div class="<?=$skin->Color?>_bottom short_item"></div><?
        }
    } else {
        $articles = fetchArticleSummaries($iDirectory);
        $iTotal   = count($articles);
        $iShown   = 0;
        $iInRow   = 0;
        foreach($articles as $ar) {
            $iShown++;
            $iInRow++;

            displayArticleSummaryFromRow($ar, 'item fillout_box_m', $skin);

            if ($iInRow == $iColumns) {
                ?><div class="<?=$skin->Color?>_bottom short_item"></div><?
                $iInRow = 0;
            }
        }
        if ($iShown > 0 && $iInRow > 0) {
            ?><div class="<?=$skin->Color?>_bottom short_item"></div><?
        }
    }
}

function fetchSingleArticle($iArID) {
    return getFirstIf(fetchArticle($iArID));
}

function fetchArticle($iArID) {
    $sKey = sprintf('article_%u',$iArID);
    return getCachedQueryResults($sKey,
        "   SELECT  ArID, ArDirID, ArDateSubmitted, ArText, ArSummary, ArTitle, ArIssueID,
                    EdNickname, EdID, EdUID, ULegalName
            FROM    Articles LEFT JOIN Editors ON ArAuthorID=EdID
                             LEFT JOIN Users ON EdUID=UID
            WHERE   ArID={$iArID} AND ArShow=1 AND ArStatus=4",
            true,3600);
}

// TODO: add pagination to article comments , $iQuantity = 10, $iOffset = 0
function fetchArticleComments($iArID) {
    $sKey = "articleComments_".$iArID;
    return getCachedQueryResults($sKey,
        "   SELECT  MsgID, MsgTitle, MsgDateCreated, MsgText, MsgUID, ULegalName
            FROM    ArticleMsgs INNER JOIN Users ON MsgUID=UID
            WHERE   MsgShow=1 AND ArID={$iArID}
            ORDER   BY MsgDateCreated DESC",
        true,3600);
}

function fetchArticleSummaries_ByIssue($iDirID, $iIssueId) {
    $sURI= trim($_SERVER['REQUEST_URI']);
    $sKey = sprintf('articlesummaries_%u_%s_%u',$iDirID,date('Ymdh'),$iIssueId);
    if(strlen($iDirID)<1){
        $iDirID=62;
        logDebug("arDirID missing in fetchArticleSummaries_ByIssue - $sURI ");
    }
    return getCachedQueryResults($sKey,
    "   SELECT  ArID, ArDirID, ArIssueId, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode FROM Articles
        WHERE   ArDirID={$iDirID} AND ArIssueId={$iIssueId} AND ArShow=1 AND ArStatus=4
        ORDER   BY ArDateModified DESC",
        true, 3600);
}

function fetchArticleSummaries($iDirID) {
    $sURI= trim($_SERVER['REQUEST_URI']);
    $sKey = sprintf('articlesummaries_%u_%s',$iDirID,date('Ymdh'));
    if(strlen($iDirID)<1){
        $iDirID=62;
        logDebug("arDirID missing in fetchArticleSummaries - $sURI ");
    }
    return getCachedQueryResults($sKey,
        "   SELECT  ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode FROM Articles
            WHERE   ArDirID={$iDirID} AND ArShow=1 AND ArArchived=0 AND ArStatus=4
            ORDER   BY ArDateModified DESC",
        true, 3600);
}

function nextArticle($sDir, $iArId) {
    $arArticles = fetchIssueArticleByDir($sDir);
    krsort($arArticles);
    foreach ($arArticles as $arArticle) {
        if($arArticle['ArID']>$iArId){
            return $arArticle['ArID'];
        }
    }

    if ($arArticles[(count($arArticles)-1)]['ArticleID']= $iArId) {
        foreach ($arArticles as $arArticle) {
            if ($arArticle['ArID']<$iArId) {
                return $arArticle['ArID'];
            }
        }
    }

    return false;
}

function fetchIssueArticleByDir($sDir) {
    switch (strtolower($sDir)) {
        case 'music':           $sArticleCats = "59,60,61"; break;
        case 'entertainment':   $sArticleCats = "18,22"; break;
        case 'games & tech':    $sArticleCats = "75,28"; break;
        case 'body & style':    $sArticleCats = "15,16"; break;
        case 'life & love':     $sArticleCats = "14,20,62,70,74"; break;
        default:                $sArticleCats = "14,15,16,18,20,22,28,59,60,61,62,70,74,75";
    }

    if (LOCAL_DEV) {
        return serializedLocalDevQuery('articles-get-bydir',$sDir);
    } else {
        $sKey = sprintf('articles-get-bydir_%s',$sDir);

        return getCachedQueryResults($sKey,
            "   SELECT  ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode
                FROM    Articles
                WHERE   ArShow=1 AND ArArchived=0 AND ArStatus=4 AND ArThumbMode > 0
                        AND ArDirID IN ({$sArticleCats})
                ORDER   BY ArID DESC",
            true, 3600);
    }
}

function fetchRandomArticles($iQuantity) {
    if (LOCAL_DEV) {
        $arOrdered = serializedLocalDevQuery('getallarticlesummaries','');
    } else {
        $sKey = sprintf('allarticlesummaries_%s',date('Ymdh'));

        $arOrdered = getCachedQueryResults($sKey,
            "   SELECT  ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode
                FROM    Articles
                WHERE   ArShow=1 AND ArArchived=0 AND ArStatus=4 AND ArThumbMode > 0
                ORDER   BY ArDateModified DESC",
            true, 3600);
    }

    if ($arOrdered && count($arOrdered) >= $iQuantity) {
        $random_keys = array_rand($arOrdered,$iQuantity);
        $random_articles = array();
        foreach($random_keys as $k) {
            $random_articles[] = $arOrdered[$k];
        }
        return $random_articles;
    }
    return array();
}

function fetchRandomArticlesByDir($iQuantity,$Dir='18,21,22') {
        $sql =" SELECT  ArID, ArDirID, ArTitle, ArSummary, ArDateSubmitted, ArThumbMode
                FROM    Articles
                WHERE   ArShow=1 AND ArArchived=0 AND ArStatus=4 AND ArThumbMode > 0
                AND     ArDirID IN ({$Dir})
                ORDER   BY ArDateModified DESC";
        $Dir = str_replace(',','_',$Dir);
        $sKey = 'allarticlesummariesbydir_'.$Dir;
        $arOrdered = getCachedQueryResults($sKey,$sql,
            true, 3600);


    if ($arOrdered && count($arOrdered) >= $iQuantity) {
        $random_keys = array_rand($arOrdered,$iQuantity);
        $random_articles = array();
        foreach($random_keys as $k) {
            $random_articles[] = $arOrdered[$k];
        }
        return $random_articles;
    }
    return array();
}



function showSectionArchivesLink($iSectionID, $sTitle, $sExtraStyle = '') {
    global $sRoot;
    ?>
    <div class="item thick"<?=($sExtraStyle != '' ? ' style="' . $sExtraStyle . '"' : '')?>>
        <a href="<?=$sRoot?>section-archives/<?=$iSectionID?>"><?=htmlspecialchars($sTitle)?></a>
    </div>
    <?
}

function showArticleTools($ar, $sDirPrefix='lg') {
    global $sImageRoot;
    if (true) {
        ?>
                    <div class="item"></div>

                    <div class="fullBorder item">
                        <div class="item <?= $sDirPrefix ?>article_sidebar_header">
                            <span class="thick caps elevenpx bsarticle_sidebar_header italics">Tools</span>
                        </div>
                        <div class="short_item"><div class="item thick">
                            <script type="text/javascript" src="http://w.sharethis.com/widget/?tabs=web%2Cpost%2Cemail&amp;charset=utf-8&amp;services=%2Cfacebook%2Cmyspace%2Cstumbleupon%2Cdigg%2Creddit%2Cdelicious&amp;style=default&amp;publisher=c6c1dee0-1f7f-46eb-afd5-319329f9809b&amp;inactivefg=%23333333&amp;linkfg=%23004F00&amp;offsetLeft=-300&amp;offsetTop=0"></script>
                        </div></div>
                    </div>

        <!--<div id="bsarticle_tools">
        <div class="lp_bottom item bsarticle_sidebar_header"><span
            class="thick caps bsarticle_sidebar_header italics">Article Tools</span></div>
        <div class="short_item lp_bottom">
        <div class="item thick dashed_bottom"><a href="#123"><img
            class="icon_small" src="<?=$sImageRoot?>icon_small_textsize.gif" /></a><a
            href="#">Text Size</a></div>
        <div class="item thick dashed_bottom"><a href="#123"><img
            class="icon_small" src="<?=$sImageRoot?>icon_small_print.gif" /></a><a
            href="#">Print</a></div>
        <div class="item thick dashed_bottom"><a href="#123"><img
            class="icon_small" src="<?=$sImageRoot?>icon_small_singlepage.gif" /></a><a
            href="#">Single Page</a></div>
        <div class="item thick dashed_bottom"><a href="#123"><img
            class="icon_small" src="<?=$sImageRoot?>icon_small_share.gif" /></a><a
            href="#">Share</a></div>
        <div class="item thick"><a href="#123"><img
            class="icon_small" src="<?=$sImageRoot?>icon_small_rss.gif" /></a><a
            href="#">RSS</a></div></div>
        </div>-->
        <?
    }
}

function showArticleTags($ar) {
    if (false) {
        ?>
        <div id="bsrelated_tags">
        <div class="lp_bottom item bsarticle_sidebar_header"><span
        class="thick caps bsarticle_sidebar_header italics">Related Tags</span></div>

        <div class="item thick lp_bottom tags">
            <span class="tags"><a href="#454">Fashion</a></span>
            <span class="tags"><a href="#454">Betsey Johnson</a></span>
            <span class="tags"><a href="#454">Zac Posen</a></span>
            <span class="tags"><a href="#454">Stell McCartney</a></span>
            <span class="tags"><a href="#454">Charity</a></span>
            <span class="tags"><a href="#454">Giving Back</a></span>
            <span class="tags"><a href="#454">Organic</a></span>
            <span class="tags"><a href="#454">Donna Karan</a></span>
        </div>
        </div>
        <?
    }
}


function showMoreBy($id, $sDirPrefix='lg') {
    global $sKiwiboxRoot;
    $arSectionItems = fetchArticlesByAuthor($id);
    /*
    $arSectionItems =
        array (
            0 => array('ArID' => 24567, 'ArTitle' => 'Bob goes to Washington'),
            1 => array('ArID' => 25567, 'ArTitle' => 'Dancing Monkeys'),
            2 => array('ArID' => 26567, 'ArTitle' => 'This is amazing!'),
            3 => array('ArID' => 25567, 'ArTitle' => 'Kiwis from Down Under'),
            4 => array('ArID' => 25567, 'ArTitle' => 'Too Much Information')
        );
     */
    ?>
    <div class="item"></div>
    <div class="fullBorder item">
        <div class="item <?= $sDirPrefix ?>article_sidebar_header"><span
            class="thick caps bsarticle_sidebar_header italics">More by this author</span>
        </div>
        <ul class="moreByAuthor">
            <?
            $i     = 0;
            $iLast = min(count($arSectionItems), 4);
            foreach ($arSectionItems as $article) {
                if ($i < $iLast) {
                    ?><li class="item<?= ($i == $iLast - 1) ? ' bottom' : '' ?>"><a class="thick"
                                href="<?=$sKiwiboxRoot?>article/<?=$article['ArID']?>"><?=$article['ArTitle']?></a></li>
                    <?
                    $i++;
                }
            }
            ?>
        </ul>
        <div class="item right_align">
            <a class="thick" href="<?=$sKiwiboxRoot?>more-by-author-of/<?=$id?>">Show More</a>
        </div>
    </div>
    <?
}


function isArticleComentOwner($aUNameAsk,$iArticleCommentID){
    $iArticleCommentOwner = fetchArCommentOwner($iArticleCommentID);
    return $aUNameAsk == $iArticleCommentOwner ? TRUE: false;
}


function fetchArCommentOwner($iArticleCommentID){
    $arArticleComment = getCommentById($iArticleCommentID);
    $ar=getFirstIf($arArticleComment);
    return $ar['MsgUID'];
}


function fetchArCommentParent($iArticleCommentID){
    $arArticleComment = getCommentById($iArticleCommentID);
    $ar=getFirstIf($arArticleComment);
    return $ar['ArID'];
}


function getCommentById($iArticleCommentID){
    $sql  = "SELECT * FROM ArticleMsgs WHERE MsgID = " . $iArticleCommentID;
    $sKey = "articleCommentById_" . $iArticleCommentID;
    return getCachedQueryResults($sKey, $sql, true, 3600);
}

function changeArticleCommentStatus($msgId){
    global $cnMS;

    $sql    = " UPDATE ArticleMsgs SET MsgShow = 0 WHERE MsgID = " . $msgId;
    $return = $cnMS->query($sql);
    $iArId  = fetchArCommentParent($msgId);
    $sKey   = 'articleComments_' . $iArId;
    clearMCache($sKey);
    return $return;
}


class ArticleCommentForm extends KBForm {
    var $iArID;

    function ArticleCommentForm($iArID, $sColorName, $sColor ) {
        global $sKiwiboxRoot;
        parent::KBForm('ArticlesComment', $sKiwiboxRoot . 'article/' . $iArID, 'item');
        $this->iArID      = $iArID;
        $this->sColorName = $sColorName;
        $this->sColor     = $sColor;
    }

    function LoadValues() {
        return true;
    }

    function ProcessPOST() {
        global $cnMS, $currentUser;

        if (!$currentUser->isLoggedIn()) { return false; }

        $sComment = fetchGP_text('comment','');
        if ($sComment == '') { return false; }

        $iUID  = $currentUser->getUID();
        $iArID = $this->iArID;
        $this->NewValues['comment'] = $sComment;
        $this->NewValues['arid'   ] = $iArID;

        $sQuery = " INSERT INTO ArticleMsgs (ArID, MsgTitle, MsgDateCreated, MsgText, MsgUID, MsgShow) " .
                    " VALUES(" . $iArID . ", '', GETDATE(), '" . strdbMSSQL($sComment) . "', " . $iUID . ", 1)";
        if (!$cnMS->query($sQuery)) { return false; }

        $sKey = "articleComments_" . $iArID;
        clearMCache($sKey);
        return true;
    }

    function Display() {
        global $currentSkin, $sStaticRoot;

        ?>
        <div class="item">
            <div class="narrow_item_nc">
                <textarea cols="75" rows="5" class="flat_text_input" value="" id="comment" name="comment"></textarea>
            </div>

            <input class="<?= $this->sColor ?>_button" type="submit" name="post_comment" value="Post Comment" />
			&nbsp;&nbsp;
			<a href="#Earn 200 KP" rel="#TipArticleCommentPoints" class="ClueTip" onclick="return false;" ><img src="<?=$sStaticRoot;?>img/points/earn_200kp_arrow.gif" align="top" border="0"></a>
			<div id="TipArticleCommentPoints" style="display: none;">
                <b>Amount:</b> 200 KiwiPoints<br/>
                <b>KiwiPoints Awarded:</b> Tuesdays<br/>
                <br/>
                <b>How to earn KiwiPoints:</b> Post a constructive comment on any article from the current issue. On Mondays editors will choose ONE comment from every article and award the selected users 200 KiwiPoints. Yes you CAN earn points on multiple articles.
            </div>
        </div>
        <?
    }
}


?>