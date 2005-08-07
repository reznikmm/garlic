<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script defines PmWiki's standard markup.  It is automatically
    included from stdconfig.php unless $EnableStdMarkup==0.

    Each call to Markup() below adds a new rule to PmWiki's translation
    engine (unless a rule with the same name has already been defined).  
    The form of the call is Markup($id,$where,$pat,$rep); 
    $id is a unique name for the rule, $where is the position of the rule
    relative to another rule, $pat is the pattern to look for, and
    $rep is the string to replace it with.
*/

## first we preserve text in [=...=] and [@...@]
Markup('[=','_begin','/\\[([=@])(.*?)\\1\\]/se',
    "Keep(\$K0['$1'].PSS('$2').\$K1['$1'])");
Markup('restore','<_end',"/$KeepToken(\\d.*?)$KeepToken/e",
    '$GLOBALS[\'KPV\'][\'$1\']');

## remove carriage returns before preserving text
Markup('\\r','<[=','/\\r/','');

# $[phrase] substitutions
Markup('$[phrase]', '>[=',
  '/\\$\\[(?>([^\\]]+))\\]/e', "XL(PSS('$1'))");

# {$var} substitutions
Markup('{$fmt}','>$[phrase]',
  '/{\\$((Group|Name|Title)(spaced)?|LastModified(By|Host)?|FullName)}/e',
  "FmtPageName('$$1',\$pagename)");
Markup('{$var}','>{$fmt}',
  '/{\\$(Version|Author|UrlPage|DefaultName|DefaultGroup|AuthId|SiteGroup)}/e',
  "\$GLOBALS['$1']");
Markup('if', 'fulltext',
  "/\\(:(if[^\n]*?):\\)(.*?)(?=\\(:if[^\n]*?:\\)|$)/sei",
  "CondText(\$pagename,PSS('$1'),PSS('$2'))");

## (:include:)
Markup('include', '>if',
  '/\\(:(include\\s+.+?):\\)/ei',
  "PRR().IncludeText(\$pagename,'$1')");

## GroupHeader/GroupFooter handling
Markup('nogroupheader', '>include',
  '/\\(:nogroupheader:\\)/ei',
  "PZZ(\$GLOBALS['GroupHeaderFmt']='')");
Markup('nogroupfooter', '>include',
  '/\\(:nogroupfooter:\\)/ei',
  "PZZ(\$GLOBALS['GroupFooterFmt']='')");
Markup('groupheader', '>nogroupheader',
  '/\\(:groupheader:\\)/ei',
  "PRR().FmtPageName(\$GLOBALS['GroupHeaderFmt'],\$pagename)");
Markup('groupfooter','>nogroupfooter',
  '/\\(:groupfooter:\\)/ei',
  "PRR().FmtPageName(\$GLOBALS['GroupFooterFmt'],\$pagename)");

## (:nl:)
Markup('nl0','<split',"/([^\n])(?>(?:\\(:nl:\\))+)([^\n])/i","$1\n$2");
Markup('nl1','>nl0',"/\\(:nl:\\)/i",'');

## \\$  (end of line joins)
Markup('\\$','>nl1',"/\\\\(?>(\\\\*))\n/e",
  "' '.str_repeat('<br />',strlen('$1'))");

## (:noheader:),(:nofooter:),(:notitle:)...
Markup('noheader', 'directives',
  '/\\(:noheader:\\)/ei',
  "SetTmplDisplay('PageHeaderFmt',0)");
Markup('nofooter', 'directives',
  '/\\(:nofooter:\\)/ei',
  "SetTmplDisplay('PageFooterFmt',0)");
Markup('notitle', 'directives',
  '/\\(:notitle:\\)/ei',
  "SetTmplDisplay('PageTitleFmt',0)");
Markup('noleft', 'directives',
  '/\\(:noleft:\\)/ei',
  "SetTmplDisplay('PageLeftFmt',0)");
Markup('noright', 'directives',
  '/\\(:noright:\\)/ei',
  "SetTmplDisplay('PageRightFmt',0)");

