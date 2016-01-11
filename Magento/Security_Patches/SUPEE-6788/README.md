Summa Review Backward Compatibility for patch SUPEE-6788
=================

This scripts allows to identify problematic points when applying SUPEE-6788 patch.

Installation:
=================

In order to install the script, please download it and place in the shell folder under:
```bash
[Magento Root Folder]/shell/
```


Running script:
=================

### Checking admin routes:
In order to check there is no admin route wrong definition, you should run the following command:
```bash
php shell/review6788.php --check routes
```

### Checking queries:
In order to check there is no problematic query, you should run the following command:
```bash
php shell/review6788.php --check queries
```

### Checking content:
In order to check there is no problematic static block, CMS page or email template, you should run the following command:
```bash
php shell/review6788.php --check content
```
And in order solve the permission content issues that you may found, you can generate a SQL dump adding the --dump option:
```bash
php shell/review6788.php --check content --dump
```


***IMPORTANT NOTE: THIS SHELL SCRIPT IS NOT BULLET PROOF. PLEASE DO A MANUAL REVIEW AND A THOROUGH TEST TO MAKE SURE NOTHING IS BROKEN***