<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005-2007 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.
*/

# $InputAttrs are the attributes we allow in output tags
SDV($InputAttrs, array('name', 'value', 'id', 'class', 'rows', 'cols', 
  'size', 'maxlength', 'action', 'method', 'accesskey', 'multiple',
  'checked', 'disabled', 'readonly', 'enctype', 'src', 'alt'));

# Set up formatting for text, submit, hidden, radio, etc. types
foreach(array('text', 'submit', 'hidden', 'password', 'radio', 'checkbox',
              'reset', 'file', 'image') as $t) 
  SDV($InputTags[$t][':html'], "<input type='$t' \$InputFormArgs />");
SDV($InputTags['text']['class'], 'inputbox');
SDV($InputTags['password']['class'], 'inputbox');
SDV($InputTags['submit']['class'], 'inputbutton');
SDV($InputTags['reset']['class'], 'inputbutton');
SDV($InputTags['radio'][':checked'], 'checked');
SDV($InputTags['checkbox'][':checked'], 'checked');

# (:input form:)
SDVA($InputTags['form'], array(
  ':args' => array('action', 'method'),
  ':html' => "<form \$InputFormArgs>",
  'method' => 'post'));

# (:input end:)
SDV($InputTags['end'][':html'], '</form>');

# (:input textarea:)
SDVA($InputTags['textarea'], array(
  ':html' => "<textarea \$InputFormArgs></textarea>"));

# (:input image:)
SDV($InputTags['image'][':args'], array('name', 'src', 'alt'));

# (:input select:)
SDVA($InputTags['select-option'], array(
  ':args' => array('name', 'value', 'label'),
  ':content' => array('label', 'value', 'name'),
  ':attr' => array('value', 'selected'),
  ':checked' => 'selected',
  ':html' => "<option \$InputFormArgs>\$InputFormContent</option>"));
SDVA($InputTags['select'], array(
  'class' => 'inputbox',
  ':html' => "<select \$InputSelectArgs>\$InputSelectOptions</select>"));

##  (:input default:) needs to occur before all other input markups.
Markup('input', 'directives',
  '/\\(:input\\s+(default)\\b(.*?):\\)/ei',
  "InputDefault(\$pagename, '$1', PSS('$2'))");

##  (:input select:) has its own markup processing
Markup('input-select', 'input',
  '/\\(:input\\s+select\\s.*?:\\)(?:\\s*\\(:input\\s+select\\s.*?:\\))*/ei',
  "InputSelect(\$pagename, 'select', PSS('$0'))");

##  (:input ...:) goes after all other input markups
Markup('input-type', '>input', 
  '/\\(:input\\s+(\\w+)(.*?):\\)/ei',
  "InputMarkup(\$pagename, '$1', PSS('$2'))");

##  The 'input+sp' rule combines multiple (:input select ... :)
##  into a single markup line (to avoid split line effects)
Markup('input+sp', '<split', 
  '/(\\(:input\\s+select\\s(?>.*?:\\)))\\s+(?=\\(:input\\s)/', '$1');

function InputToHTML($pagename, $type, $args, &$opt) {
  global $InputTags, $InputAttrs, $InputValues, $FmtV;
  if (!@$InputTags[$type]) return "(:input $type $args:)";
  ##  get input arguments
  $args = ParseArgs($args);
  ##  convert any positional arguments to named arguments
  $posnames = @$InputTags[$type][':args'];
  if (!$posnames) $posnames = array('name', 'value');
  while (count($posnames) > 0 && count(@$args['']) > 0) {
    $n = array_shift($posnames);
    if (!isset($args[$n])) $args[$n] = array_shift($args['']);
  }
  ##  merge defaults for input type with arguments
  $opt = array_merge($InputTags[$type], $args);
  ##  convert any remaining positional args to flags
  foreach ((array)@$opt[''] as $a) 
    { $a = strtolower($a); if (!isset($opt[$a])) $opt[$a] = $a; }
  $name = @$opt['name'];
  ##  set control values from $InputValues array
  ##  radio, checkbox, select, etc. require a flag of some sort,
  ##  others just set 'value'
  if (isset($InputValues[$name])) {
    $checked = @$opt[':checked'];
    if ($checked) {
      $opt[$checked] = in_array(@$opt['value'], (array)$InputValues[$name])
                       ? $checked : false;
    } else if (!isset($opt['value'])) $opt['value'] = $InputValues[$name];
  }
  ##  build $InputFormArgs from $opt
  $attrlist = (isset($opt[':attr'])) ? $opt[':attr'] : $InputAttrs;
  $attr = array();
  foreach ($attrlist as $a) {
    if (!isset($opt[$a]) || $opt[$a]===false) continue;
    $attr[] = "$a='".str_replace("'", '&#39;', $opt[$a])."'";
  }
  $FmtV['$InputFormArgs'] = implode(' ', $attr);
  $FmtV['$InputFormContent'] = '';
  foreach((array)@$opt[':content'] as $a)
    if (isset($opt[$a])) { $FmtV['$InputFormContent'] = $opt[$a]; break; }
  return FmtPageName($opt[':html'], $pagename);
}


function InputDefault($pagename, $type, $args) {
  global $InputValues;
  $args = ParseArgs($args);
  $args[''] = (array)@$args[''];
  if (!isset($args['name'])) $args['name'] = array_shift($args['']);
  if (!isset($args['value'])) $args['value'] = array_shift($args['']);
  if (!isset($InputValues[$args['name']])) 
    $InputValues[$args['name']] = $args['value'];
  return '';
}

function InputMarkup($pagename, $type, $args) {
  return Keep(InputToHTML($pagename, $type, $args, $opt));
}

