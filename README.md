# Bitly URL Shortener for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.6%2B-blue.svg)](https://wordpress.org/plugins/codehaveli-bitly-url-shortener/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**Bitly URL Shortener** seamlessly integrates your WordPress site with the Bitly API. Automatically generate short links when publishing posts, create short links for existing content, share posts using integrated social sharing options, and access developer-friendly APIs and WP-CLI commands.

## 🚀 Features

* **Automated Short Link Generation** — Automatically creates Bitly short URLs when new posts are published.
* **One-Click Generation** — Generate short links for existing or older posts directly from the WordPress post list.
* **Gutenberg Social Share Block** — Embed social sharing buttons for Facebook, LinkedIn, X/Twitter, Telegram, and WhatsApp.
* **Custom Post Type Support** — Select which post types should automatically generate Bitly short links.
* **Custom Bitly Domain Support** — Supports branded custom Bitly domains available with eligible Bitly plans.
* **Admin Meta Box** — Quickly access and manage short links from the post editor.
* **Post List Column** — View and generate short links directly from WordPress list tables.
* **WP-CLI Integration** — Bulk-generate short links from the command line.
* **REST API Endpoints** — Programmatically generate and retrieve short links.

---

## 🛠️ System Requirements

* **WordPress:** 5.6 or higher
* **Tested up to:** WordPress 7.1.2
* **PHP:** 7.4 or higher
* **Bitly Account:** Required

---

## 📦 Installation

### From the WordPress Dashboard

1. Navigate to **Plugins → Add New**.
2. Search for **Bitly URL Shortener**.
3. Click **Install Now**.
4. Activate the plugin.
5. Go to **Tools → Codehaveli Bitly**.
6. Enter your Bitly OAuth access token.
7. Click **Get GUID** to retrieve your Bitly Group GUID automatically.
8. Optionally configure:

   * Custom Bitly domains
   * Default post types
   * Automatic short-link generation
   * Social sharing preferences

### Manual Installation

1. Download the plugin ZIP file.
2. Navigate to **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP file.
4. Click **Install Now**.
5. Activate the plugin.
6. Configure the plugin from **Tools → Codehaveli Bitly**.

---

## 🔐 Bitly Configuration

To use the plugin, you need a valid Bitly OAuth access token.

1. Log in to your Bitly account.
2. Generate an OAuth access token.
3. Copy the generated token.
4. Open **Tools → Codehaveli Bitly** in your WordPress dashboard.
5. Paste the token into the access token field.
6. Click **Get GUID** to retrieve your Bitly Group GUID.
7. Save the settings.

For detailed instructions, see the [OAuth Token Guide](https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/).

---

## 🔗 Automatic Short Link Generation

The plugin can automatically generate Bitly short links when supported content is published.

You can configure which post types should automatically generate short links from the plugin settings.

Typical examples include:

* Posts
* Pages
* Custom post types

Once generated, the short URL is associated with the corresponding WordPress post.

---

## ✨ Generate Short Links Manually

Short links can also be generated manually for existing content.

### From the Post Editor

1. Open the post or page.
2. Locate the **Bitly URL Shortener** meta box.
3. Generate or retrieve the short URL.
4. Copy or share the generated Bitly link.

### From the Post List

You can generate short links directly from supported WordPress post list screens.

This is useful when working with older content that was published before the plugin was installed or configured.

---

## 📖 Developer Guide

### PHP Function

Retrieve the short URL programmatically within your theme or custom plugin.

#### Retrieve a short link for a specific post

```php
$short_url = get_wbitly_short_url( $post_id );
```

#### Retrieve a short link for the current post

```php
$short_url = get_wbitly_short_url();
```

The function can be used in themes, custom plugins, templates, and other WordPress integrations.

Example:

```php
$short_url = get_wbitly_short_url();

if ( $short_url ) {
    echo esc_url( $short_url );
}
```

---

## 💻 WP-CLI Commands

The plugin provides WP-CLI commands for generating short links in bulk.

### Generate short links for all published posts

```bash
wp wbitly generate --all
```

### Generate short links for specific post IDs

```bash
wp wbitly generate --ids=1,2,3
```

### Generate short links for the first 10 posts

```bash
wp wbitly generate --first=10
```

### Generate short links for all pages

```bash
wp wbitly generate --all --post_type=page
```

### Example for a custom post type

```bash
wp wbitly generate --all --post_type=product
```

> **Note:** Run bulk operations carefully on large websites, as generating a large number of links may take time and consume Bitly API quota.

---

## 🌐 REST API

The plugin exposes custom WordPress REST API endpoints for programmatic access.

### Authentication Requirements

The endpoints require:

* A user with the `edit_posts` capability
* A valid WordPress authentication session
* A valid `X-WP-Nonce` header

### Generate a Short URL

```text
POST /wp-json/wbitly/v1/generate/{post_id}
```

Example:

```text
POST /wp-json/wbitly/v1/generate/123
```

