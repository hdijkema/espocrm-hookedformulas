#!/bin/bash
# vi: set sw=4 ts=4:

CMD=$1;
VERSION="0.1.0"
EXT="hookedformulas-extension"
NAME="Hooked Formulas - more power to your formulas"
DESCRIPTION="Implements formulas for all hooks, including afterRelate, etc. and a bunch of new functions."
MODULE=HookedFormulas

./build-ext.sh "$CMD" "$VERSION" "$EXT" "$NAME" "$DESCRIPTION" "$MODULE"

