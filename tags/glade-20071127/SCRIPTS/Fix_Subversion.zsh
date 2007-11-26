#!/bin/zsh

setopt No_Verbose;
setopt No_X_Trace;
setopt Err_Exit;
setopt Extended_Glob;
setopt CSH_Null_Glob;

pushd $(dirname ${0})/..
    find .									\
	'('									\
	-iname "*.ada"	-or							\
	-iname "*.adb"	-or							\
	-iname "*.ads"								\
	')'									\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-ada"		     '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.c"  -or							\
	-iname "*.h"								\
	')'									\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-c"			     '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.rb"								\
	')'									\
    -exec svn propset svn:mime-type "text/x-ruby"   '{}' ';'			;

    find .									\
	'('									\
	-iname "*.cpp"	-or							\
	-iname "*.hpp"								\
	')'									\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-c++"		     '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.sh"		    -or						\
	-iname "*.zsh"		    -or						\
	-iname "*.bash"								\
	')'									\
    -exec chmod +x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-sh"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.rexx"								\
	')'									\
    -exec chmod +x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-rexx"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.bat"		    -or						\
	-iname "*.cmd"		    -or						\
	-iname "*.btm"								\
	')'									\
    -exec chmod +x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-dosbatch"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.mak"		    -or						\
	-iname "Makefile"							\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/makefile"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.vim"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-vim"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.diff"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propdel svn:keywords				     '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-patch"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.rpmmacros"	-or						\
	-iname "*.spec"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:mime-type "text/x-rpm-spec"		     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.gif"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propdel svn:eol-style		'{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propset svn:mime-type "image/gif" '{}' ';'			;

    find .									\
	'('									\
	-iname "*.bmp"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propdel svn:eol-style		'{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propset svn:mime-type "image/bmp" '{}' ';'			;

    find .									\
	'('									\
	-iname "*.mbm"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propdel svn:eol-style		'{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propset svn:mime-type "image/mbm" '{}' ';'			;

    find .									\
	'('									\
	-iname "*.png"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propdel svn:eol-style		'{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propset svn:mime-type "image/png" '{}' ';'			;

    find .									\
	'('									\
	-iname "*.jpg"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "image/jpg" '{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propdel svn:eol-style		'{}' ';'			;

    find .									\
	'('									\
	-iname "*.mp3"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "audio/mpeg mp3" '{}' ';'			\
    -exec svn propdel svn:keywords		'{}' ';'			\
    -exec svn propdel svn:eol-style		'{}' ';'			;

    find .									\
	'('									\
	-iname "*.tmpl"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/x-httpd-php"	'{}' ';'		\
    -exec svn propdel svn:keywords			'{}' ';'		\
    -exec svn propdel svn:eol-style			'{}' ';'		;

    find .									\
	'('									\
	-iname "*.css"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/css"		'{}' ';'		\
    -exec svn propdel svn:keywords			'{}' ';'		\
    -exec svn propdel svn:eol-style			'{}' ';'		;

    find .									\
	'('									\
	-iname "*.html"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/html"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.xml"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/xmp"			     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

    find .									\
	'('									\
	-iname "*.rpm"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "application/x-rpm"	    '{}' ';'		\
    -exec svn propdel svn:keywords			    '{}' ';'		\
    -exec svn propdel svn:eol-style			    '{}' ';'		;

    find .									\
	'('									\
	-iname "*.bz2"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "application/x-bzip2"   '{}' ';'		\
    -exec svn propdel svn:keywords			    '{}' ';'		\
    -exec svn propdel svn:eol-style			    '{}' ';'		;

    find .									\
	'('									\
	-iname "*.gz"		-or						\
	-iname "*.tgz"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "application/x-gtar"    '{}' ';'		\
    -exec svn propdel svn:keywords			    '{}' ';'		\
    -exec svn propdel svn:eol-style			    '{}' ';'		;

    find .									\
	'('									\
	-iname "*.txt"		-or						\
	-iname ".cvsignore"							\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/plain"	'{}' ';'		\
    -exec svn propdel svn:keywords			'{}' ';'		\
    -exec svn propdel svn:eol-style			'{}' ';'		;

    find .									\
	'('									\
	-iname "*.utz"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "application/zip"	'{}' ';'		\
    -exec svn propdel svn:keywords			'{}' ';'		\
    -exec svn propdel svn:eol-style			'{}' ';'		;

    find .									\
	'('									\
	-iname "*.thm"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "application/x-tar"	'{}' ';'		\
    -exec svn propdel svn:keywords			'{}' ';'		\
    -exec svn propdel svn:eol-style			'{}' ';'		;

    find .									\
	'('									\
	-iname "*_List"								\
	')'									\
    -exec chmod -x '{}' ';'							\
    -exec svn propset svn:mime-type "text/plain"		     '{}' ';'	\
    -exec svn propset svn:keywords "Author Date Id Revision HeadURL" '{}' ';'	\
    -exec svn propset svn:eol-style "LF"			     '{}' ';'	;

popd;

############################################################ {{{1 ###########
# vim: textwidth=0 nowrap tabstop=8 shiftwidth=4 softtabstop=4 noexpandtab
# vim: filetype=zsh encoding=utf-8 fileformat=unix
