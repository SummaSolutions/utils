#Autodeploy Script

This scripts is for deploying git projects.

Parameters:
* **[clear-cache]**: If this parameter is present, will try to call /path/to/magento/root/shell/cacheClean.php script for cache cleaning.

Usage:
```bash
/path/to/autodeploy.sh /path/to/magento/root [clear-cache]
```