(function ($) {
    if (!$) return;

    var mmqsConfig = window.mmqsConfig || {};
    var ajaxUrl = mmqsConfig.ajax_url || (window.mmqsAjaxUrl || '/wp-admin/admin-ajax.php');
    var primaryFormId = mmqsConfig.primary_form_id || '4333';
    var secondaryFormId = mmqsConfig.secondary_form_id || '4331';
    var fieldMap = mmqsConfig.field_map || {};
    var uiText = mmqsConfig.ui_text || {};
    var primaryFields = fieldMap.primary || { address: '1', postal: '1', province: '1', city: '1' };
    var secondaryFields = fieldMap.secondary || { address: '82', postal: '82', province: '82', city: '82' };

    var primaryFormSelector = '#wpforms-form-' + primaryFormId;
    var secondaryFormSelector = '#wpforms-form-' + secondaryFormId;

    function fieldSelector(fieldId, part) {
        return 'input[name="wpforms[fields][' + fieldId + '][' + part + ']" ]'.replace(' ]', ']');
    }

    var primarySelectors = {
        address1: fieldSelector(primaryFields.address || primaryFields.postal || '1', 'address1'),
        address2: fieldSelector(primaryFields.address || primaryFields.postal || '1', 'address2'),
        city: fieldSelector(primaryFields.city || primaryFields.address || '1', 'city'),
        state: fieldSelector(primaryFields.province || primaryFields.address || '1', 'state'),
        postal: fieldSelector(primaryFields.postal || primaryFields.address || '1', 'postal')
    };

    var secondarySelectors = {
        address1: fieldSelector(secondaryFields.address || secondaryFields.postal || '82', 'address1'),
        address2: fieldSelector(secondaryFields.address || secondaryFields.postal || '82', 'address2'),
        city: fieldSelector(secondaryFields.city || secondaryFields.address || '82', 'city'),
        state: fieldSelector(secondaryFields.province || secondaryFields.address || '82', 'state'),
        postal: fieldSelector(secondaryFields.postal || secondaryFields.address || '82', 'postal')
    };

    var loadingMessage = uiText.loading || 'Finding your neighbourhood - hang tight!';
    var notServicedMessage = uiText.not_serviced || 'Sorry, we currently do not service this area.';

    // Traffic Source Detection - Stores in sessionStorage (clears when browser closes)
    (function () {
        var STORAGE_KEY = 'mm_traffic_source';

        function getUrlParam(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function detectTrafficSource() {
            var srcParam = getUrlParam('src');
            if (srcParam) return srcParam.toUpperCase();

            var utmSource = getUrlParam('utm_source');
            var utmMedium = getUrlParam('utm_medium');

            if (utmSource) {
                var src = utmSource.toLowerCase();
                var medium = utmMedium ? utmMedium.toLowerCase() : '';

                if (src === 'google' && medium === 'cpc') return 'ADW';
                if (src === 'bing' && medium === 'cpc') return 'BING-ADS';
                if ((src === 'facebook' || src === 'instagram' || src === 'fb' || src === 'ig') &&
                    (medium === 'cpc' || medium === 'paid')) return 'SOCIAL-ADS';
                if (src === 'facebook' || src === 'instagram' || src === 'fb' || src === 'ig') return 'SOCIAL';
            }

            var ref = document.referrer.toLowerCase();
            if (ref) {
                if (ref.includes('facebook.com') || ref.includes('instagram.com') ||
                    ref.includes('fb.com') || ref.includes('t.co') || ref.includes('twitter.com')) {
                    return 'SOCIAL';
                }
                if (ref.includes('google.') || ref.includes('bing.') ||
                    ref.includes('yahoo.') || ref.includes('duckduckgo.')) {
                    return 'SEO';
                }
            }

            return 'SEO';
        }

        function getTrafficSource() {
            var urlSource = detectTrafficSource();

            if (urlSource && urlSource !== 'SEO' && urlSource !== 'SOCIAL') {
                sessionStorage.setItem(STORAGE_KEY, urlSource);
                return urlSource;
            }

            var storedSource = sessionStorage.getItem(STORAGE_KEY);
            if (storedSource) return storedSource;

            var finalSource = urlSource || 'SEO';
            sessionStorage.setItem(STORAGE_KEY, finalSource);
            return finalSource;
        }

        var trafficSource = getTrafficSource();
        window.mmTrafficSource = trafficSource;

        document.addEventListener('DOMContentLoaded', function () {
            var srcField155 = document.querySelector('input[name="wpforms[fields][155]"]');
            var srcField152 = document.querySelector('input[name="wpforms[fields][152]"]');

            if (srcField155) srcField155.value = trafficSource;
            if (srcField152) srcField152.value = trafficSource;
        });
    })();

    var mmNonce = null;

    function fetchNonce() {
        if (!ajaxUrl) return $.Deferred().resolve(null).promise();
        return $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: { action: 'mm_get_nonce' }
        }).then(function (res) {
            if (res && res.success && res.data && res.data.nonce) {
                mmNonce = res.data.nonce;
                return mmNonce;
            }
            return null;
        }).catch(function () {
            return null;
        });
    }

    function ensureNonce() {
        if (mmNonce) return $.Deferred().resolve(mmNonce).promise();
        return fetchNonce();
    }

    function sendAjax(data, onSuccess, onError, retried) {
        if (retried === undefined) retried = false;
        return ensureNonce().then(function () {
            var payload = $.extend({}, data, { nonce: mmNonce });
            return $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: payload,
                success: function (response) {
                    var code = response && response.data ? response.data.code : '';
                    if (!response || !response.success && code === 'NONCE_FAIL' && !retried) {
                        return fetchNonce().then(function () {
                            return sendAjax(data, onSuccess, onError, true);
                        });
                    }
                    onSuccess(response);
                },
                error: function (xhr, status, error) {
                    onError(xhr, status, error);
                }
            });
        });
    }

    function showLoading(message) {
        $('.loading-text').text(message || loadingMessage);
        $('#loading-overlay').css('display', 'flex');
    }

    function hideLoading() {
        $('#loading-overlay').css('display', 'none');
    }

    function showServiceError(message) {
        hideLoading();
        alert(message);
    }

    function normalizeAjaxErrorMessage(response, fallback) {
        if (response && response.data && response.data.message) return response.data.message;
        return fallback;
    }

    $(document).ready(function () {
        fetchNonce();

        $(primaryFormSelector).before('<h1 class="form-title">Enter Your Home Address</h1>');
        $(secondaryFormSelector).parent('.wpforms-container').hide();
        $('#postal-error').removeClass('show');
        $(primaryFormSelector).parent('.wpforms-container').show();

        $(primarySelectors.postal).on('input', function () {
            $('#postal-error').removeClass('show');
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        var franchiseDetails = null;
        var userAddress = '';
        var userSuite = '';
        var userCity = '';
        var userProvince = '';
        var userPostalCode = '';
        var redirectData = { tableName: '', rowId: '' };
        var currentRequestId = '';

        $(primarySelectors.state).after('<div id="province-error" class="error-message">Please enter the full province name (e.g., Ontario, Alberta etc.)</div>');

        var provinceMap = {
            'AB': 'Alberta',
            'BC': 'British Columbia',
            'MB': 'Manitoba',
            'NB': 'New Brunswick',
            'NL': 'Newfoundland and Labrador',
            'NS': 'Nova Scotia',
            'NT': 'Northwest Territories',
            'NU': 'Nunavut',
            'ON': 'Ontario',
            'PE': 'Prince Edward Island',
            'QC': 'Quebec',
            'SK': 'Saskatchewan',
            'YT': 'Yukon',
            'ALB': 'Alberta',
            'ALTA': 'Alberta',
            'MAN': 'Manitoba',
            'ONT': 'Ontario',
            'QUE': 'Quebec',
            'SAS': 'Saskatchewan',
            'SASK': 'Saskatchewan',
            'YUK': 'Yukon',
            'B.C.': 'British Columbia',
            'B.C': 'British Columbia',
            'N.B.': 'New Brunswick',
            'N.B': 'New Brunswick',
            'N.S.': 'Nova Scotia',
            'N.S': 'Nova Scotia',
            'N.W.T': 'Northwest Territories',
            'N.W.T.': 'Northwest Territories',
            'PEI': 'Prince Edward Island',
            'P.E.I.': 'Prince Edward Island',
            'P.E.I': 'Prince Edward Island',
            'NFLD': 'Newfoundland and Labrador',
            'NEWFOUNDLAND': 'Newfoundland and Labrador',
            'LABRADOR': 'Newfoundland and Labrador',
            'NFLD & LAB': 'Newfoundland and Labrador',
            'NFLD AND LAB': 'Newfoundland and Labrador',
            'NFL': 'Newfoundland and Labrador',
            'NWT': 'Northwest Territories',
            'QUEBEC': 'Quebec',
            'QUÉBEC': 'Quebec',
            'BRIT COL': 'British Columbia',
            'BRITISH COL': 'British Columbia',
            'NEW BRUNS': 'New Brunswick',
            'NOVA SCO': 'Nova Scotia',
            'NORTHWEST': 'Northwest Territories',
            'NUNAV': 'Nunavut'
        };

        function isValidProvince(province) {
            var fullProvinces = Object.values(provinceMap).map(function (p) { return p.toLowerCase(); });
            return fullProvinces.indexOf(province.toLowerCase()) !== -1;
        }

        $(document).on('wpformsBeforeFormSubmit', function (e, form) {
            if (!form || !form[0]) return;
            if (form[0].id === ('wpforms-form-' + primaryFormId)) {
                $('#province-error').removeClass('show');
                $('#postal-error').removeClass('show');

                var valid = true;
                var postalField = $(primarySelectors.postal);
                var postalValueRaw = postalField.val() || '';
                var postalValueClean = postalValueRaw.replace(/[\s-]/g, '');
                if (postalValueClean.length !== 6) {
                    valid = false;
                    $('#postal-error')
                        .text('Please enter a valid 6-character postal code.')
                        .addClass('show');
                    $('html, body').animate({
                        scrollTop: $('#postal-error').offset().top - 100
                    }, 500);
                }

                var provinceField = $(primarySelectors.state);
                var provinceValue = (provinceField.val() || '').trim();
                var upperProvince = provinceValue.toUpperCase();
                if (provinceMap[upperProvince]) {
                    provinceField.val(provinceMap[upperProvince]);
                } else if (!isValidProvince(provinceValue)) {
                    valid = false;
                    $('#province-error').addClass('show');
                    $('html, body').animate({
                        scrollTop: $('#province-error').offset().top - 100
                    }, 500);
                }

                if (!valid) {
                    e.preventDefault();
                    return false;
                }

                showLoading('Saving your information...');
                return true;
            }
        });

        $(primarySelectors.postal).after($('#postal-error'));

        $(primarySelectors.state).on('input', function () {
            $('#province-error').removeClass('show');
        });

        $(document).on('wpformsAjaxSubmitSuccess', primaryFormSelector, function (event, response) {
            if (response && response.success) {
                proceedWithPostalCheck();
            }
        });

        function proceedWithPostalCheck() {
            if (!currentRequestId) {
                currentRequestId = 'rq_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
            }

            userAddress = $(primarySelectors.address1 + ', #wpforms-' + primaryFormId + '-field_' + (primaryFields.address || '1')).filter(function () {
                return $(this).val();
            }).first().val();
            userSuite = $(primarySelectors.address2).val();
            userCity = $(primarySelectors.city).val();
            userProvince = $(primarySelectors.state).val();
            userPostalCode = ($(primarySelectors.postal).val() || '').replace(/[\s-]/g, '').toUpperCase();

            var userSrc = window.mmTrafficSource || 'SEO';

            showLoading('Checking availability in your area...');

            sendAjax({
                action: 'check_postal_code_ecommerce',
                postal_code: userPostalCode,
                request_id: currentRequestId
            }, function (response) {
                if (!response || typeof response.success === 'undefined') {
                    return showServiceError('We had trouble checking your address. Please try again.');
                }

                if (response.success) {
                    franchiseDetails = response.data.franchise_details;

                    sendAjax({
                        action: 'save_lead_to_db',
                        address: userAddress,
                        suite: userSuite,
                        city: userCity,
                        province: userProvince,
                        postal_code: userPostalCode,
                        referer: document.referrer,
                        src: userSrc,
                        request_id: currentRequestId
                    }, function (saveResponse) {
                        if (saveResponse && saveResponse.success) {
                            redirectData.tableName = saveResponse.data.table;
                            redirectData.rowId = saveResponse.data.row_id;
                        }

                        $(primaryFormSelector).parent('.wpforms-container').hide();
                        $('.form-title').hide();

                        if (response.data.ecommerce_enabled === 'YES') {
                            setTimeout(function () {
                                hideLoading();
                                $('#option-buttons').show();
                            }, 2500);
                        } else {
                            $('.loading-text').text('Preparing your quote form in 3...2...1.');
                            setTimeout(function () {
                                showDetailedForm();
                                hideLoading();
                            }, 2500);
                        }
                    }, function () {
                        showServiceError('We saved your address, but couldn\'t finish the next step. Please try again.');
                    });

                    return;
                }

                var code = response && response.data ? response.data.code : '';
                if (code === 'NOT_SERVICED') {
                    return showServiceError(notServicedMessage);
                }

                return showServiceError(normalizeAjaxErrorMessage(response, 'We had trouble checking your address. Please try again.'));
            }, function () {
                showServiceError('We had trouble checking your address. Please try again.');
            });
        }

        function redirectToEcommerce() {
            if (!franchiseDetails) return;
            var decoded = franchiseDetails.franch_phone.replace(/&#(\d+);/g, function (_, dec) {
                return String.fromCharCode(dec);
            });

            var params = {
                locationCode: franchiseDetails.forceId,
                address: userAddress,
                city: userCity,
                state: userProvince,
                suite: userSuite || '',
                postalCode: userPostalCode,
                country: 'Canada',
                locationPhone: decoded.replace(/\D+/g, ''),
                serviceTerritoryID: franchiseDetails.territory_id,
                locationName: franchiseDetails.franch_city,
                locationEmail: franchiseDetails.display_email,
                localSiteUrl: franchiseDetails.franch_url,
                serviceTerritoryRegion: franchiseDetails.serviceTerritoryRegion,
                tableId: redirectData.tableName,
                insertId: redirectData.rowId
            };

            var baseUrl = 'https://ecommerce.merrymaids.com';
            var queryParams = Object.keys(params).map(function (key) {
                return key + '=' + encodeURIComponent(params[key] || '');
            }).join('&');

            window.location.href = baseUrl + '?' + queryParams;
        }

        function showDetailedForm() {
            $('#option-buttons').hide();
            var detailedForm = $(secondaryFormSelector);
            detailedForm.parent('.wpforms-container').show();

            if (redirectData.rowId && !detailedForm.find('input[name="wpforms[fields][existing_row_id]"]').length) {
                detailedForm.append('<input type="hidden" name="wpforms[fields][existing_row_id]" value="' + redirectData.rowId + '">');
                detailedForm.append('<input type="hidden" name="wpforms[fields][existing_table]" value="' + redirectData.tableName + '">');
            }

            var mapboxInput = detailedForm.find('mapbox-address-autofill input');
            mapboxInput.val(userAddress)
                .prop('readonly', true)
                .attr('disabled', 'disabled')
                .css({
                    'background-color': '#f8f8f8',
                    'cursor': 'not-allowed',
                    'color': '#666666'
                })
                .removeAttr('data-autocomplete')
                .removeAttr('role')
                .removeAttr('aria-autocomplete')
                .removeAttr('aria-controls');

            var mainAddressField = detailedForm.find('#wpforms-' + secondaryFormId + '-field_' + (secondaryFields.address || '82'));
            mainAddressField.val(userAddress)
                .prop('readonly', true)
                .attr('disabled', 'disabled')
                .css({
                    'background-color': '#f8f8f8',
                    'cursor': 'not-allowed',
                    'color': '#666666'
                });

            var addressFields = {};
            addressFields['wpforms[fields][' + (secondaryFields.address || '82') + '][address1]'] = userAddress;
            addressFields['wpforms[fields][' + (secondaryFields.address || '82') + '][address2]'] = userSuite || '';
            addressFields['wpforms[fields][' + (secondaryFields.city || secondaryFields.address || '82') + '][city]'] = userCity;
            addressFields['wpforms[fields][' + (secondaryFields.province || secondaryFields.address || '82') + '][state]'] = userProvince;
            addressFields['wpforms[fields][' + (secondaryFields.postal || secondaryFields.address || '82') + '][postal]'] = userPostalCode;

            Object.keys(addressFields).forEach(function (fieldName) {
                var value = addressFields[fieldName];
                var field = detailedForm.find('input[name="' + fieldName + '"]');
                if (field.length) {
                    field.val(value)
                        .prop('readonly', true)
                        .css({
                            'background-color': '#f8f8f8',
                            'cursor': 'not-allowed',
                            'color': '#666666'
                        });
                }

                var hiddenField = detailedForm.find('input[type="hidden"][name="' + fieldName + '"]');
                if (!hiddenField.length) {
                    detailedForm.append('<input type="hidden" name="' + fieldName + '" value="' + value + '">');
                } else {
                    hiddenField.val(value);
                }
            });

            if (!detailedForm.find('.start-over-link').length) {
                detailedForm.find(secondarySelectors.postal).after(
                    '<a class="start-over-link">Need to change address? Start Again.</a>'
                );
            }

            var secondaryFieldPrefix = 'wpforms[fields][' + (secondaryFields.address || '82') + ']';
            detailedForm.off('input change keydown keyup keypress focus blur', 'input[name^="' + secondaryFieldPrefix + '"]')
                .on('input change keydown keyup keypress focus blur', 'input[name^="' + secondaryFieldPrefix + '"]', function (e) {
                    e.preventDefault();
                    return false;
                });

            detailedForm.find('mapbox-address-autofill').css('pointer-events', 'none');

            setTimeout(function () {
                $(document).trigger('wpforms-address-autocomplete-selected');
                setTimeout(function () {
                    mapboxInput.val(userAddress);
                    mainAddressField.val(userAddress);
                }, 500);
            }, 100);
        }

        $('#quick-form').click(function () {
            showLoading('Preparing your booking...');
            setTimeout(function () {
                redirectToEcommerce();
            }, 2500);
        });

        $('#detailed-form').click(function () {
            $('.quote-btn').removeClass('active inactive');
            $(this).addClass('active');
            $('#quick-form').addClass('inactive');
            showDetailedForm();
        });

        $(document).on('click', '.start-over-link', function () {
            location.reload();
        });
    });
})(window.jQuery);
