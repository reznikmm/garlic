<?php if (!defined('PmWiki')) exit();
# Author: Zet - http://www.cube3d.de
#  changed for pmwiki 2.x  by p@ddy.ch
#  adapted: (:$Forum:) (:$Forumcomment:)

if ($ForumLoaded) return;
$ForumLoaded=1;

SDV($HandleActions['forum'],'Handleforum');
SDV($HandleActions['forumcomment'],'HandleForumComment');

SDV($AuthorCookieExpires,$Now+60*60*24*30);
SDV($AuthorCookieDir,'/');
SDV($AuthorGroup,'Profiles');

if (!isset($Author)) {
  if (isset($_POST['author'])) {
    $Author = htmlspecialchars(stripmagic($_POST['author']),ENT_QUOTES);
    setcookie('author',$Author,$AuthorCookieExpires,$AuthorCookieDir);
  } else {
    $Author = htmlspecialchars(stripmagic(@$_COOKIE['author']),ENT_QUOTES);
  }
  $Author = preg_replace('/(^[^[:alpha:]]+)|[^-\\w ]/','',$Author);
}

if (isset($EnablePathInfo) && !$EnablePathInfo)   /* pog no slashes: \$PageName */
  {
  SDV($ForumTagFmt,"
<CENTER><B>Post a Message to the Forum</B></CENTER>
          <form action='\$ScriptUrl' method='get'>
        <input type='hidden' name='pagename' value='$PageUrl' />
        <input type='hidden' name='action' value='forum' />
        <input type='hidden' name='timeid' value='".DecHex(time())."' />
        <table>
        <tr><td align=right>Name:</td>
                <td align=left><input type='text' name='name' value='".$Author."' size='40' />
                </td></tr>
        <tr><td align=right>Topic title:</td>
                <td align=left><input type='text' name='topic' value='' size='40' />
                </td></tr>
        <tr><td align=right>Message:</td>
                <td align=left><textarea name='message' cols=40 rows=12></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input class='button' type='submit' value='OK' />&nbsp;<input class='button' type='reset'>
            </td></tr></table></form><CENTER>Please only press the OK button ONCE.</CENTER><BR /><HR>
        ");
} else SDV($ForumTagFmt,"
<CENTER><B>Post a Message to the Forum</B></CENTER>
        <form action='$PageUrl' method='get'>
        <input type='hidden' name='pagename' value='".$_GET['n']."' />
        <input type='hidden' name='action' value='forum' />
        <input type='hidden' name='timeid' value='".DecHex(time())."' />
        <table>
        <tr><td align=right>Name:</td>
                <td align=left><input type='text' name='name' value='".$Author."' size='40' />
                </td></tr>
        <tr><td align=right>Topic title:</td>
                <td align=left><input type='text' name='topic' value='' size='40' />
                </td></tr>
        <tr><td align=right>Message:</td>
                <td align=left><textarea name='message' cols=40 rows=12></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input class='button' type='submit' value='OK' />&nbsp;<input class='button' type='reset'>
            </td></tr></table></form><CENTER>Please only press the OK button ONCE.</CENTER><BR /><HR>
        ");

if (isset($EnablePathInfo) && !$EnablePathInfo)
  SDV($ForumCommentTagFmt,"
<BR /><CENTER><B>Post a Reply to This Topic</B></CENTER>
          <form action='$ScriptUrl' method='get'>
        <input type='hidden' name='pagename' value='\$PageName' />
        <input type='hidden' name='action' value='forumcomment' />
        <table>
        <tr><td align=right>Name:</td>
                <td align=left><input type='text' name='name' value='".$Author."' size='40' />
                </td></tr>
        <tr><td align=right>Reply:</td>
                <td align=left><textarea name='message' cols=40 rows=12></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input type='submit' value='OK' />&nbsp;<input type='reset'>
            </td></tr></table></form><CENTER>Please only press the OK button ONCE.</CENTER>
        ");
SDV($ForumCommentTagFmt,"
<BR /><CENTER><B>Post a Reply to This Topic</B></CENTER>
        <form action='$PageUrl' method='get'>
        <input type='hidden' name='pagename' value='".$_GET['n']."' />
        <input type='hidden' name='action' value='forumcomment' />
        <table>
        <tr><td align=right>Name:</td>
                <td align=left><input type='text' name='name' value='".$Author."' size='40' />
                </td></tr>
        <tr><td align=right>Reply:</td>
                <td align=left><textarea name='message' cols=40 rows=12></textarea>
                </td></tr>
        <tr><td>&nbsp;</td>
            <td><input class='button' type='submit' value='OK' />&nbsp;<input class='button' type='reset'>
            </td></tr></table></form><CENTER>Please only press the OK button ONCE.</CENTER>
        ");

Markup('{$Forum}', '>{$var}','/\\(:\\$Forum:\\)/',Keep($ForumTagFmt));
Markup('{$Forumcomment}', '>{$var}','/\\(:\\$Forumcomment:\\)/',Keep($ForumCommentTagFmt));
Markup('^-_-','block','/^-_-(.*)$/','<DIV ALIGN=\'center\'>$1</DIV>');

#SDV($InlineReplacements['/\\[\\[\\$Forum\\]\\]/e'],
#  "FmtPageName(\$GLOBALS['ForumTagFmt'],\$pagename)");

#SDV($InlineReplacements['/\\[\\[\\$Forumcomment\\]\\]/e'],
#  "FmtPageName(\$GLOBALS['ForumCommentTagFmt'],\$pagename)");

function HandleForum($pagename) {
        global $TimeFmt,$Now,$_GET,$newpage,$Group,$Name;
        $timeID=$_GET["timeid"];
        $topictitle=StripCSlashes($_GET["topic"]);
        $wikilink=$topictitle.$timeID;
        $name=StripCSlashes($_GET["name"]);
        $rcpage = ReadPage($pagename,"");
        #TestLog("rcpage1 /$rcpage/");
        $pos=strpos($rcpage['text'],"(:\$Forum:)");
        $len=strlen("(:\$Forum:)");
        $before=substr($rcpage['text'],0,$pos+$len);
        $after=substr($rcpage['text'],$pos+$len);
        $newpage1=strtr($wikilink,".:,;-_!\"§$%&/()=?{[]}\\&auml;&ouml;&uuml;'#+*`´&eacute;&Eacute;&egrave;&Egrave;&aacute;&Aacute;&agrave;&Agrave;&iacute;&Iacute;&igrave;&Igrave;",
        "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");    /* why substitutes something? */
        $newpage1="[[".$newpage."]]";
        #TestLog("Forum:  /$pagename/$wikilink/$newpage/G:$Group/N:$Name");
  /*v1  $newpage=FmtWikiLink('',$newpage,NULL,'PageName',$pagename);  */
        $newpage=MakePageName($pagename, $wikilink);
      //FmtWikiLink('',$match[1],NULL,'PageName',$pagename);
        $rcpage['text'] = $before.
                "\n||''$name''||[[".substr($newpage,strpos($newpage,'.')+1)."]] $topictitle &nbsp;||%green%&nbsp;''".
                strftime($TimeFmt,$Now).
                "''%%||".$after;
        WritePage($pagename,$rcpage);

        $rcpage = ReadPage($newpage,"");
        #TestLog("rcpage2 /$rcpage/");
        /* pog: changes Wikisyntax v2 */
        $rcpage['text']="[[Main.Home | Home]] &raquo; [[".substr($pagename,strpos($pagename,'.')+1)." | Forum]] &raquo;\n\n-_-[++'''Forum Topic: ".StripCSlashes($topictitle)." ++]'''\n-_-'''Posted by&nbsp;$name on "
                .strftime($TimeFmt,$Now)."'''\n"
                .StripCSlashes($_GET["message"])
                ."\n\n----\n\n'''[++Replies:++]''' \n\n(:\$Forumcomment:)\n\n----";
        #TestLog("Text" .$rcpage['text']);
        WritePage($newpage,$rcpage);
        Redirect($pagename);
}

function HandleForumComment($pagename) {

        global $TimeFmt,$Now,$_GET,$newpage,$Group,$Name;
        $comment=StripCSlashes($_GET["message"]);
        $name=StripCSlashes($_GET["name"]);

        $rcpage = ReadPage($pagename,"");
        $pos=strpos($rcpage['text'],"(:\$Forumcomment:)");
        $before=substr($rcpage['text'],0,$pos);
        $after=substr($rcpage['text'],$pos);
        $rcpage['text'] = $before.
                "\n\n%green%Posted by ".$name." on ".strftime($TimeFmt,$Now)."%%[[<<]]\n$comment\n\n----\n".$after;
        #TestLog("F-Comment: /$pageName/");
        WritePage($pagename,$rcpage);
        Redirect($pagename);
}
?>