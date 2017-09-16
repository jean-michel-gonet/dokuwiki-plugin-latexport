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

(This procedure is far easier if you install Tex Live: https://tug.org/texlive/ )

To use system fonts, and not be restricted to the ones packaged in Latex, use the `fontspec`package:

	\usepackage{fontspec}
	\setmainfont{Haettenschweiler}

To know the name of the font:

1. Open the Font Book
2. Locate your desired font
3. Right click on it, and select _Show in Finder_
4. Right click on the file and click on _Get Info_
5. Copy the path to the font file, open a console, and type:
