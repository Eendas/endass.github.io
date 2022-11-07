jQuery(document).ready(function($) {
    $('.paysera-delivery-terminal-country-selection').on('select2:select', function (e) {
        let data = e.params.data;
        let country = data['id'];

        let response_container = $('.paysera-delivery-terminal-city');

        $('.paysera-delivery-terminal-location').hide();
        $('.paysera-delivery-terminal-location-selection').empty();

        if (country === 'default') {
            response_container.hide();
            return;
        }
        
        $('.paysera-delivery-terminal-city-selection').empty();

        let shipping_methods = {};
        $('select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]').each(function () {
            shipping_methods[$(this).data('index')] = $(this).val();
        });
        let shipping_methods_keys = Object.keys(shipping_methods);
        let shipping_method = $.trim(shipping_methods[shipping_methods_keys[0]]);

        $.ajax({
            type: 'POST',
            url: ajax_object.ajaxurl,
            data: {
                action: 'change_paysera_country',
                country: data['id'],
                shipping_method: shipping_method
            },
            success:function(response) {
                let cities = JSON.parse(response);

                let newOption = new Option(cities['default'], 'default', true, true);
                $('.paysera-delivery-terminal-city-selection').append(newOption).trigger('change');

                $.each(cities, function(index, item) {
                    if (index === 'default') {
                        return;
                    }
                    let newOption = new Option(item, index, false, false);
                    $('.paysera-delivery-terminal-city-selection').append(newOption).trigger('change');
                });

                response_container.show();
            },
            error:function() {
                alert('There was an error while fetching the data.');
            }
        });
    });

    $('.paysera-delivery-terminal-city-selection').on('select2:select', function (e) {
        let data = e.params.data;
        let city = data['id'];

        let response_container = $('.paysera-delivery-terminal-location');

        if (city === 'default') {
            response_container.hide();
            return;
        }

        $('.paysera-delivery-terminal-location-selection').empty();

        let shipping_methods = {};
        $('select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]').each(function () {
            shipping_methods[$(this).data('index')] = $(this).val();
        });
        let shipping_methods_keys = Object.keys(shipping_methods);
        let shipping_method = $.trim(shipping_methods[shipping_methods_keys[0]]);

        $.ajax({
            type: 'POST',
            url: ajax_object.ajaxurl,
            data: {
                action: 'change_paysera_city',
                country: $('.paysera-delivery-terminal-country-selection').select2('data')[0]['id'],
                city: data['id'],
                shipping_method: shipping_method
            },
            success:function(response) {
                let terminals = JSON.parse(response);

                let newOption = new Option(terminals['default'], 'default', true, true);
                $('.paysera-delivery-terminal-location-selection').append(newOption).trigger('change');

                $.each(terminals, function(index, item) {
                    if (index === 'default') {
                        return;
                    }
                    let newOption = new Option(item, index, false, false);
                    $('.paysera-delivery-terminal-location-selection').append(newOption).trigger('change');
                });

                response_container.show();
            },
            error:function() {
                alert('There was an error while fetching the data.');
            }
        });
    });
});
