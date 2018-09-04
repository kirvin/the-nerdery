#!/bin/bash
DB_NAME=$1
DB_USERNAME=$2
DB_PASSWORD=$3

docker exec -it the-nerdery_mysql_1 /nerdery/scripts/setup-database.sh
