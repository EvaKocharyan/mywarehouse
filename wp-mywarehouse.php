<?php
/**
  * Plugin Name: Mywarehouse API
  * Description: Connect with your Mywarehouse account
  * Version: 1.1
  * Author: Alex Melik
  * Author URI: https://github.com/AlexMeliq
  * Plugin URI: http://shahumyanmedia.com
  *
*/

class Start
{
    public function __construct()
    {

        define('MYWAREHOUSE_DIR', plugin_dir_path(__FILE__));
        define('MYWAREHOUSE_PATH', plugin_dir_url(__FILE__));

        require_once(MYWAREHOUSE_DIR.'/includes/mywarehouse-global.php');
        if (class_exists('WooCommerce')) {
            require_once(MYWAREHOUSE_DIR . '/includes/mywarehouse-settings.php');
            require_once(MYWAREHOUSE_DIR . '/includes/mywarehouse-connect.php');
            require_once(MYWAREHOUSE_DIR.'/includes/product-options.php');

            add_action( 'admin_enqueue_scripts', array($this,'my_enqueue') );


        }else{

        }
        register_activation_hook(__FILE__, 'mywarehouse_activation');
    }
    function my_enqueue() {

        wp_enqueue_script( 'datepicker', MYWAREHOUSE_PATH . '/sources/js/jquery.datetimepicker.min.js' );
        wp_enqueue_script( 'my_custom_script', MYWAREHOUSE_PATH . '/sources/js/myware_custom.js' );

        wp_enqueue_style('admin-styles', MYWAREHOUSE_PATH . '/sources/css/jquery.datetimepicker.css');
        wp_enqueue_style('admin-styles-custom', MYWAREHOUSE_PATH . '/sources/css/myware_style.css');
    }

}
new Start();

function mywarehouse_activation(){
    global $wpdb;
    $sqlSh = 	"
     SELECT *
     FROM `wp_woocommerce_shipping_zone_methods` ";
    $methodsD = $wpdb->get_row( $sqlSh );
    if(!isset($methodsD->delivery_method)) {
        $query = "ALTER TABLE wp_woocommerce_shipping_zone_methods ADD delivery_method VARCHAR(11) NOT NULL";

        $added = $wpdb->query($query);
    }
}
