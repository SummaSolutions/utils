#!/bin/bash

projPath=$1;
clearCache=$2;
shellPath="shell/";
cacheCleanScript="cacheClean.php";
date=`date;`;

if [ -n "$projPath" ]; then #$projPath should be set
    if [ -d $projPath ]; then #$projPath should be a valid directory
        cd $projPath; #set context to project directory
        if [ `git rev-parse --is-inside-work-tree` == "true" ]; then  #if $projPath is inside a git repository
            head1=`git rev-parse HEAD;`; #save repository head
            pullMessage=`git pull;`;
            head2=`git rev-parse HEAD;`; #save repository new head
            if [ $head1 != $head2 ]; then #if heads are different, then we have an update (we should delete cache)
                if [ "$clearCache" == "clear-cache" ]; then
                    if [ -d $shellPath ]; then #$shellPath should be a directory
                        cd $shellPath; #set context into magento var directory
                        if [ -f $cacheCleanScript ]; then
                            cleanCacheMessage=`php $cacheCleanScript;`;
                            echo "$date - cache cleared";
                        else
                            echo "$date - cache not cleared, $cacheCleanScript file not found on Magento shell directory";
                        fi
                    else
                        echo "$date - $shellPath directory not found on $projPath, please check $projPath is Magento root path";
                    fi
                fi
                echo "$date - deploy done";
            fi
        else
            echo "$date - error: $projPath not a git repository";
        fi
    else
        echo "$date - error: $projPath directory doesn't exists";
    fi
else
    echo "$date - usage: autodeploy.sh MAGENTO_ROOT_DIRECTORY [clear-cache]";
fi