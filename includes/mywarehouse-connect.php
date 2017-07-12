<?php

class Synchronize
{
    public function __construct()
    {
        session_start();

        add_action("add_meta_boxes", array($this, "ProductServerInsideStatus"));

        add_action('woocommerce_order_status_processing', array($this, 'newOrder'));

        add_action("add_meta_boxes", array($this, "ProductServerInsideStatus"));

        add_action( 'wp_ajax_to_server', array($this, 'to_server') );
    }

    public $newOrder;

    public $empties = array();

    public $productProperties;




    public function to_server() {

        $ID = $_POST['post_ID'];
        $date = $_POST['_initial_stock_arrival_date'];
        $date = explode(' ', $date);
        $date = implode('T', $date);

        $this->productProperties->reg_price = $_POST['_regular_price'];
        $this->productProperties->product_sku = $_POST['_sku'];
        $this->productProperties->product_weight_in_grams = $_POST['_weight'];
        $this->productProperties->product_depth_in_mms = $_POST['_length'];
        $this->productProperties->product_width_in_mms = $_POST['_width'];
        $this->productProperties->product_height_in_mms = $_POST['_height'];
        $this->productProperties->product_barcode = $_POST['_barcode'];
        $this->productProperties->product_description = $_POST['_description'];
        $this->productProperties->product_pack_quantity = $_POST['_pack_quantity'];
        $this->productProperties->product_customs_value = $_POST['_customs_value'];
        $this->productProperties->product_initial_stock_qty = $_POST['_stock'];
        $this->productProperties->product_initial_stock_arrival_date = $date;
        foreach ($this->productProperties as $key => $property) {
            if (empty($property)) {
                array_push($this->empties, $key);
            }
        };
        if (count($this->empties)) {
            echo "Please input the required values - ". implode(', ', $this->empties);
        }else{
            $prodO = $this->getProduct($ID);
           if(!is_string($prodO) && $prodO !== false){echo 'Please Refresh page'; exit;}
            if (!count($this->empties) && is_string($prodO)) {

                $r = $this->newProduct($this->productProperties);
                if($r->create_new_productResult === true)
                    echo '';
                else
                    echo $r;
            }else{
                echo 'Make sure that all fields are filled and Publish post';
            }
        }
        exit;
    }

    /**
     * Set Mywarehouse credentials(demo or live)
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public static function credentials($username = 'api_pommaker_test', $password = 'GR2df9D')
    {
        return array(
            'username' => get_option('api_username_mywarehouse') ? get_option('api_username_mywarehouse') : $username,
            'password' => get_option('api_passwd_mywarehouse') ? get_option('api_passwd_mywarehouse') : $password
        );
    }

    /**
     * Connecting SoapClient
     * @param $fName
     * @param $params
     * @return mixed|string
     */
    public static function connectSoap($fName, $params)
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $wsdl = 'https://www.mywarehouse.me/mywarehouse/api_services/mywarehouse_api.asmx?WSDL';

