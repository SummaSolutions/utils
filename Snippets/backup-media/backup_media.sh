#!/bin/bash

BACKUP_SOURCE="/mnt/media/"
BACKUP_DESTINATION="/home/admin/backups_media"

LOGS_DIR="$BACKUP_DESTINATION/logs"

NOTIFIER=fcapua@summasolutions.net

FILE_LASTMONTH="$BACKUP_DESTINATION/.lastmonth"
CURRENT_MONTH="`date +%m`"
FILENAME_FULL="`date +%d-%b-%Y`"
FILENAME_INCREMENTAL="`date +%d-%b-%Y-%H%M%S`"
EVENTS="$LOGS_DIR/backup_event_$FILENAME_INCREMENTAL.log"
BACKUP_FIND_OPTIONS="-type f -mtime -7 -type f -not -path "*cache*" -and -not -path "*css*" -and -not -path "*js*" -and -not -path "*.thumbs*" -and -not -path "*captcha*""
# delete files 60 days old
DELETE_FIND_OPTIONS="-type f -mtime +60 -delete"
TAR_OPTIONS="--exclude='.' --exclude='..' --exclude=cache --exclude=.*css --exclude=*.js --exclude=.thumbs --exclude=captcha"

notify() {
	echo "`date`: Server free disk space: $(df -h $PWD | awk '/[0-9]%/{print $(NF-2)}')" >> $2
	mail -s "Media $1 Backup Done `date '+%m-%d-%y-%H'`" $NOTIFIER < $2
}

backup_incremental() {
	FILE=$BACKUP_DESTINATION/$FILENAME_INCREMENTAL.tgz
	LOG=$LOGS_DIR/backup_event_$FILENAME_INCREMENTAL.log
	echo "`date`: Started INCREMENTAL backup." >> $LOG
	find $BACKUP_SOURCE $BACKUP_FIND_OPTIONS | tar czf $FILE -T -
	echo "`date`: Finished the INCREMENTAL backup. File: $FILE ($(du -h $FILE | cut -f -1))" >> $LOG
	notify "INCREMENTAL" $LOG
}

backup_total() {
	FILE=$BACKUP_DESTINATION/$FILENAME_FULL.tgz
	LOG=$LOGS_DIR/backup_event_$FILENAME_FULL.log
	echo "`date`: Started FULL backup." >> $LOG
	tar $TAR_OPTIONS -zcf $FILE $BACKUP_SOURCE
	if [ $? -eq '0' ]; then
		save_lastmonth
		echo "`date`: Finished the FULL backup. File: $FILE ($(du -h $FILE | cut -f -1))" >> $LOG
		notify "FULL" $LOG
	else
		echo "`date`: Finished with ERRORS the FULL backup." >> $LOG
	fi
}

delete_old_files() {
	find "$BACKUP_DESTINATION" -name "*.tgz" $DELETE_FIND_OPTIONS
}

save_lastmonth() {
	echo $CURRENT_MONTH > $FILE_LASTMONTH
}

get_lastmonth() {
	if [ -f $FILE_LASTMONTH ]; then
		echo `cat $FILE_LASTMONTH`
	else
		echo '0';
	fi
}

if [ ! -d $BACKUP_DESTINATION ] ; then
  mkdir $BACKUP_DESTINATION
fi

if [ ! -d $LOGS_DIR ] ; then
  mkdir $LOGS_DIR
fi

last_month=$(get_lastmonth)

if [ $last_month -lt $CURRENT_MONTH ]; then
	backup_total
else
	backup_incremental
fi

delete_old_files
