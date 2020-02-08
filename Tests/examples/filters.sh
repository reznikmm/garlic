#!/bin/bash
set -e
echo "TEST: Examples/Filters"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Filters
pushd $TEST
gnat compile -gnatg -I$1/lib/garlic/ s-*.adb
gnatdist filters.cfg -v
./mainloop | tail -n 1 | tee output.txt

grep -F ' 50 ' output.txt
