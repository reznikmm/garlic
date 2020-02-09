#!/bin/bash
set -e
echo "TEST: Examples/MultiPro"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/MultiPro
pushd $TEST
gnat compile -gnatg -I$1/lib/garlic/ s-*.adb
gnatdist multipro.cfg -v

for J in 1 2 3 4 ; do
   ./part$J > out$J.txt &
done

wait
grep Net_Loc_In_Use out[1-4].txt
