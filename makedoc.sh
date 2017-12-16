#!/bin/bash
# -------------------------------------------------------------------
# - NAME:        makedoc.sh
# - AUTHOR:      Reto Stauffer
# - DATE:        2015-08-04
# -------------------------------------------------------------------
# - DESCRIPTION: Simple to use 'create doxygen documentation' of the
#                python package in here.
# -------------------------------------------------------------------
# - EDITORIAL:   2015-08-04, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2017-06-16 09:29 on thinkreto
# -------------------------------------------------------------------


set -u

BASEDIR=`pwd`
CONFIG="doxygen.config"
OUTDIR=`grep --regexp "^OUTPUT_DIRECTORY[[:blank:]]\{1,\}=[[:blank:]]\{1,\}" doxygen.config | awk '{print $3}'`

# - Create documentation
doxygen $CONFIG
if [ $? -ne 0 ] ; then
   echo "There was a problem creating the documentation"
fi

# - Texit
(cd $OUTDIR/latex && make && cd $BASEDIR )
