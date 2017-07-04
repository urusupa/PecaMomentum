#!/bin/sh
#ポート開放してるEEEPCの自鯖からindex.txtを持ってくる
cd /home/squalo/www/PecaMomentum
/usr/local/bin/wget -O indextp.txt http://nyoron.ddo.jp/PecaMomentum/indextp.txt
/usr/local/bin/wget -O indexsp.txt http://nyoron.ddo.jp/PecaMomentum/indexsp.txt
1> /dev/null
exit