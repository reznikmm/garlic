#!/bin/bash
set -e
echo "TEST: Examples/Eratho/absolute"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Eratho/absolute
pushd $TEST
gnatdist absolute.cfg  -v
echo -e 'localhost\nlocalhost' | ./mainloop | tail -n1 > output.txt

grep -F ' 50 ' output.txt
