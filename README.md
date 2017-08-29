# dokuwiki-plugin-latexport
A latex export renderer plugin to export latex documents from Dokuwiki. In early stages. Very early stages.

Call the export with:

http://microcontroleur.agl-developpement.ch/start?do=export_latexport_tex

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
