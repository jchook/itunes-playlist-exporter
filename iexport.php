<?php
/**
 * 
 * iexport - iTunes Playlist Item Exporter
 * 
 * Auto-copy iTunes playlist items to a separate folder
 * on your computer.
 * 
 * Simply export your playlist from iTunes as xml, then
 * run the iexport script on it like so...
 * 
 * 	php iexport.php MyPlaylist.xml
 * 
 * iexport will auto-copy all of the playlist files into 
 * a folder in the current working directory.
 * 
 * @author Wesley Roberts
 * @version 2012.01.03
 * @package iExport
 * 
 */


/**
 * Seek into an array using a path
 * 
 * apath($ra, 'one/two/three') == $ra['one']['two']['three']
 */
function apath(&$array, $path, $default = null)
{
	$pointer =& $array;
	if ($keys = explode('/', trim($path, '/'))) {
		foreach ($keys as $key) {
			if (isset($pointer[$key])) {
				$pointer =& $pointer[$key];
			} else {
				return $default;
			}
		}
		return $pointer;
	}
	return $default;
}

class PlistParser extends XMLReader
{   
    public function parseFile($file) {
		if(basename($file) == $file) {
			throw new Exception("Non-relative file path expected", 1);
		}
		$this->open("file://" . $file);
		return $this->process();
	}

    public function parseString($string) {
        $this->XML($string);
        return $this->process();
    }

    private function process() {
		// plist's always start with a doctype, use it as a validity check
		$this->read();
		if($this->nodeType !== XMLReader::DOC_TYPE || $this->name !== "plist") {
			throw new Exception(sprintf("Error parsing plist. nodeType: %d -- Name: %s", $this->nodeType, $this->name), 2);
		}
		
		// as one additional check, the first element node is always a plist
		if(!$this->next("plist") || $this->nodeType !== XMLReader::ELEMENT || $this->name !== "plist") {
			throw new Exception(sprintf("Error parsing plist. nodeType: %d -- Name: %s", $this->nodeType, $this->name), 3);
		}
		
		$plist = array();	
		while($this->read()) {
			if($this->nodeType == XMLReader::ELEMENT) {
				$plist[] = $this->parse_node();
			}
		}
		if(count($plist) == 1 && $plist[0]) {
			// Most plists have a dict as their outer most tag
			// So instead of returning an array with only one element
			// return the contents of the dict instead
			return $plist[0];
		} else {
			return $plist;
		}
    }

	private function parse_node() {
		// If not an element, nothing for us to do
		if($this->nodeType !== XMLReader::ELEMENT) return;
		
		switch($this->name) {
			case 'data': 
				return base64_decode($this->getNodeText());
				break;
			case 'real':
				return floatval($this->getNodeText());
				break;
			case 'string':
				return $this->getNodeText();
				break;
			case 'integer':
				return intval($this->getNodeText());
				break;
			case 'date':
				return $this->getNodeText();
				break;
			case 'true':
				return true;
				break;
			case 'false':
				return false;
				break;
			case 'array':
				return $this->parse_array();
				break;
			case 'dict':
				return $this->parse_dict();
				break;
			default:
				// per DTD, the above is the only valid types
				throw new Exception(sprintf("Not a valid plist. %s is not a valid type", $this->name), 4);
		}			
	}
	
	private function parse_dict() {
		$array = array();
		$this->nextOfType(XMLReader::ELEMENT);
		do {
			if($this->nodeType !== XMLReader::ELEMENT || $this->name !== "key") {
				// If we aren't on a key, then jump to the next key
				// per DTD, dicts have to have <key><somevalue> and nothing else
				if(!$this->next("key")) {
					// no more keys left so per DTD we are done with this dict
					return $array;
				}
			}
			$key = $this->getNodeText();
			$this->nextOfType(XMLReader::ELEMENT);
			$array[$key] = $this->parse_node();
			$this->nextOfType(XMLReader::ELEMENT, XMLReader::END_ELEMENT);
		} while($this->nodeType && !$this->isNodeOfTypeName(XMLReader::END_ELEMENT, "dict"));
		return $array;
	}
	
	private function parse_array() {
		$array = array();
        $this->nextOfType(XMLReader::ELEMENT);
		do {
			$array[] = $this->parse_node();
			// skip over any whitespace
			$this->nextOfType(XMLReader::ELEMENT, XMLReader::END_ELEMENT);
		} while($this->nodeType && !$this->isNodeOfTypeName(XMLReader::END_ELEMENT, "array"));
		return $array;
	}
	
