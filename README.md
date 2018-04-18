# Latexport - A Latex export plugin
A latex export renderer plugin to export latex documents from Dokuwiki. In early stages. Very early stages.

## Purpose and limitations 
The main objective of this plugin are:
- It is possible to export a page or a set of pages as a structure of latex files and images.
- Export forces as little presentation choices as possible, allowing user to make their own styles.
- Mapping as naturally as possible the dokuwiki formatting into sensible latex formatting.
- Latex scripting is readable.

The main limitation of the plugin is that it is not possible to directly download a finished PDF file. It was never my intention to provide such a feature with unrestricted access for two reasons:
- Although most of its content is freely available online, I still want to sell my book.
- I rekon that online content is a perpetual work in progress. I don't want non-revised, half baked versions of my book 
circulating around.

With the procedure described below, you can obtain a PDF version in less than a minute.

Other limitations are:
- Export forces the use of certain packages (listed below).
- Text wrapping in table cells is not supported. Latex has issues with text wrapping multi-column cells. 
They're possible to overcome when you write the Latex document manually, but I haven't been able to found a 
satisfactory automated solution. In the end I had to choose between rowspan/colspan and text 
wrapping, and I chose the former.
- I did an opinionated choice about how to map a navigable page hierarchy into a readable document structure. 
After testing it with two very big documents (more than 20 chapters, more than 200 pages), I believe it works 
quite well. I hope it will work also for you.

## Installing the plugin on Dokuwiki
Either install it using Dokuwiki's plugin manager, or clone this project into the plugin repository:

```bash
cd /wherever/is/dokuwiki/lib/plugins
git clone [obtain the repository url] latexport
```

Or download the latest version as a ZIP archive and extract it into the ``plugins`` folder.

The plugin doesn't require any specific configuration or access rights.

## Using the plugin to export pages as latex
After installing the plugin, export one page by calling an url as follows:

http://xxx.yyy.com/path/to/my_page?do=export_latexport_tex

This downloads a ZIP archive with the following structure:
- The name of the archive is the same as the page. In this example it is 'my_page.zip'
- The ``aaa.tex`` file, which corresponds to the exported page.
- Each of the linked pages are represented by a file 'name_of_the_linked_page.tex'
- One folder named 'images', containing all printable media.

To export not only a single page, but a structured collection of pages, see below.

## Installing TeX on your local system
Unless you know better I suggest to use TeX Live, which provides a fully functional, ready to use, well configured TeX engine. 
It exists for almost all platforms at the official web site:
- https://tug.org/texlive/

## Creating a PDF file based on the exported latex
The exported latex document do not contain any configuration. I'm showing you how to create it separately
in the following procedure. There is a more elaborated example further down.

1. Download the zip archive from the page. You can either type the url in your browser and save the content 
in an appropriate place, or use a command similar to:
```bash
cd working_folder
curl -o content.zip www.xxx.yyy/path/to/my_page?do=export_latexport_tex
```

2. Unzip the archive into a destination folder, either with your favorite application, or using command line:
```bash
mkdir content
unzip content.zip -d content/
```

3. Prepare your root document, and save it *besides the folder where you extracted the latex archive*. 
This is the simplest example I could come with. Mind the ``graphicspath`` command, that specifies the destination folder
plus the ``images`` folder. Mind also the ``import`` command, specifying the destination folder and the root page.
Save it as ```root.tex``` or any other name that you see fit:

```latex
\documentclass{book}

\usepackage{import}
\usepackage[french]{babel}
\usepackage{hyperref}
\usepackage{array}
\usepackage{soul}
\usepackage{csquotes}
\usepackage{multirow}
\usepackage{listings}
\usepackage{makecell}
\usepackage{tabulary}
\usepackage{fontspec}
\usepackage{graphicx}
\graphicspath{ {content/images/} }

\begin{document}

\import{content/}{aaa.tex}

\end{document}
```

4. To launch the PDF generation, execute ``lualatex`` from your working folder. Execute it twice if
the document contains an index, a list of figures, a table of contents or cross references (this is a standard
latex requirement):

```bash
cd working_folder
lualatex root.tex
lualatex root.tex
```

5. If everything went correctly, you should have a PDF containing the exported page(s)

