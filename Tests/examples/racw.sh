#!/bin/bash
set -e
echo "TEST: Examples/RACW"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/RACW
pushd $TEST
gnatdist racw.cfg -v
echo -e 'localhost\nlocalhost\n1\n2\n' | ./main  > output.txt

grep -F 'at speed 1' output.txt
grep -F 'at speed 2' output.txt
