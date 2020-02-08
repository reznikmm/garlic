#!/bin/bash
set -e
echo "TEST: Examples/Eratho/dynamic"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Eratho/dynamic
pushd $TEST
gnatdist dynamic.cfg  -v
echo -e 'localhost\nlocalhost' | ./mainloop | tail -n1 > output.txt

grep -F ' 50 ' output.txt
