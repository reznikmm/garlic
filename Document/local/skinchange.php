<?php
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)

    This script enables the ?skin= and ?setskin= parameters for
    wiki pages.  The ?skin= parameter causes the current page to be 
    displayed with some alternate skin (defined by the WikiAdministrator),
    the ?setskin= parameter sets a cookie on the user's browser to
    always display pages with the requested skin.  
    
    To use this script, define an array called $PageSkinList 
    containing skin names and template files, then 
    include('cookbook/skinchange.php'); in your config.php.
    An example $PageSkinList to set up 'pmwiki', 'myskin',
    and 'classic' skins might read:

        $PageSkinList = array(
           'pmwiki' => 'pmwiki',
           'myskin' => 'myskin',
           'classic' => 'myclassicskin');

    If a URL requests a skin that isn't in this array, then PmWiki
    defaults to the skin already defined by $Skin.

    By default, the setskin cookie that is created will expire after
    one year.  You can set it to expire at the end of the browser
    session by setting $SkinCookieExpires=0;
*/

SDV($SkinCookie, $CookiePrefix.'setskin');
SDV($SkinCookieExpires, $Now+60*60*24*365);

if (isset($_COOKIE[$SkinCookie])) $sk = $_COOKIE[$SkinCookie];
if (isset($_GET['setskin'])) {
  $sk = $_GET['setskin'];
  setcookie($SkinCookie, $sk, $SkinCookieExpires, '/');
}
if (isset($_GET['skin'])) $sk = $_GET['skin'];
if (@$PageSkinList[$sk]) $Skin = $PageSkinList[$sk];
