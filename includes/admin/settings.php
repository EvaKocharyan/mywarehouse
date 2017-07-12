<?php
$delMethods = array('CSAT' => 'CSAT','IC' => 'IC','P' => 'P','RP' => 'RP','IP' => 'IP','IPS' => 'IPS','SP' => 'SP','Q' => 'Q',);
$myWrap = array('true' => 'true','false' => 'false');
?>
<div class="wrap">
    <?php if($res === false) echo '<div class="notice notice-error is-dismissible"><p>Invalid Credentials</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'; ?>
    <h1>MyWarehouse Settings</h1>
    <form class="postbox-container fForm" method="post" action="" novalidate="novalidate">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="api_username_mywarehouse">Api Username</label></th>
                <td>
                    <input name="api_username_mywarehouse" type="text" id="api_username_mywarehouse" value="<?php echo get_option('api_username_mywarehouse') ? get_option('api_username_mywarehouse') : '' ?>" class="regular-text">
                    <p class="description" id="admin-email-description">API username for Mywarehouse</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="api_passwd_mywarehouse">Api password</label></th>
                <td>
                    <input name="api_passwd_mywarehouse" type="text" id="api_passwd_mywarehouse" value="<?php echo get_option('api_passwd_mywarehouse') ? get_option('api_passwd_mywarehouse') : '' ?>" class="regular-text">
                    <p class="description" id="admin-email-description">API password for Mywarehouse</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="my_wrap">My Wrap</label>
                </th>
                <td>
                    <select name="my_wrap" id="my_wrap">
                        <?php
                        foreach ($myWrap as $method){
                            if(get_option('my_wrap') !== null&& get_option('my_wrap') == $method)
                                echo '<option selected value="' . $method . '">' . strtoupper($method) . '</option>';
                            else
                                echo '<option value="' . $method . '">' . strtoupper($method) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mywarehouse_delivery_method">Delivery Method</label>
                </th>
                <td>
                    <select name="mywarehouse_delivery_method" class="postbox-container" id="mywarehouse_delivery_method">
                        <?php
                        foreach ($delMethods as $method){
//                            var_dump(get_option('mywarehouse_delivery_method'));
                            if(get_option('mywarehouse_delivery_method') !== null && get_option('mywarehouse_delivery_method') == $method)
                                echo '<option selected value="' . $method . '">' . $method . '</option>';
                            else
                                echo '<option value="' . $method . '">' . $method . '</option>';
                        }
                        ?>
                    </select>
                    <div class="postbox-container" style="margin-left: 20px">
                        <h2 style="margin: 0">The delivery method. Must be one of the following:</h2>
                        <ul>
                            <li>"C24" - for Next Day service</li>
                            <li>"CSAT" - for Courier Saturday Morning</li>
                            <li>"IC" - for International Courier</li>
                            <li>"P" - for RM 1 st Class Postage</li>
                            <li>"RP" - for RM Recorded Postage</li>
                            <li>"IP" - for RM International Postage</li>
                            <li>"IPS" – for RM International Postage Signed For</li>
                            <li>"SP" – for RM Special Delivery Postage</li>
                            <li>"COLL" - for Personal Collection from the Warehouse</li>
                        </ul>
                    </div>
                </td>
            </tr>

            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
    <?php
    if($res !== false){

        if(count($res->get_all_skus_with_overnight_stock_levelsResult)) {
            $ret = '<div class="postbox-container prods" style="padding: 20px 80px">';
            $ret .= '<h3>All SKUs with overnight stock levels</h3>';
                $ret .= '<div id="hidd"> ';
                    $ret .= '<table align="center" style="text-align: center">';
                        $ret .= '<thead><tr>';
                            $ret .= '<th>';
                                $ret .= '<p>Product SKU</p>';
                            $ret .= '</th>';
                            $ret .= '<th>';
                                $ret .= '<p>Overnight Stock Level</p>';
                            $ret .= '</th>';
                        $ret .= '</tr></thead><tbody>';
                foreach ($res->get_all_skus_with_overnight_stock_levelsResult->mw_api_stock_item as $re) {
    //                var_dump($re);
                    $ret .= '<tr>';
                        $ret .= '<td>';
                            $ret .= '<p>' . $re->product_sku . '</p>';
                        $ret .= '</td>';
                        $ret .= '<td>';
                            $ret .= '<p>' . $re->overnight_stock_level . '</p>';
                        $ret .= '</td>';
                    $ret .= '</tr>';
                }
                    $ret .= '</tbody></table>';
                $ret .= '</div>';
            $ret .= '</div>';
            echo $ret;
        }
    }


    global $wpdb;

    $sql = 	"
	SELECT * 
	FROM `wp_woocommerce_shipping_zones`
";

    $zones = $wpdb->get_results( $sql );
    $out = '<div class="allShips postbox-container">';
    foreach($zones as $zone){
        $zone_id = $zone->zone_id;
        $zone_name = $zone->zone_name;
        $sqlSh = 	"
        SELECT *
        FROM `wp_woocommerce_shipping_zone_methods` WHERE `wp_woocommerce_shipping_zone_methods`.zone_id = '$zone_id'";
        $methodsD = $wpdb->get_results( $sqlSh );
//    var_dump($zone_id);
//    var_dump($zone_name);
        $out .= '<div class="country">';
        $out .= '<div data-id="' . $zone_id . '" class="name">';
        $out .= '<h3>' . $zone_name . '</h3>';
        $out .= '</div>';
        $out .= '<div class="shippings">';
        foreach ($methodsD as $ret){
            $wpName = 'woocommerce_' . $ret->method_id . '_' . $ret->instance_id . '_settings';
            $all = get_option($wpName);
            if($ret->is_enabled) $active = 'true'; else $active = 'false';
            $out .= '<div data-active="' . $active . '" class="shipping">';
            /*$out .= '<div class="instance_id">';
                $out .= '<span>' . $ret->instance_id . '</span>';
            $out .= '</div>';*/
            $out .= '<div class="method_name">';
            $out .= '<span>' . $all['title'] . '</span>';
            $out .= '</div>';
            $out .= '<div class="method_id_delivery">';
            $out .= '<span>' . $ret->delivery_method . '</span>';
            $out .= '<input value="' . $ret->delivery_method . '" type="text" name="method_id_delivery_' . $ret->instance_id . '">';
            $out .= '</div>';
            $out .= '<div class="update">';
            $out .= '<div><span>Update</span></div>';
            $out .= '</div>';
            $out .= '</div>';
//                var_dump($ret->instance_id);
//                var_dump($all);
        }
        $out .= '</div>';
        $out .= '</div>';
    };
    $out .= '</div>';
    echo $out;
    ?>
</div>


