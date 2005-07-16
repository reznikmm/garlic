<?php if (!defined('PmWiki')) exit();
/*    This file is a changed version of print.php that is contained in the 
	PmWiki distribution.

	The change is basically the "if" routine. this is necessary if you 
	want to have a second skin which is here used for the print layout.
*/

global $LinkPageExistsFmt, $UrlLinkTextFmt, $HTMLHeaderFmt,
	$GroupPrintHeaderFmt, $GroupPrintFooterFmt, $DropDownSkin,
	$GroupHeaderFmt, $GroupFooterFmt, $action, $GLOBALS;

/*Set one of the following variables in your local config.php to change the behaviour*/
SDV($DropDownSkin['Version'],1); /* Set Version of Skin, default '1'
		0: gives "old skin" with javascript (Win XP2 compatible)
		1: uses the new version which activates ":hover" functionality in IE 
		   with help of an .htc file
		2: same as '1' but activates also DropDown menus on the SideBar
		3: same as '1' but activates DropDown menus only for the SideBar
		   (intended for optimization,when you don't use a MenuBar)*/
SDV($DropDownSkin['SideBarEdit'],1); /*Enable edit link for the SideBar*/
SDV($DropDownSkin['MenuBarEdit'],1); /*Enable edit link for the MenuBar*/
SDV($DropDownSkin['BottomBarEdit'],1); /*Enable edit link for the BottomBar*/
SDV($DropDownSkin['GroupHeaderEdit'],1); /*Enable edit link for the GroupHeader*/
SDV($DropDownSkin['GroupFooterEdit'],1); /*Enable edit link for the GroupFooter*/
SDV($DropDownSkin['LeftSearch'],1); /*Show Searchbox in LeftPageFmt*/
SDV($DropDownSkin['PrintPreview'],1); /*Show Print Preview Button*/
SDV($DropDownSkin['PageLeftFmt'],1); /*Show PageLeftFmt including search and Logo*/
SDV($DropDownSkin['PageFooterFmt'],1); /*Show PageFooterFmt*/
SDV($DropDownSkin['PageHeaderFmt'],1); /*Show PageHeaderFmt*/
SDV($DropDownSkin['PageTitleFmt'],1); /*Show PageTitleFmt*/
SDV($DropDownSkin['PageMenuFmt'],1); /*Show PageMenuFmt*/

/*Additional markups*/
	Markup('noleft','directives','/\\(:noleft:\\)/e',"SetTmplDisplay('PageLeftFmt',0)");
	Markup('nomenu','directives','/\\(:nomenu:\\)/e',"SetTmplDisplay('PageMenuFmt',0)");

