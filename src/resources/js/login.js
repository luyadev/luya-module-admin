var observeLogin = function (form, url, secureUrl, twoFaUrl) {

    $(form).submit(function (e) {
        $('#errorsContainer').hide();
        $('.login-btn[type="submit"]').attr('disabled', true);
        $('.login-spinner').show();
        $('.login-btn-label').hide();
        
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: url,
            data: $(this).serialize(),
            error: asyncError,
            success: function (response) {
                $('.login-btn[type="submit"]').attr('disabled', false);
                $('.login-spinner').hide();
                $('.login-btn-label').show();
                var refresh = response['refresh'];
                var errors = response['errors'];
                var enterSecureToken = response['enterSecureToken'];
                var enterTwoFaToken = response['enterTwoFaToken'];

                if (enterTwoFaToken) {
                    $('#twofaForm').show();
                    $('#loginForm').hide();
                }

                if (errors.length > 0) {
                    $('#errorsContainer').html(errorsToList(errors));
                    $('#errorsContainer').show();
                    $('#password').val('');
                    $('#email').focus();
                }

                if (enterSecureToken) {
                    $('#secureForm').show();
                    $('#loginForm').hide();
                }

                if (refresh) {
                    $('#forgot').hide();
                    $('#errorsContainer').hide();
                    $('#success').css('visibility', 'visible');
                    $('#secureForm').hide();
                    $('#loginForm').hide();
                    $('.login-logo').hide();
                    location.reload();
                }
            },
            dataType: "json"
        });
    });

    $('#secureForm').submit(function (e) {
        $('#errorsSecureContainer').hide();
        $('.login-btn[type="submit"]').attr('disabled', true);
        $('.login-spinner').show();
        $('.login-btn-label').hide();
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: secureUrl,
            data: $(this).serialize(),
            error: asyncError,
            success: function (response) {
                $('.login-spinner').hide();
                $('.login-btn-label').show();
                $('.login-btn[type="submit"]').attr('disabled', false);
                var refresh = response['refresh'];
                var errors = response['errors'];

                if (errors.length > 0) {
                    $('#errorsContainer').html(errorsToList(errors));
                    $('#errorsContainer').show();
                }

                if (refresh) {
                    $('#forgot').hide();
                    $('#errorsContainer').hide();
                    $('#success').css('visibility', 'visible');
                    $('#secureForm').hide();
                    $('#loginForm').hide();
                    $('.login-logo').hide();
                    location.reload();
                }
            },
            dataType: "json"
        })
    });

    $('#twofaForm').submit(function (e) {
        $('#errorsSecureContainer').hide();
        $('.login-btn[type="submit"]').attr('disabled', true);
        $('.login-spinner').show();
        $('.login-btn-label').hide();
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: twoFaUrl,
            data: $(this).serialize(),
            error: asyncError,
            success: function (response) {
                $('.login-spinner').hide();
                $('.login-btn-label').show();
                $('.login-btn[type="submit"]').attr('disabled', false);
                var refresh = response['refresh'];
                var errors = response['errors'];
                if (errors.length > 0) {
                    $('#errorsContainer').html(errorsToList(errors));
                    $('#errorsContainer').show();
                }

                if (refresh) {
                    $('#forgot').hide();
                    $('#errorsContainer').hide();
                    $('#success').css('visibility', 'visible');
                    $('#twofaForm').hide();
                    $('#loginForm').hide();
                    $('.login-logo').hide();
                    location.reload();
                }
            },
            dataType: "json"
        })
    });

    $('#abortToken').click(function (e) {
        $('.spinner').hide();
        $('.submit-icon').show();
        $('#errorsContainer').hide();
        $('#secureForm').hide();
        $('#loginForm').show();
        $('#forgot').show();
        $('#success').css('visibility', 'hidden');

        $('.login-spinner').hide();
        $('.login-btn-label').show();
        $('.login-btn[type="submit"]').attr('disabled', false);
    });


    $('#abortTwoFa').click(function (e) {
        $('.spinner').hide();
        $('.submit-icon').show();
        $('#errorsContainer').hide();
        $('#twofaForm').hide();
        $('#loginForm').show();
        $('#forgot').show();
        $('#success').css('visibility', 'hidden');

        $('.login-spinner').hide();
        $('.login-btn-label').show();
        $('.login-btn[type="submit"]').attr('disabled', false);
    });
};

var asyncError = function(request) {
    $('#errorsContainer').html(request.statusText);
    $('#errorsContainer').show();
    $('.login-spinner').hide();
    $('.login-btn-label').show();
    $('.login-btn[type="submit"]').attr('disabled', false);
};

var errorsToList = function(errors) {
    var errorHtml = '<ul>';
    for (var i in errors) {
        errorHtml = errorHtml + '<li>' + errors[i]['message'] + '</li>';
    }
    errorHtml = errorHtml + '</ul>';
    return errorHtml;
}

var checkInputLabels = function () {
    var $loginInput = $('.login-input');

    var check = function($element) {
        var val = $element.val() ? $element.val() : '';

        var autofillBg = window.getComputedStyle($element[0], null).getPropertyValue("background-color") === 'rgb(250, 255, 189)' ? true : false;

        if(val.length >= 1 || autofillBg === true) {
            $element.addClass('is-not-empty').removeClass('is-empty');
        } else {
            $element.addClass('is-empty').removeClass('is-not-empty');
        }
    };

    $loginInput.on('keyup paste change click', function() {
        check($(this));
    });

    $loginInput.each( function() {
        check($(this));
    });
};
