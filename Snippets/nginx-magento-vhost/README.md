# Nginx Magento Virtual Host

Virtual host for Nginx, with special configuration to use with a Magento site.

Usage:

In a default Nginx installation:
- Add a text file with the content (replacing with the specific values) in /etc/nginx/sites-available/.
- Enable the new site using the vhost file name: `sudo ln -s /etc/nginx/sites-available/site.local /etc/nginx/sites-enabled/site.local`
- Activate the new site without restarting the server: `sudo service nginx reload`
- Add the new domain to the hosts file: /etc/hosts
