#!/bin/bash

cd $1;

find . -type f -iname '*.jpg' | while read i
do
     echo "Compressing $i to $2 percent";
     jpegoptim -f --strip-all -m$2 "$i";
done