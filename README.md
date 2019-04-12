# Magento 2 In-Store Pickup Extension by Cybage

## Requirements
  * Magento Community Edition 2.1.x-2.2.x
  * Site should have openssl certificate and secure url to auto-detect location via geoip.

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require cybage/storepickup:1.0.0

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](https://github.com/)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Cybage/StorePickup
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@cybage.com)
then if you still need help, open a bug report in GitHub's
[issue tracker](https://github.com/cybage/module-instorepickup/issues).

Please do not use Magento Marketplace Reviews or (especially) the Q&A for support.
There isn't a way for us to reply to reviews and the Q&A moderation is very slow.

## Need More Features?
Please contact us to get a quote
https://www.cybage.com

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).

## Other Cybage Extensions That Can Be Installed Via Composer
  *
