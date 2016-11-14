#!/usr/bin/bash
##################################
# Created by: Luis L.
# Date: 5/20/09
# File: createFolders.sh

##################################
# Variables
WORKPATH="/export/home/photo"
LOG=/var/log/folderMonitor.log
FOLDER1=abaca
FOLDER2=acepix
FOLDER3=bancroft
FOLDER4=bauer-griffin
FOLDER5=bloomberg
FOLDER6=brim
FOLDER7=buzzfoto
FOLDER8=celebrityvibe
FOLDER9=corbis
FOLDER10=everett
FOLDER11=fame
FOLDER12=filmmagic
FOLDER13=finalpixx
FOLDER14=fmicelotta
FOLDER15=freelance
FOLDER16=gamma
FOLDER17=globe
FOLDER18=inf
FOLDER19=inhouse
FOLDER20=landov
FOLDER21=londonfeat
FOLDER22=ltnews
FOLDER23=mavrix
FOLDER24=mbpictures
FOLDER25=mstorms
FOLDER26=nationalphoto
FOLDER27=pcn
FOLDER28=photoftp
FOLDER29=photolink
FOLDER30=photopass
FOLDER31=pmcmullan
FOLDER32=polaris
FOLDER33=pphotos
FOLDER34=ramey
FOLDER35=retna
FOLDER36=rexfeat
FOLDER37=sipa
FOLDER38=splash
FOLDER39=sportsphotos
FOLDER40=ssands
FOLDER41=starmax
FOLDER42=startraks
FOLDER43=tabcity
FOLDER44=upi
FOLDER45=wenn
FOLDER46=wpn
FOLDER47=x17
FOLDER48=zuma

for ((a=1; a <= 48 ; a++))
do
  eval "n=\$FOLDER$a"
  if [ ! -d $WORKPATH/$n ]; then
    mkdir $WORKPATH/$n
    chmod 770 $WORKPATH/$n
    chown $n:photoadmin $WORKPATH/$n
    echo "Created $n on `date`" >> $LOG

 # A little fix to custom folders
    chown root:freelance $WORKPATH/$FOLDER15
    chown root:itadmin   $WORKPATH/$FOLDER22
    chown root:photoadmin $WORKPATH/$FOLDER30
    chown root:photoadmin $WORKPATH/$FOLDER16
    chown root:photoadmin $WORKPATH/$FOLDER5

 fi
done
