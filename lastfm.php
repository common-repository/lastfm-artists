<?php
/*
Plugin Name: Last.fm Artists
Plugin URI: http://finalstar.net/lastfm
Description: Last.fm Artists shows images of your top or recently listened to artists.
Version: 2.0.2
Author: Gary Fenstamaker 
Author URI: http://finalstar.net
Copyright 2008  Gary Fenstamaker (email : garyf@finalstar.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Acknowledgment to Tim Eckel for the fastimagecopyresampled function
*/

##############################
# Important Constants
##############################

define("API_KEY", "ffd6c3e445e45d0d4bc124184853b772");
define("LASTFM_DIRECTORY", WP_CONTENT_URL . "/lastfmcache/"); 
define("ROOT_DIRECTORY", ABSPATH . "wp-content/lastfmcache/"); 
define("PLUGIN_DIRECTORY", dirname(__FILE__)); 
define("OPTIONS_NAME", "lastfmArtistsOptions");

##############################
# Last.fm Classes
##############################

class Lastfm_Image {
	
	var $name;
	var $url; // The image url from Last.fm
	var $image; // The new image created
	
	function check_cache($name, $size, $type) {
		
		foreach (array('jpg', 'gif', 'png', 'jpeg') as $ext) {
			$image = $name . $size . $type . $ext;
			$url = ROOT_DIRECTORY . $image;
			if ( file_exists($url) ) {
				if ( ($_SERVER['REQUEST_TIME'] - filemtime($url)) > '604800' ) { // 604800 seconds = 1 week
					return FALSE;
				}else{
					$this->image = LASTFM_DIRECTORY . $image;
					return TRUE;
				}
			}
			return FALSE;
		}
		
	}
	
	function get_image($name, $size, $time, $counter, $xml) {
		
		switch ($time) {
			case 'recent':
			case 'loved':
			case 'weekly':
				$name = urlencode($name);
				$url = 'http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=' . $name . '&api_key=' . API_KEY;
				$feed = new Lastfm_Feed($url);
				$feed->load_contents();
				$artistXML = simplexml_load_string($feed->contents);
				$this->url = $artistXML->artist->image[2];
				break;
			default:
				$this->url = $xml->topartists->artist[$counter]->image[2];
				break;
		}
		
	}
	
	function check_image() { // Checks if there is an image
		
		if ( $this->url == '' ) {
			return FALSE;
		}
		return TRUE;
		
	}
	
	function create_image($name, $size, $type) {
		
		$mime = end(explode('.', $this->url)); // Rips the filetype off the end
		$file = $name . $size . $type . '.' . $mime;
		$filename = ROOT_DIRECTORY . $file;
		
		if ( $this->call_image($filename, $mime) === FALSE ) { // Downloads image to server
			return FALSE;
		}
		
		switch ($mime) {
			case 'jpg':
			case 'jpeg':
				$src_img = imagecreatefromjpeg($filename);
				break;
			case 'png':
				$src_img = imagecreatefrompng($filename);
				break;
			case 'gif':
				$src_img = imagecreatefromgif($filename);
				break;
    	}
		
		$vars = $this->do_math($filename, $size, $type);
		extract($vars);
		
		// Cuts the image into a square
		$dst_img = imagecreatetruecolor($new_size, $size);
		fastimagecopyresampled($dst_img, $src_img, 0, 0, ($y/2), 0, $new_width, $new_height, $width, $height);
		
		// Saves the images
		switch ($mime) {
			case 'jpg':
			case 'jpeg':
				imagejpeg($dst_img, $filename, 100);
				break;
   			case 'png':
				imagepng($dst_img, $filename, 9);
				break;
			case 'gif':
				imagegif($dst_img, $filename);
				break;
		}
		
		// Detroys temp files
		imagedestroy($src_img);
		imagedestroy($dst_img);
		
		$this->image = LASTFM_DIRECTORY . $file;
		
	}
	
	function call_image($filename, $mime) { // Downloads image to server for manipulation
		
		$curl = curl_init();
		
		curl_setopt ($curl, CURLOPT_URL, $this->url);
		curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
		
		$output = curl_exec($curl);
		curl_close($curl);
		
		if ( ($dst_img = imagecreatefromstring($output)) === FALSE ) {
			return FALSE;
		}
		
		switch ($mime) {
    		case 'jpg':
    	    case 'jpeg':
    	        imagejpeg($dst_img, $filename, 100);
    	        break;
    	    case 'png':
    	        imagepng($dst_img, $filename, 9);
    	        break;
    	    case 'gif':
    	        imagegif($dst_img, $filename);
    	        break;
    	}
		
	}
	
