#!/bin/sh
###############################################################
# FILE: createNewSite.sh 
# CREATED BY: L. Lopez
# DATE: Nov 24th 2015
# VER: 0.01
# DESC: This script will create a new site. 
#
#
###############################################################
# CHANGES: 
# TODO: 
########################
# Vars
LOG=/var/log/createNewSite.log
WORKDIR=/root/scripts
PGPHOMEDIR=/opt/pgp
JAILHOMEDIR=/jail/pgp
JAILSKELETONDIR=$JAILHOMEDIR/SKELETON
PGPSKELETONDIR=$PGPHOMEDIR/SKELETON
CURRENTUSER=`id --user`
NEWSITE=$1
###############################################################

###############################################################
# GENERAL FUNCTIONS
checkUser(){
    echo "Checking credentials..."
    sleep 1
    if [ $CURRENTUSER -ne 0 ]; then
        echo "Oops! You need to be ROOT to run this..."
        echo "Exiting."
        exit 501
    fi
    echo "Good You are ROOT..."
    sleep 1
    echo ""
}
echouno(){
	echo $1
	sleep 1
}
testcase(){
    if [ $? = 0 ]; then
        echo OK.
    else
        echo "Error code $?"
        exit 1
    fi
}
usage(){
    echo ""
    echo "Hmmm... Something went wrong. Here's the usage."
    echo "Usage: `basename $0` <NEWSITENAME>"
	echo ""
}
###############################################################
createJail(){
	echouno "Creating JAIL directories..."
	cp -R $JAILSKELETONDIR $JAILHOMEDIR/$NEWSITE
	testcase
	chmod 777 $JAILHOMEDIR/$NEWSITE/log
	chown root:sftponly $JAILHOMEDIR/$NEWSITE
	chown $NEWSITE:sftponly $JAILHOMEDIR/$NEWSITE/*
	chown root:root $JAILHOMEDIR/$NEWSITE/log/pgp.log
	echouno "Creating SSH keys..."
	ssh-keygen -b 2048 -t rsa -f /jail/pgp/$NEWSITE/.ssh/$NEWSITE -q -N ""
	testcase
	echouno "Attaching public SFTP key to the system..."
	cat /jail/pgp/$NEWSITE/.ssh/$NEWSITE.pub > /usr/local/dj/keys/${NEWSITE}_authorized_keys
	testcase
}
createSite(){
	echouno "Creating PGP directories..."
	cp -R $PGPSKELETONDIR $PGPHOMEDIR/$NEWSITE
	testcase
	chown -R pgpuser:pgpuser $PGPHOMEDIR/$NEWSITE
	echouno "Creating site PGP key pair..."
	echo '===================================================================================================='
	su - pgpuser -c "pgp --gen-key ${NEWSITE}dev@dowjones.net --key-type RSA --bits 2048 --passphrase ''"
	testcase
	echo '===================================================================================================='
}
addSiteUser(){
	echouno "Creating site user..."
	useradd -K CREATE_HOME=false -s /bin/false -d / -g sftponly -c "(HOME /jail/opt/$NEWSITE)" $NEWSITE
	testcase
	echouno "Changing user password..."
	echo $NEWSITE:`date | md5sum` | chpasswd
	testcase
}
addSystemLogEntry(){
	echouno "Adding system log entries..."
	LASTUSER=`tail -2 /etc/passwd | head -1 | awk -F: '{print $1}'`
	rsync /etc/rsyslog.conf $WORKDIR/configFiles/rsyslog.conf.bk
	sed "/$LASTUSER/a if \$programname == '$NEWSITE' then /jail/pgp/$NEWSITE/log/pgp.log" /etc/rsyslog.conf >$WORKDIR/configFiles/rsyslog.auto
	testcase
	echouno "Appending system changes..."
	cat $WORKDIR/configFiles/rsyslog.auto > /etc/rsyslog.conf
	testcase
	service rsyslog restart
}
showResults(){
	cat /etc/newsite.txt
	echouno "Here are some important paths you might need to change:"
	echo "PGP.ENV: /opt/pgp/$NEWSITE/pgp.env"
	echo "PGPENCRYPT.SH /opt/pgp/$NEWSITE/pgpencrypt.sh"
	echo "SFTP private key: /jail/pgp/$NEWSITE/.ssh/$NEWSITE"
}
################################
# RUNTIME STARTS HERE
checkUser
if [ -z $NEWSITE ]; then #No site entered?
	usage
	exit 1
fi

NEWSITE=`echo $NEWSITE | tr '[a-z]' '[A-Z]'`
addSiteUser
createJail
createSite
addSystemLogEntry
showResults
