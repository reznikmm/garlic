#!/bin/bash
set -e
DIR=`dirname "$0"`

export PATH=$1/bin:$PATH
unset LANG

T=20

timeout $T $DIR/echo/echo.sh $1

echo Tests completed
