**Icaro Cloud Supervisor & Monitor (ICSM)**

Copyright (C) 2015 DISIT Lab http://www.disit.org - University of Florence

**Dependencies**

- Apache Web Server ver. 2 and url rewrite module on
- MySQL 5.x
- PHP 5.x
- Operating system tested: Linux Ubuntu, Windows and OSx
- Tested also with LAMP/XAMPP,  MAMP or WAMP suite.
- Browser tested: Chrome, Firefox, Safari and IE1x
- The .htaccess in the root folder is required for the url rewrite mechanism

**Installation Guide**
Before start the installation you could need to edit the "config.inc.php" in the "system" folder to set "$baseUrl" and "$baseDir" variables. By default both come with the value "/SM/" and if you define a "SM" folder in your apache server document root (htdocs) this is ready to use. If you are going to change this name or the path, you must edit those variables. The, define MySQL settings for user ($dbUser), password ($dbPwd), host ($dbHost) and schema Name ($dbName).

Execute "<your web server address>/SM/install.php" by typing it on the address bar URL of your browser.
Access to the login page by "<your server host>/SM". Login in the system using admin credentials (user:admin and password: admin are preconfigured as default). Go in the Settings menu and select "Plugins" and install "WebTail", "Dashboard" and "Notificaton".  
Finally in the Settings menu select "Applications" and then click "install" on the displayed boxes.

