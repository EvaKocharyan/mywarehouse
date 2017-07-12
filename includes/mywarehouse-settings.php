<?php
/**
 * Class AdminPage
 */

class AdminPage
{
    /**
     * AdminPage constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menu'));
    }

    /**
     * Creating admin page and functionality
     */
    public function my_warehouse_admin()
    {
        $this->updateSettingsFromList();
        $res = $this->checkValidMyWarehouseLogin();
        require_once(MYWAREHOUSE_DIR . '/includes/admin/settings.php');
    }

    /**
     * Register admin page
     */
    public function register_menu()
    {
        add_menu_page('MyWarehouse', 'MyWarehouse', 'read', 'my_warehouse', array($this, 'my_warehouse_admin')/*, plugins_url('sh-newsletter/sources/img/gmail.png')*/);
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool|string
     */
    public function insertOption($name = '', $value = '')
    {
        if (!$name) return 'false - name is empty';
        $option = get_option($name);
        if ($option && $value != '') {
            update_option($name, $value);
        } elseif (!$option && $value != '') {
            add_option($name, $value);
        } else {
            return 'false - wrong';
        }
        return true;
    }

    /**
     * Updating full information
     */
    public function updateSettingsFromList()
    {
        if (isset($_POST['submit'])) {
            foreach ($_POST as $key => $item) {
                if ($key != 'submit')
                    $this->insertOption((string)$key, (string)$item);
            }
        }
    }

    public function checkValidMyWarehouseLogin()
    {
        require_once(MYWAREHOUSE_DIR . '/includes/mywarehouse-connect.php');

        $params = array(
            "mw_api_credentials" => Synchronize::credentials(),
        );
        $isConnect = Synchronize::connectSoap('get_all_skus_with_overnight_stock_levels', $params);
        if (is_string($isConnect)) return false;
        return $isConnect;
    }
}

new AdminPage();

