#Backup Media Script

This script does Magento media backups in two ways:
* Complete (if there is no other backup for the current month)
* Incremental (if there is another backup for the current month)

It also send an email notification with the result of the process (requires `mail` command to be working).

Usage (as a system cronjob):
```sh
# Magento images backup
0 3 * * 0 /path/to/backup_media.sh
```

