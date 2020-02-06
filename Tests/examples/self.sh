#!/bin/bash
set -e
echo "TEST: Examples/Self"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Self
pushd $TEST
gnatdist self.cfg  -v
echo 'localhost' | ./main  > output.txt

grep -F '1996' output.txt