# Syntax of the latexport plugin
The plugin doesn't define any new syntax. It does define mappings between usual _Dokuwiki_ syntax and _Latex_ syntax. Most of the time 
it consists into straight forward mapping: foot notes into foot notes, italic into italic, quotes into quotes and code blocks into code 
blocks. I've listed less intuitive elements in the next points.

## Including a page into another
This is actually the main feature of the plugin. If you place a standalone internal link in a bullet element, latexport replaces it by the content of the destination page:

```dokuwiki
===== Généralités =====  
* [[path:to:a:destination:page|Link title is only visible online]]  
* [[path:to:another:destination:page|Link title is only visible online]]
* [[more:like:this|etc.]]
```

Map from the following dokuwiki structure:
The root document:
- H1:  
  - The first H1 opens the *main matter*. The text of header is ignored.  
  - The second H1 opens the *appendix*  
  - The third and next H1 are considered chapters in the appendix.
- H2: Opens a *part*. The text of header is placed as title of the part. Also, H2 following the third or next H1 are considered chapters in the appendix.- H3: Opens a *chapter*. The text of header is placed as title of the chapter.- H4: Opens a *section*. The text of header is placed as title of the section.- H5: Opens a *subsection*. The text of header is placed as title of the part.- Unordered list item starting with a link, includes the destination page, using the current level of heading as the base level.
In the destination page:- The H1 opens a *chapter*, *section*, *subsection*, etc depending on the level of heading in the referring page. Text of header is used as title of the heading.- The H1 never opens a level higher than *chapter*.- Lower header levels open a lower level headings.- Unordered list item starting with a link, includes the destination page, using the current level of heading as the base level.

## Cross-reference / linking two pages

## Friendly plugins - mathjax

## Friendly plugins - anchor

## Friendly plugins:
If you wish, install the following plugins:
- mathjax: This supports displaying latex-like formulae online. It is 
  fully compatible with latexport, which will render the formulae in the PDF file.
- anchor: This allows including anchors to specific places of a page. It is compatible with latexport, 
  which will render the anchor as a cross-reference with page number. You probably want to have the 'normal' option, 
  referred in the install instructions.

## Using system fonts
To use system fonts, and not be restricted to the ones packaged in Latex, use the `fontspec`package:

```latex
\usepackage{fontspec}
\setmainfont{Lucida Bright}
```

To know the name of the font:

1. Open the Font Book
2. Locate your desired font
3. Right click on it, and select _Show in Finder_
4. Right click on the file and click on _Get Info_
5. Copy the path to the font file, open a console, and type:

```bash
otfinfo -i '/Path/To/The/File/Name of the font.ttf'
```

The last command shows a series of identifiers:
```
Family:              Lucida Bright 
Subfamily:           Demibold 
Full name:           Lucida Bright Demibold 
PostScript name:     LucidaBright-Demi 
Version:             Version 1.69 
Unique ID:           Lucida Bright Demibold 
Trademark:           Lucida® is a registered trademark of Bigelow & Holmes Inc. 
Copyright:           © 1991 by Bigelow & Holmes Inc. Pat. Des. 289,422.                      
                     All Rights Reserved. © 1990-1991 Type Solutions, Inc. All Rights Reserved
Vendor ID:           B&H
```

Use the font family name as main font, as in the example above.

## Exporting multiple pages to latex - Links between pages
You can establish two kind of links between pages that affect the latex export:
- A bullet item containing only an internal link to another page is replaced by the content of the destination page
- An internal link to  link to In-line links: It is replaced by the p reference in the final document.

The plugin helps you to structure your pages in the same way. 

# Structuring dokuwiki to look good both online and printed
Making a satisfactorily structured dokuwiki site to export nicely as a printed document is not hard, provided
you follow certain directions.

