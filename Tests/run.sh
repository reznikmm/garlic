#!/bin/bash
set -e
DIR=`dirname "$0"`

export PATH=$1/bin:$PATH
unset LANG

T=20

timeout $T $DIR/examples/bank.sh
timeout $T $DIR/examples/messages.sh
timeout $T $DIR/examples/racw.sh
timeout $T $DIR/examples/self.sh
timeout $T $DIR/linktest/linktest.sh

echo Tests completed
