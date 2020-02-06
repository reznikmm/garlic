#!/bin/bash
set -e
echo "TEST: Examples/Bank"
DIR=`dirname "$0"`
TEST=$DIR/../../Examples/Bank
EXPECT=$DIR/expect.out
pushd $TEST
gnatdist simcity.cfg -v
popd
cp $TEST/example .

coproc P1 { $TEST/bank_server; }
coproc P2 { $TEST/bank_client; }
coproc P3 { $TEST/bank_client; }

echo -e 'l\nexample\np' >&${P1[1]}
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line

echo -e 'poor\nxxxx' >&${P2[1]}
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line

echo -e 'rich\nzzzz\nt\n1000\npoor' >&${P3[1]}
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line
read -u ${P1[0]} line; echo $line

while read -u ${P2[0]} line; do
   echo "X:" $line
   if [ x"$line" == x'=> Receive 1000 from rich' ]; then
     echo Found: $line
     break
   fi
done

echo q >&${P3[1]}; cat <&${P3[0]} > /dev/null
echo q >&${P2[1]}; cat <&${P2[0]} > /dev/null
echo q >&${P1[1]}; cat <&${P1[0]} > /dev/null

rm -f server.out client.out client2.out test.out example

