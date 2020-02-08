#!/bin/bash
set -e
echo "TEST: Linktest"
DIR=`dirname "$0"`
GPR=$DIR/../../gnat/linktest.gpr
gprbuild -p -P $GPR
