# Latexport - A Latex export plugin
A latex export renderer plugin to export latex documents from Dokuwiki. 

## User documentation
For user documentation, visit the plugin home page:
- https://www.dokuwiki.org/plugin:latexport

# Troubleshooting

## The fearsome 0 bytes font file in Mac OS X
(See www.dmertl.com/blog/?p=11 )
(See https://en.wikipedia.org/wiki/Resource_fork )

If `otfinfo` complains of the file being too small, check from the command line if the file has zero length:

	MacBook-Pro:Fonts me$ otfinfo -i Playbill 
	otfinfo: Playbill: OTF file corrupted (too small)
	MacBook-Pro:Fonts me$ ls -la Playbill 
	-rw-rw-r--@ 1 me       staff  0 Jun 15  2010 Playbill

Zero length is visible only from command line. If you check the size from the Finder 
you see a non-zero size. Also you can tell that the file is not corrupt because you can open it in the Font Book.

For some reason, lots of font files have their content hidden in metadata attributes. You can check if it's your 
case with the `xattr`command. There are two versions. The short one:

	MacBook-Pro:Fonts me$ xattr Playbill 	
	com.apple.FinderInfo
	com.apple.ResourceFork

And the long one:

	MacBook-Pro:Fonts me$ xattr -l Playbill 
	com.apple.FinderInfo:
	00000000  46 46 49 4C 44 4D 4F 56 00 00 04 80 00 01 00 00  |FFILDMOV........|
	00000010  00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00  |................|
	00000020
	com.apple.ResourceFork:
	00000000  00 00 01 00 00 00 B8 2F 00 00 B7 2F 00 00 00 78  |......./.../...x|
	00000010  .. .. .. .. .. .. .. .. .. .. .. .. .. .. .. ..
	00000050              [This is very long]
	0000B880  .. .. .. .. .. .. .. .. .. .. .. .. .. .. .. .. 
	0000B890  0B 03 AE CB 60 08 50 6C 61 79 62 69 6C 6C 08 50  |....`.Playbill.P|
	0000B8A0  6C 61 79 62 69 6C 6C                             |laybill|
	0000b8a7

You can see that `com.apple.ResourceFork` attribute contains the whole data. To extract the data as binary in a 
separated file, use `xattr` in conjunction with `xxd`, as demostrated below. 

	MacBook-Pro:Fonts me$ xattr -px com.apple.ResourceFork Playbill | xxd -r -p > Playbill.ttf	

If everything went right, you should have a second file with non-zero length:

	MacBook-Pro:Fonts me$ ls -la Playbill*
	-rw-rw-r--@ 1 me       staff      0 Jun 15  2010 Playbill
	-rw-r--r--+ 1 me       staff  47271 Sep 17 09:36 Playbill.ttf

Alas, although you can open this file in Font Book, if you _Validate Font_ it shows a _System Validation_ error. Also, 
`otfinfo` returns yet another error:

	MacBook-Pro:Fonts me$ otfinfo -i Playbill.ttf 
	otfinfo: Playbill.ttf: not an OpenType font (bad magic number)

To overcome this problem, I uploaded the TTF file to a online font converter (for example, https://onlinefontconverter.com/ ), 
and converted it into TTF (yes, same). Then:
1. Download the result.
2. Uninstall the original font.
3. Install your converted font. If you processed a font file with multiple variations - like bold, italic - you will probably 
have one file per variation; in that case install them all.
4. Check them with `otfinfo`. 

To me this worked.

# Extending the dokuwiki-plugin-latexport
To develop extension to the plugin you need:

- A development environment with PHP5 or later.
- A development version of dokuwiki and a configured web site.
- PhpUnit installed as a PHAR in the path.
- Checkout the dokuwiki-plugin-latexport in the corresponding plugin folder of dokuwiki.

I'm assuming that you've got a working environment with *Apache* and *PHP* on your development machine. If not, you can check the [wiki](wiki).

## Download development version of dokuwiki

To retrieve the development version of dokuwiki you need to have git installed. Then follow 
instructions in https://www.dokuwiki.org/devel:git

- Go to your development folder, checkout the development version and switch to the stable branch.

```
git clone https://github.com/splitbrain/dokuwiki.git
git checkout stable
```

This should have created a dokuwiki folder with all sources, including a ``_test`` folder with unit tests.

Complete the installation by visiting the ``install.php``:
* http://localhost:8080/dokuwiki/install.php

## Download development version of this plugin

Go to the plugin folder, and checkout the source code for this plugin:

```
cd lib/plugins
git clone https://github.com/jean-michel-gonet/dokuwiki-plugin-latexport.git latexport
```

Verify that the Latexport plugin is present by visiting 
* http://localhost:8080/dokuwiki/doku.php?do=admin&page=extension

## Unit testing
As plugin has a quite complex behavior, it is extensively tested with a PHPUnit test suite included with PHAR

- Install PHPUnit from the PHAR - Visit https://phar.phpunit.de/ to check what is the latest version, and use or modify the following commands:

```bash
wget https://phar.phpunit.de/phpunit-9.1.4.phar
chmod +x phpunit-9.1.4.phar
mv phpunit-9.1.4.phar /usr/local/bin/phpunit
```
- Verify the installation:

```bash
phpunit --version
PHPUnit 9.1.4 by Sebastian Bergmann and contributors.
```

- Install PHPAb from the PHAR -  Visit https://github.com/theseer/Autoload/releases to check for the latest release version, and use or modify the following commands:
```bash
wget https://github.com/theseer/Autoload/releases/download/1.25.9/phpab-1.25.9.phar
chmod +x phpab-1.25.9.phar
mv phpab-1.25.9.phar /usr/local/bin/phpab
```
- Verify the installation:

```bash
phpab --version
phpab 1.25.9 - Copyright (C) 2009 - 2020 by Arne Blankerts and Contributors
```

Test commands:
```bash
cd /wherever/is/dokuwiki/_test
phpunit --group plugin_latexport
phpunit --group plugin_latexport --testdox
```

## Adding the timezone configuration

You may be required to add the timezone configuration.

```bash
php --ini
Configuration File (php.ini) Path: /usr/local/etc/php/5.6
Loaded Configuration File:         /usr/local/etc/php/5.6/php.ini
Scan for additional .ini files in: /usr/local/etc/php/5.6/conf.d
```

Edit the ``php.ini`` configuration file and add one of the supported time zones (see http://php.net/manual/en/timezones.php) 
by uncommenting the ``date.timezone`` entry:

```
[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = Europe/Paris
```

## The ``.htaccess`` file
Also copy a .htaccess to the root dokuwiki folder (see https://www.dokuwiki.org/rewrite):

```
## Enable this to restrict editing to logged in users only

## You should disable Indexes and MultiViews either here or in the
## global config. Symlinks maybe needed for URL rewriting.
#Options -Indexes -MultiViews +FollowSymLinks

## make sure nobody gets the htaccess, README, COPYING or VERSION files
<Files ~ "^([\._]ht|README$|VERSION$|COPYING$)">
    <IfModule mod_authz_host>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_host>
        Order allow,deny
        Deny from all
    </IfModule>
</Files>

## Don't allow access to git directories
<IfModule alias_module>
    RedirectMatch 404 /\.git
</IfModule>

## Uncomment these rules if you want to have nice URLs using
## $conf['userewrite'] = 1 - not needed for rewrite mode 2
RewriteEngine on

RewriteRule ^_media/(.*)              lib/exe/fetch.php?media=$1  [QSA,L]
RewriteRule ^_detail/(.*)             lib/exe/detail.php?media=$1  [QSA,L]
RewriteRule ^_export/([^/]+)/(.*)     doku.php?do=export_$1&id=$2  [QSA,L]
RewriteRule ^$                        doku.php  [L]
RewriteCond %{REQUEST_FILENAME}       !-f
RewriteCond %{REQUEST_FILENAME}       !-d
RewriteRule (.*)                      doku.php?id=$1  [QSA,L]
RewriteRule ^index.php$               doku.php

## Not all installations will require the following line.  If you do,
## change "/dokuwiki" to the path to your dokuwiki directory relative
## to your document root.
#RewriteBase /dokuwiki
#
## If you enable DokuWikis XML-RPC interface, you should consider to
## restrict access to it over HTTPS only! Uncomment the following two
## rules if your server setup allows HTTPS.
#RewriteCond %{HTTPS} !=on
#RewriteRule ^lib/exe/xmlrpc.php$      https://%{SERVER_NAME}%{REQUEST_URI} [L,R=301]
```

- Check a info.php, to see if PHP is installed correctly.
- Create an entry to your local DNS to have a nice URL.
- Follow the installation procedure: http://local.your.dokuwiki/install.php
- Copy some content if you have.
- Clone the latex plugin in

``` bash
cd /path/to/dokuwiki/lib/plugins
git clone https://github.com/jean-michel-gonet/dokuwiki-plugin-latexport.git latexport
Cloning into 'latexport'...
```
