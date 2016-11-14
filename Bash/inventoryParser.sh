#!/bin/sh
#
###################################
# CreatedBy: Luis L.
# File:      inventoryParser.sh
# Desc:      This file will convert raw data to SQL 
#
#############
# Variables #
#############
if  [ -f overwrite.sh ]; then
	buildHwHome=/Users/siul/toBuild/hw/
	buildSwHome=/Users/siul/toBuild/sw/
else
	buildHwHome=/home/inventory/toBuild/hw/	
	buildSwHome=/home/inventory/toBuild/sw/
	currentBuildDir=/home/inventory/invData/
fi

cleanMysqlData(){
	mysql inventory -usqladmin --password=`cat $HOME/pass` -e "TRUNCATE TABLE hardware"
	mysql inventory -usqladmin --password=`cat $HOME/pass` -e "TRUNCATE TABLE software"
}

fetchRawHWTxt(){
	for txtFiles in ./*.txt; do 
		cat $txtFiles | grep @ | awk -F@ '{ print $1 }' > $buildHwHome$txtFiles
		cat $txtFiles | grep Logged			>>$buildHwHome$txtFiles
	done
}

fetchRawSWTxt(){
	for txtFiles in ./*.txt; do 
		cat $txtFiles | grep % | grep -v "KB" | awk -F% '{ print $1 }' > $buildSwHome$txtFiles
	done
}

importTxtHWData(){
    for txtFiles in $buildHwHome*.txt; do

		USERNAME=`cat $txtFiles | awk 'NR>1{exit};1' | awk -F- '{ print $2}'`
		COMNAME=`cat $txtFiles 	| awk 'NR>1{exit};1'`
		OSNAME=`cat $txtFiles	| awk 'NR>2{exit};2' | sed '1d'   | awk -F\| '{print $1}'`
		SRVPACK=`cat $txtFiles 	| awk 'NR>3{exit};3' | sed '1,2d' | awk '{ print $2}'`
		OSVER=`cat $txtFiles 	| awk 'NR>4{exit};4' | sed '1,3d' | awk '{ print $2}'`
		COMSERIAL=`cat $txtFiles| grep "Computer Serial" 		  | awk '{ print $1}'` 
		COMIP=`cat $txtFiles 	| grep "Computer IP Address" | sed '2,4d'`
		COMRAM=`cat $txtFiles	| awk 'NR>7{exit};7' | sed '1,6d' | awk '{ print $1}'`
		COMCORE=`cat $txtFiles	| awk 'NR>8{exit};8' | sed '1,7d'`
		PROCTYPE=`cat $txtFiles	| awk 'NR>9{exit};9' | sed '1,8d'`
		COMHD=`cat $txtFiles	| awk 'NR>10{exit};10'|sed '1,9d'`
		LOGGEDIN=`cat $txtFiles | grep Logged`
	
		mysql inventory -usqladmin --password=`cat $HOME/pass` -e "INSERT INTO hardware VALUES('','$USERNAME','$COMNAME','$OSNAME','$SRVPACK',
																							   '$OSVER','$COMRAM','$COMCORE','$PROCTYPE','$COMHD',
																							   '$COMSERIAL','$COMIP','$LOGGEDIN',NOW())"
	done
}

convertSwToCSV(){
	cd $buildSwHome
#	COUNT=60
#	for ((a=1; a <= $COUNT; a++)) 
#	do
#   nothing
#	done
	for txtFiles in *; do
		USERNAME=`cat $currentBuildDir$txtFiles | grep Logged | awk -F '\'  '{print $2}'`
		UNIQUEKEY=`cat $buildHwHome$txtFiles | grep "Computer Serial" | awk '{print $1}'`

		cat $txtFiles | awk '{sub(/[^/]*$/,dataInserted)}1' dataInserted="&,$UNIQUEKEY, $USERNAME," > $txtFiles 
	
	done
}

importTxtSWData(){
	cd $buildSwHome
	for txtFiles in ./*.txt; do	
		mysql inventory -usqladmin --password=`cat $HOME/pass` -e "	LOAD DATA LOCAL INFILE '$txtFiles' 
																	INTO TABLE software
																	FIELDS TERMINATED BY ','
																	LINES TERMINATED BY '\n'"
    done
}

##########################
# Runtime Starts here
#
fetchRawHWTxt
fetchRawSWTxt
convertSwToCSV
cleanMysqlData
importTxtHWData
importTxtSWData
