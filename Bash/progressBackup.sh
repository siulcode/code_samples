#!/bin/sh
#
# CREATED BY: Luis L.
# DATE: 05/26/09
# FILE: backup_config
# DESC: This is the configuration file for the Progress backup.
#          

###########################
# VARIABLES
STARTTIME=`date +%a-%D-@-%X-%z`
LOGFILE=/var/log/backupProgress.log
BACKUP_HOME=/root/db_backup
REMOTE_SERVER=<remote_server>
DBFOLDERS=/dbs/c*
BACKUP_FILE="Progress_$(date +%a).tar.bz2"
SERVER_IP="<IP>"
SHARE_NAME="Technology\DTI_BACKUP"
SMB_PATH="/root/domainShare"
USER_NAME="<USER>"
USER_PASSWORD="<PASS>"
DOMAIN="DC"
MOUNTED_SHARE=`df | grep "DTI_BACKUP" | awk -F/ '{print $4}'` sCM=/root/scripts/scratchFolder/cm
sCONVCM=/root/scripts/scratchFolder/convcm
sCMTEST1=/root/scripts/scratchFolder/cmtest1
sCMTEST2=/root/scripts/scratchFolder/cmtest2
sCMTEST3=/root/scripts/scratchFolder/cmtest3
LIMIT=50
DEBUG_MODE=true


loadDebug(){
 if [ $DEBUG_MODE = "true" ]; then
  echo -e $1
 fi
}

loadDebug "------------------------ NEW PID STARTED: $$ ON: $STARTTIME ------------------------\n" > $LOGFILE stopAllDBs(){  loadDebug "---- STOPPING ALL DATABASES ----\n" >> $LOGFILE  loadDebug "---- CM ----" >> $LOGFILE  /pbs/bin/lockdb cm  sleep 2

 loadDebug "---- CONVCM ----\n" >> $LOGFILE  /pbs/bin/lockdb convcm  sleep 2

 loadDebug "---- CMTEST1 ----\n" >> $LOGFILE  /pbs/bin/lockdb cmtest1  sleep 2

 loadDebug "---- CMTEST2 ----\n" >> $LOGFILE  /pbs/bin/lockdb cmtest2  sleep 2

 loadDebug "---- CMTEST3 ----\n" >> $LOGFILE  /pbs/bin/lockdb cmtest3  sleep 2 }

startAllDBs(){
 loadDebug "---- UNLOCKING AND STARTING ALL DATABASES ----\n" >> $LOGFILE  loadDebug "UNLOCKING -- CM --\n" >> $LOGFILE  /pbs/bin/unlockdb cm

 loadDebug "UNLOCKING -- CONVCM --\n" >> $LOGFILE  /pbs/bin/unlockdb convcm

 loadDebug "UNLOCKING -- CMTEST1 --\n" >> $LOGFILE  /pbs/bin/unlockdb cmtest1

 loadDebug "UNLOCKING -- CMTEST2 --\n" >> $LOGFILE  /pbs/bin/unlockdb cmtest2

 loadDebug "UNLOCKING -- CMTEST3 --\n" >> $LOGFILE  /pbs/bin/unlockdb cmtest3  sleep 2

 loadDebug "STARTING -- CM --\n" >> $LOGFILE  /pbs/bin/cm -s

 loadDebug "STARTING -- CONVCM --\n" >> $LOGFILE  /pbs/bin/convcm -s

 loadDebug "STARTING -- CMTEST1 --\n" >> $LOGFILE
 /pbs/bin/cmtest1 -s

 loadDebug "STARTING -- CMTEST2 --\n" >> $LOGFILE
 /pbs/bin/cmtest2 -s

 loadDebug "STARTING -- CMTEST3 --\n" >> $LOGFILE
 /pbs/bin/cmtest3 -s

}

runLocalBackup(){
 loadDebug "-- COPYING PROGRESS DATABASE FILES --\n" >> $LOGFILE  cp -R -v $DBFOLDERS $BACKUP_HOME

}

mountRemoteShare(){
 if [ "$MOUNTED_SHARE" = "Technology" ]; then 
   loadDebug "-------- SHARE MOUNTED ALREADY -------\n" >> $LOGFILE  else
   loadDebug "---------- MOUNTING REMOTE DATAFILE -----------\n" >> $LOGFILE
   mount.cifs //$SERVER_IP/$SHARE_NAME $SMB_PATH -o username=$USER_NAME,password=$USER_PASSWORD,dom=$DOMAIN
 fi

}

