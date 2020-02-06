#!/bin/bash
set -e
DIR=`dirname "$0"`

export PATH=$1/bin:$PATH
unset LANG

$DIR/examples/bank.sh
$DIR/examples/messages.sh
$DIR/examples/racw.sh
