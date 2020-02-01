#!/bin/bash

####
# Split MySQL dump SQL file into one file per table
# based on https://gist.github.com/jasny/1608062
####

#adjust this to your case:
START="/-- Table structure for table/"
# or 
#START="/DROP TABLE IF EXISTS/"


if [ $# -lt 1 ] || [[ $1 == "--help" ]] || [[ $1 == "-h" ]] ; then
        echo "USAGE: extract all tables:"
        echo " $0 DUMP_FILE"
        echo "extract one table:"
        echo " $0 DUMP_FILE [TABLE]"
        exit
fi

if [ $# -ge 2 ] ; then
        #extract one table $2
        csplit -s -ftable $1 "/-- Table structure for table/" "%-- Table structure for table \`$2\`%" "/-- Table structure for table/" "%40103 SET TIME_ZONE=@OLD_TIME_ZONE%1"
else
        #extract all tables
        csplit -s -ftable $1 "$START" {*}
fi

[ $? -eq 0 ] || exit

mv table00 head

FILE=`ls -1 table* | tail -n 1`
if [ $# -ge 2 ] ; then
        mv $FILE foot
else
        csplit -b '%d' -s -f$FILE $FILE "/40103 SET TIME_ZONE=@OLD_TIME_ZONE/" {*}
        mv ${FILE}1 foot
fi

for FILE in `ls -1 table*`; do
        NAME=`head -n1 $FILE | cut -d$'\x60' -f2`
        cat head $FILE foot > "$NAME.sql"
done

rm head foot table*
