#!/bin/ksh
###############################################################
# FILE: jailprocess
# DATE: June 1st 15
# DESC: This script will go through each of the folders inside the jail home and process
# 		either the ftpto or the ftpfrom functions, by encrypting, decrypting and sftp the files
#		to their destination.
#
#		Each of the files are tracked under the DONE folders for each site. The sites are located
#		under /opt/pgp/{sitename}, and the jail directories are under /jail/pgp/{username}.
#
###############################################################
# CHANGES:
#	**Refactored the code for better readable lines. @@Luis L. june 23@@
#
# TODO:
#	**Separate each functionality into its own function container. @@Luis L.@@
#
########################
# Vars
LOG=/tmp/jailprocess.log

###############################################################


ps -ef | grep "\/jail\/pgp\/jailprocess" | grep -v grep | grep -v $$ | grep -v "vi " > $LOG 2>&1
logger -t jailprocess -p local0.notice -f ${LOG}
retorno=$?
if [ "${retorno}" -ne 0 ]; then
    logger -t jailprocess -p local0.notice "jailprocess already running..."
    exit 1
fi

for i in `find /jail/pgp -type f | egrep -v "\.ssh|\.sh_history|\.profile|\/upload|\/done|pgp\.log"`; do
    site=`echo ${i} | cut -d'/' -f4`
    logger -t ${site} -p local0.notice "********************************************************************************"
    logger -t ${site} -p local0.notice "START PROCESSING ${i}"
    logger -t ${site} -p local0.notice "********************************************************************************"
    process=`echo ${i} | cut -d'/' -f5`
    DIRNAM=`dirname ${i}`
    BASENAME=`basename ${i}`
    echo ${BASENAME} | grep "\." > /dev/null 2>&1
    retorno=$?
    if [ "${retorno}" -eq 0 ]; then
    	file=`echo ${BASENAME} | tr '\.' '\/' | xargs dirname | tr '\/' '.' `
    	ext=`echo ${BASENAME} | tr '\.' '\/' | xargs basename`
    else
    	file=${BASENAME}
    	ext=""
    fi
    retorno=0
    trys=0
    while [ "${retorno}" -eq 0 ] || [ "${trys}" -gt 30 ]; do
    	lsof ${i} > $LOG 2>&1
        retorno=$?
        logger -t ${site} -p local0.notice "lsof ${i} > $LOG 2>&1 / retorno=${retorno}"
        logger -t ${site} -p local0.notice -f ${LOG}
        trys=`expr ${trys} + 1`
        sleep 2
    done
    echo "file=${i};site=${site};process=${process};DIRNAM=${DIRNAM};BASENAME=${BASENAME};file=${file};ext=${ext}" > $LOG 2>&1
    logger -t ${site} -p local0.notice -f ${LOG}
    rm /tmp/${site}_${process}.$$ > /dev/null 2>&1
    touch /tmp/${site}_${process}.$$

	case ${process} in
		'ftpto' ) logger -t ${site} -p local0.notice  "mv ${i} /opt/pgp/${site}/"
			mv ${i} /opt/pgp/${site}/   > /tmp/${site}_${process}.$$ 2>&1
			#cp ${i} /opt/pgp/${site}/   > /tmp/${site}_${process}.$$ 2>&1
			RC=$?
			logger -t ${site} -p local0.notice  "RC=${RC}"
			logger -t ${site} -p local0.notice -f /tmp/${site}_${process}.$$
			if [ "${RC}" -eq 0 ]; then
				logger -t ${site} -p local0.notice  "chown \"pgpuser:pgpuser\" /opt/pgp/${site}/${BASENAME}"
				chown "pgpuser:pgpuser" /opt/pgp/${site}/${BASENAME}  > /tmp/${site}_${process}.$$ 2>&1
				RC=$?
				logger -t ${site} -p local0.notice  "RC=${RC}"
				logger -t ${site} -p local0.notice -f /tmp/${site}_${process}.$$
				if [ "${RC}" -eq 0 ]; then
					logger -t ${site} -p local0.notice  "su - pgpuser -c \"/opt/pgp/pgp.script ${site} encrypt ${BASENAME} pgp\""
					su - pgpuser -c "/opt/pgp/pgp.script ${site} encrypt ${BASENAME} pgp"  > /tmp/${site}_${process}.$$  2>&1
					RC=$?
					logger -t ${site} -p local0.notice  "RC=${RC}"
					logger -t ${site} -p local0.notice -f /tmp/${site}_${process}.$$
					if [ "${RC}" -eq 0 ]; then
						logger -t ${site} -p local0.notice  "su - pgpuser -c \"/opt/pgp/pgp.script ${site} ${process} ${BASENAME} pgp\""
						su - pgpuser -c "/opt/pgp/pgp.script ${site} ftpto ${BASENAME} pgp"  > /tmp/${site}_${process}.$$  2>&1
						RC=$?
						logger -t ${site} -p local0.notice  "RC=${RC}"
						logger -t ${site} -p local0.notice -f /tmp/${site}_${process}.$$
					fi
				fi
			fi
			rm /jail/pgp/${site}/done/${process}_${BASENAME} 2> /dev/null
			touch /jail/pgp/${site}/done/${process}_${BASENAME}
			rm ${i}
		;;

		'ftpfrom' )  logger -t ${site} -p local0.notice "su - pgpuser -c \"/opt/pgp/pgp.script ${site} dirin ${BASENAME} pgp\" "
			su - pgpuser -c "/opt/pgp/pgp.script ${site} dirin ${BASENAME} pgp"  > /tmp/${site}_dirin.$$ 2>&1
			egrep -v "^1=|^##|^RC=" /tmp/${site}_dirin.$$ > /opt/pgp/${site}/listing.dirin.${BASENAME}
			LC=`wc -l /opt/pgp/${site}/listing.dirin.${BASENAME} | awk '{print $1}'`
			logger -t ${site} -p local0.notice  "LC=${LC}"
			logger -t ${site} -p local0.notice -f /opt/pgp/${site}/listing.dirin.${BASENAME}
			grep -v "^#"  /tmp/${site}_dirin.$$ > /tmp/${site}_upload.$$ ${site}_pertinet.$$
			logger -t ${site} -p local0.notice -f /tmp/${site}_pertinet.$$
			if [ "${LC}" -ne 0 ]; then
				for k in `cat /opt/pgp/${site}/listing.dirin.${BASENAME}`; do
					BASENAME=`echo ${k} | sed 's/\.pgp$//'`
					logger -t ${site} -p local0.notice "su - pgpuser -c \"/opt/pgp/pgp.script ${site} ${process} ${BASENAME} pgp\""
					su - pgpuser -c "/opt/pgp/pgp.script ${site} ftpfrom ${BASENAME} pgp"  > /tmp/${site}_ftpfrom.$$ 2>&1
					grep -v "^#"  /tmp/${site}_ftpfrom.$$ > /tmp/${site}_upload.$$ ${site}_pertinet.$$
					logger -t ${site} -p local0.notice -f /tmp/${site}_pertinet.$$
					logger -t ${site} -p local0.notice -f /opt/pgp/${site}/listing.ftpfrom.${BASENAME}
					logger -t ${site} -p local0.notice -f /tmp/${site}_${process}.$$
					if [ "${LC}" -ne 0 ]; then
						logger -t ${site} -p local0.notice "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
						logger -t ${site} -p local0.notice "START decrypting ${k}"
						logger -t ${site} -p local0.notice "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
						logger -t ${site} -p local0.notice "su - pgpuser -c \"/opt/pgp/pgp.script ${site} decrypt ${BASENAME} pgp\""
						su - pgpuser -c "/opt/pgp/pgp.script ${site} decrypt ${BASENAME} pgp" > /tmp/${site}_decrypt.$$ 2>&1
						RC=$?
						rm /opt/pgp/${site}/${BASENAME}.pgp
						logger -t ${site} -p local0.notice  "RC=${RC}"
						logger -t ${site} -p local0.notice -f /tmp/${site}_decrypt.$$
						if [ "${RC}" -eq 0 ]; then
							if [ -f "/opt/pgp/${site}/${BASENAME}" ]; then
								echo ${i} | cut -d'/' -f6 | tr '#' '/' > /opt/pgp/${site}/whereto
								chmod 777 /opt/pgp/${site}/whereto
								logger -t ${site} -p local0.notice "su - pgpuser -c \"/opt/pgp/pgp.script ${site} upload ${BASENAME} pgp\""
								su - pgpuser -c "/opt/pgp/pgp.script ${site} upload ${BASENAME} pgp"  > /tmp/${site}_upload.$$ 2>&1
								RC=$?
								logger -t ${site} -p local0.notice  "RC=${RC}"
								grep -v "^#"  /tmp/${site}_upload.$$ > /tmp/${site}_upload.$$ ${site}_pertinet.$$
								logger -t ${site} -p local0.notice -f /tmp/${site}_pertinet.$$
							else
								logger -t ${site} -p local0.notice  "file: /opt/pgp/${site}/${BASENAME} not found"
							fi
						fi
						logger -t ${site} -p local0.notice "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
						logger -t ${site} -p local0.notice "END decrypting ${k}"
						logger -t ${site} -p local0.notice "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
					fi
				done
			fi
			echo "rm ${i}"
			cp ${i}  /jail/pgp/${site}/done/${process}_${BASENAME}
			rm ${i}
		;;
	esac
    logger -t ${site} -p local0.notice "********************************************************************************"
    logger -t ${site} -p local0.notice "END PROCESSING ${i}"
    logger -t ${site} -p local0.notice "********************************************************************************"
    tail -100 /jail/pgp/${site}/log/pgp.log > /jail/pgp/${site}/log/last_pgp.log
done
