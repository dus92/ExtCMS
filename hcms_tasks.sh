#!/bin/bash
touch ./cron2.dat
STAT=`ps aux|grep hcms_tasks.s[h]|wc|awk '{print $1}'`
echo "$STAT"
if [ "$STAT" -gt 2 ]; then
exit 0
 else
	while : 
	do 
		sleep 1
		wget -w 2 -b --spider http://dev1.dmlabs.ru/cms/cron.php || break	 
	done
fi