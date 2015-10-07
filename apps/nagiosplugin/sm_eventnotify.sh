#!/bin/sh
# Arguments, 1) Service state; 2) State type; 3) Attempt times; 4) Host Address; 5) Service name; 6) Notifiy server

echo "$1,$2,$3,$4,$5" > /usr/local/shinken/yale.log
case "$1" in
OK)
        ;;
WARNING)
        ;;
UNKNOWN)
        ;;
CRITICAL)
        case "$2" in
        SOFT)
                case "$3" in
                1)
                        curl -X POST -H "Content-Type: application/xml" -d "ciao" http://apiuser:apipassword@localhost/IcaroSM/api/notify/alert/1/1
                        ;;
                esac
                ;;
        HARD)
        		 curl -X POST -H "Content-Type: application/xml" -d "$1,$2,$3,$4,$5" http://apiuser:apipassword@localhost/IcaroSM/api/notify/alert/1/1
                ;;
        esac
        ;;
esac
exit 0