## (:spacewikiwords:)
Markup('spacewikiwords', 'directives',
  '/\\(:(no)?spacewikiwords:\\)/ei',
  "PZZ(\$GLOBALS['SpaceWikiWords']=('$1'!='no'))");

## (:linkwikiwords:)
Markup('linkwikiwords', 'directives',
  '/\\(:(no)?linkwikiwords:\\)/ei',
  "PZZ(\$GLOBALS['LinkWikiWords']=('$1'!='no'))");

## (:messages:)
Markup('messages', 'directives',
  '/^\\(:messages:\\)/ei',
  "'<:block>'.Keep(
    FmtPageName(implode('',(array)\$GLOBALS['MessagesFmt']), \$pagename))");

## (:comment:)
Markup('comment', 'directives', '/\\(:comment .*?:\\)/i', '');

## character entities
Markup('&','directives','/&amp;(?>([A-Za-z0-9]+|#\\d+|#[xX][A-Fa-f0-9]+));/',
  '&$1;');

## (:title:)
Markup('title','>&',
  '/\\(:title\\s(.*?):\\)/ei',
  "PZZ(\$GLOBALS['PCache'][\$pagename]['title']=PSS('$1'))");

## (:keywords:)
Markup('keywords', '>&', 
  "/\\(:keywords?\\s+([^'\n]+?):\\)/ei",
  "PZZ(\$GLOBALS['HTMLHeaderFmt'][] = 
    \"<meta name='keywords' content='$1' />\")");
