#!/bin/bash
set -e
echo "TEST: Examples/Messages"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Messages
pushd $TEST
gnatdist pingpong.cfg -v
rm -rf *.ali *.o dsa
gnatdist -v pingpong.cfg p2
./main  > output.txt
LINES=`cat output.txt| wc -l`

if [ x$LINES != "x220" ]; then
  echo Unexpected result $LINES
  cat output.txt
  false
fi
