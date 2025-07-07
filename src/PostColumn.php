<?php

namespace Codehaveli\Wbitly;

if (! defined('ABSPATH')) {
    exit;
}

class PostColumn
{
    /**
     * Registered post types for columns.
     *
     * @var array
     */
    private static $post_types = [];

    /**
     * Initialize hooks and load post types.
     */
    public static function init()
    {
        self::$post_types = OptionManager::get('wbitly_custom_post', ['post']);
        add_action('admin_init', [self::class, 'setup_columns']);
    }

    /**
     * Hook filters and actions per post type.
     */
    public static function setup_columns()
    {
        foreach (self::$post_types as $post_type) {
            add_filter("manage_{$post_type}_posts_columns", [self::class, 'add_share_column']);
            add_action("manage_{$post_type}_posts_custom_column", [self::class, 'render_share_column'], 10, 2);
            add_action('admin_footer', [self::class, 'print_copy_script']);
        }
    }

    /**
     * Add the "Share" column header immediately after the "Title" column.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public static function add_share_column($columns)
    {
        $last_key = array_key_last($columns);

        // Extract the last column
        $last_column = [$last_key => $columns[$last_key]];

        // Remove the last column temporarily
        unset($columns[$last_key]);

        // Insert our custom column
        $columns['wbitly_url'] = __('Bitly URL', 'wbitly');

        // Re-add the last column
        return $columns + $last_column;
    }

    /**
     * Render the share icons in the custom column.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public static function render_share_column($column, $post_id)
    {
        if ('wbitly_url' !== $column) {
            return;
        }

        #if post status is not publish, do not show the share column
        if ('publish' !== get_post_status($post_id)) {
            return;
        }

        #if access token or guid not available then show settings page link
        if (! OptionManager::get_access_token() || ! OptionManager::get_guid()) {
            echo '<br><a href="' . esc_url(admin_url('tools.php?page=wbitly')) . '">' . esc_html__('Configure the plugin', 'wbitly') . '</a>';
            return;
        }

        $url = Manager::get_short_url($post_id);
        if (! $url) {
            if (current_user_can('edit_post', $post_id)) {
                echo '<button class="button button-secondary wbitly-generate-url" data-post-id="' . esc_attr($post_id) . '">' . esc_html__('Generate Bitly URL', 'wbitly') . '</button>';
            } else {
                echo esc_html__('No URL available', 'wbitly');
            }
            return;
        }

        $esc_url       = esc_url($url);
        $encoded_url   = rawurlencode($url);
        $email_subject = rawurlencode('Check this out');

?>
        <div class="wbitly-url-share">
            <p>
                <a href="<?php echo $esc_url; ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo $esc_url; ?>
                </a>
            </p>
            <div class="wbitly-social-icons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>"
                    target="_blank" rel="noopener noreferrer"
                    class="wbitly-icon wbitly-icon-facebook"
                    title="<?php echo esc_attr__('Share on Facebook', 'wbitly'); ?>"></a>

                <a href="mailto:?subject=<?php echo $email_subject; ?>&body=<?php echo $encoded_url; ?>"
                    class="wbitly-icon wbitly-icon-email"
                    title="<?php echo esc_attr__('Share via Email', 'wbitly'); ?>"></a>

                <a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>"
                    target="_blank" rel="noopener noreferrer"
                    class="wbitly-icon wbitly-icon-x"
                    title="<?php echo esc_attr__('Share on X (Twitter)', 'wbitly'); ?>"></a>

                <button type="button" class="wbitly-icon wbitly-icon-copy"
                    title="<?php echo esc_attr__('Copy URL', 'wbitly'); ?>"
                    aria-label="<?php echo esc_attr__('Copy URL to clipboard', 'wbitly'); ?>"
                    data-copy-text="<?php echo esc_attr($esc_url); ?>"
                    style="background: none; border: none; cursor: pointer; padding: 0;"></button>
            </div>
        </div>
    <?php
    }

    /**
     * Print inline JS script to handle copy-to-clipboard.
     */
    public  static function print_copy_script()
    {
    ?>
        <script type="text/javascript">
            (function() {
                document.querySelectorAll('.wbitly-icon-copy').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var copyUrl = this.getAttribute('data-copy-text');
                        if (copyUrl) {
                            const textarea = document.createElement("textarea");
                            textarea.value = copyUrl;
                            document.body.appendChild(textarea);
                            textarea.select();
                            try {
                                document.execCommand("copy");
                                alert("URL copied to clipboard!");
                            } catch (err) {
                                alert("Copy not supported");
                            }
                            document.body.removeChild(textarea);
                        }
                    });
                });


                document.querySelectorAll('.wbitly-generate-url').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const postId = this.getAttribute('data-post-id');
                        const btn = this;
                        btn.disabled = true;
                        btn.textContent = 'Generating...';

                        fetch('<?php echo esc_url(rest_url('wbitly/v1/generate/')); ?>' + postId, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                                },
                                body: JSON.stringify({})
                            })
                            .then(response => response.json())
                            .then(data => {
                                
                                if (data && data.share_block) {
                                    btn.parentElement.innerHTML = data.share_block;
                                } else {
                                    btn.textContent = 'Failed';
                                    alert(data.message || 'Error generating URL');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                btn.textContent = 'Error';
                                alert('Request failed.');
                            });
                    });
                });
            })();
        </script>
<?php
    }
}
