<?php if (!defined('PmWiki')) exit();
/*  Copyright 2003-2004 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This file adds "?action=diag" and "?action=phpinfo" actions to PmWiki.  
    This produces lots of diagnostic output that may be helpful to the 
    software authors when debugging PmWiki or other scripts.
*/

ini_set('track_errors','1');

if ($action=='diag') {
  header('Content-type: text/plain');
  print_r($GLOBALS);
  exit();
}

if ($action=='phpinfo') { phpinfo(); exit(); }

function Ruleset() {
  global $MarkupTable;
  $out = array();
  BuildMarkupRules();
  foreach($MarkupTable as $id=>$m) 
    $out[] = sprintf("%-16s %-16s %-16s",$id,@$m['cmd'],@$m['seq']);
  return implode("\n",$out);
}

$HandleActions['ruleset'] = 'HandleRuleset';

function HandleRuleset($pagename) {
  header("Content-type: text/plain");
  print Ruleset();
}

function DisplayStopWatch() {
  global $StopWatch;
  StopWatch('now');
  $out[] = "<pre>";
  foreach((array)$StopWatch as $k => $x) {
    $out[] = "$x\n";
  }
  array_pop($StopWatch);
  $out[] = '</pre>';
  return implode('',$out);
}

$FmtP['/\\$StopWatch/e'] = 'DisplayStopWatch()';

