<?php if (!defined('PmWiki')) exit();
##  This is a sample config.php file.  To use this file, copy it to
##  local/config.php, then edit it for whatever customizations you want.
##  Also, be sure to take a look at http://www.pmichaud.com/wiki/Cookbook
##  for more details on the types of customizations that can be added
##  to PmWiki.

##  $WikiTitle is the name that appears in the browser's title bar.

$WikiTitle = 'GNAT GLADE';

##  $ScriptUrl is your preferred URL for accessing wiki pages
##  $PubDirUrl is the URL for the pub directory.

$ScriptUrl = 'http://gnat-glade.sourceforge.net/pmwiki.php';
$PubDirUrl = 'http://gnat-glade.sourceforge.net/pub';

##  If you want to use URLs of the form .../pmwiki.php/Group/PageName
##  instead of .../pmwiki.php?p=Group.PageName, try setting
##  $EnablePathInfo below.  Note that this doesn't work in all environments,
##  it depends on your webserver and PHP configuration.  You might also 
##  want to check http://www.pmwiki.org/wiki/Cookbook/CleanUrls more
##  details about this setting and other ways to create nicer-looking urls.

$EnablePathInfo = 1;

## $PageLogoUrl is the URL for a logo image -- you can change this
## to your own logo if you wish.

$PageLogoUrl = "http://gnat-glade.sourceforge.net/ada.jpg";

## If you want to have a custom skin, then set $Skin to the name
## of the directory (in pub/skins/) that contains your skin files.
## See PmWiki.LayoutBasics and Cookbook.Skins.

$Skin = 'dropdown';

## You'll probably want to set an administrative password that you
## can use to get into password-protected pages.  Also, by default 
## the "attr" passwords for the PmWiki and Main groups are locked, so
## an admin password is a good way to unlock those.  See PmWiki.Passwords
## and PmWiki.PasswordsAdmin.

$DefaultPasswords['admin']  = '$1$.HWpOj1B$XuhpxFURi8khZnXGg2HG80';
#$DefaultPasswords['edit']   = '$1$.HWpOj1B$XuhpxFURi8khZnXGg2HG80';
$DefaultPasswords['attr']   = '$1$.HWpOj1B$XuhpxFURi8khZnXGg2HG80';
$DefaultPasswords['upload'] = '$1$.HWpOj1B$XuhpxFURi8khZnXGg2HG80';

##  PmWiki comes with graphical user interface buttons for editing;
##  to enable these buttons, set $EnableGUIButtons to 1.  

$EnableGUIButtons = 1;

##  If you want uploads enabled on your system, set $EnableUpload=1.
##  You'll also need to set a default upload password, or else set
##  passwords on individual groups and pages.  For more information
##  see PmWiki.UploadsAdmin.

$EnableUpload = 1;
$EnableTransitions = 0;

##  Setting $EnableDiag turns on the ?action=diag and ?action=phpinfo
##  actions, which often helps the PmWiki authors to troubleshoot 
##  various configuration and execution problems.
# $EnableDiag = 1;                         # enable remote diagnostics

##  By default, PmWiki doesn't allow browsers to cache pages.  Setting
##  $EnableIMSCaching=1; will re-enable browser caches in a somewhat
##  smart manner.  Note that you may want to have caching disabled while
##  adjusting configuration files or layout templates.
# $EnableIMSCaching = 1;                   # allow browser caching

##  Set $SpaceWikiWords if you want WikiWords to automatically 
##  have spaces before each sequence of capital letters.
# $SpaceWikiWords = 1;                     # turn on WikiWord spacing

##  Set $LinkWikiWords to zero if you don't want WikiWord links (i.e.,
##  all links are made using [[...]].
# $LinkWikiWords = 0;                      # disable WikiWord links

##  If you want only the first occurrence of a WikiWord to be converted
##  to a link, set $WikiWordCountMax=1.
# $WikiWordCountMax = 1;                   # converts only first WikiWord
# $WikiWordCountMax = 0;                   # another way to disable WikiWords

##  The $WikiWordCount array can be used to control the number of times
##  a WikiWord is converted to a link.  This is useful for disabling
##  or limiting specific WikiWords.
# $WikiWordCount['PhD'] = 0;               # disables 'PhD'
# $WikiWordCount['PmWiki'] = 1;            # convert only first 'PmWiki'

