<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script handles various "fixup transitions" that might need to 
    occur to help existing sites smoothly upgrade to newer releases of 
    PmWiki.  Rather than put the workarounds in the main code files, we 
    try to centralize them here so we can see what's deprecated and a
    simple switch ($EnableTransitions=0, or ?trans=0 in the url) can tell 
    the admin if his site is relying on an outdated feature or
    way of doing things.
*/

## if ?trans=0 is specified, then we don't do any fixups.
if (@$_REQUEST['trans']==='0') return;

## Beta50 switches Main.AllRecentChanges to be $SiteGroup.AllRecentChanges .
## This setting keeps Main.AllRecentChanges going if it exists.
if (PageExists('Main.AllRecentChanges')) 
  SDV($RecentChangesFmt['Main.AllRecentChanges'],
    '* [[$Group.$Name]]  . . . $CurrentTime $[by] $AuthorLink');

## Beta50 switches Main.ApprovedUrls to be $SiteGroup.ApprovedUrls .
## This setting keeps using Main.ApprovedUrls if it exists.
if (PageExists('Main.ApprovedUrls')) {
  if (PageExists(FmtPageName($ApprovedUrlPagesFmt[0], $pagename))) 
    $ApprovedUrlPagesFmt[] = 'Main.ApprovedUrls';
  else array_unshift($ApprovedUrlPagesFmt, 'Main.ApprovedUrls');
}

## $PageEditFmt has been deprecated in favor of using wiki markup forms
## to layout the edit page (as controlled by the $EditFormPage variable).
## However, some sites and skins have customized $PageEditFmt -- if
## that appears to have happened we restore PmWiki's older defaults here.
## If not, then we take any $EditMessages (which may have come from
## cookbook scripts) and stick them into the new $MessagesFmt array.
if ($PageEditFmt || $PagePreviewFmt || $HandleEditFmt) {
  SDV($PageEditFmt, "<div id='wikiedit'>
    <a id='top' name='top'></a>
    <h1 class='wikiaction'>$[Editing \$FullName]</h1>
    <form method='post' action='\$PageUrl?action=edit'>
    <input type='hidden' name='action' value='edit' />
    <input type='hidden' name='n' value='\$FullName' />
    <input type='hidden' name='basetime' value='\$EditBaseTime' />
    \$EditMessageFmt
    <textarea id='text' name='text' rows='25' cols='60'
      onkeydown='if (event.keyCode==27) event.returnValue=false;'
      >\$EditText</textarea><br />
    $[Author]: <input type='text' name='author' value='\$Author' />
    <input type='checkbox' name='diffclass' value='minor' \$DiffClassMinor />
      $[This is a minor edit]<br />
    <input type='submit' name='post' value=' $[Save] ' />
    <input type='submit' name='preview' value=' $[Preview] ' />
    <input type='reset' value=' $[Reset] ' /></form></div>");
  if (@$_POST['preview']) 
    SDV($PagePreviewFmt, "<div id='wikipreview'>
      <h2 class='wikiaction'>$[Preview \$FullName]</h2>
      <p><b>$[Page is unsaved]</b></p>
      \$PreviewText
      <hr /><p><b>$[End of preview -- remember to save]</b><br />
      <a href='#top'>$[Top]</a></p></div>");
  SDV($HandleEditFmt, array(&$PageStartFmt,
    &$PageEditFmt, 'wiki:$[PmWiki.EditQuickReference]', &$PagePreviewFmt,
    &$PageEndFmt));
  $EditMessageFmt = implode('', $MessagesFmt) . $EditMessageFmt;
  if ($action=='edit' && IsEnabled($EnableGUIButtons, 0)) 
    array_push($EditFunctions, 'GUIEdit');
} else $MessagesFmt[] = $EditMessageFmt;

    
function GUIEdit($pagename, &$page, &$new) {
  global $EditMessageFmt;
  $EditMessageFmt .= GUIButtonCode($pagename);
}

## In beta50 several utility pages change location to the new Site
## group.  These settings cause some skins (that use translations)
## to know to link to the new locations.
XLSDV('en', array(
  'Main/SearchWiki' => XL('Site/Search'),
  'PmWiki.EditQuickReference' => XL('Site/EditQuickReference'),
  'PmWiki.UploadQuickReference' => XL('Site/UploadQuickReference'),
  ));

