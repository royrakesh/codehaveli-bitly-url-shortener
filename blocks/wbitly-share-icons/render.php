<?php

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */

if (! is_singular('post')) {
    return '';
}

$post_id = get_the_ID();
if (! $post_id) {
    return '';
}

$short_url = get_wbitly_short_url($post_id);
if (empty($short_url)) {
    return '';
}

// Block attributes
$icons       = $attributes['showIcons'] ?? [];
$icon_size   = $attributes['iconSize'] ?? '24';
$custom_size = $attributes['customSize'] ?? 24;
$size        = $icon_size === 'custom' ? (int) $custom_size : (int) $icon_size;
$fill_color  = $attributes['fillColor'] ?? null;

// Wrapper function to inject width/height into SVGs
function svg_with_size($svg, $size, $fill_color = null)
{
    // Step 1: Replace width and height attributes
    $svg = str_replace(
        ['width="100"', 'height="100"'],
        ['width="' . esc_attr($size) . '"', 'height="' . esc_attr($size) . '"'],
        $svg
    );

    // Step 2: If no fill color, return as-is
    if ($fill_color === null) {
        return $svg;
    }

    // Step 3: Match all <path> tags
    preg_match_all('/<path\b[^>]*>/i', $svg, $matches);

    if (empty($matches[0])) {
        return $svg; // no <path> tags found
    }

    $paths = $matches[0];
    $path_count = count($paths);

    // Target first <path> or only <path>
    $target_path = $paths[0];

    // Replace or inject fill in that tag
    if (preg_match('/fill=["\'].*?["\']/', $target_path)) {
        // Replace existing fill attribute
        $new_path = preg_replace(
            '/fill=["\'].*?["\']/',
            'fill="' . esc_attr($fill_color) . '"',
            $target_path
        );
    } else {
        // Insert fill attribute before closing '>'
        $new_path = preg_replace(
            '/<path\b([^>]*)\/?>/i',
            '<path$1 fill="' . esc_attr($fill_color) . '">',
            $target_path
        );
    }

    // Replace the original path tag in the SVG
    $svg = preg_replace('/' . preg_quote($target_path, '/') . '/', $new_path, $svg, 1);

    return $svg;
}




