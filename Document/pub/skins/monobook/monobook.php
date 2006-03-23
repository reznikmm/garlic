<?php /*>*/ if (!defined('PmWiki')) exit();
#
# monobook/monobook.php v0.5
#
# PmWiki Monobook skin code
# Copyright 2005 Dominique Faure (dfaure@cpan.org)
# This file is part of the PmWiki Monobook skin; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or (at your
# option) any later version.
#
# 05/06/01: Initial release.
# 05/06/03: Handling of forgotten ?action=print and minor tweaks.
# 05/06/09: Applied suggested changes and relooked print action.
# 05/06/10: Added support of RightBar sub pages compatible with HansB's
#           GeminiSkin and FixFlowSkin.
# 05/06/16: Corrected RightBar placement and added handling of Site group and
#           related directives.
# 05/06/17: Fixed forgotten AllRecentChanges link.
# 05/08/05: Revamped attachment links. Dropped deprecated WikiHelp link in page
#           footer. Fixed underlined titles style.
# 05/09/05: Fixed several visual bugs including latest SideBar styling.
# 05/09/07: Added handling of Site.PageActions (introduced with PmWiki v2.0
#           default skin).
# 05/10/06: Added header customization feature.
#

# Get current query string to construct *real* internal link.
# aka.: <a href='$PageUrl$PageQueryString#topcontent'>...</a>
global $PageQueryString;
$PageQueryString = $_SERVER["QUERY_STRING"] ? '?' . $_SERVER["QUERY_STRING"] : '';

# Skin Parts handling
global $SkinPartFmt, $WikiTitle;
SDVA($SkinPartFmt, array(
'wikititle' => "$WikiTitle - {\$Titlespaced}",
'title' => '{$Titlespaced}',
'pageactions' => '
* [[$[View] -> {$FullName}]] %comment%[[{$Groupspaced}/&hellip; -> {$Group}]]%%
* [[$[Edit Page] -> {$FullName}?action=edit]]
* [[$[Page Attributes] -> {$FullName}?action=attr]]
* [[$[Page History] -> {$FullName}?action=diff]]
* [[$[Upload] -> {$FullName}?action=upload]]
',
'leftbardisabled' => false,
'tabsdisabled' => false,
));

function RenderStyle($pagename, $params) {
  global $SkinPartFmt;
  preg_match('/^\s*(!?)\s*(\S+)\s*(.*)$/s', $params, $m);
  $bool = $SkinPartFmt[$m[2]];
  print ($m[1] ? ! $bool : $bool) ? "<style type='text/css'>$m[3]</style>" : '';
}

function RenderMarkupText($pagename, $part, $fmt) {
  global $SkinPartFmt, $PCache;
  $n = "skin_$part";
  if(!isset($PCache[$pagename][$n]))
    $PCache[$pagename][$n] = preg_replace($fmt, '', MarkupToHTML($pagename, $SkinPartFmt[$part]));
  print $PCache[$pagename][$n];
}

function RenderTitle($pagename) {
  RenderMarkupText($pagename, 'wikititle', "/(<[^>]+>|\r\n?|\n\r?)/");
}
function RenderPart($pagename, $part) {
  RenderMarkupText($pagename, $part, "/<\/?p>/");
}

function RetrievePageMarkup($pagelist) {
  foreach($pagelist as $p) {
    if (PageExists($p)) {
      $page = RetrieveAuthPage($p, 'read', false, READPAGE_CURRENT);
      $text = $page['text'];
      break;
    }
  }
  return $text;
}