### Fetch an Existing Short URL

```text
GET /wp-json/wbitly/v1/meta/{post_id}
```

Example:

```text
GET /wp-json/wbitly/v1/meta/123
```

Replace `{post_id}` with the ID of the WordPress post.

---

## 🎨 Gutenberg Block

The plugin includes a custom Gutenberg block for displaying social sharing buttons.

### Bitly Share Icons

To use the block:

1. Open the WordPress block editor.
2. Add the **Bitly Share Icons** block.
3. Select the social platforms you want to display.
4. Configure the available styling, size, and layout options.
5. Publish or update the content.

Supported platforms include:

* Facebook
* LinkedIn
* X/Twitter
* Telegram
* WhatsApp

The block uses the generated Bitly short URL when available.

---

## ⚙️ Configuration

The plugin settings allow you to configure features such as:

* Bitly OAuth access token
* Bitly Group GUID
* Custom Bitly domain
* Automatic short-link generation
* Supported post types
* Social sharing options

Access the plugin settings from:

```text
Tools → Codehaveli Bitly
```

---

## 🏷️ Custom Bitly Domain

If your Bitly account supports branded domains, you can configure a custom Bitly domain.

This allows generated links to use your branded domain instead of the default `bit.ly` domain.

Example:

```text
bit.ly/example-link
```

With a branded domain:

```text
yourbrand.co/example-link
```

Custom domains are subject to Bitly account and plan availability.

---

## 🔒 Security

The plugin uses WordPress capability checks and nonce validation for protected administrative and REST API operations.

You should:

* Keep WordPress updated.
* Keep the plugin updated.
* Protect your Bitly OAuth access token.
* Avoid exposing API tokens in public repositories.
* Use appropriate WordPress user permissions.

---

## ❓ Frequently Asked Questions

### Do I need a Bitly account?

Yes. A Bitly account and a valid OAuth access token are required.

### Can I generate links for existing posts?

Yes. You can generate links manually from the WordPress dashboard or in bulk using WP-CLI.

### Does the plugin support custom post types?

Yes. You can configure supported post types for automatic short-link generation.

### Can I use a branded Bitly domain?

Yes, provided your Bitly account and plan support branded domains.

### Can developers access the generated short URL?

Yes. You can use:

* The `get_wbitly_short_url()` PHP function
* The REST API endpoints
* WP-CLI commands

### Does the plugin support the Gutenberg editor?

Yes. The plugin includes the **Bitly Share Icons** Gutenberg block.

---

## 🐛 Troubleshooting

### Short links are not being generated

Check the following:

1. Verify that your Bitly OAuth token is valid.
2. Confirm that the Bitly Group GUID is configured correctly.
3. Ensure the post type is enabled in the plugin settings.
4. Check that the post is being published rather than saved as a draft.
5. Review WordPress and server logs for API errors.

### WP-CLI command is not available

Ensure that:

* WP-CLI is installed correctly.
* The plugin is active.
* You are running the command from the correct WordPress installation.

Example:

```bash
wp plugin is-active codehaveli-bitly-url-shortener
```

---

## 🤝 Contributing

Contributions, bug reports, and feature requests are welcome.

To contribute:

1. Fork the repository.
2. Create a feature or bug-fix branch.
3. Make your changes.
4. Test your changes.
5. Submit a pull request.

Please ensure that contributions follow WordPress coding standards where applicable.

---

## 🔗 Official Links

* **WordPress Plugin Page:** [Bitly URL Shortener on WordPress.org](https://wordpress.org/plugins/codehaveli-bitly-url-shortener/)
* **GitHub Repository:** [royrakesh/codehaveli-bitly-url-shortener](https://github.com/royrakesh/codehaveli-bitly-url-shortener)
* **Official Homepage:** [Codehaveli](https://www.codehaveli.com/)
* **Bitly:** [bitly.com](https://bitly.com)

---

## 📜 Terms & Disclaimer

* This is an unofficial plugin and is not directly affiliated with or endorsed by Bitly.
* The plugin requires access to Bitly services and APIs.
* Bitly services are subject to their own availability, API limits, pricing, and terms.
* Custom branded domains may require a paid Bitly plan.

Before using Bitly, please review:

* [Bitly Privacy Policy](https://bitly.com/pages/privacy)
* [Bitly Terms of Service](https://bitly.com/pages/terms-of-service)

---

## 🐛 Bug Reports & Support

For bug reports, feature requests, and technical issues, please use the [GitHub Repository](https://github.com/royrakesh/codehaveli-bitly-url-shortener).

For setup guides and documentation, visit [Codehaveli](https://www.codehaveli.com/).

---

## 📄 License

Distributed under the **GPLv2 or later** License.

This plugin is licensed under the terms of the GNU General Public License, version 2 or later.

See the `LICENSE` file for more information.

---

**Made for WordPress users who want simple, automated Bitly short-link management.**
