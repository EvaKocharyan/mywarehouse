<?php

class RegisterWooFields
{
    function __construct()
    {
        add_action('woocommerce_product_options_general_product_data', array($this, 'wc_custom_add_custom_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'wc_custom_save_custom_fields'));

        add_filter('manage_edit-shop_order_columns', array($this, 'new_columns'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'new_columns_values'), 2);

    }

    public function wc_custom_add_custom_fields()
    {
        woocommerce_wp_text_input(array(
            'id' => '_barcode',
            'label' => 'Product Barcode',
            'description' => 'It is important field for tracking product',
            'desc_tip' => 'true',
            'placeholder' => 'bar-code-1234',
        ));
        woocommerce_wp_text_input(array(
            'id' => '_customs_value',
            'label' => 'Product customs value',
            'description' => 'It is important field for tracking product',
            'desc_tip' => 'true',
            'placeholder' => ''
        ));
        woocommerce_wp_text_input(array(
            'id' => '_pack_quantity',
            'label' => 'Product pack quantity',
            'description' => 'It is important field for tracking product',
            'desc_tip' => 'true',
            'placeholder' => ''
        ));
        woocommerce_wp_textarea_input(array(
            'id' => '_description',
            'label' => 'Product Short Description',
            'description' => 'It is important field for tracking product',
            'desc_tip' => 'true',
            'placeholder' => 'Description'
        ));
        woocommerce_wp_text_input(array(
            'id' => '_initial_stock_arrival_date',
            'label' => 'Product initial stock arrival date',
            'description' => 'It is important field for tracking product',
            'desc_tip' => 'true',
            'placeholder' => ''
        ));
    }

    /**
     * @param $post_id
     */
    public function wc_custom_save_custom_fields($post_id)
    {

        if (!empty($_POST['_barcode'])) {
            update_post_meta($post_id, '_barcode', esc_attr($_POST['_barcode']));
        }
        if (!empty($_POST['_pack_quantity'])) {
            update_post_meta($post_id, '_pack_quantity', esc_attr($_POST['_pack_quantity']));
        }
        if (!empty($_POST['_customs_value'])) {
            update_post_meta($post_id, '_customs_value', esc_attr($_POST['_customs_value']));
        }
        if (!empty($_POST['_description'])) {
            update_post_meta($post_id, '_description', esc_attr($_POST['_description']));
        }
        if (!empty($_POST['_initial_stock_arrival_date'])) {
            update_post_meta($post_id, '_initial_stock_arrival_date', esc_attr($_POST['_initial_stock_arrival_date']));
        }
    }

    function new_columns($columns)
    {
        $new_columns = (is_array($columns)) ? $columns : array();
        unset($new_columns['order_actions']);

        $new_columns['track_code'] = 'Tracking Code';
        //stop editing

        $new_columns['order_actions'] = $columns['order_actions'];
        return $new_columns;
    }

    function new_columns_values($column)
    {
        global $post;
        $data = get_post_meta($post->ID);

        //start editing, I was saving my fields for the orders as custom post meta
        //if you did the same, follow this code
        if ($column == 'track_code') {
            echo(isset($data['track_code']) ? $data['track_code'][0] : '');
        }

    }
}
new RegisterWooFields();