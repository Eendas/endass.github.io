jQuery(document).ready(function($) {
    $('.paysera-delivery-terminal-city-selection').select2();
    $('.paysera-delivery-terminal-country-selection').select2();
    $('.paysera-delivery-terminal-location-selection').select2();

    $(document).on( 'added_to_cart removed_from_cart updated_cart_totals', function () {
        $('.paysera-delivery-error').each(function () {
            $(this).parent().parent().children().attr('disabled', true);
        });
    });

    $(document).on('updated_checkout cfw_updated_checkout', function () {
        let shippingCountryId, shippingCountry, shippingCity, shippingCountryOption, shippingState;

        let isShippingToAnotherAddressEnabled = $('#ship-to-different-address-checkbox').is(':checked');

        shippingCountry = $('#billing_country');
        shippingCity = $('#billing_city').val();
        shippingState = $('#billing_state').val();

        if (isShippingToAnotherAddressEnabled === true) {
            shippingCountry = $('#shipping_country');
            shippingCity = $('#shipping_city').val();
            shippingState = $('#shipping_state').val();
        }

        $('.paysera-delivery-error').each(function () {
            $(this).parent().parent().children().attr('disabled', true);
        });

        shippingCountryOption = shippingCountry.find('option');
        shippingCountryId = shippingCountry.val();

        if (typeof shippingCountry.val() === 'undefined' || shippingCountry.val() === null) {
            shippingCountryId = shippingCountryOption.eq(1).val();
        }

        let paysera_terminal_country = $('.paysera-delivery-terminal-country');
        let paysera_terminal_country_selection = $('.paysera-delivery-terminal-country-selection');
        let shipping_methods = {};

        $('select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]').each(function () {
            shipping_methods[$(this).data('index')] = $(this).val();
        });

        if (Object.keys(shipping_methods).length > 0) {
            let shipping_methods_keys = Object.keys(shipping_methods);
            let shipping_method = $.trim(shipping_methods[shipping_methods_keys[0]]);
            let paysera_delivery_method = 'paysera_delivery';

            if (shipping_method.indexOf(paysera_delivery_method) !== -1) {
                paysera_terminal_country_selection.val('default');
                paysera_terminal_country_selection.trigger('change');
                let paysera_delivery_terminal_method = 'terminal';

                if (shipping_method.indexOf(paysera_delivery_terminal_method) !== -1) {
                    paysera_terminal_country_selection.empty();
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajaxurl,
                        data: {
                            action: 'change_paysera_method',
                            shipping_method: shipping_method
                        },
                        success:function(response) {
                            let countries = JSON.parse(response);
                            let newOption = new Option(countries['default'], 'default', true, true);
                            paysera_terminal_country_selection.append(newOption).trigger('change');

                            $.each(countries, function(index, item) {
                                if (index === 'default') {
                                    return;
                                }

                                let newCountryOption;

                                if (index === shippingCountryId) {
                                    newCountryOption = new Option(item, index, false, true);

                                    let city_container = $('.paysera-delivery-terminal-city');

                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajaxurl,
                                        data: {
                                            action: 'change_paysera_country',
                                            country: shippingCountryId,
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

                                                let newCityOption;

                                                if (item === shippingCity || item === shippingState || item === shippingState + ' apskr.') {
                                                    let location_container = $('.paysera-delivery-terminal-location');
                                                    let cityData;

                                                    if (item === shippingCity) {
                                                        cityData = shippingCity;
                                                    } else if (item === shippingState) {
                                                        cityData = shippingState;
                                                    } else {
                                                        cityData = shippingState + ' apskr.';
                                                    }

                                                    newCityOption = new Option(item, index, false, true);

                                                    $.ajax({
                                                        type: 'POST',
                                                        url: ajax_object.ajaxurl,
                                                        data: {
                                                            action: 'change_paysera_city',
                                                            country: $('.paysera-delivery-terminal-country-selection').select2('data')[0]['id'],
                                                            city: cityData,
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

                                                            location_container.show();
                                                        },
                                                        error:function() {
                                                            alert('There was an error while fetching the data.');
                                                        }
                                                    });
                                                } else {
                                                    newCityOption = new Option(item, index, false, false);
                                                }

                                                $('.paysera-delivery-terminal-city-selection').append(newCityOption).trigger('change');
                                            });

                                            city_container.show();
                                        },
                                        error:function() {
                                            alert('There was an error while fetching the data.');
                                        }
                                    });
                                } else {
                                    newCountryOption = new Option(item, index, false, false);
                                }

                                paysera_terminal_country_selection.append(newCountryOption).trigger('change');
                            });
                        },
                        error:function() {
                            alert('There was an error while fetching the data.');
                        }
                    });
                    paysera_terminal_country.show();
                } else {
                    paysera_terminal_country.hide();
                }
            } else {
                paysera_terminal_country.hide();
            }
            $('.paysera-delivery-terminal-city').hide();
            $('.paysera-delivery-terminal-location').hide();
            $('.paysera-delivery-terminal-city-selection').empty();
            $('.paysera-delivery-terminal-location-selection').empty();
        }
    });
});
