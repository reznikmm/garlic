<?php if (!defined('PmWiki')) exit();

Markup (
   "sflogo_small_white",
   ">links",
   "/\\(:sflogo small white (.*?):\\)/",
   '<A HREF="http://sourceforge.net" TARGET="_blank"><IMG SRC="http://sourceforge.net/sflogo.php?group_id=$1&amp;type=1" ALT="SourceForge.net Logo" ALIGN=TEXTTOP width="88" height="31" border="0"></A>');

Markup (
   "sflogo_medium_white",
   ">links",
   "/\\(:sflogo medium white (.*?):\\)/",
   '<A HREF="http://sourceforge.net" TARGET="_blank"><IMG SRC="http://sourceforge.net/sflogo.php?group_id=$1&amp;type=2" ALT="SourceForge.net Logo" ALIGN=TEXTTOP width="125" height="37" BORDER="0"></A>');

Markup (
   "sflogo_large_white",
   ">links",
   "/\\(:sflogo large white (.*?):\\)/",
   '<A HREF="http://sourceforge.net" TARGET="_blank"><IMG SRC="http://sourceforge.net/sflogo.php?group_id=$1&amp;type=5" ALT="SourceForge.net Logo" ALIGN=TEXTTOPwidth="210" height="62" border="0"></A>');

# Special Version to go with Ada Answers
Markup (
   "sflogo_aa",
   ">links",
   "/\\(:sflogo ada (.*?):\\)/",
   '<A HREF="http://sourceforge.net" TARGET="_blank"><IMG SRC="http://sourceforge.net/sflogo.php?group_id=$1&amp;type=5" ALT="SourceForge.net Logo" ALIGN=TEXTTOP WIDTH=234 HEIGHT=60 BORDER=0></A>');

Markup("sf_package",   ">links", "/§p§(.*?)§§/",    "<cite style='color: limegreen;'>$1</cite>");
Markup("sf_command",   ">links", "/§c§(.*?)§§/",    "<var style='color: royalblue;'>$1</var>");
Markup("sf_function",  ">links", "/§f§(.*?)§§/",    "<code style='color: darkturquoise;'>$1</code>");
Markup("sf_keyword",   ">links", "/§k§(.*?)§§/",    "<strong style='color: cadetblue;'>$1</strong>");
Markup("sf_attribute", ">links", "/§a§(.*?)§§/",    "<strong style='color: indianred;'>$1</strong>");
Markup("sf_type",      ">links", "/§t§(.*?)§§/",    "<strong style='color: tomato;'>$1</strong>");
Markup("sf_keyboard",  ">links", "/§b§(.*?)§§/",    "<kbd style='color: slateblue;'>$1</kbd>");
Markup("sf_exsample",  ">links", "/§x§(.*?)§§/",    "<samp style='color: darkorchid;'>$1</samp>");
Markup("sf_filename",  ">links", "/§n§(.*?)§§/",    "<cite style='color: midnightblue;'>$1</cite>");

# vim: wrap tabstop=4 shiftwidth=4 softtabstop=4 noexpandtab
?>
