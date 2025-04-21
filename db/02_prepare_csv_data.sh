#!/bin/bash -ex
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
unzip -d $SCRIPT_DIR/init_data -o $SCRIPT_DIR/init_data/snapshot.zip