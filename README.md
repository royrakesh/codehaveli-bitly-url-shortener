=== Bitly URL Shortener ===
Contributors: codehaveli,royrakesh
Tags: Bitly, Short url, Url shortener, post, connector, social share, gutenberg
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
Donate link: https://www.paypal.com/paypalme/royrakesh92
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bitly URL Shortener uses the functionality of Bitly API to generate Bitly short link without leaving your WordPress site.

== Description ==

**Bitly URL Shortener** (Previously Codehaveli Bitly URL Shortener) uses the functionality of Bitly API to generate bitly short link automatically from your WordPress dashboard when you publish new post.

Bitly URL Shortener allows you to connect your WordPress Website to the Bitly API via access token and Group GUID.

### Features 

* Generate Bitly link automatically when publishing posts without leaving your site.
* Share Bitly link from your Post List and Post Edit page with social share buttons.
* Gutenberg block for displaying social share icons (Facebook, LinkedIn, X/Twitter, Telegram, WhatsApp).
* Support for Custom Post Types with option to choose from your list of post types.
* Support for Custom Bitly Domain (for paid Bitly plans).
* Generate Bitly link of your old posts with just one click from post list.
* WP-CLI support for bulk generating short links via command line.
* REST API endpoints for programmatic access to short URL generation.
* Post column in admin showing generated short URLs.
* Metabox in post edit page for quick access to short URL and share options.
* Optimized code with modern PHP 7.4+ features and proper error handling.


### Terms of Use 

This is not an official plugin of [https://bitly.com](https://bitly.com)

This plugin only connects your [https://bitly.com](https://bitly.com) account to your WordPress site.

Please read [privacy](https://bitly.com/pages/privacy) and [terms of service](https://bitly.com/pages/terms-of-service) of [Bitly](https://bitly.com) before using this plugin.



### Bug reports

Bug reports for Bitly URL Shortener are welcomed in our Bitly URL Shortener [repository on GitHub](https://github.com/royrakesh/codehaveli-bitly-url-shortener). Please note that GitHub is not a support forum, and that issues that are not properly qualified as bugs will be closed.

### Further Reading

For more info on Bitly and Codehaveli, check out the following:

* [Codehaveli](https://www.codehaveli.com/) official homepage
* Read "How to generate Bitly OAuth access token?" from [Codehaveli Blog](https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/)
* Bitly [API Documentation](https://bitly.is/2XxT9BN) 
* Follow Codehaveli on [Facebook](https://www.facebook.com/codehaveli), [Instagram](https://www.instagram.com/codehaveli/) & [Twitter](https://twitter.com/codehaveli)
* Plugin [GitHub Repository](https://github.com/royrakesh/codehaveli-bitly-url-shortener)


== Installation ==

1. Visit the plugins page within your dashboard and select Add New
1. Search for **Bitly URL Shortener**
1. Activate Bitly URL Shortener from your Plugins page
1. Go to **Tools > Codehaveli Bitly** from your WordPress admin menu
1. Get your access token from [https://bitly.com](https://bitly.com) and save it in the access token field
   * Need help? Check our guide: [How to generate Bitly OAuth access token?](https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/)
1. Click on **Get GUID** button if you don't have the Group GUID (the plugin will retrieve it from your Bitly account via API call)
1. (Optional) Enter your custom Bitly domain if you're on a paid plan
1. (Optional) Select which post types should automatically generate Bitly links
1. (Optional) Enable social share buttons if you want to display share options
1. You're ready to go! From now on, whenever you publish a post, your Bitly link will be generated automatically



== Frequently Asked Questions ==

= How to get the short link of a post? =

Use the `get_wbitly_short_url()` function in your theme or plugin:

`$link = get_wbitly_short_url($post_id); // Returns short URL or false`

If no post ID is provided, it will use the current post in the loop:

`$link = get_wbitly_short_url(); // Uses current post`

= How to use the Gutenberg block? =

1. In the block editor, search for "Bitly Share Icons" block
2. Add the block to your post or page
3. Customize which social icons to display (Facebook, LinkedIn, X/Twitter, Telegram, WhatsApp)
4. Adjust icon size and styling as needed

= How to generate short links via WP-CLI? =

The plugin includes WP-CLI support. Use the following commands:

`wp wbitly generate --all` - Generate shortlinks for all published posts
`wp wbitly generate --ids=1,2,3` - Generate for specific post IDs
`wp wbitly generate --first=10` - Generate for first 10 posts
`wp wbitly generate --all --post_type=page` - Generate for all pages

= How to use the REST API? =

The plugin provides REST API endpoints:

* `POST /wp-json/wbitly/v1/generate/{post_id}` - Generate short URL for a post
* `GET /wp-json/wbitly/v1/meta/{post_id}` - Get existing short URL for a post

Both endpoints require authentication and appropriate permissions. You need to:
* Be logged in as a user with `edit_posts` capability
* Include a valid WordPress nonce in the request header (`X-WP-Nonce`)



== Screenshots ==
1. Settings Panel - Configure your Bitly API credentials and options
2. Settings access from Tools menu or plugin list
3. Generated URL column in post list showing short links
4. Share button in post list for quick sharing
5. Custom Post type selection checkboxes
6. Social share icons block in Gutenberg editor
7. Metabox in post edit page with short URL and share options
8. Copy short link from front end


== Changelog ==

= 1.5.0 =
* Added Gutenberg block for social share icons
* Added WP-CLI support for bulk generating short links
* Added REST API endpoints for programmatic access
* Improved code structure with modern PHP 7.4+ features
* Enhanced error handling and logging
* Updated minimum requirements: PHP 7.4, WordPress 5.6
* Improved security with better input validation
* Code refactoring and optimization

= 1.4.1 = 
* Updated: tested up to value
* Security Fix
* Updated Twitter logo to X
* Refactor ajax code

= 1.3.3 = 
* Updated: tested up to value

= 1.3.2 = 
* Updated: tested up to value
* Add support for [ Yoast Duplicate Post ](https://wordpress.org/plugins/duplicate-post/ )

= 1.3.1 = 
* PHP Warning: Fixed

= 1.2.2 =
* Name changed from `Codehaveli Bitly URL Shortener` to `Bitly URL Shortener` due to SEO conflict with codehaveli.com 

= 1.2.1 =
* Support WordPress Core Shortlinks Filters.
* Shortener link from Front end.
* Code optimized.

= 1.1.4 =
* Meta Box added in Post Edit Page, now you can share link from your post page
* Some security issue fixed.

= 1.1.3 =
* Custom Post Type Selection Added and default set to 'post'.
* Small bug fixed

= 1.1.2 =
* Custom Post Type Support Added

= 1.1.1 =
* Versioning fixed. 
* Code optimized. 

= 1.1 =
* Option added to share link from post list
* Generate old posts link from post list
* Function refactored
* Error handler added
* Some bug fixed

= 1.0 =
* Initial Release
