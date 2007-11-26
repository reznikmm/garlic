#!/bin/zsh

setopt Extended_Glob;
setopt CSH_Null_Glob;

pushd $(dirname ${0})/..
for I in (#i)./**/*~(gen*file|configure*|*.bash|*.btm|*.cmd|*.sh|*.zsh|*.rexx)*~(*/.svn/*)(.);  do
	chmod -x "${I}";
	svn propdel svn:executable "${I}";
    done; unset I;

    for I in (#i)./**/(gen*file|configure*|*.bash|*.btm|*.cmd|*.sh|*.zsh|*.rexx)~(*/.svn/*)(.); do
	chmod +x "${I}";
	svn propset svn:executable "*" "${I}";
    done; unset I
popd;

# vim: set nowrap tabstop=8 shiftwidth=4 softtabstop=4 noexpandtab :
# vim: set textwidth=0 filetype=zsh foldmethod=marker nospell :
