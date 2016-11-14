#!/bin/sh
#
# CREATED BY: Luis L.
# DATE: 12/14/11
# FILE: restoreFromLiveDb.sh 
# DESC: Restore from latest Prod backup.
###########################
# Notes & changes
#
# **Syntax**
# restoreFromLiveDb.sh <Target DatabaseName> 
#
# **Base commands:
# rm context.lk
# prostrct repair $RESTOREDIR/context                                                     
# prostrct list $RESTOREDIR/context                                                       
# cat context.st
# rfutil $RESTOREDIR/context -C aiarchiver disable
# proutil $RESTOREDIR/context -C DisableSiteReplication source                            
# rfutil $RESTOREDIR/context -C aimage end                                                
# prostrct remove $RESTOREDIR/context ai                                                  
# procopy $RESTOREDIR/context /dbs/cmtest2/cmcontext2
#
# **Exit error codes:
# PF test - 430/431	
# Data Directory - 130
#
# **Changed WORKINGDIR path, it targets the /app mount for easier space management @LuisL.061512@
# **Changed TARGETDB to be dynamically assigned as the first param. @LuisL.080212@
# **Refactored prepTarget, it includes all test databases now @LuisL.082012@
# **Changed target from cmtest2 to cmtest6 - JIRA NYP-614 @LuisL.032614@
###########################
# VARIABLES
STARTTIME=`date +%a-%D-@-%X-%z`
MAINLOG=/var/log/LiveToQaRestore.log
PRODSERVER="skpkdti01"
LIVEDBFILE=live.tar.bz2
WORKINGDIR=/app/staging/recover
RESTOREDIR=$WORKINGDIR/cm
ARCHIVEDIR=/root/share
KEY="/root/.ssh/dtikey"
DATADIR=/root/scripts/scratchFolder
PRODBACKUPFILE="$2"
BACKUP_HOME=/dbs/db_backup
PFDIR=/root/scripts/EBUPFBackup
TARGETDB=$1
SOURCE_INSTANCES="cm context cmaddress services ar gl tmc"
###########################

function lockTarget(){
    echo
	echo "NOTICE: Locking $TARGETDB for copy"
	echo
    sleep 2
    lockdb $TARGETDB
    unlockdb $TARGETDB
}

function prepTarget(){
	if [ $TARGETDB = "cmtest6" ];then
        for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm6;;
                context )
                    CONTEXT=cmcontext6;;
                cmaddress )
                    CMADDRESS=cmaddress6;;
                services )
                    SERVICES=cmservices6;;
                ar )
                    AR=ar6;;
                gl )
                    GL=gl6;;
                tmc )
                    TMC=tmc6;;
            esac
        done
    fi
	if [ $TARGETDB = "cmtest5" ];then
		for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm5;;
                context )
                    CONTEXT=cmcontext5;;
                cmaddress )
                    CMADDRESS=cmaddress5;;
                services )
                    SERVICES=cmservices5;;
                ar )
                    AR=ar5;;
                gl )
                    GL=gl5;;
                tmc )
                    TMC=tmc5;;
            esac
        done
	fi
	if [ $TARGETDB = "cmtest4" ];then
    	for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm4;;
                context )
                    CONTEXT=cmcontext4;;
                cmaddress )
                    CMADDRESS=cmaddress4;;
                services )
                    SERVICES=cmservices4;;
                ar )
                    AR=ar4;;
                gl )
                    GL=gl4;;
                tmc )
                    TMC=tmc4;;
            esac
        done
    fi
	if [ $TARGETDB = "cmtest3" ];then
        for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm3;;
                context )
                    CONTEXT=cmcontext3;;
                cmaddress )
                    CMADDRESS=cmaddress3;;
                services )
                    SERVICES=cmservices3;;
                ar )
                    AR=ar3;;
                gl )
                    GL=gl3;;
                tmc )
                    TMC=tmc3;;
            esac
        done
    fi
	if [ $TARGETDB = "cmtest2" ];then
        for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm2;;
                context )
                    CONTEXT=cmcontext2;;
                cmaddress )
                    CMADDRESS=cmaddress2;;
                services )
                    SERVICES=cmservices2;;
                ar )
                    AR=ar2;;
                gl )
                    GL=gl2;;
                tmc )
                    TMC=tmc2;;
            esac
        done
    fi
	if [ $TARGETDB = "cmtest1" ];then
        for INSTANCE in $SOURCE_INSTANCES;do
            case $INSTANCE in
                cm )
                    CM=cm1;;
                context )
                    CONTEXT=cmcontext1;;
                cmaddress )
                    CMADDRESS=cmaddress1;;
                services )
                    SERVICES=cmservices1;;
                ar )
                    AR=ar1;;
                gl )
                    GL=gl1;;
                tmc )
                    TMC=tmc1;;
            esac
        done
    fi

	TARGET_INSTANCES="$CM $CONTEXT $CMADDRESS $SERVICES $AR $GL $TMC"
}