wrapUpFiles(){
 loadDebug "------ WRAPPING UP FILES AND MOVING TO SMB SHARE ------\n" >> $LOGFILE  tar -cjf $BACKUP_FILE $BACKUP_HOME

 if [ -e $SMB_PATH/$BACKUP_FILE ]; then
  rm -f $SMB_PATH/$BACKUP_FILE
 fi
 
 mv $BACKUP_FILE $SMB_PATH
 
}

cleanUp(){
 rm -rf $BACKUP_HOME/*  

}

loadAllConfigs(){
. /pbs/config/cm
. /pbs/config/pbs
. /pbs/config/lm
. /pbs/config/convcm

}

loadDBStatus(){
   /pbs/bin/cm -c > $sCM
   /pbs/bin/convcm -c > $sCONVCM
   /pbs/bin/cmtest1 -c > $sCMTEST1
   /pbs/bin/cmtest2 -c > $sCMTEST2
   /pbs/bin/cmtest3 -c > $sCMTEST3
}

testConvcm(){
 if [ -e $sCONVCM ] && [ -s $sCONVCM ]; then
  SIZE=`cat $sCONVCM | wc -l`

  if [ $SIZE -gt $LIMIT ]; then
   STATUS=successful
   echo "convcm DB is UP" >> $LOGFILE
  else
   STATUS=failed
   echo "convcm DB is DOWN" >> $LOGFILE
  fi
 
 fi
}

testCmtest1(){
 if [ -e $sCMTEST1 ] && [ -s $sCMTEST1 ]; then
  SIZE=`cat $sCMTEST1 | wc -l`

  if [ $SIZE -gt $LIMIT ]; then
   STATUS=successful
   echo "Cmtest1 DB is UP" >> $LOGFILE
  else
   STATUS=failed
   echo "Cmtest1 DB is DOWN" >> $LOGFILE
  fi
 
 fi
}

testCmtest2(){
 if [ -e $sCMTEST2 ] && [ -s $sCMTEST2 ]; then
  SIZE=`cat $sCMTEST2 | wc -l`

  if [ $SIZE -gt $LIMIT ]; then
   STATUS=successful
   echo "Cmtest2 DB is UP" >> $LOGFILE
  else
   STATUS=failed
   echo "Cmtest2 DB is DOWN" >> $LOGFILE
  fi
 
 fi
}

testCmtest3(){
 if [ -e $sCMTEST3 ] && [ -s $sCMTEST3 ]; then
  SIZE=`cat $sCMTEST3 | wc -l`

  if [ $SIZE -gt $LIMIT ]; then
   STATUS=successful
   echo "Cmtest3 DB is UP" >> $LOGFILE
  else
   STATUS=failed
   echo "Cmtest3 DB is DOWN" >> $LOGFILE
  fi
 
 fi
}

verifyDBs(){
 if [ $DEBUG_MODE = "true" ]; then
   loadDebug "-------------------------- NOW I WILL BE VERIFING ALL DATABASES -------------------------\n" >> $LOGFILE
   loadDBStatus
 fi
   
 loadDebug "---------------- verifying CM ----------------\n" >> $LOGFILE  if [ -e $sCM ] && [ -s $sCM ]; then
  SIZE=`cat $sCM | wc -l`
 
  if [ $SIZE -gt $LIMIT ]; then
   STATUS=successful
   echo "CM DB is UP" >> $LOGFILE
  else
   STATUS=failed
   echo "CM DB is DOWN" >> $LOGFILE
  fi

  loadDebug "---------------- verifying CONVCM ------------\n" >> $LOGFILE
  if [ $STATUS = "successful" ]; then
   testConvcm
  fi
 
  loadDebug "---------------- verifying CMTEST1 -----------\n" >> $LOGFILE
  if [ $STATUS = "successful" ]; then
   testCmtest1
  fi
 
  loadDebug "---------------- verifying CMTEST2 -----------\n" >> $LOGFILE
  if [ $STATUS = "successful" ]; then
   testCmtest2
  fi
 
  loadDebug "---------------- verifying CMTEST3 -----------\n" >> $LOGFILE
  if [ $STATUS = "successful" ]; then
   testCmtest3
  fi

 fi

 STATUS=`echo $STATUS | tr "a-z" "A-Z"`
 cat $LOGFILE | mail -s"PROGRESS DATABASE BACKUP | STATUS: $STATUS " llopez@nypost.com }

