# pos_distribution
PoS Drupal distribution

## Warning: This distribution installs the PoS site in English , Deutsch and French, this is to make the installation process a bit faster. If you need any other language you can enable it after the installation ends.

# How to install it

* Download the zip files
* Unzip the files in your Apache web folder (/www)
* Open a browser an access to the new site (preferably by domain) and follow the Drupal wizard installation

# Knowed errors:

## 1. certificate

Is possible that in the installation process the installation fails do the fact that your environment doesn’t validate correctly some certificate.

cURL error 60: SSL certificate problem: unable to get local issuer certificate (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
Couldn't communicate with the H5P Hub. Please try again later.

To solve this, take a look at this issue:
https://github.com/yabacon/paystack-php/wiki/cURL-error-60:-SSL-certificate-problem:-unable-to-get-local-issuer-certificate-(see-http:--curl.haxx.se-libcurl-c-libcurl-errors.html)


To resolve the error, you need to define your CURL certificate authority information path

To do that:

* Download the latest curl recognized certificates here: https://curl.haxx.se/ca/cacert.pem
* Save the cacert.pem file in a reachable destination.
* Then, in your php.ini file, scroll down to where you find [curl].
    You should see the CURLOPT_CAINFO option commented out. Uncomment and point it to the cacert.pem file. You should have a line like this:
    * curl.cainfo = “certificate path\cacert.pem”
    * Save and close your php.ini. Restart your webserver and try your request again.

 ## 2. Access by domain

Due to an issue with the business rules module, module used in PoS, is important that you access by domain in your site to be able to install the Driver distribution correctly.

If you are working in your local machine and it is windows you must edit you hosts files an add something like this:

```
127.0.0.1 yoursite.local.eu
```

Then you must add a virtual host in your apache (in the file httpd.conf), it can be something like this:

```
<VirtualHost *:80>   
    DocumentRoot "path_to_your_drupal_site\web"   
    ServerName yoursite.local.eu   
    ServerAlias yoursite.local.eu
    <Directory "path_to_your_drupal_site web\">
        Options +Indexes +Includes +FollowSymLinks +MultiViews
        AllowOverride All
        Require local     
    </Directory> 
</VirtualHost>
```




