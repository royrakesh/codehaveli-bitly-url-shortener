<p align="center">
  <img src="plugin-logo.png?raw=true" alt="Bitly URL Shortener Logo">
</p>

# Bitly URL Shortener

## Description

**Bitly URL Shortener** is a WordPress plugin that integrates with the Bitly API to automatically generate short links for your posts directly from your WordPress dashboard. Easily connect your Bitly account and manage short URLs without leaving your site.

- Generate Bitly short URLs for posts and custom post types.
- View and copy short URLs from the post editor, admin bar, or sidebar.
- Social sharing buttons for Facebook, X (Twitter), and Email.
- Gutenberg block support for social icons.
- Secure and privacy-friendly: stores only the minimum required data.

## Installation

1. Download and extract the plugin zip or clone this repository into your `wp-content/plugins` directory.
2. Activate the plugin from the WordPress Plugins menu.
3. Go to **Tools > Codehaveli Bitly** to configure your Bitly Access Token and Group GUID.

## Usage

- After activation and setup, publish a post to automatically generate a Bitly short URL.
- The short URL will appear in the post sidebar, metabox, and admin bar (for allowed roles).
- Use the "Generate Bitly URL" button if a short URL is not yet created.
- Enable social sharing buttons from the plugin settings.

## Settings

- **Access Token:** Your Bitly OAuth access token. [How to generate?](https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/)
- **Group GUID:** Your Bitly group GUID. Click "Get GUID" after saving your access token.
- **Domain:** (Optional) Custom Bitly domain (leave blank for default `bit.ly`).
- **Enable Social Share Button:** Show share buttons on post edit/list screens.
- **Post Types:** Select which post types should have Bitly short URLs.

## Developer Notes

- REST API endpoints are available for generating and retrieving short URLs.
- Plugin follows WordPress coding standards and supports PHPStan and PHPCS for code quality.
- Assets are built using `@wordpress/scripts` and Webpack.

## Contributing

Pull requests and bug reports are welcome! Please use [GitHub Issues](https://github.com/codehaveli/codehaveli-bitly-url-shortener/issues) for bug reports.

## License

GPL v2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Credits

Developed by [Codehaveli](https://www.codehaveli.com/).

---

**Disclaimer:** This plugin is not an official Bitly product. Please review [Bitly's privacy policy](https://bitly.com/pages/privacy) and [terms of service](https://bitly.com/pages/terms-of-service) before use.