	function do_math($filename, $size, $type) {
		
		$imageInfo = getimagesize($filename);
		
		// Finds the ratio of width to height
		$ratio = $imageInfo[0] / $imageInfo[1];
		// Creates the new dimensions
		$new_size = ($type == 'wide') ? $size + 30 : $size;
		$new_width = ($ratio < 1) ? round($new_size * $ratio) : $new_size;
		$new_height = ($ratio > 1) ? round($new_size / $ratio) : $new_size;
		// Finds the differences between the new dimensions and the size desired
		$x = $new_size - $new_width;
		$y = $new_size - $new_height;
		// Adds the differences to the dimensions so they can be cut into a square
		$new_width = $new_width + $y + $x;
		$new_height = $new_height + $y + $x;
		$y = ($type == 'wide') ? $new_width - $new_size : $y;
		
		$vars = array('new_width' => $new_width, 'new_height' => $new_height, 'y' => $y, 'width' => $imageInfo[0], 'height' => $imageInfo[1], 'new_size' => $new_size);
		
		return $vars;
		
	}
	
	
}

class Lastfm_Feed {
	
	var $url;
	var $contents; // Contents of the feed
	
	function __construct($url) {
		
		$this->url = $url;
		
	}
	
	function load_contents() { // Downloads the contents of the feed into $this->contents
		
		$curl = curl_init();
	
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		
		$this->contents = curl_exec($curl);
		
		curl_close($curl);
		
	}
	
}

class Lastfm {
	
	var $user;
	var $time; // Time period
	var $number;
	var $size;
	var $feed;
	var $type; // Square or wide
	var $display; // Display information
	var $style; // CSS
	
	function __construct($args = NULL) {
		
		if ( function_exists('get_option') ) { // For Wordpress
			$options = get_option(OPTIONS_NAME);
		}
		
		if ( isset($args) ) {  // Replaces Wordpress options or sets options for standalone
			foreach ( $args as $key => $option ) {
				$options[$key] = $option;
			}
		}
		
		$this->user = $options['user'];
		$this->time = $options['time'];
		$this->number = $options['number'];
		$this->size = $options['size'];
		$this->type = $options['type'];
		$this->display = $options['display'];
		$this->style = $options['style'];
		
	}
	
	function select_feed() {
		
		switch ($this->time) {
			case 'weekly':
				$this->feed = 'http://ws.audioscrobbler.com/2.0/?method=user.getweeklyartistchart&user=' . $this->user . '&api_key=' . API_KEY;
				break;
			case 'recent':
				$this->feed = 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=' . $this->user . '&api_key=' . API_KEY;
				break;
			case 'loved':
				$this->feed = 'http://ws.audioscrobbler.com/2.0/?method=user.getlovedtracks&user=' . $this->user . '&api_key=' . API_KEY;
				break;
			default:
				$this->feed = 'http://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user=' . $this->user . '&period=' . $this->time . '&api_key=' . API_KEY;
				break;
		}
		
	}
	
