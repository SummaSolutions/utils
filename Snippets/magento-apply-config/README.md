# Apply Magento Configuration Script

This scripts allows to apply all configs set in a CSV file into a Magento installation using n98-magerun for that.

CSV Format:

Path,Value,Action,Scope,Scope ID
Path | Value | Action | Scope | Scope ID
------------ | ------------- | ------------- | ------------- | -------------
web/unsecure/base_url | http://example.local/ | set | default
web/unsecure/base_url | | delete | websites | 10

There is also an example.csv to check allowed format.


Usage:
```bash
php /path/to/apply-magento-config.php -f=/path/to/settings.csv -n=/path/to/n98-magerun.phar -r=/path/to/magento 
```

