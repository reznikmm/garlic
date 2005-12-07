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

## $LinkIndexFile is the index file for backlinks and link= option
if (IsEnabled($EnableLinkIndex, 1)) {
  SDV($LinkIndexFile, "$WorkDir/.linkindex");
  $EditFunctions[] = 'PostLinkIndex';
}

## $SearchPatterns holds patterns for list= option
SDVA($SearchPatterns['all'], array());
$SearchPatterns['normal'][] = '!\.(All)?Recent(Changes|Uploads)$!';
$SearchPatterns['normal'][] = '!\.Group(Print)?(Header|Footer|Attributes)$!';

SDV($SearchResultsFmt, "<div class='wikisearch'>\$[SearchFor]
  $HTMLVSpace\$MatchList
  $HTMLVSpace\$[SearchFound]$HTMLVSpace</div>");
SDV($SearchQuery, str_replace('$', '&#036;', 
  htmlspecialchars(stripmagic(@$_REQUEST['q']), ENT_NOQUOTES)));
XLSDV('en', array(
  'SearchFor' => 'Results of search for <em>$Needle</em>:',
  'SearchFound' => 
    '$MatchCount pages found out of $MatchSearched pages searched.'));

Markup('pagelist', 'directives',
  '/\\(:pagelist(\\s+.*?)?:\\)/ei',
  "FmtPageList('\$MatchList', \$pagename, array('o' => PSS('$1 ')))");
Markup('searchresults', 'directives',
  '/\\(:searchresults(\\s+.*?)?:\\)/ei',
  "FmtPageList(\$GLOBALS['SearchResultsFmt'], \$pagename,
       array('o' => PSS('$1'), 'req' => 1))");
Markup('searchbox', '>links',
  '/\\(:searchbox(\\s.*?)?:\\)/e',
  "SearchBox(\$pagename, ParseArgs(PSS('$1')))");

SDV($HandleActions['search'], 'HandleSearchA');
SDV($HandleAuth['search'], 'read');

## SearchBox generates the output of the (:searchbox:) markup.
## If $SearchBoxFmt is defined, that is used, otherwise a searchbox
## is generated.  Options include group=, size=, label=.
function SearchBox($pagename, $opt) {
  global $SearchBoxFmt, $SearchBoxOpt, $SearchQuery, $EnablePathInfo;
  if (isset($SearchBoxFmt)) return FmtPageName($SearchBoxFmt, $pagename);
  SDVA($SearchBoxOpt, array('size' => '40', 
    'label' => FmtPageName('$[Search]', $pagename),
    'group' => @$_REQUEST['group'],
    'value' => str_replace("'", "&#039;", $SearchQuery)));
  $opt = array_merge((array)$SearchBoxOpt, (array)$opt);
  $group = $opt['group'];
  $out[] = FmtPageName("
    class='wikisearch' action='\$PageUrl' method='get'><input
    type='hidden' name='action' value='search' />", $pagename);
  if (!IsEnabled($EnablePathInfo, 0)) 
    $out[] = "<input type='hidden' name='n' value='$pagename' />";
  if ($group) 
    $out[] = "<input type='hidden' name='group' value='$group' />";
  $out[] = "<input type='text' name='q' value='{$opt['value']}' 
    size='{$opt['size']}' /><input class='wikisearchbutton' 
    type='submit' value='{$opt['label']}' /></form>";
  return "<form ".Keep(implode('', $out));
}

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
  $GLOBALS['SearchExcl'] = (array)@$opt['-'];
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

  $opt = array_merge((array)$MakePageListOpt, $opt);
  $readf = $opt['readf'];
  # we have to read the page if order= is anything but name
  $order = $opt['order'];
  $readf |= $order && ($order!='name') && ($order!='-name');

  $pats = @(array)$SearchPatterns[$opt['list']];
  if (@$opt['group']) array_unshift($pats, "/^({$opt['group']})\./i");

  # inclp/exclp contain words to be included/excluded.  
  $inclp = array(); $exclp = array();
  foreach((array)@$opt[''] as $i)  { $inclp[] = '/'.preg_quote($i, '/').'/i'; }
  foreach((array)@$opt['+'] as $i) { $inclp[] = '/'.preg_quote($i, '/').'/i'; }
  foreach((array)@$opt['-'] as $i) { $exclp[] = '/'.preg_quote($i, '/').'/i'; }
  $searchterms = count($inclp) + count($exclp);
  $readf += $searchterms;                         # forced read if incl/excl

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

  # link= (backlinks)
  if (@$opt['link']) { 
    $link = MakePageName($pagename, $opt['link']);
    $linkpat = "/(^|,)$link(,|$)/i";
    $readf++;
    $xlist = BacklinksTo($link, false);
    $list = array_diff($list, $xlist);
  }
  $xlist = array();
 
  StopWatch('MakePageList scan');
  foreach((array)$list as $pn) {
    if ($readf) {
      $page = ($readf >= 1000) 
              ? RetrieveAuthPage($pn, 'read', false, READPAGE_CURRENT)
              : ReadPage($pn, READPAGE_CURRENT);
      if (!$page) continue;
      if (@$linkpat && !preg_match($linkpat, @$page['targets'])) 
        { $PCache[$pn]['targets'] = @$page['targets']; $xlist[]=$pn; continue; }
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
  StopWatch('MakePageList sort');
  SortPageList($matches, $order);
  StopWatch('MakePageList update');
  if ($xlist) LinkIndexUpdate($xlist);
  StopWatch('MakePageList end');
  return $matches;
}

function SortPageList(&$matches, $order) {
  $code = '';
  foreach(preg_split("/[\\s,|]+/", $order, -1, PREG_SPLIT_NO_EMPTY) as $o) {
    if ($o{0}=='-') { $r = '-'; $o = substr($o, 1); }
    else $r = '';
    if ($o == 'size' || $o == 'time') $code .= "\$c = @(\$x['$o']-\$y['$o']); ";
    else $code .= "\$c = @strcasecmp(\$x['$o'],\$y['$o']); ";
    $code .= "if (\$c) return $r\$c;\n";
  }
  if ($code) 
    uasort($matches, create_function('$x,$y', "$code return 0;"));
}

## HandleSearchA performs ?action=search.  It's basically the same
## as ?action=browse, except it takes its contents from Site.Search.
function HandleSearchA($pagename, $level = 'read') {
  global $PageSearchForm, $FmtV, $HandleSearchFmt, 
    $PageStartFmt, $PageEndFmt;
  SDV($HandleSearchFmt,array(&$PageStartFmt, '$PageText', &$PageEndFmt));
  SDV($PageSearchForm, '$[$SiteGroup/Search]');
  PCache($pagename, RetrieveAuthPage($pagename, 'read'));
  $form = ReadPage(FmtPageName($PageSearchForm, $pagename), READPAGE_CURRENT);
  $text = @$form['text'];
  if (!$text) $text = '(:searchresults:)';
  $FmtV['$PageText'] = MarkupToHTML($pagename,$text);
  PrintFmt($pagename, $HandleSearchFmt);
}

########################################################################
## The functions below provide different formatting options for
## the output list, controlled by the fmt= parameter and the
## $FPLFunctions hash.
########################################################################

## $FPLFunctions is a list of functions associated with fmt= options
SDVA($FPLFunctions, array(
  'bygroup' => 'FPLByGroup',
  'simple'  => 'FPLSimple',
  'group'   => 'FPLGroup'));

## FPLByGroup provides a simple listing of pages organized by group
function FPLByGroup($pagename, &$matches, $opt) {
  global $FPLByGroupStartFmt, $FPLByGroupEndFmt, $FPLByGroupGFmt,
    $FPLByGroupIFmt, $FPLByGroupOpt;
  SDV($FPLByGroupStartFmt,"<dl class='fplbygroup'>");
  SDV($FPLByGroupEndFmt,'</dl>');
  SDV($FPLByGroupGFmt,"<dt><a href='\$ScriptUrl/\$Group'>\$Group</a> /</dt>\n");
  SDV($FPLByGroupIFmt,"<dd><a href='\$PageUrl'>\$Name</a></dd>\n");
  SDVA($FPLByGroupOpt, array('readf' => 0, 'order' => 'name'));
  $matches = MakePageList($pagename, array_merge((array)$FPLByGroupOpt, $opt));
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
  $topt['order'] = (@$opt['trail']) ? '' : 'name';
  $matches = MakePageList($pagename, 
                 array_merge($topt, (array)$FPLSimpleOpt, $opt));
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
  $matches = MakePageList($pagename, array_merge((array)$FPLGroupOpt, $opt));
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


########################################################################
## The functions below optimize backlinks by maintaining an index
## file of link cross references (the "link index").
########################################################################

## The BacklinksTo($pagename, $incl) function reads the current
## linkindex file and returns all listed pages that have $pagename
## as a target.  If $incl is false, then BacklinksTo returns
## the pages in the linkindex that *don't* have $pagename as a target.
## Note that if the linkindex is incomplete then so is the returned list.
function BacklinksTo($pagename, $incl=true) {
  global $LinkIndexFile;
  if (!$LinkIndexFile) return array();
  StopWatch('BacklinksTo begin');
  $excl = ! $incl;
  $pagelist = array();
  $fp = @fopen($LinkIndexFile, 'r');
  if ($fp) {
    $linkpat = "/[=,]{$pagename}[\n,]/i";
    while (!feof($fp)) {
      $line = fgets($fp, 4096);
      while (substr($line, -1, 1) != "\n" && !feof($fp)) 
        $line .= fgets($fp, 4096);
      if (strpos($line, '=') === false) continue;
      if (preg_match($linkpat, $line) xor $excl) {
        list($n,$t) = explode('=', $line, 2);
        $pagelist[] = $n;
      }
    }
    fclose($fp);
  }
  StopWatch('BacklinksTo end');
  return $pagelist;
}

## The LinkIndexUpdate($pagelist) function updates the linkindex
## file with the target information for the pages in $pagelist.
## If the targets are cached then LinkIndexUpdate uses that,
## otherwise the pages are read to get the current targets.
function LinkIndexUpdate($pagelist) {
  global $LinkIndexFile, $PCache, $LinkIndexTime;
  SDV($LinkIndexTime, 10);
  if (!$pagelist || !$LinkIndexFile) return;
  StopWatch('LinkIndexUpdate begin');
  $pagelist = (array)$pagelist;
  Lock(2);
  $ofp = fopen("$LinkIndexFile,new", 'w');
  $timeout = time() + $LinkIndexTime;
  foreach($pagelist as $n) {
    if (time() > $timeout) break;
    if (isset($PCache[$n]['targets'])) $targets=$PCache[$n]['targets'];
    else {
      $page = ReadPage($n, READPAGE_CURRENT);
      if (!$page) continue;
      $targets = @$page['targets'];
    }
    fputs($ofp, "$n=$targets\n");
  }
  $ifp = @fopen($LinkIndexFile, 'r');
  if ($ifp) {
    while (!feof($ifp)) {
      $line = fgets($ifp, 4096);
      while (substr($line, -1, 1) != "\n" && !feof($ifp)) 
        $line .= fgets($ifp, 4096);
      $i = strpos($line, '=');
      if ($i === false) continue;
      $n = substr($line, 0, $i);
      if (in_array($n, $pagelist)) continue;
      fputs($ofp, $line);
    }
    fclose($ifp);
  }
  fclose($ofp);
  if (file_exists($LinkIndexFile)) unlink($LinkIndexFile); 
  rename("$LinkIndexFile,new", $LinkIndexFile);
  fixperms($LinkIndexFile);
  StopWatch('LinkIndexUpdate end');
}

## PostLinkIndex is inserted into $EditFunctions to update
## the linkindex whenever a page is saved.
function PostLinkIndex($pagename, &$page, &$new) {
  global $IsPagePosted, $PCache;
  if (!$IsPagePosted) return;
  $PCache[$pagename]['targets'] = $new['targets'];
  LinkIndexUpdate($pagename);
}