if ($action=='print') { /* Switch Template for Print output*/
	$LinkPageExistsFmt = "<a class='wikilink' href='\$PageUrl?action=print'>\$LinkText</a>";
	$UrlLinkTextFmt = "<cite class='urllink'>\$LinkText</cite> [<a class='urllink' href='\$Url'>\$Url</a>]";
	SDV($GroupPrintHeaderFmt,'(:include $Group.GroupPrintHeader:)(:nl:)');
	SDV($GroupPrintFooterFmt,'(:nl:)(:include $Group.GroupPrintFooter:)');
	$GroupHeaderFmt = $GroupPrintHeaderFmt;
	$GroupFooterFmt = $GroupPrintFooterFmt;
	#$DoubleBrackets["/\\[\\[mailto:($UrlPathPattern)(.*?)\\]\\]/"] = 
	#  "''\$2'' [mailto:\$1]";
	LoadPageTemplate($pagename, "$SkinDir/print.tmpl");
}
else {
	/*Load default styles*/
	$html = "<link rel='stylesheet' href='$SkinDirUrl/dropdown.css' type='text/css' />\n";

	/* Change SkinVersion according to  variable $DropDownSkinVersion*/
	if ($DropDownSkin['Version'] == 0) { /*load style and necessary JavaScript portion for IE*/
		$html .= "<link rel='stylesheet' href='$SkinDirUrl/dropdownmenuv0.css' type='text/css' />\n";
		/*necessary for working drop down menus with IE*/
		$html .= '<script type="text/javascript" language="javascript">';
		$html .= "\n<!--//--><![CDATA[//><!--\nstartList = function() {\n";
		$html .= "if (document.all&&document.getElementById) {\n";
		$html .= 'divRoot = document.getElementById("menubar");';
		$html .= "\n";
		$html .= 'navRoot = divRoot.getElementsByTagName("ul")[0];';
		$html .= "\nif (navRoot) {\n";
		$html .= "for (i=0; i<navRoot.childNodes.length; i++) {\n";
		$html .= "node = navRoot.childNodes[i];\n";
		$html .= 'if (node.nodeName=="LI") {';
		$html .= "\nnode.onmouseover=function() {\n";
		$html .= 'this.className+=" over";}';
		$html .= "\nnode.onmouseout=function() {\n";
		$html .= 'this.className=this.className.replace(" over", "");';
		$html .= "\n}}}}}}\n";
		$html .= "window.onload=startList;\n";
		$html .= "//--><!]]></script>\n";
	}
	else { /* add :hover functionality to IE for all elements and load style*/
		$html .= '<!--[if IE]><style type="text/css" media="screen">body{behavior:url($SkinDirUrl/csshover.htc);}</style><![endif]-->';
		$html .= "\n";
	};
	if ($DropDownSkin['Version'] == 1) {/* MenuBar with new menus */
		$html .= "<link rel='stylesheet' href='$SkinDirUrl/dropdownmenuv1.css' type='text/css' />\n";
	}
	elseif ($DropDownSkin['Version'] == 2) {/* MenuBar and SideBar with new menus*/
		$html .= "<link rel='stylesheet' href='$SkinDirUrl/dropdownmenuv1.css' type='text/css' />\n";
		$html .= "<link rel='stylesheet' href='$SkinDirUrl/dropdownmenuv3.css' type='text/css' />\n";
	}
	elseif ($DropDownSkin['Version'] == 3) {/* only SideBar with new menus*/
		$html .= "<link rel='stylesheet' href='$SkinDirUrl/dropdownmenuv3.css' type='text/css' />\n";
	};

	$HTMLHeaderFmt[] = $html; /*place all above HTML into the header section*/

	if (!$DropDownSkin['PageLeftFmt']) {/*Remove area PageLeftFmt from display*/
		SetTmplDisplay('PageLeftFmt',0);
		$DropDownSkin['SideBarEdit'] = 0; /*remove edit-link from footer*/
	};
	if (!$DropDownSkin['PageFooterFmt']) {/*Remove area PageFooterFmt from display*/
		SetTmplDisplay('PageFooterFmt',0);
	};
	if (!$DropDownSkin['PageHeaderFmt']) {/*Remove area PageHeaderFmt from display*/
		SetTmplDisplay('PageHeaderFmt',0);
	};
	if (!$DropDownSkin['PageTitleFmt']) {/*Remove area PageTitleFmt from display*/
		SetTmplDisplay('PageTitleFmt',0);
	};
	if (!$DropDownSkin['PageMenuFmt']) {/*Remove area PageMenuFmt from display*/
		SetTmplDisplay('PageMenuFmt',0);
		$DropDownSkin['MenuBarEdit'] = 0; /*remove edit-link from footer*/
	};
};
/* correct width of "#right", when no PageLeftFmt is visible*/
function IsPageLeftFmtVisible($x) { 
	global $DropDownSkin;
	if ((isset($GLOBALS['TmplDisplay']['PageLeftFmt']) and !$GLOBALS['TmplDisplay']['PageLeftFmt']) or !$DropDownSkin['PageLeftFmt']) {
		echo 'style="width:98%"';
	};
	return;
};

/* hide or show Print Preview Button */
function DisplayPrintPreview($x) {
	global $DropDownSkin,$pagename,$ScriptUrl,$EnablePathInfo;
	if ($DropDownSkin['PrintPreview']) {
		$html = "<div id='print'><a href='".$ScriptUrl;
		$html .= (@$EnablePathInfo) ? "/".str_replace('.','/',$pagename) : "?n=".$pagename;
		$html .= "?action=print' target='_blank'>".XL("Printable View")."</a></div>";
	};
	echo $html;
	return;
};

