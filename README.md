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
(see 

If `otfinfo` complains of the file being too small, check from the command line if the file has zero length:

	MacBook-Pro:Fonts me$ otfinfo -i Playbill 
	otfinfo: Playbill: OTF file corrupted (too small)
	MacBook-Pro:Fonts me$ ls -la Playbill 
	-rw-rw-r--@ 1 me       staff  0 Jun 15  2010 Playbill

Zero length is visible only from command line. If you check the size from the Finder you see a non-zero size. Also you can tell that the file is not corrupt because you can open it in the Font Book.

For some reason, lots of font files have their content hidden in metadata attributes. You can check if it's your case:

After that, you can try `otfinfo` again. Chances are that it returns yet another error:

	MacBook-Pro:Fonts me$ otfinfo -i Lucida\ Bright.ttf 
	otfinfo: Lucida Bright.ttf: not an OpenType font (bad magic number)

