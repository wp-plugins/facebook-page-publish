=== Facebook Page Publish ===
Contributors: mtschirs
Tags: posts, facebook, page, publish
Requires at least: 3.0
Tested up to: 3.1
Stable tag: trunk

"Facebook Page Publish" publishes your blog posts to your fan page.

== Description ==

"Facebook Page Publish" publishes your blog posts to your fan page! Posts appear on the wall of your fan page as if you would share a link. The authors [gravatar](http://gravatar.com) or a random post image, the title, categories and a short excerpt of your post are shown.

Decide yourself when and what post to publish to your page.

Uses the modern facebook graph-API and integrates easily into your WordPress Blog.

All you need is (see *Installation*):

* A facebook application (application ID + secret)
* A facebook fan page (page ID)

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

Please use the forum.

== Screenshots ==

1. The settings page.
2. Check to publish your post to your page.
3. An example post on facebook.

== Changelog ==

= 0.1.0 =
* First internal alpha release.

= 0.2.0 =
* Security: Only authors can publish to facebook.
* Bugfix: Only posts can be published (no pages etc.).
* Bugfix: Character encoding for categories and title fixed.
* Bugfix: Facebook link description length is 420 chars max.

= 0.2.1 =
* Bugfix: Not all images in a post where found.
* Bugfix: Default transparent image prevents FB from choosing a poor random image for posts containing no images.
* Bugfix: Graph meta tags are now only rendered when displaying a single post.
* Update: Detailed setup instructions now available from the options page.

= 0.2.2 =
* Bugfix: <!--more--> tags now recognized (thanks to *tbjers*!).
* Bugfix: Apostrophes (') no longer slashed (thanks to *dmeglio*!).
* Update: SSL_VERIFY and ALWAYS_POST_TO_FACEBOOK constants for manual configuration.

= 0.2.3 =
* Bugfix: scheduled posts handled correctly now (thanks to *ksoszka*!).

== Upgrade Notice ==

= 0.2.1 =
Bugfixes, upgrade recommended.

= 0.2.2 =
Bugfixes, upgrade recommended.