	function check_cache_dir($cacheDir) { // Checks for cache dir and trys to create if not there
		
		if ( is_dir($cacheDir) === FALSE ) {
			if ( mkdir($cacheDir, 0755) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
		
	}
	
	function check_cache_file($url) {
		
		if ( file_exists($url) ) {
			if ( ($_SERVER['REQUEST_TIME'] - filemtime($url)) > '86400' ) { // 86400 seconds = 1 day
				return FALSE;
			}else{
				$result = file_get_contents($url);
				return $result;
			}
		}else{
			return FALSE;
		}
		
	}
	
}

class Lastfm_List {
	
	var $contents;
	
	function __construct() {
		
		$this->contents = '<ul class="lastfm_list lastfm">';
		
	}
	
	function add_element($name, $url, $imageUrl, $style, $plays = NULL) {
		
		if ( isset($plays) ) { // With information
			$this->contents .= "<li class='lastfm_item lastfm' style='$style'>
									<a href='$url'><img src='$imageUrl' alt='$name' title='$name' class='lastfm_image lastfm'  style='$style'/></a>
									<span class='lastfm_meta lastfm_name lastfm' style='$style'>$name<br />$plays</span>
								</li><br />";
		}else{ // Without information
			$this->contents .= "<li class='lastfm_item lastfm' style='$style'><a href='$url'><img src='$imageUrl' alt='$name' title='$name' class='lastfm_image lastfm'  style='$style'/></a></li>";
		}
		
	}
	
	function close_list() {
		
		$this->contents .= '</ul>';
		
	}
	
	function blank() { // Checks if the list is empty
		
		if ( $this->contents == '<ul class="lastfm_list lastfm"></ul>' ) {
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	
	function save_list($filename) { // Creates the cache file
		
		$file = fopen($filename, 'w');
		fwrite($file, $this->contents);
		fclose($file);
		
	}
	
}

##############################
# Main Functions
##############################

function lastfmartists_main($lastfm) {
	
	if ( function_exists('curl_init') ===  FALSE ) {
		return 'PHP 5 with cURL is required to run this app.';
	}
	
	if ( $lastfm->check_cache_dir(ROOT_DIRECTORY) === FALSE ) {
		return 'Cache Directory could not be created.  Please create the cache directory "' . LASTFM_DIRECTORY . '" in the Last.fm Artists plugin folder with CHMOD of 0755.';
	}
	
	$lastfm->select_feed();
	
	$cacheFile = ROOT_DIRECTORY . $lastfm->time . $lastfm->number . $lastfm->size . $lastfm->type . $lastfm->display . '.html';
	
	if ( $lastfm->time != 'recent' ) { // Recent artists has no cache file
		if ( $result = $lastfm->check_cache_file($cacheFile) ) {
			return $result;
		}
	}
	
	$feed = new Lastfm_Feed($lastfm->feed);
	$feed->load_contents();
	
	if ( $feed->contents == '' ) { 
		if ( file_exists($cacheFile) ) { // Uses the cache file if the feed is down
			$result = file_get_contents($cacheFile);
			return $result;
		}else{
			return 'No images to display.  The feed is down.';
		}
	}
	
	$list = new Lastfm_List();
	
	$xml = simplexml_load_string($feed->contents);
	
	$limit = $lastfm->number;
	$artists = array(''); // An array to put artists in so the same artist is not shown twice
	
	for ( $counter = 0; $counter < $limit; $counter++ ) {
		if ( $limit - $lastfm->number > $lastfm->numebr + 5 ) { // If the script skips over five entries, break operation
			break;
		}
		
		switch ($lastfm->time) {
			case 'weekly':
				$name = $xml->weeklyartistchart->artist[$counter]->name;
				$url = $xml->weeklyartistchart->artist[$counter]->url;
				$plays = $xml->weeklyartistchart->artist[$counter]->playcount . ' Plays';
				break;
			case 'recent':
				$name = $xml->recenttracks->track[$counter]->artist;
				$url = 'http://last.fm/music/' . urlencode($name);
				$date = intval($xml->recenttracks->track[$counter]->date['uts']); // Changes the date into an integer
				$plays = date('j M Y', $date) . '<br />' . date('g:i a', $date);  // Date on one line, time on the other
				break;
			case 'loved':
				$name = $xml->lovedtracks->track[$counter]->artist->name;
				$url = $xml->lovedtracks->track[$counter]->artist->url;
				$date = intval($xml->lovedtracks->track[$counter]->date['uts']); // Changes the date into an integer
				$plays = date('j M Y', $date) . '<br />' . date('g:i a', $date);  // Date on one line, time on the other
				break;
			default:
				$name = $xml->topartists->artist[$counter]->name;
				$url = $xml->topartists->artist[$counter]->url;
				$plays = $xml->topartists->artist[$counter]->playcount . ' Plays';
				break;
		}
		
		$specialChars = array(' ','+','?','!','@','#','$','%','^','&','*','(',')','{','}','<','>','[',']','|','"',"'",';',',','.','/','~','`','=',':');
		$sinName = str_replace($specialChars, '', $name); // Removes special characters
		
		if ( in_array($sinName, $artists) ) { // Checks if the artists is in the array. If yes, then the artist is skipped
			$limit++;
			continue;
		}
		array_push($artists, $sinName); // Puts the artist in the array so the artists is not shown twice
		
		$image = new Lastfm_Image();
		
		if ( $image->check_cache($sinName, $lastfm->size, $lastfm->type) ) {
			if ( $lastfm->display == 'yes' ) {
				$list->add_element($name, $url, $image->image, $lastfm->style, $plays);
			}else{
				$list->add_element($name, $url, $image->image, $lastfm->style);
			}
			continue;
		}
		
		$image->get_image($name, $lastfm->size, $lastfm->time, $counter, $xml);
		
		if ( $image->check_image() === FALSE ) { // Checks if the image is blank. If yes, then the artist is skipped
			$limit++;
			continue;
		}
		
		if ( $image->create_image($sinName, $lastfm->size, $lastfm->type ) === FALSE ) {
			return 'Could not create images.';
		}
		
		if ( $lastfm->display == 'yes' ) {
			$list->add_element($name, $url, $image->image, $lastfm->style, $plays);
		}else{
			$list->add_element($name, $url, $image->image, $lastfm->style);
		}
	}
	
	$list->close_list();
	
	if ( $list->blank() ) {
		return "No images to display.";
	}
	
	$list->save_list($cacheFile);
	
	return $list->contents;
	
}

function lastfmartists_display($args = NULL) { // For non-Worpress users
	
	if( isset($args) ) {
		$lastfm = new Lastfm($args);
	}else{
		$lastfm = new Lastfm();
	}
	
	$results = lastfmartists_main($lastfm);
	
	echo $results;
	
}

function lastfmadmin_init() { // Wordpress admin page
	
	$admin = dirname(__FILE__) . '/admin.php';
	
    add_submenu_page('plugins.php', 'Last.fm Artists Plugin', 'Last.fm Artists', 8, $admin);
	
}

function lastfm_filter($content) { // For Wordpress post
	
	while (strpos($content, '{lastfmartists') !== false) {
		$args['style'] = "float:none!important; display: inline!important;";
		$args['display'] = 'no';
		// { lastfmartists | number | time | size | type }		
		if (eregi('\{lastfmartists\|([0-9]+)\|([0-9a-z]+)\|([0-9]+)\|([a-z]+)\}', $content, $out)) {
			$args['number']  = $out[1];
			$args['time'] = $out[2];
			$args['size'] = $out[3];
			$args['type'] = $out[4];
			
			ob_start();
			lastfmartists_display($args);
			$result = ob_get_contents();
    		ob_clean();
		}elseif (eregi('\{lastfmartists\|([0-9]+)\|([0-9a-z]+)\|([0-9]+)\}', $content, $out)) {
			$args['number']  = $out[1];
			$args['time'] = $out[2];
			$args['size'] = $out[3];
			
			ob_start();
			lastfmartists_display($args);
			$result = ob_get_contents();
    		ob_clean();
		}elseif (eregi('\{lastfmartists\|([0-9]+)\|([0-9a-z]+)\}', $content, $out)) {
			$args['number']  = $out[1];
			$args['time'] = $out[2];
			
			ob_start();
			lastfmartists_display($args);
			$result = ob_get_contents();
    		ob_clean();
		}elseif (eregi('\{lastfmartists\|([0-9]+)\}', $content, $out)) {
			$args['number']  = $out[1];
			
			ob_start();
			lastfmartists_display($args);
			$result = ob_get_contents();
    		ob_clean();
		}else{
			ob_start();
			lastfmartists_display($args);
			$result = ob_get_contents();
    		ob_clean();
		}
		
		$content = preg_replace('/{lastfmartists.*}/', $result, $content);
	}
	return $content;
	
}

function lastfm_style() { // For Wordpress Themes
	
	$data = get_option(OPTIONS_NAME);
	echo '<!-- Style added by Last.fm Artists -->';
	?>
	<style type="text/css">
		<?php echo $data['style']; ?>
	</style>
	<?php
	
}

function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
  // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
  // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
  // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
  // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
  //
  // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
  // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
  // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
  // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
  // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
  // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
  // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

  if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }
  if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
    $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
    imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
    imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
    imagedestroy ($temp);
  } else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  return true;
}

##############################
# Widget Functions
##############################

function lastfmwidget($args) { // Main widget function
	
	extract($args);
	$data = get_option(OPTIONS_NAME);
	echo $before_widget . $before_title . $data['title'] . $after_title;
	
	if ( is_admin() === FALSE ) {
		$lastfm = new Lastfm();
		$results = lastfmartists_main($lastfm);
		echo $results;
	}
	
	echo $after_widget;
	
}

function lastfmwidget_init() {
	
	register_sidebar_widget('Last.fm', 'lastfmwidget');
	register_widget_control('Last.fm', 'lastfmwidget_control');
	
}

function lastfmwidget_control() { // The settings in the widget box
	
	$data = get_option(OPTIONS_NAME);
	
	if ( isset($_POST['lastfmArtistsSubmit']) ) {
		$data['title'] = strip_tags(stripslashes($_POST['lastfm_title']));
		update_option(OPTIONS_NAME, $data);
	}
	
	?>
	
	<p>
		<label for="lasfm_title">Widget Title: </label>
		<input type="text" name="lastfm_title" value="<?php echo $data['title']; ?>" />
		<input type="hidden" id="lastfmArtistsSubmit" name="lastfmArtistsSubmit" value="1" />
	</p>
	
	<?php
	
}

##############################
# Wordpress Hooks/Filters
##############################

if ( function_exists('add_action') ) {
	add_action('plugins_loaded', 'lastfmWidget_init');
	add_action('admin_menu', 'lastfmadmin_init');
	add_action('wp_head', 'lastfm_style');
}

if ( function_exists('add_filter') ) {
	add_filter('the_content', 'lastfm_filter');
}
?>