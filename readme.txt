=== Last.fm Artists ===
Contributors: thegary
Tags: last.fm, charts, artists, lastfm, widget, music, sidebar, image, recent, weekly, images, overall, listen
Requires at least: 2.0
Tested up to: 2.7
Stable tag: 2.0.2

Last.fm Artists is a Wordpress plugin that allows a user to display images of their top artists on their blog.

== Description ==

**Requires PHP5.**

Last.fm Artists is a Wordpress plugin that allows a user to display images of their top artists, recently lisetened to artists, and the artists of their recently loved tracks.  It will download the images of the artists, resize them, and display them in a list so they can be put on their blog.

**Features:**
Show your top artists of all time
Show your top artists over a period of the last 7 days, 3 months, 6 month, or 1 year.
Show your recently listened to artistsShow the artists of your recently loved tracks
Show the images as either a square or wide rectangle
Display information about an artist and your playcount or time last played of the artist
Edit the CSS or choose from several pre-built styles

There also is a standalone version avaiable [here](http://finalstar.net/lastfm).

== Installation ==

Full instructions on usage [here](http://finalstar.net/lastfm/instructions).

== Future Features ==

* Upload your own images
Have suggestions for future features? [Contact me](http://finalstar.net/contact).

== Change Log ==

+ **v 2.0.2:**
  * Removed CURLOPT_FOLLOWLOCATION
  * Added Support section
  * Sanitized inputs
+ **v 2.0.1:**
  * Set CURLOPT_FOLLOWLOCATION to zero
+ **v 2.0:**
  * Complete rewrite of the backend
  * Added the ability to display wide images
  * Added the ability to display artists' name and playcount/time played
  * Added "Recently Listened To" support
  * Added "Loved Tracks" support
  * CSS support with prebuilt styles
  * Reworked the cache so it will work on all servers with the necessary settings
  * Reworked the way urls are handled so that it will work on all servers with the necessary settings
  * Better explainations on the admin page
  * Faster Overall
+ **v 1.0.11:**
  * Changed the way Last.fm Artists handles remote URLs for hosts who don't have `allow_url_fopen` on
+ **v 1.0.10:**
  * Fixed mkdir bug
+ **v 1.0.9:**
  * Fixed cache bug for non-standard urls
  * Fixed headers
+ **v 1.0.8:**
  * Fixed username bug
+ **v 1.0.7:**
  * Fixed issue of cache not clearing
+ **v 1.0.6:**
  * Fixed issue of blog post not appearing
+ **v 1.0.5:**
  * Added non-widget support
  * Added the ability to include in posts
  * Notification when the cache directory cannot be created
+ **v 1.0.4:**
  * Fixed issue between cache and ModRewrite
+ **v 1.0.3:**
  * Fixed cache issue
+ **v 1.0.2:**
  * Added the ability to create the cache folder automatically
+ **v 1.0.1:**
  * Fixed weekly issue
+ **v 1.0:**
  * Release!
  * Displays top artists images
  * Weekly support, 3 Month support, 6 Month support, 12 Month support, Overall support
  * Widget