Markup('description', '>&',
  "/\\(:description\\s+(.+?):\\)/ei",
  "PZZ(\$GLOBALS['HTMLHeaderFmt'][] = \"<meta name='description' content='\".
    str_replace('\\'','&#39;',PSS('$1')).\"' />\")"); 

#### inline markups ####
## ''emphasis''
Markup("''",'inline',"/''(.*?)''/",'<em>$1</em>');

## '''strong'''
Markup("'''","<''","/'''(.*?)'''/",'<strong>$1</strong>');

## '''''strong emphasis'''''
Markup("'''''","<'''","/'''''(.*?)'''''/",'<strong><em>$1</em></strong>');

## @@code@@
Markup('@@','inline','/@@(.*?)@@/','<code>$1</code>');

## '+big+', '-small-'
Markup("'+",'inline',"/'\\+(.*?)\\+'/",'<big>$1</big>');
Markup("'-",'inline',"/'\\-(.*?)\\-'/",'<small>$1</small>');

## '^superscript^', '_subscript_'
Markup("'^",'inline',"/'\\^(.*?)\\^'/",'<sup>$1</sup>');
Markup("'_",'inline',"/'_(.*?)_'/",'<sub>$1</sub>');

## [+big+], [-small-]
Markup('[+','inline','/\\[(([-+])+)(.*?)\\1\\]/e',
  "'<span style=\'font-size:'.(round(pow(6/5,$2strlen('$1'))*100,0)).'%\'>'.
    PSS('$3</span>')");

## {+ins+}, {-del-}
Markup('{+','inline','/\\{\\+(.*?)\\+\\}/','<ins>$1</ins>');
Markup('{-','inline','/\\{-(.*?)-\\}/','<del>$1</del>');

## [[<<]] (break)
Markup('[[<<]]','inline','/\\[\\[&lt;&lt;\\]\\]/',"<br clear='all' />");

###### Links ######
## [[free links]]
Markup('[[','links',"/(?>\\[\\[\\s*)(\\S.*?)\\]\\]($SuffixPattern)/e",
  "Keep(MakeLink(\$pagename,PSS('$1'),NULL,'$2'),'L')");

## [[!Category]]
SDV($CategoryGroup,'Category');
SDV($LinkCategoryFmt,"<a class='categorylink' href='\$LinkUrl'>\$LinkText</a>");
Markup('[[!','<[[','/\\[\\[!(.*?)\\]\\]/e',
  "Keep(MakeLink(\$pagename,PSS('$CategoryGroup/$1'),NULL,'',\$GLOBALS['LinkCategoryFmt']),'L')");

## [[target | text]]
Markup('[[|','<[[',
  "/(?>\\[\\[([^|\\]]+)\\|\\s*)(.*?)\\s*\\]\\]($SuffixPattern)/e",
  "Keep(MakeLink(\$pagename,PSS('$1'),PSS('$2'),'$3'),'L')");

## [[text -> target ]]
Markup('[[->','>[[|',
  "/(?>\\[\\[([^\\]]+?)\\s*-+&gt;\\s*)(\\S.+?)\\]\\]($SuffixPattern)/e",
  "Keep(MakeLink(\$pagename,PSS('$2'),PSS('$1'),'$3'),'L')");

## [[#anchor]]
Markup('[[#','<[[','/(?>\\[\\[#([A-Za-z][-.:\\w]*))\\]\\]/e',
  "Keep(\"<a name='$1' id='$1'></a>\",'L')");

## [[target |#]] reference links
Markup('[[|#', '<[[|',
  "/(?>\\[\\[([^|\\]]+))\\|#\\]\\]/e",  
  "Keep(MakeLink(\$pagename,PSS('$1'),'['.++\$MarkupFrame[0]['ref'].']'),'L')");

## bare urllinks 
Markup('urllink','>[[',
  "/\\b(?>(\\L))[^\\s$UrlExcludeChars]*[^\\s.,?!$UrlExcludeChars]/e",
  "Keep(MakeLink(\$pagename,'$0','$0'),'L')");

## mailto: links 
Markup('mailto','<urllink',
  "/\\bmailto:([^\\s$UrlExcludeChars]*[^\\s.,?!$UrlExcludeChars])/e",
  "Keep(MakeLink(\$pagename,'$0','$1'),'L')");

## inline images
Markup('img','<urllink',
  "/\\b(?>(\\L))([^\\s$UrlExcludeChars]+$ImgExtPattern)(\"([^\"]*)\")?/e",
  "Keep(\$GLOBALS['LinkFunctions']['$1'](\$pagename,'$1','$2','$4','$1$2',
    \$GLOBALS['ImgTagFmt']),'L')");

## bare wikilinks
Markup('wikilink','>urllink',"/\\b($GroupPattern([\\/.]))?($WikiWordPattern)/e",
  "Keep(WikiLink(\$pagename,'$0'),'L')");

## escaped `WikiWords
Markup('`wikiword', '<wikilink',
  "/`(($GroupPattern([\\/.]))?($WikiWordPattern))/e",
  "Keep('$1')");

## Lines that begin with displayed images receive their own block.  A
## pipe following the image indicates a "caption" (generates a linebreak).
Markup('^img', '<block',
  "/^((?>(\\s+|%%|%[A-Za-z][-,=:#\\w\\s'\"]*%)*)$KeepToken(\\d+L)$KeepToken)(\\s*\\|\\s?)?(.*)$/e",
  "PSS((strpos(\$GLOBALS['KPV']['$3'],'<img')===false) ? '$0' : 
       '<:block><div>$1' . ('$4' ? '<br />' : '') .'$5</div>')");

#### Block markups ####
## process any <:...> markup
Markup('^<:','>block','/^(<:([^>]+)>)?/e',"Block('$2')");

# unblocked lines w/block markup become anonymous <:block>
Markup('^!<:', '<^<:',
  '/^(?!<:)(?=.*<(form|div|table|p|ul|ol|dl|h[1-6]|blockquote|pre|hr|textarea)\\b)/',
  '<:block>');

## bullet lists
Markup('^*','block','/^(\\*+)\\s?/','<:ul,$1>');

## numbered lists
Markup('^#','block','/^(#+)\\s?/','<:ol,$1>');

## indented (->) /hanging indent (-<) text
Markup('^->','block','/^(?>(-+))&gt;\\s?/','<:indent,$1>');
Markup('^-<','block','/^(?>(-+))&lt;\\s?/','<:outdent,$1>');

## definition lists
Markup('^::','block','/^(:+)([^:]+):/','<:dl,$1><dt>$2</dt><dd>');

## preformatted text
Markup('^ ','block','/^(\\s)/','<:pre,1>$1');

## Q: and A:
Markup('^Q:', 'block', '/^Q:(.*)$/', "<:block><p class='question'>$1</p>");
Markup('^A:', 'block', '/^(A:.*)$/', "<:block><p class='answer'>$1</p>");

## blank lines
Markup('blank','<^ ','/^\\s*$/','<:vspace>');

## tables
## ||cell||, ||!header cell||, ||!caption!||
Markup('^||||', 'block', 
  '/^\\|\\|.*\\|\\|.*$/e',
  "FormatTableRow(PSS('$0'))");
## ||table attributes
Markup('^||','>^||||','/^\\|\\|(.*)$/e',
  "PZZ(\$GLOBALS['BlockMarkups']['table'][0] = PQA(PSS('<table $1>')))
    .'<:block>'");

## headings
Markup('^!', 'block',
  '/^(!{1,6})\\s?(.*)$/e',
  "'<:block><h'.strlen('$1').PSS('>$2</h').strlen('$1').'>'");

## horiz rule
Markup('^----','>^->','/^----+/','<:block><hr />');

#### (:table:) markup (AdvancedTables)

function Cells($name,$attr) {
  global $MarkupFrame;
  $attr = preg_replace('/([a-zA-Z]=)([^\'"]\\S*)/',"\$1'\$2'",$attr);
  $tattr = @$MarkupFrame[0]['tattr'];
  $name = strtolower($name);
  $out = array('<:block>');
  if (strncmp($name, 'cell', 4) != 0 || @$MarkupFrame[0]['closeall']['div']) {
    $out[] = @$MarkupFrame[0]['closeall']['div']; 
    unset($MarkupFrame[0]['closeall']['div']);
    $out[] = @$MarkupFrame[0]['closeall']['table']; 
    unset($MarkupFrame[0]['closeall']['table']);
  }
  if ($name == 'div') {
    $MarkupFrame[0]['closeall']['div'] = "</div>";
    $out[] = "<div $attr>";
  }
  if ($name == 'table') $MarkupFrame[0]['tattr'] = $attr;
  if (strncmp($name, 'cell', 4) == 0) {
    if (!@$MarkupFrame[0]['closeall']['table']) {
       $MarkupFrame[0]['closeall']['table'] = "</td></tr></table>";
       $out[] = "<table $tattr><tr><td $attr>";
    } else if ($name == 'cellnr') $out[] = "</td></tr><tr><td $attr>";
    else $out[] = "</td><td $attr>";
  }
  return implode('', $out);
}

Markup('^table', '<block',
  '/^\\(:(table|cell|cellnr|tableend|div|divend)(\\s.*?)?:\\)/ie',
  "Cells('$1',PSS('$2'))");
Markup('^>>', '<table',
  '/^&gt;&gt;(.*?)&lt;&lt;(.*)$/',
  '(:div:)%div $1 apply=div%$2 ');

#### special stuff ####
## (:markup:) for displaying markup examples
Markup('markup', '<[=',
  "/(^|\\(:nl:\\))\\(:markup:\\)[^\\S\n]*\\[=(.*?)=\\]/seim",
  "'$1'.Keep('<table class=\"markup\" align=\"center\"><tr><td class=\"markup\"><pre>'.wordwrap(PSS('$2'),70).'</pre></td></tr><tr><td>').PSS('\n$2\n(:divend:)</td></tr></table>\n')");
Markup('markupend', '>markup',
  "/(^|\\(:nl:\\))\\(:markup:\\)[^\\S\n]*\n(.*?)\\(:markupend:\\)/seim",
  "'$1'.Keep('<table class=\"markup\" align=\"center\"><tr><td class=\"markup\"><pre>'.wordwrap(PSS('$2'),70).'</pre></td></tr><tr><td>').PSS('\n$2\n(:divend:)</td></tr></table>\n')");
$HTMLStylesFmt['markup'] = "
  table.markup { border: 2px dotted #ccf; width:90%; }
  table.markup td { padding-left:10px; padding-right:10px; }
  td.markup { border-bottom: 1px solid #ccf; }
  p.question { font-weight:bold; }
  ";

