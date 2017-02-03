#!/bin/sh
##################
# LAST MOD: DEC 12 2013.
# FILE: checkServerStatus.sh
# DESC: Checks filesystem, Mem & processor time, 
#       then it reports any past threshold to syslog >messages. 
################################################################
# Variables
#######################################################
MINPROC=2 #Max load average on first interval output -->[3] 2 2
MINMEM=10 #Minimum available physical memory (in Megs)
MINSWAP=50 #Minimum available swap 
MAXUSEDSPACE=40 #Max percentage used space
DATAFILE=.system.dat
#######################################################

# GENERIC 
function logReport(){ 
    echo __FUNC__
}

function Sleep(){
    sleep 1    
}
function testme(){
    foo=$(echo "10 * 2" | bc)
    bar=2
    if [ $foo -ge 1 ];then 
        echo "$foo nice..."
    fi
    echo ""
    MY=`cat $DATAFILE`
    echo $MY
}        
# END GENERIC

function checkProcessor(){
    echo "NOTICE: Checking Processor"
    Sleep
    PROCTIME=`uptime | awk '{print $8}' | awk -F, '{print $1}' | awk -F. '{print $1}'`
    if [ $PROCTIME -ge $MINPROC ];then 
        logger -s "Error: Processor average above threshold, current Proc value is $PROCTIME"
    fi
}

function checkMemory(){
    echo "NOTICE: Checking Available memory"
    Sleep
    free | grep Mem
}

function checkSwap(){
    echo "NOTICE: Checking used swap percentage"
    Sleep
    free | grep Swap
}

function checkStorage(){
#    echo "NOTICE: Checking storage limits"
    Sleep
    ROOTSPACE=`df | awk '{print $5 $6}' | awk -F% '{print $1}' | head -2 | tail -1`
    if [ $ROOTSPACE -gt $MAXUSEDSPACE ];then 
        echo "Warning: The volume Root has a percentage of $ROOTSPACE%, its above the $MAXUSEDSPACE% threashold"
    fi 
}

######################################################
# RUNTIME STARTS HERE
#testme
#checkProcessor
#checkMemory
#checkSwap
checkStorage
