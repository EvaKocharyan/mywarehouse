jQuery(document).ready(function ($) {
    var input = jQuery('#_initial_stock_arrival_date');
    input.wrap('<div class="input-group" id="datetimepicker1"></div>');
    input.datetimepicker({
        format:'Y-m-d H:m:s'
    });

    $('.sendD').on({
        click: function () {
            checkAndSend()
        }
    });


    function checkAndSend() {
        var url = "/wp-admin/admin-ajax.php",
            post_ID = $('[name=post_ID]').val(),
            _initial_stock_arrival_date = $('[name=_initial_stock_arrival_date]').val(),
            _regular_price = $('[name=_regular_price]').val(),
            _sku = $('[name=_sku]').val(),
            _weight = $('[name=_weight]').val(),
            _length = $('[name=_length]').val(),
            _width = $('[name=_width]').val(),
            _height = $('[name=_height]').val(),
            _barcode = $('[name=_barcode]').val(),
            _description = $('[name=_description]').val(),
            _pack_quantity = $('[name=_pack_quantity]').val(),
            _customs_value = $('[name=_customs_value]').val(),
            _stock = $('[name=_stock]').val();
        jQuery.ajax({

            url: url,

            type:"POST",

            cache:false,

            data:{

                action: "to_server",
                _initial_stock_arrival_date: _initial_stock_arrival_date,
                _regular_price: _regular_price,
                _sku: _sku,
                _weight: _weight,
                _length: _length,
                _width: _width,
                _height: _height,
                _barcode: _barcode,
                _description: _description,
                _pack_quantity: _pack_quantity,
                _customs_value: _customs_value,
                _stock: _stock,
                post_ID: post_ID

            },

            beforeSend: function () {
                // $('.inside .PP').append('<div class="overE"></div>')
                $('.sendD').addClass('sending');
            },

            success:function(data){
                console.log(data);
                if( Object.prototype.toString.call(data) == '[object String]' ) {
                    if(data != '') {
                        $('.error, .notice-error.myB').remove();
                        $('.sendD').removeClass('sending');
                        $('.wp-header-end').after('<div class="notice notice-error myB is-dismissible"><p>' + data + '</p></div>');
                    }else{
                        $('#product-mywarehouse-status .inside').empty().append('<div class="okUpload"><p>Uploaded on the Mywarehouse server</p></div>');
                    }
                }
            },

            error: function(err){
                console.log(err);
            }

        });
    }




    //Settings page
    $('.allShips .name').each(function () {
        $(this).on({
            click: function () {
                // $('.allShips .name').removeClass('opened');
                $(this).toggleClass('opened');
            }
        })
    })








});