<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004, 2005 Patrick R. Michaud (pmichaud@pobox.com)
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

global $HTTPHeaders, $KeepToken, $pagename,
  $GroupPattern, $NamePattern, $WikiWordPattern, $SuffixPattern,
  $PageNameChars, $MakePageNamePatterns, $CaseConversions;

$HTTPHeaders[] = 'Content-type: text/html; charset=utf-8';

$KeepToken = "\263\263\263";
$pagename = $_REQUEST['n'];
if (!$pagename) $pagename = $_REQUEST['pagename'];
if (!$pagename &&
      preg_match('!^'.preg_quote($_SERVER['SCRIPT_NAME'],'!').'/?([^?]*)!',
          $_SERVER['REQUEST_URI'],$match))
    $pagename = urldecode($match[1]);
$pagename = preg_replace('!/+$!','',$pagename);

$GroupPattern = '[\\w\\x80-\\xfe]+(?:-[[\\w\\x80-\\xfe]+)*';
$NamePattern = '[\\w\\x80-\\xfe]+(?:-[[\\w\\x80-\\xfe]+)*';
$WikiWordPattern = 
  '[A-Z][A-Za-z0-9]*(?:[A-Z][a-z0-9]|[a-z0-9][A-Z])[A-Za-z0-9]*';
$SuffixPattern = '(?:-?[[:alnum:]\\x80-\\xfe]+)*';

SDV($PageNameChars, '-[:alnum:]\\x80-\\xfe');
SDV($MakePageNamePatterns, array(
    "/'/" => '',                           # strip single-quotes
    "/[^$PageNameChars]+/" => ' ',         # convert everything else to space
    "/(?<=^| )(.)/eu" => "utf8toupper('$1')", 
    "/ /" => ''));

function utf8toupper($x) {
  global $CaseConversions;
  static $lower, $upper;
  if (function_exists('mb_strtoupper')) return mb_strtoupper($x, 'UTF-8');
  if (!$lower) {
    foreach($CaseConversions as $k => $v) {
      if (!preg_match('/^([0-9a-f]+)(-([0-9a-f]+))?(\\/(\\d+))?$/', $k, $m))
        continue;
      $cp0 = hexdec($m[1]); $cp1 = hexdec(@$m[3]); $step = @$m[5];
      if ($cp1 < $cp0) $cp1 = $cp0;
      if ($step < 1) $step = 1;
      $s = $cp0; $t = hexdec($v);
      while ($s <= $cp1) {
        if ($s < 128) $lower[] = chr($s);
        else $lower[] = chr(0xc0+(($s >>6 ) & 0x1f)) . chr(0x80+($s & 0x3f));
        if ($t < 128) $upper[] = chr($t);
        else $upper[] = chr(192+(($t>>6) & 0x1f)) . chr(128+($t & 0x3f));
        $s+=$step; $t+=$step;
      }
    }
  }
  return str_replace($lower, $upper, $x);
}

SDV($CaseConversions, array(
  # ASCII
  '61-7a'   => '41',
  # Latin-1
  'e0-f6'   => 'c0',                        
  'f8-fe'   => 'd8',
  # Cyrillic
  '450-45f' => '400',
  '430-44f' => '410',
  '48b-4bf/2' => '48a',
  '4c2-4ce/2' => '4c1',
  '4d1-4ff/2' => '4d0',
));

