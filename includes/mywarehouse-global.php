<?php
/**
 * Class WP_Mywarehouse
 */
class WP_Mywarehouse
{
    /**
     * WP_Mywarehouse constructor.
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'mywarehouse_activation'));
        add_action('admin_notices', array($this, 'checkProblem'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));

    }

    /**
     * Global problem checking
     */
    public function checkProblem()
    {
        if (!class_exists('WooCommerce'))
            echo $this->WP_errors('missWoo');
        /**
         * check if user inserted Api username and password when woocommerce installed
         */
        if (class_exists('WooCommerce')) {
            if (!empty(str_replace(' ', '', get_option('api_username_mywarehouse'))) && !empty(str_replace(' ', '', get_option('api_passwd_mywarehouse')))) {
            } else echo $this->WP_errors('missCredentials');
        }
    }

    /**
     * @param $type
     * @return mixed
     */
    public function WP_errors($type)
    {
        $err = array(
            'missWoo' => '<div class="notice notice-error is-dismissible"><p>Please install <a href="https://wordpress.org/plugins/woocommerce/">Woocommerce</a> or <a href="/wp-admin/plugins.php?action=deactivate&plugin=wp-mywarehouse%2Fwp-mywarehouse.php&plugin_status=all&paged=1&s&_wpnonce=3e24b3d8fd">deactivate</a> Mywarehouse API plugin</p></div>',
            'missCredentials' => '<div class="notice notice-error is-dismissible"><p>Please Insert Your Mywarehouse account <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=my_warehouse">Credentials</a> for live or testing server</p></div>'
        );
        return $err[$type];
    }

    public function action_links($links)
    {
        return array_merge(
            array(
                'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=my_warehouse">Settings</a>'
            ),
            $links
        );
    }


}

new WP_Mywarehouse();
