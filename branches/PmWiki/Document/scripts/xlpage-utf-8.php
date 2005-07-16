<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script configures PmWiki to use utf-8 in page content and
    pagenames.  There are some unfortunate side effects about PHP's
    utf-8 implementation, however.  First, since PHP doesn't have a
    way to do pattern matching on upper/lowercase UTF-8 characters,
    WikiWords are limited to the ASCII-7 set, and all links to page
    names with UTF-8 characters have to be in double brackets.
    Second, we have to assume that all non-ASCII characters are valid
    in pagenames, since there's no way to determine which UTF-8
    characters are "letters" and which are punctuation.
*/

global $HTTPHeaders, $Newline, $KeepToken, $pagename,
  $GroupPattern, $NamePattern, $MakePageNameFunction, $WikiWordPattern;

$HTTPHeaders[] = 'Content-type: text/html; charset=utf-8';

$Newline = "\xc0\x8a";
$KeepToken = "\263\263\263";
$pagename = $_REQUEST['n'];
if (!$pagename) $pagename = $_REQUEST['pagename'];

if (!$pagename &&
      preg_match('!^'.preg_quote($_SERVER['SCRIPT_NAME'],'!').'/?([^?]*)!',
          $_SERVER['REQUEST_URI'],$match))
    $pagename = urldecode($match[1]);
$pagename = preg_replace('!/+$!','',$pagename);

$GroupPattern = '[\\w\\x80-\\xff]+(?:-[[\\w\\x80-\\xff]+)*';
$NamePattern = '[\\w\\x80-\\xff]+(?:-[[\\w\\x80-\\xff]+)*';
$MakePageNameFunction = 'MakeUTF8PageName';
$WikiWordPattern = 
  '[A-Z][A-Za-z0-9]*(?:[A-Z][a-z0-9]|[a-z0-9][A-Z])[A-Za-z0-9]*';

if (!isset($Author)) {
  if (isset($_POST['author'])) {
    $Author = htmlspecialchars(stripmagic($_POST['author']),ENT_QUOTES);
    setcookie('author',$Author,$AuthorCookieExpires,$AuthorCookieDir);
  } else {
    $Author = htmlspecialchars(stripmagic(@$_COOKIE['author']),ENT_QUOTES);
  }
  $Author = preg_replace('/(^[^[:alpha:]\\x80-\\xff]+)|[^-\\w\\x80-\\xff ]/',
    '', $Author);
}

## MakeUTF8PageName is used to convert a UTF-8 string into a valid pagename.
## It assumes that PHP has been compiled with mbstring support.
function MakeUTF8PageName($basepage,$x) {
  global $PagePathFmt;
  $PageNameChars = '-\\w\\x80-\\xff';
  if (!preg_match('/(?:([^.\\/]+)[.\\/])?([^.\\/]+)$/',$x,$m)) return '';
  $name = preg_replace("/[^$PageNameChars]+/", ' ', $m[2]);
  $name = preg_replace('/(?<=^| )(.)/eu', 
    "mb_strtoupper('$1','UTF-8')", $name);
  $name = str_replace(' ', '', $name);
  if ($m[1]) {
    $group = preg_replace("/[^$PageNameChars]+/", ' ', $m[1]);
    $group = preg_replace('/(?<=^| )(.)/eu', 
      "mb_strtoupper('$1','UTF-8')", $group);
    $group = str_replace(' ', '', $group);
    return "$group.$name";
  }
  foreach((array)$PagePathFmt as $pg) {
    $pn = FmtPageName(str_replace('$1',$name,$pg),$basepage);
    if (PageExists($pn)) return $pn;
  }
  $group=preg_replace('/[\\/.].*$/','',$basepage);
  return "$group.$name";
}

