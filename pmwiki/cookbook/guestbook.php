<?php if (!defined('PmWiki')) exit();
# (pmwiki 0.5x  Zet - http://www.cube3d.de)
#  changed for pmwiki 2.x   p@ddy.ch
#   remove slash in SDV(... $ScriptUrl..) SDV(.. $PageUrl  )
#   (:$Guestbook:)

/*[[$Guestbook]] */
if ($GuestbookLoaded) return;
$GuestbookLoaded=1;

SDV($HandleActions['guestbook'],'HandleGuestbook');
$pagename = $_REQUEST['n'];
if (!$pagename) $pagename = $_REQUEST['pagename'];
if (!$pagename &&
    preg_match('!^'.preg_quote($_SERVER['SCRIPT_NAME'],'!').'/?([^?]*)!',
      $_SERVER['REQUEST_URI'],$match))
  $pagename = urldecode($match[1]);
if (preg_match('/[\\x80-\\xbf]/',$pagename))
  $pagename=utf8_decode($pagename);
$pagename = preg_replace('![^[:alnum:]\\x80-\\xff]+$!','',$pagename);
$name = FmtPageName('$FullName',$pagename);
#TestLog("pagename: $pagename  REQUEST_URI:{$_SERVER['REQUEST_URI']} Script_Name:{$_SERVER['SCRIPT_NAME']} PageUrl:$PageUrl ");
#TestLog("Fmtname:$name");
if (isset($EnablePathInfo) && !$EnablePathInfo)
  SDV($GuestbookTagFmt,"<form action='".$_SERVER['REQUEST_URI']."' method='get'><input
    type='hidden' name='pagename' value='".$pagename."'><input type='hidden'
    name='action' value='guestbook' />
        <table><tr><td align=right>Name:</td>
                <td align=left>
                        <input type='text' name='name' value='' size='40' />
                </td></tr>
        <tr><td align=right>Message:</td>
                <td align=left>
                        <textarea name='message' cols=40 rows=5></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input class='button' type='submit' value='OK' /><input class='button' type='reset'>
                    </form></td></tr></table>");
SDV($GuestbookTagFmt,"
        <form action='".$_SERVER['SCRIPT_NAME']."/".$pagename."' method='get'>
        <input type='hidden' name='action' value='guestbook' />
        <input
    type='hidden' name='pagename' value='".$pagename."'>
        <table><tr><td align=right>Name:</td>
                <td align=left>
                        <input type='text' name='name' value='' size='40' />
                </td></tr>
                <tr><td align=right>Homepage:</td>
                <td align=left>
                        <input type='text' name='homepage' value='' size='40' />
                </td></tr>
        <tr><td align=right>Message:</td>
                <td align=left>
                        <textarea name='message' cols=40 rows=5></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input class='button' type='submit' value='OK' />&nbsp;&nbsp;<input class='button' type='reset'>
                    </form></td></tr></table><CENTER>Please only press the OK button ONCE.</CENTER><BR /><HR>");

# SDV($InlineReplacements['/\\[\\[\\$Guestbook\\]\\]/e'],
#  "FmtPageName(\$GLOBALS['GuestbookTagFmt'],\$pagename)");

#http://www.blug.ch/wiki?message=Formulareingabe+%FCber+G%E4stebuch
#                                &action=guestbook&name=Webmaster&homepage=
function HandleGuestbook($pagename){  /* ($pagename) */
#       global $homepage,$pagename;
#        TestLog("gb-REQUEST_URI-{$_SERVER['REQUEST_URI']} ") ;
#        TestLog("gb-pagename-$pagename");
#       echo "homepage =".$homepage.$HTTP_GET_VARS["homepage"] ;
        global $TimeFmt,$Now,$_GET;
        $default = "----";
        $rcpage = ReadPage($pagename,"");
        $pos=strpos($rcpage['text'],"(:\$Guestbook:)");
        $len=strlen("(:\$Guestbook:)");
        $before=substr($rcpage['text'],0,$pos+$len);
        $after=substr($rcpage['text'],$pos+$len);
        $rcpage['text'] = $before.
           /*   "\n$$$$\$default".  pog? */
                "\n\n%green%Posted on ''".strftime($TimeFmt,$Now).
                " by ''".StripCSlashes($_GET["name"])."%%[[<<]]".
                (($_GET["homepage"])?StripCSlashes($_GET["homepage"]):"")."\n\n".StripCSlashes($_GET['message']).
                "\n\n----".$homepage. "". $HTTP_GET_VARS["homepage"].
                $after;
        WritePage($pagename,$rcpage);
        Redirect($pagename);
}
Markup('{$Guestbook}', '>{$var}','/\\(:\\$Guestbook:\\)/',Keep($GuestbookTagFmt));
#Markup('Guestbook', 'directives', '/\\(:$Guestbook:\\)/e',"Guestbook()");

?>