Readme and Decription for the DropDownSkin for PmWiki 2.x:
==========================================================
This skin and all accompaning documentation was created by Klonk (Karl Loncarek) 
and could be used freely. For the file "csshover.htc" there might be a special 
license, but better look at "http://www.xs4all.nl/~peterned/" for this. Look out 
for "whatever:hover".

If you want to know more about the history of this skin take a look at the file
"history.txt".

Here is the description (PmWiki markup):

!!About

I created this skin just for fun and for showing the possibilities and power of CSS. I got the idea and valauble hints from different sources from the internet and other skins (thanks for BronwynSkin).

I integrated really special stuff in this skin:

# Tabs for editing, upload, viewing, attributes (idea taken from BronwynSkin)
# Integration of a document @@[=$Group.MenuBar=]@@ which is displayed ahead of the text.
# The really special thing is: the [=MenuBar=] is displayed as drop down menu
# Link at the bottom of the page to jump to the top of the page
# Two additional links for directly editing the documents @@[=$Group.SideBar=]@@ and @@[=$Group.MenuBar=]@@.
# If no @@[=$Group.MenuBar=]@@ exists then automatically @@[=Main.MenuBar=]@@ is loaded. If none of both exist, well then nothing is loaded and displayed. You will not recognize that.
# If the directive @@[=(:noheader:)=]@@ is used the edit links for the MenuBar and SideBar disappear from the footer. This is nice for using PmWiki as [=CMS=] which has edit capabilities, that are not too obvious.
# Menu mode selectable (compatibility mode; dropdown menu in MenuBar, dropdown menu in both SideBar and MenuBar, dropdown menu only in SideBar.
# Highly customizable: a lot components can be removed or are switchable
# Link bar at the bottom (BottomBar) can be customized (wiki document)

!!Download
Attach:dropdown.zip

This is always the latest version as there are comments in the history below. Additionally within this archive you can find a file @@history.txt@@ which tells you the version.

!!Installation
Copy the content of the above [=ZIP=]-file to @@/path/to/pmwiki/pub/skins/@@ and add the following code to your local configuration file:
  [=$Skin = 'dropdown';=]

If you also want to use the different print layout you also have to add:
  [=$ActionSkin['print'] = 'dropdown';=]

There is also some further functionality which could be set:

  [=$DropDownSkin['Version'] = 0;=]
->Depending which value you set different functionality is achieved:
** '''0''' activates compatibility mode (works same way as before, allows only one menu sublevel)
** '''1''' activates new functionality with up to 3 menu sublevels and menu only in the MenuBar
** '''2''' same as '''1''' but dropdown menu in SideBar and MenuBar possible
** '''3''' same as '''1''' but dropdown menu only in SideBar

 [=$DropDownSkin['SideBarEdit'] = 0;=]
->Hides the edit link for the SideBar in the footer.

 [=$DropDownSkin['MenuBarEdit'] = 0;=]
->Hides the edit link for the MenuBar in the footer.

 [=$DropDownSkin['BottomBarEdit'] = 0;=]
->Hides the edit link for the BottomBar in the footer.

 [=$DropDownSkin['GroupHeaderEdit'] = 0;=]
->Hides the edit link for the GroupFooter in the footer.

 [=$DropDownSkin['GroupFooterEdit'] = 0;=]
->Hides the edit link for the GroupFooter in the footer.

 [=$DropDownSkin['LeftSearch'] = 0;=]
->Hides the searchbox on the left side of the page.

 [=$DropDownSkin['PrintPreview'] = 0;=]
->Hides the Print Preview button.

 [=$DropDownSkin['PageLeftFmt'] = 0;=]
->Disables (hides) the left side for every wikipage, including Search and Logo. Disables also edit link for SideBar.

 [=$DropDownSkin['PageFooterFmt'] = 0;=]
->Disables (hides) the footer area for every wikipage.

 [=$DropDownSkin['PageHeaderFmt'] = 0;=]
->Disables (hides) the header area (tabs) and the edit links for MenuBar and SideBar for the every wikipage.

 [=$DropDownSkin['PageTitleFmt'] = 0;=]
->Disables (hides) the pagetitle for every wikipage.

 [=$DropDownSkin['PageMenuFmt'] = 0;=]
->Disables (hides) the MenuBar (if one existed) for every wikipage and also removes the MenuBar edit link.

!!Internals / Examples
!!!!!!For versions up to 13-Jan-2005 or $DropDownSkin['Version']=0 (=compatibility mode)
I had to integrate some small javascript code in the @@.tmpl@@ file to get the drop down menus also working in [=IE=] (Some faulty implementation of @@:hover@@). It did already work before this "hack" with Gecko based browsers (Mozilla, [=FireFox=]). It should also work with Opera.

