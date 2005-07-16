<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004-2005 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script implements (:pagelist:) and friends -- it's one
    of the nastiest scripts you'll ever encounter.  Part of the reason
    for this is that page listings are so powerful and flexible, so
    that adds complexity.  They're also expensive, so we have to
    optimize them wherever we can.

    The core function is FmtPageList(), which will generate a 
    listing according to a wide variety of options.  FmtPageList takes 
    care of initial option processing, and then calls a "FPL"
    (format page list) function to obtain the formatted output.
    The FPL function is chosen by the 'fmt=' option to (:pagelist:).

    Each FPL function calls MakePageList() to obtain the list
    of pages, formats the list somehow, and returns the results
    to FmtPageList.  FmtPageList then returns the output to
    the caller, and calls Keep() (preserves HTML) or PRR() (re-evaluate
    as markup) as appropriate for the output being returned.
*/

## $SearchPatterns holds patterns for list= option
SDVA($SearchPatterns['all'], array());
$SearchPatterns['normal'][] = '!\.(All)?Recent(Changes|Uploads)$!';
$SearchPatterns['normal'][] = '!\.Group(Print)?(Header|Footer|Attributes)$!';

SDV($SearchResultsFmt, "<div class='wikisearch'>\$[SearchFor]
  $HTMLVSpace\$MatchList
  $HTMLVSpace\$[SearchFound]$HTMLVSpace</div>");
SDV($SearchBoxFmt, 
  "<form class='wikisearch' action='$ScriptUrl'
    method='get'><input type='hidden' name='n'
    value='$[Site/Search]' /><input class='wikisearchbox'
    type='text' name='q' value='\$SearchQuery' size='40' /><input
    class='wikisearchbutton' type='submit' value='$[Search]' /></form>");
SDV($SearchQuery, str_replace('$', '&#036;', 
  htmlspecialchars(stripmagic(@$_REQUEST['q']), ENT_NOQUOTES)));
XLSDV('en', array(
  'SearchFor' => 'Results of search for <em>$Needle</em>:',
  'SearchFound' => 
    '$MatchCount pages found out of $MatchSearched pages searched.'));

## $FPLFunctions is a list of functions associated with fmt= options
SDVA($FPLFunctions, array(
  'bygroup' => 'FPLByGroup',
  'simple'  => 'FPLSimple',
  'group'   => 'FPLGroup'));

Markup('pagelist', 'directives',
  '/\\(:pagelist(\\s+.*?)?:\\)/ei',
  "FmtPageList('\$MatchList', \$pagename, array('o' => PSS('$1 ')))");
Markup('searchresults', 'directives',
  '/\\(:searchresults(\\s+.*?)?:\\)/ei',
  "FmtPageList(\$GLOBALS['SearchResultsFmt'], \$pagename,
       array('o' => PSS('$1'), 'req' => 1))");
Markup('searchbox', '>links',
  '/\\(:searchbox:\\)/ie',
  "FmtPageName(\$GLOBALS['SearchBoxFmt'], \$pagename)");


## FmtPageList combines options from markup, request form, and url,
## calls the appropriate formatting function, and returns the string
## (calling Keep() or PRR() as appropriate).
function FmtPageList($fmt, $pagename, $opt) {
  global $GroupPattern, $FmtV, $FPLFunctions;
  # if (isset($_REQUEST['q']) && $_REQUEST['q']=='') $_REQUEST['q']="''";
  $rq = htmlspecialchars(stripmagic(@$_REQUEST['q']), ENT_NOQUOTES);
  $FmtV['$Needle'] = $opt['o'] . ' ' . $rq;
  if (preg_match("!^($GroupPattern(\\|$GroupPattern)*)?/!i", $rq, $match)) {
    $opt['group'] = @$match[1];
    $rq = substr($rq, strlen(@$match[1])+1);
  }
  $opt = array_merge($opt, ParseArgs($opt['o'] . ' ' . $rq), @$_REQUEST);
  if (@($opt['req'] && !$opt['-'] && !$opt[''] && !$opt['+'] && !$opt['q']))
    return;
  $GLOBALS['SearchIncl'] = array_merge((array)@$opt[''], (array)@$opt['+']);
  $GLOBALS['SearchExcl'] = (array)$opt['-'];
  $GLOBALS['SearchGroup'] = @$opt['group'];
  $matches = array();
  $fmtfn = @$FPLFunctions[$opt['fmt']];
  if (!function_exists($fmtfn)) $fmtfn = 'FPLByGroup';
  $out = $fmtfn($pagename, $matches, $opt);
  $FmtV['$MatchCount'] = count($matches);
  if ($fmt != '$MatchList') 
    { $FmtV['$MatchList'] = $out; $out = FmtPageName($fmt, $pagename); }
  if ($out{0} == '<') return '<div>'.Keep($out).'</div>';
  PRR(); return $out;
}

## MakePageList generates a list of pages using the specifications given
## by $opt.
function MakePageList($pagename, $opt) {
  global $MakePageListOpt, $SearchPatterns, $EnablePageListProtect, $PCache,
    $FmtV;
  StopWatch('MakePageList begin');
  SDVA($MakePageListOpt, array('list' => 'default'));

  $opt = array_merge($MakePageListOpt, $opt);
  $readf = $opt['readf'];
  # we have to read the page if order= is anything but name
  $order = $opt['order'];
  $readf |= $order && ($order!='name') && ($order!='-name');

  $pats = (array)$SearchPatterns[$opt['list']];
  if ($opt['group']) array_unshift($pats, "/^({$opt['group']})\./i");

  # inclp/exclp contain words to be included/excluded.  
  $inclp = array(); $exclp = array();
  foreach((array)@$opt[''] as $i)  { $inclp[] = '/'.preg_quote($i, '/').'/i'; }
  foreach((array)@$opt['+'] as $i) { $inclp[] = '/'.preg_quote($i, '/').'/i'; }
  foreach((array)@$opt['-'] as $i) { $exclp[] = '/'.preg_quote($i, '/').'/i'; }
  $searchterms = count($inclp) + count($exclp);
  $readf += $searchterms;                         # forced read if incl/excl

  # link= (backlinks)
  if (@$opt['link']) { 
    $linkpat = "/,{$opt['link']},/";              # find in target= attribute
    $readf = 1;                                   # forced read
  }
 
  if (@$opt['trail']) {
    $trail = ReadTrail($pagename, $opt['trail']);
    foreach($trail as $tstop) {
      $pn = $tstop['pagename'];
      $list[] = $pn;
      $tstop['parentnames'] = array();
      PCache($pn, $tstop);
    }
    foreach($trail as $tstop) 
      $PCache[$tstop['pagename']]['parentnames'][] =
        $trail[$tstop['parent']]['pagename'];
  } else $list = ListPages($pats);
  if (IsEnabled($EnablePageListProtect, 0)) $readf = 1000;
  $matches = array();
  $FmtV['$MatchSearched'] = count($list);
  foreach((array)$list as $pn) {
    if ($readf) {
      $page = ($readf == 1000) 
              ? RetrieveAuthPage($pn, 'read', false, READPAGE_CURRENT)
              : ReadPage($pn, READPAGE_CURRENT);
      if (!$page) continue;
      if ($linkpat && !preg_match($linkpat, ",{$page['targets']},")) continue;
      if ($searchterms) {
        $text = $pn."\n".@$page['targets']."\n".@$page['text'];
        foreach($inclp as $i) if (!preg_match($i, $text)) continue 2;
        foreach($exclp as $i) if (preg_match($i, $text)) continue 2;
      }
      $page['size'] = strlen(@$page['text']);
    } else $page = array();
    $page['pagename'] = $page['name'] = $pn;
    PCache($pn, $page);
    $matches[] = & $PCache[$pn];
  }
  SortPageList($matches, $order);
  StopWatch('MakePageList end');
  return $matches;
}


function SortPageList(&$matches, $order) {
  $code = '';
  foreach(preg_split("/[\\s,|]+/", $order, -1, PREG_SPLIT_NO_EMPTY) as $o) {
    if ($o{0}=='-') { $r = '-'; $o = substr($o, 1); }
    else $r = '';
    if ($o == 'size' || $o == 'time') $code .= "\$c = \$x['$o']-\$y['$o']; ";
    else $code .= "\$c = strcasecmp(\$x['$o'],\$y['$o']); ";
    $code .= "if (\$c) return $r\$c;\n";
  }
  if ($code) 
    uasort($matches, create_function('$x,$y', "$code return 0;"));
}


## FPLByGroup provides a simple listing of pages organized by group
function FPLByGroup($pagename, &$matches, $opt) {
  global $FPLByGroupStartFmt, $FPLByGroupEndFmt, $FPLByGroupGFmt,
    $FPLByGroupIFmt, $FPLByGroupOpt;
  SDV($FPLByGroupStartFmt,"<dl class='fplbygroup'>");
  SDV($FPLByGroupEndFmt,'</dl>');
  SDV($FPLByGroupGFmt,"<dt><a href='\$ScriptUrl/\$Group'>\$Group</a> /</dt>\n");
  SDV($FPLByGroupIFmt,"<dd><a href='\$PageUrl'>\$Name</a></dd>\n");
  SDVA($FPLByGroupOpt, array('readf' => 0, 'order' => 'name'));
  $matches = MakePageList($pagename, array_merge($FPLByGroupOpt, $opt));
  if (@$opt['count']) array_splice($matches, $opt['count']);
  $out = array();
  foreach($matches as $pc) {
    $pgroup = FmtPageName($FPLByGroupGFmt, $pc['pagename']);
    if ($pgroup != @$lgroup) { $out[] = $pgroup; $lgroup = $pgroup; }
    $out[] = FmtPageName($FPLByGroupIFmt, $pc['pagename']);
  }
  return FmtPageName($FPLByGroupStartFmt, $pagename) . implode('', $out) .
             FmtPageName($FPLByGroupEndFmt, $pagename);
}


## FPLSimple provides a simple bullet list of pages
function FPLSimple($pagename, &$matches, $opt) {
  global $FPLSimpleStartFmt, $FPLSimpleIFmt, $FPLSimpleEndFmt, $FPLSimpleOpt;
  SDV($FPLSimpleStartFmt, "<ul class='fplsimple'>");
  SDV($FPLSimpleEndFmt, "</ul>");
  SDV($FPLSimpleIFmt, "<li><a href='\$PageUrl'>\$FullName</a></li>");
  SDVA($FPLSimpleOpt, array('readf' => 0));
  $topt['order'] = ($opt['trail']) ? '' : 'name';
  $matches = MakePageList($pagename, array_merge($topt, $FPLSimpleOpt, $opt));
  if (@$opt['count']) array_splice($matches, $opt['count']);
  $out = array();
  foreach($matches as $pc) 
    $out[] = FmtPageName($FPLSimpleIFmt, $pc['pagename']);
  return FmtPageName($FPLSimpleStartFmt, $pagename) . implode('', $out) .
             FmtPageName($FPLSimpleEndFmt, $pagename);
}
   

## FPLGroup provides a simple bullet list of groups
function FPLGroup($pagename, &$matches, $opt) {
  global $FPLGroupStartFmt, $FPLGroupIFmt, $FPLGroupEndFmt, $FPLGroupOpt;
  SDV($FPLGroupStartFmt, "<ul class='fplgroup'>");
  SDV($FPLGroupEndFmt, "</ul>");
  SDV($FPLGroupIFmt, "<li><a href='\$ScriptUrl/\$Group'>\$Group</a></li>");
  SDVA($FPLGroupOpt, array('readf' => 0, 'order' => 'name'));
  $matches = MakePageList($pagename, array_merge($FPLGroupOpt, $opt));
  $out = array();
  foreach($matches as $pc) {
    $group = preg_replace('/\\.[^.]+$/', '', $pc['pagename']);
    if (@!$seen[$group]++) {
      $out[] = FmtPageName($FPLGroupIFmt, $pc['pagename']);
      if ($opt['count'] && count($out) >= $opt['count']) break;
    }
  }
  return FmtPageName($FPLGroupStartFmt, $pagename) . implode('', $out) .
             FmtPageName($FPLGroupEndFmt, $pagename);
}
