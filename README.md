# iTunesExport

Easily export actual song files from iTunes playlists.

Track names can optionally be renamed to keep playlist order.


## Why Does This Exist?

iTunes does not make it easy to export playlist song files.
I created this script to make it easy to import my playlists into [8tracks.com](http://8tracks.com/ "The Greatest Internet Radio Ever").


## How Do I Use It?

The usage is simple enough for any Github user.

1. Create your playlist in iTunes
1. Export your playlist as XML (right-click playlist, choose Export)
1. Invoke iexport.php in terminal with the XML file as its only argument

```bash
php iexport.php MyPlaylist.xml
```

## Options

**-d, --destination**
	Change destination folder of the export. Defaults to the current working directory.

**-T, --preserve-track-numbers**
	Keeps original track numbers. Otherwise, filenames are rewritten to preserve playlist order.


## Installation
If you choose, you may install iexport

```bash
echo '#!/usr/bin/env php' > iexport
cat iexport.php >> iexport
chmod a+x iexport
sudo mv iexport /usr/local/bin
```

Once installed, the usage of iexport is simplified.

```bash
iexport playlist.xml
```

# License

Copyright (c) 2012 Wesley Roberts

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.