The document @@[=MenuBar=]@@ consists of nested lists, also you need an additional style statement for the menut titles. Here is an example (taken from Main.SideBar):
 
 [=* %menutitle% Main.SideBar
 ** [[Main/Home Page]]
 ** [[Main/WikiSandbox]]
 ** [[Main/AllRecentChanges]]

 * %menutitle% [[PmWiki/PmWiki]]
 ** [[PmWiki/Installation | Download and Install]]
 ** [[PmWiki/Tips For Editing]]
 ** [[PmWiki/Documentation Index]]
 ** [[PmWiki/FAQ]]
 ** [[PmWiki/PmWikiPhilosophy]]=]

The style @@[=%menutitle%=]@@ is again needed for proper display of the drop down menu within [=IE=].

!!!!!!For versions after 13-Jan-2005
This skin is based on nested lists and uses heavily the @@:hover@@ feature. Most actual browsers support this feature. IE has limitations: It only interprets @@a:hover@@ (links).

I found a source in the web where this @@:hover@@ functionality is activated for all HTML entities within IE. This behaviour is programmed with help of an external script @@csshover.htc@@. More Information about that can be found at [[http://www.xs4all.nl/~peterned/ |Peterned]]. Search for @@whatever:hover@@. Known problem: For Windows [=XP2=] the webserver has to send the type "text/x-compressed" for ".htc" files. This script is only loaded by IE.

I changed from bulleted lists to numbered lists for compatibility with existing SideBar's. So the MenuBar and SideBar could have the same layout. Just one Exception. The SideBar can hold some other text too, but I would not recommend it for the MenuBar.
 
 [=# %menutitle% Main.SideBar
 ## [[Main/Home Page]]
 ## [[Main/WikiSandbox]]
 ## [[Main/AllRecentChanges]]
 ----
 # %menutitle% [[PmWiki/PmWiki]]
 ## [[PmWiki/Installation | Download and Install]]
 ## [[PmWiki/Tips For Editing]]
 ## [[PmWiki/Documentation Index]]
 ##%menuitem%Additional submenu
 ### [[Cookbook/Skins]]
 #### [[Cookbook/DropDownSkin]]
 #### [[Cookbook/LinesSkin]]
 ### [[Cookbook/Cookbook]]
 ##%separator%-
 ## [[PmWiki/FAQ]]
 ## [[PmWiki/PmWikiPhilosophy]]
 (:searchbox:)=]

Beware: You have to use @@----@@ for separating the main menu entries. It is necessary for interrupting "UL". This might change somewhen depending how this will be handled by PmWiki in future. You should also not mix links and normal text in one menuentry. This could corrupt the menu.

Above you can see several styles: @@[=%menutitle%=]@@ is necessary for correct display of the Main menuentry. @@[=%menuitem%=]@@ is necessary when an entry in the menu is not a link. You can use @@[=%separator%=]@@ for displaying a separator line in the menu. For IE compatibility (to see a line) you have to add some text (no matter what text, but I suggest "-").

In the BottomBar the links or text should be written within a numbered list for correct display in a row. Example:

 [=#[[Main/SearchWiki |SearchWiki]]
 #[[{$Group}/RecentChanges |RecentChanges]]
 #[[Main/AllRecentChanges |AllRecentChanges]]
 #[[PmWiki/WikiHelp |WikiHelp]]=]

But you can also place here whatever text or link you like. I would recommend only to place some few useful links here, because otherwise you'll distract from the real interesting stuff: the content! When no BottomBar document exists the above sample (HTML-hard coded) is displayed.

!!Translations
One thing could be translated in your own language (just add this line to your local XLPage (if is does not exist yet):

  [='Read Page' => '',
  'Page Attributes' => '',
  'All Recent Changes' => '',
  'Edit' => '',
  'Top' => '',
  'Go' => '',
  'SideBar' => '',
  'MenuBar' => '',
  'BottomBar' => '',
  'Main/AllRecentChanges' => '',
  '$Group/RecentChanges' => '',
=]

!!Additional hints
!!!!!!Removing components of the skin (temporarily)
These are standard markups that work also with this skin:

 [=(:notitle:)=]
->Removes the pagename

 [=(:noheader:)=]
->Removes the bars and the edit links for the MenuBar and SideBar in the footer

 [=(:nofooter:)=]
->Removes the whole footer

Here are some markups that are available only with this skin as it provides this functionality itself.

 [=(:noleft:)=]
->Removes the whole left side including search and logo (taken from Cookbook/RemovingLeftContent).

 [=(:nomenu:)=]
->Removes the complete menubar

!!!!!!Add search to MenuBar and SideBar
Simply place the following directive in the MenuBar or SideBar, but not within the list. Do it as a simple text entry (see example above).

 [=(:searchbox:)=]

!!Contributor
Karl Loncarek ([[~Klonk]])