=== Facebook Page Publish ===
Contributors: mtschirs
Tags: post, Facebook, page, profile, publish
Requires at least: 3.0
Tested up to: 3.1
Stable tag: trunk

"Facebook Page Publish" publishes your blog posts to your Facebook profile or page.

== Description ==

"Facebook Page Publish" publishes your blog posts to the wall of your Facebook profile or page. Posts appear on the wall of your choice as if you would share a link. The authors [gravatar](http://gravatar.com), a self-choosen or random post image, the title, author, categories and a short excerpt of your post can be shown.

Decide yourself when and what post to publish. Local and remote publishing based e.g. on the post category.

Uses the modern Facebook graph-API and integrates easily into your WordPress Blog.

All you need do to is (see *Installation*):

* Create a [Facebook application](https://www.facebook.com/developers/createapp.php)

Technical features:

* 100% userfriendly, easy to install & remove
* Lightweight, clean code

== Installation ==

1. Install the plugin from your wordpress admin panel.

OR

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

Done? Then go to the plugin's settings page and follow the detailed setup instructions.

== Frequently Asked Questions ==

= I have a question, what should I do? =

Please post your question in the [forum](http://wordpress.org/tags/facebook-page-publish)!

== Screenshots ==

1. Check to publish your post to Facebook.
2. An example post on Facebook.
3. The settings page.

== Changelog ==

= 0.3.2 =
* Critical bugfix: fpp_get_post_image crashed when theme support for post thumbnails was not supported!

= 0.3.1 =
* Bugfix: Password protected posts: incorrect title and image was shown
* Bugfix: Shortcodes are now processed and no longer (incompletely) stripped (thanks to *cntrlwiz*!)
* Bugfix: diagnosis script URL now correct
* Bugfix: Author name now taken from first / last name, if those are not empty (thanks to *cntrlwiz*!)
* Bugfix: Timeout for http requests now 20s, 5s was too short on some servers (thanks to *misterjoecity*!)
* Bugfix: Fixed error in fpp_acquire_profile_access_token (thanks to *misterjoecity*!!)
* Update: Diagnostic script detects SSL availability and https connections (thanks to *mioto*!)
* Update: New settings introduced: disallow publishing of post excerpt, include links
* Update: Thumbnail from post: use featured thumbnail, if available (thanks to *Luis Marcos Loaiza*!)
* Update: Profile and page ID's are now automatically detected, major GUI redesign

= 0.3.0 =
* Update: Publishes to a page or profile
* Update: More userfriendly error reporting
* Update: New settings introduced: publishing policy (thanks to *Li-An*!) and appearance customization.
* Major bugfixes: Scheduled and remote posts (thanks to *ksoszka*!), posting as password-protected, private or draft (thanks to *tbjers*!)

= 0.2.2 =
* Bugfix: <!--more--> tags now recognized (thanks to *tbjers*!).
* Bugfix: Apostrophes (') no longer slashed (thanks to *dmeglio*!).
* Update: SSL_VERIFY and ALWAYS_POST_TO_FACEBOOK constants for manual configuration.

= 0.2.1 =
* Bugfix: Not all images in a post where found.
* Bugfix: Default transparent image prevents FB from choosing a poor random image for posts containing no images.
* Bugfix: Graph meta tags are now only rendered when displaying a single post.
* Update: Detailed setup instructions now available from the options page.

= 0.2.0 =
* Security: Only authors can publish to Facebook.
* Bugfix: Only posts can be published (no pages etc.).
* Bugfix: Character encoding for categories and title fixed.
* Bugfix: Facebook link description length is 420 chars max.

= 0.1.0 =
* First internal alpha release.

== Upgrade Notice ==

= 0.3.2 =
Critical bugfixes, upgrade strongly recommended.

= 0.3.1 =
Updates, bugfixes, upgrade recommended.

= 0.3.0 =
Major update and bugfixes, upgrade strongly recommended.


= 0.2.2 =
Bugfixes, upgrade recommended.

= 0.2.1 =
Bugfixes, upgrade recommended.