<?php /*>*/ if (!defined('PmWiki')) exit();
#
# monobook v0.1
#
# PmWiki Monobook skin code
# Copyright 2005 Dominique Faure (dfaure@cpan.org)
# This file is part of the PmWiki Monobook skin; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or (at your
# option) any later version.
#
# + - - - +
# .sidebar. + +---+ +---+ +---+ - - - - - +
# .(logo) . . | (tabs)  | |   |      main .
# .       . +-+---+-+---+-+---+-----------+
# .       . |tabpage                      |
# +-------+ | content                     |
# |(page) | |                             |
# |       | |                             |
# |       | |                             |
# +-------+ |                             |
# .       . |                             +
# +-------+ |                             |
# |search | +-----------------------------+
# +-------+ +-----------------------------+
#           | footer                      |
#           +-----------------------------+
#
# Get current query string to construct *real* internal link.
# aka.: <a href='$PageUrl$PageQueryString#TopOfPage'>...</a>
global $PageQueryString;
$PageQueryString =
  $_SERVER["QUERY_STRING"] ? '?' . $_SERVER["QUERY_STRING"] : '';

# Markup extension
# (:noleft:)  => remove sidebar
# (:notabs:)  => remove tabs above page content
# (:noprint:) => remove print action link
#
Markup('noleft', 'directives', '/\\(:noleft:\\)/ei', "NoLeftBar()");
Markup('notabs', 'directives', '/\\(:notabs:\\)/ei', "NoTabs()");
Markup('noprint', 'directives', '/\\(:noprint:\\)/ei',
  "SetTmplDisplay('PagePrintFmt',0)");

$SkinParts = array();

function NoLeftBar() {
  global $SkinParts;
  $SkinParts['LeftBarDisabled'] = true;
  SetTmplDisplay('PageLeftFmt', 0);
}

function NoTabs() {
  global $SkinParts;
  $SkinParts['TabsDisabled'] = true;
  SetTmplDisplay('PageTabsFmt', 0);
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

$RightBarClass = isset($PageRightBarList[$rb]) ?
  $PageRightBarList[$rb] : $PageRightBarList[$DefaultRightBar];

global $action;
SetTmplDisplay('PageRightFmt', 0);
Markup('noright', 'directives', '/\\(:noright:\\)/e',
  "SetTmplDisplay('PageRightFmt', 0)");
Markup('showright', 'directives', '/\\(:showright:\\)/e',
   ($action != 'browse') ? "" : "SetTmplDisplay('PageRightFmt', 1)");

function RenderStyle($pagename, $params) {
  preg_match('/^\s*(!?)\s*(\S+)\s*(.*)$/s', $params, $match);
  global $SkinParts;
  $bool = $SkinParts[$match[2]];
  print ($match[1] ? ! $bool : $bool) ? "<style type='text/css'>$match[3]</style>"
                                      : "";
}

# Contextual Tabs
$ArrActions = array();

function DefineAction($pagename, $params) {
  global $ArrActions;
  preg_match('/^(\S+)\s*([^\|]+)(\|(\S+))?$/s', $params, $match);
  $ArrActions[] = array('action' => $match[1],
                        'label' => $match[2],
                        'url' => $match[4]);
}

function RenderActions($pagename, $pageurl) {
  global $ArrActions, $action;

  foreach($ArrActions as $a)
    $ul .= '<li>'
        . ($action == $a['action'] ?
            ($a['url'] ?
              "<a id='active' href='$a[url]'>$a[label]</a>" :
              "<p id='active'>$a[label]</p>") :
            "<a href='$pageurl"
            . ($a['action'] == 'browse' ? ''
                                        : "?action=$a[action]")
            . "'>$a[label]</a>")
        . "</li>\n";
  print "<ul>\n$ul\n</ul>";
}

global $IMapLinkFmt, $LinkPageCreateFmt, $LinkUploadCreateFmt;
SDV($IMapLinkFmt['Attach:'],
"<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a>");

SDV($LinkUploadCreateFmt,
"<a class='createlinkupload' href='\$LinkUpload'>\$LinkText</a>");

$LinkPageCreateFmt = 
"<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";