function PrepRestoreFromFile(){
	cd $RESTOREDIR
	for INSTANCE in $SOURCE_INSTANCES; do
		echo
		echo "NOTICE: Working with $INSTANCE..."
		echo
		sleep 2
		rm $INSTANCE.lk
		cp -f $PFDIR/$INSTANCE.st $RESTOREDIR
		prostrct repair $RESTOREDIR/$INSTANCE
		echo
		echo "NOTICE: Please review data change"
		echo
		sleep 1
		prostrct list $RESTOREDIR/$INSTANCE
		sleep 3

		#Make sure the PFs stay the same	
		testPfFiles

		echo "WARNING: Disabling replication"
		echo
		sleep 3
	
		echo "NOTICE: Running... rfutil $RESTOREDIR/$INSTANCE -C aiarchiver disable"
		echo
		sleep 3
		rfutil $RESTOREDIR/$INSTANCE -C aiarchiver disable
		
		echo "NOTICE: Running... proutil $RESTOREDIR/$INSTANCE -C DisableSiteReplication source"
		echo
		sleep 3
		echo y | proutil $RESTOREDIR/$INSTANCE -C DisableSiteReplication source
		
		echo "NOTICE: Running... rfutil $RESTOREDIR/$INSTANCE -C aimage end"
		echo
		sleep 3
		rfutil $RESTOREDIR/$INSTANCE -C aimage end
	done
}

function doRestore(){
	echo
	echo "NOTICE: Restoring Database"
	echo 
	sleep 2
	prepTarget # LOAD NECESSARY VARS 
	for INSTANCE in $SOURCE_INSTANCES; do
        case $INSTANCE in
            cm )
	            echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$CM >$DATADIR/restore.dat;;
			context )
                echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$CONTEXT >>$DATADIR/restore.dat;;
            cmaddress )
	            echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$CMADDRESS >>$DATADIR/restore.dat;;
			services )
				echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$SERVICES >>$DATADIR/restore.dat;;
            ar )
	            echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$AR >>$DATADIR/restore.dat;;
			gl )
				echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$GL >>$DATADIR/restore.dat;;
            tmc )
                echo y | procopy $RESTOREDIR/$INSTANCE /dbs/$TARGETDB/$TMC >>$DATADIR/restore.dat;;
        esac
	done
	echo 
	echo "NOTICE: Copy is done, Starting the target database now..."
	echo 
	sleep 3
	$TARGETDB -s
}

function fetchRestoreDir(){
	if [ $DEBUGMODE = true ]; then
		mount.cifs //skpcnypfap01.nypost/Technology/DTI_BACKUP /root/share -o username="srv_dti",password="B@ckup1211",dom="nypost"
		echo "Debug mode enabled. Running interactively..."
		echo "Using //skpcnypfap01.nypost/Technology/DTI_BACKUP as a source path..."
		LOOPME=true
		while true; do	
			read -p "Please type the filename (q to abort):" file
			case $file in
				[PROD_Progress_]* ) echo Using $file; PROBACKUPFILE=$file; \
									[ ! -f /root/share/$PROBACKUPFILE ] && echo "ERROR: File not found!" && cleanUp && exit; \
									echo "Copying file from archive..."; \
									echo "rsync -ah --progress /root/share/$PROBACKUPFILE $WORKINGDIR/"; \
									rsync -ah --progress /root/share/$PROBACKUPFILE $WORKINGDIR/; \
									cd $WORKINGDIR; tar xvjf $PROBACKUPFILE; rm -f $PROBACKUPFILE; \
									mv $WORKINGDIR/dbs/db_backup/cm $WORKINGDIR/; LOOPME=false;;
				[q]* ) cleanUp; exit;;		
				* ) echo "Filename must match a file from the DTI_BACKUP directory in production."; \
				break;;
			esac
			[ $LOOPME ] && break;
		done
		cleanUp	
	else
		if [ -d $RESTOREDIR ] && [ -f $WORKINGDIR/$PRODBACKUPFILE ];then
			echo 
			echo "WARNING: Found archived file on working dir, deleting old restore directory and restoring the DB from it..."
			sleep 3
			rm -rf $RESTOREDIR
			cd $WORKINGDIR; tar xvjf $PROBACKUPFILE; rm -f $PROBACKUPFILE
		fi	
		if [ -d $RESTOREDIR ];then
			echo
			echo "NOTICE: Data is already unwrapped"
			echo
			sleep 3 
		else
			cd $WORKINGDIR
			echo "NOTICE: Grabbing from archive..."
			sleep 3
			#scp -r -i $KEY root@$PRODSERVER:$BACKUP_HOME/cm $WORKINGDIR
			rsync -av /archives/DTI_BACKUP/`date +%a --date="yesterday" | tr 'a-z' 'A-Z'`/cm $WORKINGDIR
			if [ -d $RESTOREDIR ];then
				echo "NOTICE: Data files have been unwrapped and now are ready to be used."
				sleep 3	
			fi
		fi
	fi
}