/* hide or show Searchbox within LeftPageFmt*/
function IsSearchboxActive($x) {
	global $DropDownSkin,$ScriptUrl;
	if ($DropDownSkin['LeftSearch']) {
		$URL = (@$EnablePathInfo) ? "/".XL("Main/SearchWiki") : "?n=".str_replace('/','.',XL("Main/SearchWiki"));
		$html = "<div id='search'>\n<form action='$ScriptUrl".$URL."'>\n";
		$html .= "<input type='hidden' name='pagename' value='".XL("Main/SearchWiki")."' />\n";
		$html .= "<a href='$ScriptUrl".$URL."'>".XL("SearchWiki")."</a><br />\n";
		$html .= "<input class='wikisearchbox' type='text' name='q' value='' />\n";
		$html .= "<input class='wikisearchbutton' type='submit' value='".XL("Go")."' />\n</form>\n</div>";
	};
	echo $html;
	return;
};

/* Show Editlinks in the footer only when header is active */
function DisplayEditButtons($x) {
	global $ScriptUrl,$pagename,$EnablePathInfo,$DropDownSkin,$DefaultGroup;
      $Group = FmtPageName('$Group',$pagename);
	$GroupURL = (@$EnablePathInfo) ? "/$Group/" : "?n=$Group.";
	$html = "";
	/*Checks whether a BottomBar already exists, if not, default is displayed*/
	if (!PageExists($Group."/BottomBar") and !PageExists($DefaultGroup."/BottomBar") and !PageExists("Main/BottomBar")) {
		$html .= "<ol>\n<li><a href='$ScriptUrl";
		$html .= (@$EnablePathInfo) ? "/".XL("Main/SearchWiki") : "?n=".str_replace('/','.',XL("Main/SearchWiki"));
		$html .=  "'>".XL("SearchWiki")."</a></li>\n";
		$html .= "<li><a href='$ScriptUrl".$GroupURL.FmtPageName('$Name',substr(XL('$Group/RecentChanges'),1))."'>".XL("Recent Changes")."</a></li>\n";
		$html .= "<li><a href='$ScriptUrl";
		$html .= (@$EnablePathInfo) ? "/".XL("Main/AllRecentChanges") : "?n=".str_replace('/','.',XL("Main/AllRecentChanges"));
		$html .= "'>".XL("All Recent Changes")."</a></li>\n";
		$html .= "<li><a href='$ScriptUrl";
		$html .= (@$EnablePathInfo) ? "/".XL("PmWiki/WikiHelp") : "?n=".str_replace('/','.',XL("PmWiki/WikiHelp"));
		$html .= "'>".XL("WikiHelp")."</a></li>\n";
            $html .= "</ol>\n";
	};
	/*Check which of the edit links may be shown*/
	if ((!isset($GLOBALS['TmplDisplay']['PageHeaderFmt']) || $GLOBALS['TmplDisplay']['PageHeaderFmt']) and $DropDownSkin['PageHeaderFmt']) {
		$html .= "<p class='vspace'></p><ol><li class='bar'><a href='#Topp'>&#9650; ".XL("Top")." &#9650;</a></li>\n";
		if ($DropDownSkin['SideBarEdit'] or $DropDownSkin['MenuBarEdit'] or $DropDownSkin['BottomBarEdit']) {$html .= "<li>".XL("Edit").": </li>\n";};
		if ($DropDownSkin['SideBarEdit']) {$html .= "<li class='bar'><a href='$ScriptUrl".$GroupURL."SideBar?action=edit'>".XL("SideBar")."</a></li>\n";};
		if ($DropDownSkin['MenuBarEdit']) {$html .= "<li class='bar'><a href='$ScriptUrl".$GroupURL."MenuBar?action=edit'>".XL("MenuBar")."</a></li>\n";};
		if ($DropDownSkin['BottomBarEdit']) {$html .= "<li class='bar'><a href='$ScriptUrl".$GroupURL."BottomBar?action=edit'>".XL("BottomBar")."</a></li>\n";};
		if ($DropDownSkin['GroupHeaderEdit']) {$html .= "<li class='bar'><a href='$ScriptUrl".$GroupURL."GroupHeader?action=edit'>".XL("GroupHeader")."</a></li>\n";};
		if ($DropDownSkin['GroupFooterEdit']) {$html .= "<li class='bar'><a href='$ScriptUrl".$GroupURL."GroupFooter?action=edit'>".XL("GroupFooter")."</a></li>\n";};
		$html .= "</ol>\n";
	}
	echo $html;
	return;
}


?>