// SVG ICON MAP
$svgs = [
    'facebook' => <<<SVG
<!-- Facebook -->
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 48 48">
<path fill="#039be5" d="M24 5A19 19 0 1 0 24 43A19 19 0 1 0 24 5Z"></path>
<path fill="#fff" d="M26.572,29.036h4.917l0.772-4.995h-5.69v-2.73c0-2.075,0.678-3.915,2.619-3.915h3.119v-4.359c-0.548-0.074-1.707-0.236-3.897-0.236c-4.573,0-7.254,2.415-7.254,7.917v3.323h-4.701v4.995h4.701v13.729C22.089,42.905,23.032,43,24,43c0.875,0,1.729-0.08,2.572-0.194V29.036z"></path>
</svg>
SVG,
    'linkedin' => <<<SVG
<!-- LinkedIn -->
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 48 48">
<path fill="#0288D1" d="M42,37c0,2.762-2.238,5-5,5H11c-2.761,0-5-2.238-5-5V11c0-2.762,2.239-5,5-5h26c2.762,0,5,2.238,5,5V37z"></path>
<path fill="#FFF" d="M12 19H17V36H12zM14.485 17h-.028C12.965 17 12 15.888 12 14.499 12 13.08 12.995 12 14.514 12c1.521 0 2.458 1.08 2.486 2.499C17 15.887 16.035 17 14.485 17zM36 36h-5v-9.099c0-2.198-1.225-3.698-3.192-3.698-1.501 0-2.313 1.012-2.707 1.99C24.957 25.543 25 26.511 25 27v9h-5V19h5v2.616C25.721 20.5 26.85 19 29.738 19c3.578 0 6.261 2.25 6.261 7.274L36 36 36 36z"></path>
</svg>
SVG,
    'x' => <<<SVG
<!-- X -->
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 50 50">
<path fill='#111' d="M11 4C7.134 4 4 7.134 4 11v28c0 3.866 3.134 7 7 7h28c3.866 0 7-3.134 7-7V11c0-3.866-3.134-7-7-7H11zm2.086 9h7.937l5.637 8.01L34.5 13h2.5l-8.21 9.613L37.91 37H29.98l-6.54-9.29L15.5 37h-2.5l9.31-10.896L13.086 13zm3.83 2l14.11 20h3.065L20.982 15h-4.066z"></path>
</svg>
SVG,
    'telegram' => <<<SVG
<!-- Telegram -->
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 48 48">
<path fill="#29b6f6" d="M24 4A20 20 0 1 0 24 44A20 20 0 1 0 24 4Z"></path>
<path fill="#fff" d="M33.95,15l-3.746,19.126c0,0-0.161,0.874-1.245,0.874c-0.576,0-0.873-0.274-0.873-0.274l-8.114-6.733
l-3.97-2.001l-5.095-1.355c0,0-0.907-0.262-0.907-1.012c0-0.625,0.933-0.923,0.933-0.923l21.316-8.468
c-0.001-0.001,0.651-0.235,1.126-0.234C33.667,14,34,14.125,34,14.5C34,14.75,33.95,15,33.95,15z"></path>
</svg>
SVG,
    'whatsapp' => <<<SVG
<!-- WhatsApp -->
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 48 48">
<path fill="#25D366" d="M4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5
c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98
c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303z"></path>
<path fill="#fff" d="M19.268,16.045c-0.355-0.79-0.729-0.806-1.068-0.82c-0.277-0.012-0.593-0.011-0.909-0.011
c-0.316,0-0.83,0.119-1.265,0.594c-0.435,0.475-1.661,1.622-1.661,3.956c0,2.334,1.7,4.59,1.937,4.906
c0.237,0.316,3.282,5.259,8.104,7.161c4.007,1.58,4.823,1.266,5.693,1.187c0.87-0.079,2.807-1.147,3.202-2.255
c0.395-1.108,0.395-2.057,0.277-2.255c-0.119-0.198-0.435-0.316-0.909-0.554s-2.807-1.385-3.242-1.543
c-0.435-0.158-0.751-0.237-1.068,0.238c-0.316,0.474-1.225,1.543-1.502,1.859c-0.277,0.317-0.554,0.357-1.028,0.119
c-0.474-0.238-2.002-0.738-3.815-2.354c-1.41-1.257-2.362-2.81-2.639-3.285c-0.277-0.474-0.03-0.731,0.208-0.968
c0.213-0.213,0.474-0.554,0.712-0.831c0.237-0.277,0.316-0.475,0.474-0.791c0.158-0.317,0.079-0.594-0.04-0.831
C20.612,19.329,19.69,16.983,19.268,16.045z"></path>
</svg>
SVG,
];


$share_links = [
    'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($short_url),
    'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($short_url),
    'x'        => 'https://twitter.com/intent/tweet?url=' . rawurlencode($short_url),
    'telegram' => 'https://telegram.me/share/url?url=' . rawurlencode($short_url),
    'whatsapp' => 'https://wa.me/?text=' . rawurlencode($short_url),
    'tiktok'   => $short_url,
];

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <div role="navigation">
        <?php
        foreach ($icons as $icon) {
            if (isset($svgs[$icon])) {
                $href = $share_links[$icon] ?? '#';
                // Add target="_blank" and rel="noopener noreferrer" for safety and new tab open
                echo '<a href="' . esc_url($href) . '" target="_blank" rel="noopener noreferrer" title="Share on ' . esc_attr(ucfirst($icon)) . '" class="wbitly-share-icon" aria-label="Share on ' . esc_attr(ucfirst($icon)) . '">';
                echo svg_with_size($svgs[$icon], $size, $fill_color);
                echo '<span class="sr-only">Share on ' . esc_html(ucfirst($icon)) . '</span>';
                echo '</a>';
            }
        }

        ?>
    </div>
</div>