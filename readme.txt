=== CSV Posts Importer ===
Contributors: groupbwt
Tags: importer, csv, acf, post
Requires at least: 3.7
Tested up to: 4.7.2
Stable tag: 1.0.0
License: GPLv2 or later

Plugin imports posts from CSV file, allowing to connect CSV field with post field when creating a post.

== Description ==

Plugin imports posts from CSV file, allowing to connect CSV field with post field when creating a post.

* Custom Post Type support
* Advanced Custom Fields (ACF) support

= Available fields: =

* Post Content: (string) The post content. Default empty.
* Post Title: (string) The post title. Default empty.
* Comment Status: (string) Defines if the post can accept comments. Accepts 'open' or 'closed'. Default is the value of 'default_comment_status' option.
* Post Category: (string, comma separated) Post category names. Defaults to value of the 'default_category' option.
* Featured Image: (string) The uri of the post thumbnail. Default is empty.
* ID: (int) The post ID. If the ID does not exist, importer will try to create a new post with suggested ID.
* Post Date: (string in format 'yyyy-mm-dd hh:mm:ss') Post date. Default is the current time.
* Post Date GTM: (string in format 'yyyy-mm-dd hh:mm:ss') The date of the post in the GMT timezone. Default is the value of Post Date.
* Post Content Filtered: (string) A filtered post content. Default is empty.
* Post Excerpt: (string) The post excerpt. Default is empty.
* Post Status: (string) The post status. Default is 'published'.
* Ping Status: (string) Defines if the post can accept pings. Accepts 'open' or 'closed'. Default is the value of 'default_ping_status' option.
* Post Password: (string) The password to access the post. Default is empty.
* Slug: (string) The post name. Default is the sanitized post title when creating a new post.
* To Ping: (string) Space or carriage return-separated list of URLs to ping. Default empty.
* Pinged: (string) Space or carriage return-separated list of URLs that have been pinged. Default is empty.
* Post Parent: (int) Set this for the post it belongs to, if any. Default is 0.
* Menu Order:(int) The order number your want for the post to be displayed. Default is 0.
* GUID: (string) Global Unique ID for referencing the post. Default is empty.
* Taxonomy: (JSON) JSON of taxonomy terms keyed by their taxonomy name. Taxonomy must already exist. Default is empty.
* Meta: (JSON) JSON of post meta values keyed by their post meta key. Default is empty.

== Installation ==

1. Upload the entire `bwt-csv-importer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'CSV Importer' menu in your WordPress admin panel.
