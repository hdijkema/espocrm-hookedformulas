#!/bin/bash
# vi: set sw=4 ts=4:

INSTALL_PREFIX=~/crm
INSTALL_FILES="$INSTALL_PREFIX/data/installed"

function usage()
{
    echo ""
    echo "usage: $0 <buildext|cleanup|install|uninstall> <version> <extension-filename>  <extension-name> <description> <module-dir>"
    echo ""
    echo "  buildext - builds the EspoCRM extension in the 'build' directory."
    echo "  cleanup - removes the 'build' directory."
    echo "  install - installs the files of this extension into the EspoCRM tree at '$INSTALL_PREFIX'"
    echo "  uninstall - uninstalls the files of this extension from '$INSTALL_PREFIX', if , '$INSTALL_FILES/<module-dir' exists"
    echo ""
    exit 1;
}

if [ "$#" -ne 6 ]; then
	usage
fi

CMD=$1;
VERSION=$2
EXT=$3
NAME=$4
DESCRIPTION=$5
MODULE=$6

CDIR=`pwd`
BUILD_DIR="$CDIR/build"

DIRS="application client custom api"
TARDIRS=""
for D in $DIRS
do
   if [ -d $D ]; then
      TARDIRS="$TARDIRS $D"
   fi
done


if [ "$CMD" == "install" ]; then
    mkdir -p $INSTALL_FILES
	tar cf - $TARDIRS | (cd $INSTALL_PREFIX; tar hxvf - ) >$INSTALL_FILES/$MODULE
    FILES=`cat $INSTALL_FILES/$MODULE`
    for F in $FILES
    do
       F="$INSTALL_PREFIX/$F"
       if [ -f "$F" ]; then
          echo "installed $F"
       fi
    done
    echo "$MODULE installed, installed files in $INSTALL_FILES/$MODULE"
elif [ "$CMD" == "uninstall" ] ; then
    if [ -r $INSTALL_FILES/$MODULE ]; then
       FILES=`cat $INSTALL_FILES/$MODULE`
       for F in $FILES
       do
          F="$INSTALL_PREFIX/$F"
          if [ -f "$F" ]; then
            echo "removing $F"
            rm $F;
          fi
       done
    else 
       echo "Cannot uninstall, $INSTALL_FILES/$MODULE does not exist"
    fi
elif [ "$CMD" == "cleanup" ]; then
    rm -rf $BUILD_DIR
elif [ "$CMD" == "buildext" ]; then
	DIR=/tmp/$EXT
	rm -rf $DIR
	mkdir $DIR

	MANIFEST=$DIR/manifest.json
	DT=`date +%Y-%m-%d`
	
	echo "{" 											>$MANIFEST
	echo "  \"name\": \"$NAME\", " 						>>$MANIFEST
	echo "  \"version\": \"$VERSION\", " 				>>$MANIFEST
	echo "  \"acceptableVersions\": [ \">=5.8.5\" ], "	>>$MANIFEST
	echo "  \"php\": [ \">=7.0.0\" ], " 				>>$MANIFEST
	echo "  \"releaseDate\": \"$DT\", " 				>>$MANIFEST
	echo "  \"author\": \"Hans Dijkema\", "				>>$MANIFEST
	echo "  \"description\": \"$DESCRIPTION\""			>>$MANIFEST
	echo "}"											>>$MANIFEST

	mkdir $DIR/files
    if [ -d scripts ]; then
        TARDIRS="$TARDIRS scripts"
    fi
	tar cf - $TARDIRS | (cd $DIR/files; tar xf - )

	mkdir $DIR/scripts

	F=$DIR/scripts/BeforeInstall.php
	echo "<?php"									>$F
    echo "class BeforeInstall"						>>$F
	echo "{"										>>$F
	echo "  public function run($container) {"		>>$F
	echo "  }" 										>>$F
	echo "}"										>>$F
	echo "?>"										>>$F

	F=$DIR/scripts/AfterInstall.php
	echo "<?php"									>$F
    echo "class AfterInstall"						>>$F
	echo "{"										>>$F
	echo "  public function run($container) {}" 	>>$F
	echo "}"										>>$F
	echo "?>"										>>$F

	F=$DIR/scripts/BeforeUninstall.php
	echo "<?php"									>$F
    echo "class BeforeUninstall"					>>$F
	echo "{"										>>$F
	echo "  public function run($container) {}" 	>>$F
	echo "}"										>>$F
	echo "?>"										>>$F

	F=$DIR/scripts/AfterUninstall.php
	echo "<?php"									>$F
    echo "class AfterUninstall"						>>$F
	echo "{"										>>$F
	echo "  public function run($container) {}" 	>>$F
	echo "}"										>>$F
	echo "?>"										>>$F

    if [ -d $DIR/files/scripts ]; then
        (cd $DIR/files; tar cf - scripts) | (cd $DIR; tar hxvf - )
        rm -rf $DIR/files/scripts
    fi

	EXTENSION="espocrm-$EXT-$VERSION.zip"
	EXT_FILE="$BUILD_DIR/$EXTENSION"

	mkdir -p $BUILD_DIR

    echo "creating extension in $EXT_FILE"
    rm -f $EXT_FILE
	(cd $DIR;zip -r $EXT_FILE .)

	echo "$EXTENSION created in directory $BUILD_DIR"
else 
    echo ""
    echo "Command '$CMD' given, but it is not a supported command"
    usage
fi

