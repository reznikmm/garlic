<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    The APR compatible MD5 encryption algorithm in _crypt() below is 
    based on code Copyright 2005 by D. Faure and the File::Passwd
    PEAR library module by Mike Wallner <mike@php.net>.

    This script enables simple authentication based on username and 
    password combinations.  At present this script can authenticate
    from passwords held in arrays or in .htpasswd-formatted files,
    but eventually it will support authentication via sources such
    as LDAP and Active Directory.

    To configure a .htpasswd-formatted file for authentication, do
        $AuthUser['htpasswd'] = '/path/to/.htpasswd';
    prior to including this script.  

    Individual username/password combinations can also be placed
    directly in the $AuthUser array, such as:
        $AuthUser['pmichaud'] = crypt('secret');

    To authenticate against an LDAP server, put the url for
    the server in $AuthUser['ldap'], as in:
        $AuthUser['ldap'] = 'ldap://ldap.example.com/ou=People,o=example?uid';
*/

# Let's set up an authorization prompt that includes usernames.
SDV($AuthPromptFmt, array(&$PageStartFmt,
  "<p><b>$[Password required]</b></p>
    <form name='authform' action='{$_SERVER['REQUEST_URI']}' method='post'>
      $[Name]: <input tabindex='1' type='text' name='authid' value='' /><br />
      $[Password]: <input tabindex='2' type='password' name='authpw' value='' />
      <input type='submit' value='OK' />\$PostVars</form>
      <script language='javascript' type='text/javascript'><!--
        document.authform.authid.focus() //--></script>", &$PageEndFmt));

# This is a helper function called when someone meets the
# authentication credentials:
function AuthenticateUser($authid) {
  $GLOBALS['AuthId'] = $authid;
  @session_start(); $_SESSION['authid'] = $authid;
}

# If the admin hasn't configured any password entries, just return.
if (!$AuthUser) return;

# Now, let's get the $id and $pw to be checked -- we'll first take them 
# from a submitted form, if any; if not there then we'll check and see
# if they're available from HTTP basic authentication.  If we don't
# have any $id at all, we just exit since there's nothing to 
# authenticate here.
if (@$_POST['authid']) 
  { $id = $_POST['authid']; $pw = $_POST['authpw']; }
else if (@$_SERVER['PHP_AUTH_USER']) 
  { $id = $_SERVER['PHP_AUTH_USER']; $pw = $_SERVER['PHP_AUTH_PW']; }
else return;

# Okay, we have $id and $pw, now let's see if we can find any
# matching entries.  First, let's check the $AuthUser array directly:
if (@$AuthUser[$id]) 
  foreach((array)($AuthUser[$id]) as $c)
    if (crypt($pw, $c) == $c) { AuthenticateUser($id); return; }

# Now lets check any .htpasswd file equivalents
foreach((array)($AuthUser['htpasswd']) as $f) {
  $fp = fopen($f, "r"); if (!$fp) continue;
  while ($x = fgets($fp, 1024)) {
    $x = rtrim($x);
    list($i, $c, $r) = explode(':', $x, 3);
    if ($i == $id && _crypt($pw, $c) == $c) 
      { fclose($fp); AuthenticateUser($id); return; }
  }
  fclose($fp);
}

# LDAP authentication.  
if ($AuthUser['ldap'] && $id && $pw 
    && preg_match('!ldap://([^:]+)(?::(\d+))?/(.+)$!', 
                  $AuthUser['ldap'], $match)) {
  list($z, $server, $port, $path) = $match;
  list($basedn, $attr, $sub) = explode('?', $path);
  if (!$port) $port=389;
  if (!$attr) $attr = 'uid';
  if (!$sub) $sub = 'one';
  $ds = ldap_connect($server, $port);
  ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
  if (ldap_bind($ds, @$AuthLDAPBindDN, @$AuthLDAPBindPassword)) {
    $fn = ($sub == 'sub') ? 'ldap_search' : 'ldap_list';
    $sr = $fn($ds, $basedn, "($attr=$id)", array($attr));
    $x = ldap_get_entries($ds, $sr);
    if ($x['count'] == 1) {
      $dn = $x[0]['dn'];
      if (ldap_bind($ds, $dn, $pw)) AuthenticateUser($id);
    }
  }
  ldap_close($ds);
}

#  The _crypt function provides support for SHA1 encrypted passwords 
#  (keyed by '{SHA}') and Apache MD5 encrypted passwords (keyed by 
#  '$apr1$'); otherwise it just calls PHP's crypt() for the rest.
#  The APR MD5 encryption code was contributed by D. Faure.

function _crypt($plain, $salt=null) {
  if (strncmp($salt, '{SHA}', 5) == 0) 
    return '{SHA}'.base64_encode(pack('H*', sha1($plain)));
  if (strncmp($salt, '$apr1$', 6) == 0) {
    preg_match('/^\\$apr1\\$([^$]+)/', $salt, $match);
    $salt = $match[1];
    $length = strlen($plain);
    $context = $plain . '$apr1$' . $salt;
    $binary = pack('H32', md5($plain . $salt . $plain));
    for($i = $length; $i > 0; $i -= 16) 
      $context .= substr($binary, 0, min(16, $i));
    for($i = $length; $i > 0; $i >>= 1)
      $context .= ($i & 1) ? chr(0) : $plain{0};
    $binary = pack('H32', md5($context));
    for($i = 0; $i < 1000; $i++) {
      $new = ($i & 1) ? $plain : $binary;
      if ($i % 3) $new .= $salt;
      if ($i % 7) $new .= $plain;
      $new .= ($i & 1) ? $binary : $plain;
      $binary = pack('H32', md5($new));
    }
    $q = '';
    for ($i = 0; $i < 5; $i++) {
      $k = $i + 6;
      $j = $i + 12;
      if ($j == 16) $j = 5;
      $q = $binary{$i}.$binary{$k}.$binary{$j} . $q;
    }
    $q = chr(0).chr(0).$binary{11} . $q;
    $q = strtr(strrev(substr(base64_encode($q), 2)),
           'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
           './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
    return "\$apr1\$$salt\$$q";
  }
  if (md5($plain) == $salt) return $salt;
  return crypt($plain, $salt);
}
