# Apache Magento Virtual Host

Virtual host for Apache, with special configuration to use with a Magento site.

Usage:

In a default Apache 2 installation:
- Add a text file with the content (replacing with the specific values) in /etc/apache2/sites-available/.
- Enable the new site using the vhost file name: `sudo a2ensite site.local`
- Activate the new site without restarting the server: `sudo service apache2 reload`
- Add the new domain to the hosts file: /etc/hosts
