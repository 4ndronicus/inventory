#!/bin/env bash
# Grab a datestamp so we can name our file
DATESTAMP=`date +%Y-%m-%d`

POSTSERVER="http://[ADD YOUR WEB SERVER HERE]/maint.php?"

# ConnectionTimeout directive for the SSH connection commands
CONNTO=3

#
# Grabs the data for a host, puts that data into the output file
#
function getdatafor(){

   HOST=$1
   POSTSERVER=$2
   NOTE=""

   printf "\n\n#### IP ADDRESS: ${HOST} ####\n"

   printf "Checking whether host is alive: "
   ping -c 1 -w 5 ${HOST} > /dev/null 2>&1
   RET=$?
#   printf "Ping check status: ${RET}\n"
   if [[ $RET == 0 ]] ; then
     printf "Host is up.\n"
     ALIVE="UP"
   else
     printf "Host is not responding.\n"
     ALIVE="DOWN"
   fi

   printf "Determining OS: "
   OS=`ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=${CONNTO} ${HOST} cat /etc/redhat-release 2>&1` > /dev/null 2>&1
   OSRET=$?
   if [[ $OSRET == 0 ]] ; then
     ALIVE="UP"
   fi
#   printf "Last status returned: $OSRET\n"
#   printf "Command output: ${OS}\n"
   
   if [[ $OS == *"man-in-the-middle"* ]] ; then
    # OS=""
#     printf "Found key fingerprint error, host must be up.\n"
     NOTE="Key fingerprint error"
     ALIVE="UP"
   fi

   if [[ $OS == *"ermission"* && $OS == *"denied"* ]] ; then
#     printf "Found 'Permission denied', host must be up.\n"
     NOTE="Permission denied - check key"
     ALIVE="UP"
    # OS=""
   fi

   SERVERNAME=""
   DNSHOSTNAME=""
   MACADDR=""
   ARPHOST=""
   ARCH=""

   if [[ $OSRET != 0 ]] ; then
     OS=""
   fi
   printf "${OS}\n"

   if [[ $ALIVE == "UP" ]]; then

	   printf "Determining Hostname from Host: "
	   SERVERNAME=`ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=${CONNTO} ${HOST} hostname 2> /dev/null` > /dev/null 2>&1
	   SERVERRET=$?
	   SERVERNAME=`echo ${SERVERNAME} | tr [:upper:] [:lower:]`
	   printf "${SERVERNAME}\n"
	   printf "Last status returned: $SERVERRET\n"
	   
	   printf "Determining Hostname from DNS: "
	   DNSHOSTNAME=`dig -x ${HOST} | awk '/^;; ANSWER SECTION:$/ { getline ; print $5 }' | awk -F "." '{print $1}' 2> /dev/null` > /dev/null 2>&1
	   DNSHOSTNAME=`echo ${DNSHOSTNAME} | tr [:upper:] [:lower:]`
	   printf "${DNSHOSTNAME}\n"

	   printf "Determining MAC Address from host: "
	   MACADDR=`ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=${CONNTO} ${HOST} ip addr | grep -i "state up" -A 1 | tail -n 1 | awk '{print $2}' 2> /dev/null` > /dev/null 2>&1
	   printf "${MACADDR}\n"
	#   printf "Last status returned: $?\n"
	#   printf "Command output: ${MACADDR}\n"

	#   printf "Determining MAC Address from arp table: "
	#   ARPMACADDR=`arp -a | grep -v incomplete | grep ${HOST} | awk '{ print $4 }'`
	#   printf "${ARPMACADDR}\n"

	   printf "Determining Hostname from arp table: "
	   ARPHOST=`arp -a | grep -v incomplete | grep ${HOST} | awk '{ print $1 }'`
	   if [[ $ARPHOST == "?" ]] ; then
	     ARPHOST=""
	   fi
	   printf "${ARPHOST}\n"
	   ARPHOST=`echo ${ARPHOST} | tr [:upper:] [:lower:]`
	  
	   printf "Determining Architecture: "
	   ARCH=`ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=${CONNTO} ${HOST} arch 2> /dev/null` > /dev/null 2>&1
	   printf "${ARCH}\n"
	#   printf "Last status returned: $?\n"
	#   printf "Command output: ${ARCH}\n"
   fi

   POSTURL=`echo "${POSTSERVER}ipaddr=${HOST}&alive=${ALIVE}&hostname=${SERVERNAME}&dnshost=${DNSHOSTNAME}&arphost=${ARPHOST}&os=${OS}&connfrom=${HOSTNAME}&arch=${ARCH}&mac=${MACADDR}&note=${NOTE}"`
   wget -O /dev/null "${POSTURL}"
}

#
# Scan this list of IPs - can place this script on each jump host and
# configure the ip ranges as appropriate for the given jump host.
# the server you're posting this all to must be accessible from
# any server you are running this script on.
#
IPBLOCK=10.20
for i in {30..40} 50 54 55; do
  for j in {1..255}; do
    HOST=${IPBLOCK}.${i}.${j}
    getdatafor ${HOST} ${POSTSERVER}
  done
done
