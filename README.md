# dokuwiki-plugin-latexport
A latex export renderer plugin to export latex documents from Dokuwiki. In early stages. Very early stages.

Call the export with:

http://xxx.yyy.com/start?do=export_latexport_tex

## Install Tex environment

On Mac: Use http://www.tug.org/mactex/mactex-download.html

## Generate the PDF file.

Be sure that the following snippet is present in the document header:

	\documentclass{book}
	...
	\usepackage{fontspec}
	\setmainfont[Ligatures=TeX]{xits}
	...
	\begin{document}

Then compile the TeX document with:
	lualatex [name-of-page].tex

## Install PHP 7

add the taps, unlink the old PHP if required and add php7

```bash
brew tap homebrew/dupes
brew tap homebrew/versions
brew tap homebrew/homebrew-php
brew unlink php56
brew install php70
brew install php70-xdebug
brew install mcrypt php70-mcrypt
```

And the result?

```bash
$ php --version
PHP 7.0.0 (cli) (built: Dec  2 2015 13:05:57) ( NTS )
Copyright (c) 1997-2015 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2015 Zend Technologies
```

All ready to go!

References

- Original instructions: https://gist.github.com/davebarnwell/1d413ffbc9660469e9aa685d8387b87f
- homebrew instructions http://justinhileman.info/article/reinstalling-php-on-mac-os-x/
- from Justin Hileman https://www.twitter.com/bobthecow


## Use installed fonts in Mac OS X
(To follow this procedure you need `fontspec` package and `otfinfo` utility. If you don't know how to get them, install Tex Live from the official web site: https://tug.org/texlive/ )

To use system fonts, and not be restricted to the ones packaged in Latex, use the `fontspec`package:

	\usepackage{fontspec}
	\setmainfont{Lucida Bright}

To know the name of the font:

1. Open the Font Book
2. Locate your desired font
3. Right click on it, and select _Show in Finder_
4. Right click on the file and click on _Get Info_
5. Copy the path to the font file, open a console, and type:

	otfinfo -i '/Path/To/The/File/Name of the font.ttf'

The last command shows a series of identifiers:

	Family:              Lucida Bright
	Subfamily:           Demibold
	Full name:           Lucida Bright Demibold
	PostScript name:     LucidaBright-Demi
	Version:             Version 1.69
	Unique ID:           Lucida Bright Demibold
	Trademark:           Lucida® is a registered trademark of Bigelow & Holmes Inc.
	Copyright:           © 1991 by Bigelow & Holmes Inc. Pat. Des. 289,422. 
	                     All Rights Reserved. © 1990-1991 Type Solutions, Inc. All Rights Reserved.
	Vendor ID:           B&H

Use the font family name as main font, as in the example above.

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
