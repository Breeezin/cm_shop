#!/bin/bash

ps -ef | fgrep Newsletter | fgrep -v grep > /dev/null
if [ $? -eq 0 ] ; then
	echo "Still running..."
	exit 1
fi
cd ..
echo "Running Newsletter, output is emailed to macbjorck@mac.com"
nohup ./periodicSendNewsletter.php < /dev/null | mail -e macbjorck@mac.com 2>&1 &