	private function getNodeText() {
		$string = $this->readString();
		// now gobble up everything up to the closing tag
		$this->nextOfType(XMLReader::END_ELEMENT);
		return $string;
	}
	
	private function nextOfType() {
		$types = func_get_args();
		// skip to next
		$this->read();
		// check if it's one of the types requested and loop until it's one we want
		while($this->nodeType && !(in_array($this->nodeType, $types))) {
			// node isn't of type requested, so keep going
			$this->read();
		}
	}
	
	private function isNodeOfTypeName($type, $name) {
		return $this->nodeType === $type && $this->name === $name;
	}
}




// option aliases
$alias = array(
	'd' => 'destination',
	'o' => 'destination',
	't' => 'type',
	'T' => 'preserve-track-numbers',
);

$args = array();
$opts = array(
	'type' => 'xml',
	'preserve-track-numbers' => false,
	'destination' => null,
);

$cmd = array_shift($argv);

// standard arg parsing
$key = null;
$val = null;
foreach ($argv as $arg)
{
	if ($arg[0] == '-') {
		if (!is_null($key)) {
			$opts[$key] = true;
			$key = null;
		}
		if (substr($arg, 0, 2) == '--') {
			$key = substr($arg, 2);
			if (strrpos($key, '=')) {
				list($key, $val) = explode('=', $key, 2);
			}
		} elseif (strlen($arg) > 1) {
			$key = substr($arg, 1, 1);
			$val = substr($arg, 2);
		} else {
			// -
		}
	} else {
		$val = $arg;
	}
	if (!is_null($val)) {
		if (!is_null($key)) {
			if (isset($alias[$key])) {
				$key = $alias[$key];
			}
			$opts[$key] = $val;
			$key = null;
		} else {
			$args[] = $val;
		}
		$val = null;
	}
}

// print usage message if we're missing playlist
if (!isset($args[0])) {
	exit ('Usage: iexport [options] playlist.xml');
}

if (!file_exists($args[0])) {
	exit ('Playlist file does not exist: ' . $args[0]);
}

switch($opts['type']) {
	case 'XML':
	case 'xml':
		
		/* Load XML
		if (!($xml = simplexml_load_file($args[0]))) {
			exit('Invalid XML file ' . $args[0]);
		}
		*/
		$parser = new PlistParser();
		$xml = $parser->parseFile(realpath($args[0]));
		
		if (!$xml || !isset($xml['Tracks']) || !$xml['Tracks']) {
			exit('Invalid Playlist plist XML file');
		}
		
		// Get tracks
		$tracks = array();
		foreach ($xml['Tracks'] as $id => $track) {
			if (isset($track['Location'])) {
				$find = '^file\:\/\/';
				$replace = '';
				$tracks[$id] = preg_replace("/$find/", $replace, urldecode($track['Location']));
			}
		}
		
		// get playlists
		$lists = array();
		foreach ($xml['Playlists'] as $id => $list) {
			$lists[$id] = array(
				'name' => $list['Name'],
				'tracks' => array_map(function($item) use($tracks) 
				{
					if (isset($item['Track ID']) && $tracks[$item['Track ID']]) {
						return $tracks[$item['Track ID']];
					}
					return false;
				}, $list['Playlist Items']),
			);
		}
		
		foreach ($lists as $list) {
			$name = preg_replace('/[^a-zA-Z0-9\ ]/', '_', $list['name']);
			$dest = $opts['destination'] . $name;
			$cmds = array(
				'mkdir -p "' . $dest . '"',
			);
			
			$trackNumber = 1;
			foreach ($list['tracks'] as $track)
			{
				$destname = trim(basename($track));
				if (!$opts['preserve-track-numbers']) {
					$destname = $trackNumber . ' ' . ltrim($destname, '1234567890');
				}
				$destpath = $dest . '/' . $destname;
				$cmds[] = 'cp "' . $track . '" "' . $destpath . '"';
				$trackNumber++;
			}
			
			foreach ($cmds as $cmd) {
				// echo "$cmd\n";
				echo shell_exec($cmd);
			}
		}
		
		
		break;
	default:
		exit('Unsupported type: ' . $opts['type'] . ', try xml');
}


?>