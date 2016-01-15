#Autodeploy Script

This script is for deploying git projects.

Parameters:
* **[/path/to/magento/root]**: The path to the magento root directory
* **[clear-cache]** (optional): If this parameter is present, will try to call /path/to/magento/root/shell/cacheClean.php script for cache cleaning.

Usage:
```bash
/path/to/autodeploy.sh /path/to/magento/root [clear-cache]
```