        $options = array(
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );
        $soap = new SoapClient($wsdl, $options);
        try {
            $result = $soap->__soapCall($fName, array($params));
            if(count($result)){
                return $result;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Check isset product from Mywarehouse
     * @param $id
     * @return bool|mixed|string
     */
    public function getProduct($id)
    {

        $prod = wc_get_product($id);
//        var_dump($prod->post->post_status);
        if ($prod) {
            if($prod->post->post_status !== 'draft') {
                $sku = $prod->get_sku();
                $params = array(
                    "mw_api_credentials" => $this->credentials(),
                    "product_sku" => $sku,
                );

                return $sku ? $this->connectSoap('get_product_by_sku', $params) : false;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * @param $properties
     * @return mixed|string
     * Add new product to Mywarehouse
     */
    public function newProduct($properties)
    {
        $params = array(
            "mw_api_credentials" => $this->credentials(),
            "new_product" => $properties,
        );
        return $this->connectSoap('create_new_product', $params);
    }


    /**
     * @param $order_id
     */
    public function newOrder($order_id)
    {

        $order = new WC_Order( $order_id );
        $items = $order->get_items();


        $this->newOrder->order_delivery_name = $order->shipping_first_name . ' ' . $order->shipping_last_name;
        $this->newOrder->order_delivery_address_1 = $order->shipping_address_1;
        $this->newOrder->order_delivery_address_2 = $order->shipping_address_2 ? $order->shipping_address_2 : $order->shipping_address_1;
        $this->newOrder->order_delivery_town = $order->shipping_state ? $order->shipping_state : $order->shipping_city;
        $this->newOrder->order_delivery_postcode = $order->shipping_postcode;
        $this->newOrder->order_delivery_county = $order->shipping_city;
        $this->newOrder->order_delivery_country = WC()->countries->countries[$order->shipping_country];
        $this->newOrder->order_mobile_number = $order->billing_phone ? $order->billing_phone : '0000000';
        $this->newOrder->order_email_address = $order->billing_email ? $order->billing_email : 'test@gmail.com';

        if($this->newOrder->order_delivery_country == 'United Kingdom (UK)') $this->newOrder->order_delivery_country = 'United Kingdom';
//        $this->newOrder->order_reference = get_post_meta($order->post->ID,'_order_number_formatted')[0];
        $this->newOrder->order_reference = get_post_meta($order->post->ID,'_order_key')[0];
        $this->newOrder->order_special_instructions = $_POST['order_comments'] ? $_POST['order_comments'] : 'Order instruction is empty';
        $this->newOrder->order_delivery_carrier_code = get_option( 'mywarehouse_delivery_method' );
        $this->newOrder->order_mywrap = get_option( 'my_wrap' );
        $this->newOrder->order_vat_amount = $order->get_total_tax();
        $this->newOrder->order_total = $order->get_total();
        $this->newOrder->order_lines = new stdClass();
        $this->newOrder->order_lines->mw_api_order_line = array();
        foreach($items as $item){
            $prod = wc_get_product($item['product_id']);
            $sku = $prod->get_sku();
            array_push($this->newOrder->order_lines->mw_api_order_line, array(
                'line_product_sku' => 'POM104',
                'line_product_qty' => $item['qty']
            ));

        };
        $this->newOrder->order_lines = json_decode(json_encode($this->newOrder->order_lines));
        $this->newOrder->order_lines->mw_api_order_line = json_decode(json_encode($this->newOrder->order_lines->mw_api_order_line));
        $params = array(
            "mw_api_credentials" => $this->credentials(),
            "new_order" => $this->newOrder,
        );
        $resp = $this->connectSoap('create_new_order', $params);
        var_dump($this->newOrder);
        var_dump($resp);
        exit;
        add_post_meta( $order_id, 'mywarehouse_order_id', $resp->create_new_orderResult);
        if(isset($resp->create_new_orderResult)){

            $lastInfo = $this->getOrderResult($resp->create_new_orderResult);
            if(!empty($lastInfo)){
                if($lastInfo->get_order_statusResult->order_courier_tracking_code){
                    add_post_meta($order_id, 'track_code', $lastInfo->get_order_statusResult->order_courier_tracking_code);
                }
                else {
                    add_post_meta($order_id, 'track_info', $lastInfo->get_order_statusResult->order_despatch_status);
                }
            };

        }else{
            return;
        }
    }
    /**
     * @param $screen
     */
    public function checkUpdate($screen)
    {
        if($screen->id == 'edit-shop_order'){
            $args = array(
                'post_type' => 'shop_order',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'track_info',
                    )
                )
            );
            $query = get_posts($args);
            if(!empty($query)) {
                foreach ($query as $re) {
                    $inf = get_post_meta($re->ID, 'track_info')[0];
                    if (strtolower($inf) == 'pending') {
                        $ordId = get_post_meta($re->ID, 'mywarehouse_order_id')[0];
                        $check = $this->getOrderResult($ordId);
                        if (!empty($check)) {
                            if (isset($check->get_order_statusResult->order_despatch_status)) {
                                $status = strtolower($check->get_order_statusResult->order_despatch_status);
                                $order = new WC_Order($re->ID);
                                if ($status == 'cancelled') {
                                    $order->update_status('cancelled');
                                    delete_post_meta($re->ID, 'track_info');

//                                    $this->SendUserTrackingCode($order->billing_email, $check);

                                } elseif ($status == 'despatched') {
                                    $order->update_status('completed');
                                    delete_post_meta($re->ID, 'track_info');

//                                    $this->SendUserTrackingCode($order->billing_email, $check);

                                }
                                if (isset($check->get_order_statusResult->order_courier_tracking_code)) {
                                    $trCode = $check->get_order_statusResult->order_courier_tracking_code;
                                    add_post_meta($re->ID, 'tracking_number', $trCode);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $order_unique_id
     * @return mixed|string
     */
    public function getOrderResult($order_unique_id)
    {
        $params2 = array(
            "mw_api_credentials" => $this->credentials(),
            "order_unique_id" => $order_unique_id,
        );
        return $respm = $this->connectSoap('get_order_status', $params2);
    }

    /**
     * @param $email
     * @param $trackInfo
     * @return mixed
     */
    public function SendUserTrackingCode($email, $trackInfo)
    {
        $despStatus = $trackInfo->get_order_statusResult->order_despatch_status;
//        $trCode = $trackInfo->get_order_statusResult->order_courier_tracking_code;
        $despDate = $trackInfo->get_order_statusResult->order_despatch_date;

        require_once(MYWAREHOUSE_DIR . '/includes/sources/email/email_template.php');
        //Can use $temp variable from this template


        $headers = array('Content-Type: text/html; charset=UTF-8');
        $m = wp_mail($email, 'Order Track Info from Mywarehouse', $temp, $headers);
        return $m;
    }


    /**
     * Show status in sidebar (top right section)
     * ProductServerInsideStatus
     */
    public function ProductServerInsideStatus()
    {
        add_meta_box("product-mywarehouse-status", "Product Status", array($this, "sideInfo"), "product", "side", "high", null);
    }

    /**
     * sideInfo
     * make sidebar
     */
    public function sideInfo()
    {
        $out = '<div class="PP">';
        if(isset($_GET['post'])) {
            $id = $_GET['post'];
            $pr = $this->getProduct($id);
        }else{
            $pr = false;
        }
        if ($pr && !is_string($pr)) {
            $out .= '<div class="okUpload"><p>Uploaded on the Mywarehouse server</p></div>';
        } else {
            $out .= '<div class="dokUpload"><p>Doesn\'t uploaded on the Mywarehouse server</p></div>';
            $out .= '<div class="sendD button button-primary button-small right"><span>Upload</span></div>';
        }
        $out .= '</div>';
        echo $out;
    }
}
new Synchronize();