## The traditional book structure
The traditional page order for books, as suggested by common practice (see https://en.wikibooks.org/wiki/LaTeX/Document_Structure),
is the following:
- Frontmatter
  - Half-title
  - Empty
  - Title page
  - Information (copyright notice, ISBN, etc.)
  - Dedication if any, else empty
  - Table of contents
  - List of figures (can be in the backmatter too)
  - Preface chapter
- Mainmatter
  - Main topic
- Appendix
  - Some subordinate chapters
- Backmatter
  - Bibliography
  - Glossary / Index

The main matter is usually divided in chapters, but chapters may be organized in parts.

## A usual way to structure dokuwiki
The most straight forward way to structure 

## How to write formulas
- https://tex.stackexchange.com/questions/503/why-is-preferable-to
- https://www.sharelatex.com/learn/Aligning_equations_with_amsmath
- Inline formulas delimited with $ ... $ are changed to \( ... \)
- Inline formulas delimited with \( ... \) are left untouched.
- Display formulas delimited with $$ ... $$ and \[ ... \] are changed to \begin{equation} ... \end{equation}. Explicit \tag{.} command is removed, as it is not supported outside the amsmath package.
- Display formulas explicitly delimited with a \begin{xx} ... \end{xx} are left untouched.

## How to make cross references
- https://en.wikibooks.org/wiki/LaTeX/Labels_and_Cross-referencing

# Troubleshooting
If the downloaded ZIP file is not valid, or it contains a *.zip.cpgz file, then you may have some text warnings in it.To find out, open it with a text editor.

## Use installed fonts in Mac OS X
(To follow this procedure you need `fontspec` package and `otfinfo` utility. If you don't know how to get them, install Tex Live from the official web site: https://tug.org/texlive/ )

## The fearsome 0 bytes font file in Mac OS X
(See www.dmertl.com/blog/?p=11 )
(See https://en.wikipedia.org/wiki/Resource_fork )

If `otfinfo` complains of the file being too small, check from the command line if the file has zero length:

	MacBook-Pro:Fonts me$ otfinfo -i Playbill 
	otfinfo: Playbill: OTF file corrupted (too small)
	MacBook-Pro:Fonts me$ ls -la Playbill 
	-rw-rw-r--@ 1 me       staff  0 Jun 15  2010 Playbill

Zero length is visible only from command line. If you check the size from the Finder you see a non-zero size. Also you can tell that the file is not corrupt because you can open it in the Font Book.

For some reason, lots of font files have their content hidden in metadata attributes. You can check if it's your case with the `xattr`command. There are two versions. The short one:

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

You can see that `com.apple.ResourceFork` attribute contains the whole data. To extract the data as binary in a separated file, use `xattr` in conjunction with `xxd`, as demostrated below. 

	MacBook-Pro:Fonts me$ xattr -px com.apple.ResourceFork Playbill | xxd -r -p > Playbill.ttf	

If everything went right, you should have a second file with non-zero length:

	MacBook-Pro:Fonts me$ ls -la Playbill*
	-rw-rw-r--@ 1 me       staff      0 Jun 15  2010 Playbill
	-rw-r--r--+ 1 me       staff  47271 Sep 17 09:36 Playbill.ttf

Alas, although you can open this file in Font Book, if you _Validate Font_ it shows a _System Validation_ error. Also, `otfinfo` returns yet another error:

	MacBook-Pro:Fonts me$ otfinfo -i Playbill.ttf 
	otfinfo: Playbill.ttf: not an OpenType font (bad magic number)

To overcome this problem, I uploaded the TTF file to a online font converter (for example, https://onlinefontconverter.com/ ), and converted it into TTF (yes, same). Then:
1. Download the result.
2. Uninstall the original font.
3. Install your converted font. If you processed a font file with multiple variations - like bold, italic - you will probably have one file per variation; in that case install them all.
4. Check them with `otfinfo`. 

To me this worked.

# Extending the dokuwiki-plugin-latexport
To develop extension to the plugin you need:

- A development environment with PHP5 or later.
- A development version of dokuwiki and a configured web site.
- PhpUnit installed as a PHAR in the path.
- Checkout the dokuwiki-plugin-latexport in the corresponding plugin folder of dokuwiki.

## A development environment with PHP5 or later
As dokuwiki is still compatible with PHP5, I use both PHP7 and PHP5 to ensure that plugin is also compatible with both versions.

How to achieve this depends on your own development machine and operative system.

### Installing PHP and Apache with HomeBrew on Mac OS X

- Original instructions: https://gist.github.com/davebarnwell/1d413ffbc9660469e9aa685d8387b87f
- homebrew instructions http://justinhileman.info/article/reinstalling-php-on-mac-os-x/
- from Justin Hileman https://www.twitter.com/bobthecow
- https://stackoverflow.com/questions/39456022/php7-installed-by-homebrew-doesnt-work-with-apache-on-macos

By default Mac OS X contains php 5. If you need version 7 you can install with brew:
- Stop Apache: sudo apachectl stop
- Then install PHP with http support:

```bash
brew tap homebrew/core
brew tap homebrew/homebrew-php
brew unlink php56
brew install php70 --with-httpd
brew install php70-xdebug
brew install mcrypt php70-mcrypt
```
- Then reboot your computer.

OS X 10.8 and newer come with php-fpm pre-installed. You may need to force the system to use the brew version. Ensure that  ``/usr/local/sbin`` is before ``/usr/sbin`` in your PATH:

  PATH="/usr/local/sbin:$PATH"

Check that PHP is correctly installed in command line:

```bash
$ php --version
PHP 7.0.0 (cli) (built: Dec  2 2015 13:05:57) ( NTS )
Copyright (c) 1997-2015 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2015 Zend Technologies
```
You still need to connect Apache with the new PHP. If you were using the built-in Apache, this can be confusing because ``--with-httpd`` just installed a second, brew version, whose configuration file is located in ``/usr/local/etc/httpd/httpd.conf``:

- Configure it to use port 80: 

```Listen 80```

- Usually php7 is already activated by brew:

```LoadModule php7_module        /usr/local/Cellar/php70/7.0.27_19/libexec/apache2/libphp7.so```

- Activate the rewrite module: 

```LoadModule rewrite_module libexec/apache2/mod_rewrite.so```

- Unactivate the server pool management by commenting out:

```#Include /private/etc/apache2/extra/httpd-mpm.conf```

- Activate the virtual servers by uncommenting:

```Include /private/etc/apache2/extra/httpd-vhosts.conf```

Now you can add the configuration for your web site in ``extra/http-vhosts.conf``, adding the ``FilesMatch`` tag like in:

```
<VirtualHost *:80>
    # Official address for user web site:
    ServerName local.whatever.com

    # Email of administrator:
    ServerAdmin me@myself.com

    # Activate php
    <FilesMatch .php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    . . .
</VirtualHost>
```

## A development version of dokuwiki and a configured web site

To retrieve the development version of dokuwiki you need to have git installed. Then follow instructions in https://www.dokuwiki.org/devel:git

- Go to your development folder, checkout the development version and switch to the stable branch.

```
git checkout https://github.com/splitbrain/dokuwiki.git
git checkout stable
```
This should have created a dokuwiki folder with all sources, including a ``_test`` folder with unit tests.

I'm assuming you've got PHP and Apache configured (see above) and you activated the virtual hosts in Apache. Now you need to associate a virtual host to the dokuwiki folder:

```
<VirtualHost *:80>
    # Official address for user web site:
    ServerName local.microcontroleur.agl-developpement.ch

    # Email of administrator:
    ServerAdmin info@agl-developpement.ch

    # Activate php
    <FilesMatch .php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    # Grants access to everybody:
    DocumentRoot /path/to/your/development/site/dokuwiki
    <Directory   /path/to/your/development/site/dokuwiki>
        DirectoryIndex index.php index.html

        # For Apache 2.2
        Order allow,deny
        Allow from all

        # For Apache 2.4
        Require all granted

        # To enable .htaccess
        AllowOverride all
    </Directory>
</VirtualHost>
```
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

## Unit testing
As plugin has a quite complex behavior, it is extensively tested with a PHPUnit test suite included with PHAR

- Install PHPUnit from the PHAR:

```bash
wget https://phar.phpunit.de/phpunit-5.phar
chmod +x phpunit-5.7.26.phar
sudo mv phpunit-5.7.26.phar /usr/local/bin/phpunit
```
- Verify the installation:

```bash
phpunit --version
PHPUnit 5.7.26 by Sebastian Bergmann and contributors.
```

- Install PHPAb from the PHAR:
```bash
wget https://github.com/theseer/Autoload/releases/download/1.24.1/phpab-1.24.1.phar
chmod +x phpunit-5.7.26.phar
sudo mv phpunit-5.7.26.phar /usr/local/bin/phpab
```
- Verify the installation:

```bash
phpab --version
phpab 1.24.1 - Copyright (C) 2009 - 2018 by Arne Blankerts and Contributors
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

Edit the ``php.ini`` configuration file and add one of the supported time zones (see http://php.net/manual/en/timezones.php) by uncommenting the ``date.timezone`` entry:

```
[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = Europe/Paris
```

## Install PHP 7

