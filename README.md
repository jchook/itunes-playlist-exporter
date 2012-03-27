# iTunesExport

Easily export actual song files from iTunes playlists.


## License
This software is free to use under the [MIT License](http://www.opensource.org/licenses/mit-license.html "MIT License").


## Why Does This Exist?
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
echo \#\!`which php` > iexport
cat iexport.php >> iexport
chmod a+x iexport
sudo mv iexport /usr/local/bin
```

Once installed, the usage of iexport is simplified.
```bash
iexport playlist.xml
```