##  By default, PmWiki is configured such that only the first occurrence
##  of 'PmWiki' in a page is treated as a WikiWord.  If you want to 
##  restore 'PmWiki' to be treated like other WikiWords, uncomment the
##  line below.
# unset($WikiWordCount['PmWiki']);

##  If you want to disable WikiWords matching a pattern, you can use 
##  something like the following.  Note that the first argument has to 
##  be different for each call to Markup().  The example below disables
##  WikiWord links like COM1, COM2, COM1234, etc.
# Markup('COM\d+', '<wikilink', '/\\bCOM\\d+/', "Keep('$0')");

##  $DiffKeepDays specifies the minimum number of days to keep a page's
##  revision history.  The default is 3650 (approximately 10 years).

$DiffKeepDays=30;

##  The refcount.php script enables ?action=refcount, which helps to
##  find missing and orphaned pages.  See PmWiki.RefCount.
# if ($action == 'refcount') include_once('scripts/refcount.php');

##  The rss.php script enables ?action=rss and ?action=rdf, which
##  provides RSS feeds for a site based on WikiTrails.  See PmWiki.RSS.
# if ($action == 'rss' || $action == 'rdf') include_once('scripts/rss.php');

##  PmWiki allows a great deal of flexibility for creating custom markup.
##  To add support for '*bold*' and '~italic~' markup (the single quotes
##  are part of the markup), uncomment the following lines. 
##  (See PmWiki.CustomMarkup and the Cookbook for details and examples.)
# Markup("'~", "inline", "/'~(.*?)~'/", "<i>$1</i>");        # '~italic~'
# Markup("'*", "inline", "/'\\*(.*?)\\*'/", "<b>$1</b>");    # '*bold*'

##  If you want to have to approve links to external sites before they
##  are turned into links, uncomment the line below.  See PmWiki.UrlApprovals.
##  Also, setting $UnapprovedLinkCountMax limits the number of unapproved
##  links that are allowed in a page (useful to control wikispam).

#include_once('scripts/urlapprove.php');
#$UnapprovedLinkCountMax = 10;

##  The following lines make additional editing buttons appear in the
##  edit page for subheadings, lists, tables, etc.
$GUIButtons['h2'] = array(400, '\\n!! ', '\\n', '$[Heading]',
                    '$GUIButtonDirUrlFmt/h2.gif"$[Heading]"');
$GUIButtons['h3'] = array(402, '\\n!!! ', '\\n', '$[Subheading]',
                    '$GUIButtonDirUrlFmt/h3.gif"$[Subheading]"');
$GUIButtons['indent'] = array(500, '\\n->', '\\n', '$[Indented text]',
                    '$GUIButtonDirUrlFmt/indent.gif"$[Indented text]"');
$GUIButtons['outdent'] = array(510, '\\n-<', '\\n', '$[Hanging indent]',
                    '$GUIButtonDirUrlFmt/outdent.gif"$[Hanging indent]"');
$GUIButtons['ol'] = array(520, '\\n# ', '\\n', '$[Ordered list]',
                    '$GUIButtonDirUrlFmt/ol.gif"$[Ordered (numbered) list]"');
$GUIButtons['ul'] = array(530, '\\n* ', '\\n', '$[Unordered list]',
                    '$GUIButtonDirUrlFmt/ul.gif"$[Unordered (bullet) list]"');
$GUIButtons['hr'] = array(540, '\\n----\\n', '', '',
                    '$GUIButtonDirUrlFmt/hr.gif"$[Horizontal rule]"');
$GUIButtons['table'] = array(600,
                      '||border=1 width=80%\\n||!Hdr ||!Hdr ||!Hdr ||\\n||     ||     ||     ||\\n||     ||     ||     ||\\n', '', '', 
                     '$GUIButtonDirUrlFmt/table.gif"$[Table]"');

$HTTPHeaders = array (
   "Expires: Tue, 01 Jan 2006 00:00:00 GMT",
   "Cache-Control: no-store, no-cache, must-revalidate",
   "Content-type: text/html; charset=utf-8;");

$PageSkinList = array (
   'pmwiki' => 'pmwiki',
   'dropdown' => 'dropdown',
   'monobook' => 'monobook');


include_once('cookbook/sourceforge.php');
include_once('scripts/xlpage-utf-8.php');
include_once('local/skinchange.php');

?>