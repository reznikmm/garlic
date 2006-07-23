<?php /*>*/ if (!defined('PmWiki')) exit();
#
# monobook/monobook.php
#
# PmWiki Monobook skin code
# Copyright 2005-2006 Dominique Faure (dfaure@cpan.org)
#
# Based on original Monobook's mediawiki skin.
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#
# See http://www.pmwiki.org/wiki/Cookbook/MonobookSkin for info.
#
define(MONOBOOK_VERSION, '2006-04-28');

# Skin Parts handling
global $SkinPartFmt, $WikiTitle;
SDVA($SkinPartFmt, array(
'wikititle' => "$WikiTitle - {\$Titlespaced}",
'title' => '{$Titlespaced}',
'pageactions' => '
* %item rel=nofollow% [[$[View] -> {$FullName}?action=browse]] %comment%[[{$Groupspaced}/&hellip; -> {$Group}]]%%
* %item rel=nofollow% [[$[Edit Page] -> {$FullName}?action=edit]]
* %item rel=nofollow% [[$[Page Attributes] -> {$FullName}?action=attr]]
* %item rel=nofollow% [[$[Page History] -> {$FullName}?action=diff]]
* %item rel=nofollow% [[$[Upload] -> {$FullName}?action=upload]]
',
'leftbardisabled' => false,
'tabsdisabled' => false,
'attachalias' => 'AttachClip:',
'rightbardisabled' => true,
));
global $SkinPartStylesFmt;
SDVA($SkinPartStylesFmt, array(
'leftbardisabled' => '#content { margin-left: 0; }',
'tabsdisabled' => '#header { border-bottom: none; }',
));

# Ensure local CSS customization files are included *after* skin style
global $HTMLHeaderFmt;
$HTMLHeaderFmt[] = "<link rel='stylesheet' type='text/css' href='\$SkinDirUrl/monobook.css' />\n";

function RenderStyle($pagename, $params) {
  global $SkinPartFmt, $SkinPartStylesFmt;
  preg_match('/^\s*(!?)\s*(\S+)$/s', $params, $m);
  $bool = $SkinPartFmt[$m[2]];
  $style = $SkinPartStylesFmt[$m[2]];
  print ($m[1] ? ! $bool : $bool) ? "<style type='text/css'>$style</style>" : '';
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

# links decoration
global $EnableSkinLinkDecoration;
if(IsEnabled($EnableSkinLinkDecoration, 1)) {
  global $SkinPartFmt, $IMapLinkFmt, $LinkFunctions, $IMap;
  global $LinkPageCreateFmt, $LinkUploadCreateFmt, $UrlLinkFmt;

  $intermap = $SkinPartFmt['attachalias'];

  $IMapLinkFmt['Attach:'] = "<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a>";
  $IMapLinkFmt[$intermap] = "<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='$SkinDirUrl/attachment.png' /></a>";
  $LinkFunctions[$intermap] = 'LinkUpload';
  $IMap[$intermap] = '$1';

  $LinkPageCreateFmt = "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";
  $LinkUploadCreateFmt = "<a class='createlinktext' href='\$LinkUpload'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='$SkinDirUrl/attachnew.png' /></a>";
#  $UrlLinkFmt =  "<span class='urllink'><a class='urllink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a></span>";
}

# $StopWatch handling
global $EnableDiag, $FmtP;
if(!$EnableDiag) $FmtP['/\\$StopWatch/e'] = '';

# Right bar handling -- WARNING: this is un-maintained code
if($SkinPartFmt['rightbardisabled'])
  SetTmplDisplay('PageRightFmt', 0);
else
{
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
}