function deleteImageFiles(){
	NOIMAGEMESSAGE="Database contains no ai areas. (6954)"

    for INSTANCE in $SOURCE_INSTANCES; do
        prostrct remove $RESTOREDIR/$INSTANCE ai |
        while read LINE; do
            if [ "$LINE" != "$NOIMAGEMESSAGE" ];then
                prostrct remove $RESTOREDIR/$INSTANCE ai
            fi
            if [ "$LINE" = "$NOIMAGEMESSAGE" ];then
                continue
            fi
        done
    done
}

function testPfFiles(){
	echo
	echo "NOTICE: Testing PF files..."
	echo
	sleep 3 
	if [ -d $RESTOREDIR ];then
		COUNT=`diff $RESTOREDIR/$INSTANCE.st $PFDIR/$INSTANCE.st | wc -l`
		if [ $COUNT -ne 0 ];then
			echo "ERROR: $INSTANCE PF file is corrupted... Exiting."
			echo "Exit 420"
			exit 420
		fi
	else
		echo "ERROR: Restore directory not found... Exiting."
		exit 431
	fi
}

function doCleanUpAndVerification(){
	echo 
	echo "NOTICE: Checking restore logs and doing house keeping..."
	echo
	sleep 3
	if [ -d $RESTOREDIR ];then
		COPYVER=$(cat $DATADIR/restore.dat | grep "blocks copied" | wc -l)
		if [ $COPYVER -eq 7 ];then
			echo 
			echo "NOTICE: The database has been restored properly"
			# Lets append the copy data to the main log
		    if [ -s $DATADIR/restore.dat ]; then
		        echo
        		echo "NOTICE: Appending copied results..."
		        echo 
        		cat $DATADIR/restore.dat >> $MAINLOG
		    fi
			echo "NOTICE: Removing restore directory"
			echo
			sleep 3
	        rm -rf $RESTOREDIR
			rm -rf $RESTOREDIR/dbs
			if [ -f $WORKINGDIR/$PRODBACKUPFILE ];then
				echo "NOTICE: Removing archive file"
                sleep 3	
			#	rm -f $WORKINGDIR/$PRODBACKUPFILE
			fi
            if [ -f $WORKINGDIR/$LIVEDBFILE ];then
                echo "NOTICE: Removing live archive file"
                sleep 3
                rm -f $WORKINGDIR/$LIVEDBFILE
            fi
		else
			echo "ERROR: The databases did not get restored properly, please review the logs and delete\n
						 the fetched data for the next run"
			exit 410
		fi
	fi
}

function cleanUp(){
	umount /root/share
}

############################
# RUNTIME STARTS HERE
. /pbs/config/cm
echo "============NEW PROCESS STARTED ON: $(date +%D-%T)============"
DEBUGMODE=false
if [ ! -z $2 ] && [ $2 = manual ];then
	DEBUGMODE=true
fi
fetchRestoreDir
PrepRestoreFromFile
lockTarget
deleteImageFiles
doRestore
doCleanUpAndVerification
echo "============PROCESS ENDED ON: $(date +%D-%T)============"
echo "============APPENDING COPY RESULTS============"