function InputSelect($pagename, $type, $markup) {
  global $InputTags, $InputAttrs, $FmtV;
  preg_match_all('/\\(:input\\s+\\S+\\s+(.*?):\\)/', $markup, $match);
  $selectopt = (array)$InputTags[$type];
  $opt = $selectopt;
  $optionshtml = '';
  $optiontype = isset($InputTags["$type-option"]) 
                ? "$type-option" : "select-option";
  foreach($match[1] as $args) {
    $optionshtml .= InputToHTML($pagename, $optiontype, $args, $oo);
    $opt = array_merge($opt, $oo);
  }
  $attrlist = array_diff($InputAttrs, array('value'));
  foreach($attrlist as $a) {
    if (!isset($opt[$a]) || $opt[$a]===false) continue;
    $attr[] = "$a='".str_replace("'", '&#39;', $opt[$a])."'";
  }
  $FmtV['$InputSelectArgs'] = implode(' ', $attr);
  $FmtV['$InputSelectOptions'] = $optionshtml;
  return Keep(FmtPageName($selectopt[':html'], $pagename));
}


## Form-based authorization prompts (for use with PmWikiAuth)

SDVA($InputTags['auth_form'], array(
  ':html' => "<form action='{$_SERVER['REQUEST_URI']}' method='post' 
    name='authform'>\$PostVars"));
SDV($AuthPromptFmt, array(&$PageStartFmt, 'page:$SiteGroup.AuthForm',
  "<script language='javascript' type='text/javascript'><!--
    try { document.authform.authid.focus(); }
    catch(e) { document.authform.authpw.focus(); } //--></script>",
  &$PageEndFmt));

## The section below handles specialized EditForm pages.
## We don't bother to load it if we're not editing.

if ($action != 'edit') return;

SDV($PageEditForm, '$SiteGroup.EditForm');
SDV($PageEditFmt, '$EditForm');
if (@$_REQUEST['editform']) {
  $PageEditForm=$_REQUEST['editform'];
  $PageEditFmt='$EditForm';
}
$Conditions['e_preview'] = '(boolean)$_POST["preview"]';

XLSDV('en', array(
  'ak_save' => 's',
  'ak_saveedit' => 'u',
  'ak_preview' => 'p',
  'ak_textedit' => ',',
  'e_rows' => '23',
  'e_cols' => '60'));

# (:e_preview:) displays the preview of formatted text.
Markup('e_preview', 'directives',
  '/^\\(:e_preview:\\)/e',
  "Keep(\$GLOBALS['FmtV']['\$PreviewText'])");

# If we didn't load guiedit.php, then set (:e_guibuttons:) to
# simply be empty.
Markup('e_guibuttons', 'directives', '/\\(:e_guibuttons:\\)/', '');

# Prevent (:e_preview:) and (:e_guibuttons:) from 
# participating in text rendering step.
SDV($SaveAttrPatterns['/\\(:e_(preview|guibuttons):\\)/'], ' ');

SDVA($InputTags['e_form'], array(
  ':html' => "<form action='{\$PageUrl}?action=edit' method='post'><input 
    type='hidden' name='action' value='edit' /><input 
    type='hidden' name='n' value='{\$FullName}' /><input 
    type='hidden' name='basetime' value='\$EditBaseTime' />"));
SDVA($InputTags['e_textarea'], array(
  ':html' => "<textarea \$InputFormArgs 
    onkeydown='if (event.keyCode==27) event.returnValue=false;' 
    >\$EditText</textarea>",
  'name' => 'text', 'id' => 'text', 'accesskey' => XL('ak_textedit'),
  'rows' => XL('e_rows'), 'cols' => XL('e_cols')));
SDVA($InputTags['e_author'], array(
  ':html' => "<input type='text' \$InputFormArgs />",
  'name' => 'author', 'value' => $Author));
SDVA($InputTags['e_changesummary'], array(
  ':html' => "<input type='text' \$InputFormArgs />",
  'name' => 'csum', 'size' => '60', 'maxlength' => '100',
  'value' => htmlspecialchars(stripmagic(@$_POST['csum']), ENT_QUOTES)));
SDVA($InputTags['e_minorcheckbox'], array(
  ':html' => "<input type='checkbox' \$InputFormArgs />",
  'name' => 'diffclass', 'value' => 'minor'));
if (@$_POST['diffclass']=='minor') 
  SDV($InputTags['e_minorcheckbox']['checked'], 'checked');
SDVA($InputTags['e_savebutton'], array(
  ':html' => "<input type='submit' \$InputFormArgs />",
  'name' => 'post', 'value' => ' '.XL('Save').' ', 
  'accesskey' => XL('ak_save')));
SDVA($InputTags['e_saveeditbutton'], array(
  ':html' => "<input type='submit' \$InputFormArgs />",
  'name' => 'postedit', 'value' => ' '.XL('Save and edit').' ',
  'accesskey' => XL('ak_saveedit')));
SDVA($InputTags['e_savedraftbutton'], array(':html' => ''));
SDVA($InputTags['e_previewbutton'], array(
  ':html' => "<input type='submit' \$InputFormArgs />",
  'name' => 'preview', 'value' => ' '.XL('Preview').' ', 
  'accesskey' => XL('ak_preview')));
SDVA($InputTags['e_cancelbutton'], array(
  ':html' => "<input type='submit' \$InputFormArgs />",
  'name' => 'cancel', 'value' => ' '.XL('Cancel').' ' ));
SDVA($InputTags['e_resetbutton'], array(
  ':html' => "<input type='reset' \$InputFormArgs />",
  'value' => ' '.XL('Reset').' '));