function RenderActions($pagename, $actionslist) {
  global $action, $SkinPartFmt;
  $pagelist = preg_split('/\s+/', $actionslist, -1, PREG_SPLIT_NO_EMPTY);
  $text = RetrievePageMarkup($pagelist);
  SDV($text, preg_replace("/(\r\n|\n?\r)/", "\n", $SkinPartFmt['pageactions']));
  $ls = explode("\n", MarkupToHTML($pagename, $text));
  $lRe = "|(.*?)<a.*?href='(.*?)'.*?>(.*?)</a>(.*)|i";
  foreach($ls as $i => $l) {
    if(preg_match($lRe, $l, $l1)) {
      $laction = preg_match("/action=(.*)/i", $l1[2], $a) ? $a[1] : 'browse';
      if($action == $laction) {
          $ls[$i] = $l1[1];
        if($l1[4] && preg_match($lRe, $l1[4], $l2))
          $ls[$i] .= "<a id='active' href='" . $l2[2] . "'>" . $l2[3] . "</a>";
        else
          $ls[$i] .= "<p id='active'>" . $l1[3] . "</p>";
      }
    }
  }
  print implode("\n", $ls);
}

function RenderDivPart($pagename, $params) {
  $pagelist = preg_split('/\s+/', $params, -1, PREG_SPLIT_NO_EMPTY);
  $class = array_shift($pagelist);
  $id = array_shift($pagelist);
  $text = RetrievePageMarkup($pagelist);
  if(!isset($text)) return;
  print "<div class='$class' id='$id'>";
  print MarkupToHTML($pagename, $text);
  print "</div><!-- id='$id' -->";
}

# Markup extension
# (:noleft:), (:nosidebar:) => remove sidebar
# (:notabs:) => remove tabs above page content
# (:noprint:) => remove print action link
#
Markup('noleft', 'directives', '/\\(:noleft:\\)/ei', "NoLeftBar()");
Markup('nosidebar', 'directives', '/\\(:nosidebar:\\)/ei', "NoLeftBar()");
Markup('notabs', 'directives', '/\\(:no(action|tab)s?:\\)/ei', "NoTabs()");

function NoLeftBar() {
  global $SkinPartFmt;
  $SkinPartFmt['leftbardisabled'] = true;
  SetTmplDisplay('PageLeftFmt', 0);
}

function NoTabs() {
  global $SkinPartFmt;
  $SkinPartFmt['tabsdisabled'] = true;
  SetTmplDisplay('PageTabsFmt', 0);
}

# link decoration
# links decoration
global $EnableSkinLinkDecoration, $IMapLinkFmt, $LinkPageCreateFmt, $LinkUploadCreateFmt;
if(IsEnabled($EnableSkinLinkDecoration, 1)) {
  $IMapLinkFmt['Attach:'] = "<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='$SkinDirUrl/attachment.png' /></a>";
  $LinkUploadCreateFmt = "<a class='createlinktext' href='\$LinkUpload'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='$SkinDirUrl/attachnew.png' /></a>";
  $LinkPageCreateFmt = "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";
}

# Right bar handling
global $Now, $RightBarClass;
SDV($RightbarCookieExpires, $Now + 60*60*24*365); # cookie expire time defaults to 1 year
SDV($DefaultRightBar, 'narrow');
$PageRightBarList = array (
  '0'      => 'rb-none',
  'off'    => 'rb-none',
  'on'     => 'rb-narrow',
  '1'      => 'rb-narrow',
  'narrow' => 'rb-narrow',
  '2'      => 'rb-normal',
  'normal' => 'rb-normal',
  '3'      => 'rb-wide',
  'wide'   => 'rb-wide',
);
if(isset($_COOKIE['setrb'])) $rb = $_COOKIE['setrb'];
if(isset($_GET['setrb'])) {
  $rb = $_GET['setrb'];
  setcookie('setrb', $rb, $RightbarCookieExpires, '/');
}
if(isset($_GET['rb'])) $rb = $_GET['rb'];

$RightBarClass = isset($PageRightBarList[$rb]) ? $PageRightBarList[$rb] : $PageRightBarList[$DefaultRightBar];

global $action;
SetTmplDisplay('PageRightFmt', 0);
Markup('noright', 'directives', '/\\(:noright:\\)/e',
  "SetTmplDisplay('PageRightFmt', 0)");
Markup('showright', 'directives', '/\\(:showright:\\)/e',
   ($action != 'browse') ? "" : "SetTmplDisplay('PageRightFmt', 1)");
