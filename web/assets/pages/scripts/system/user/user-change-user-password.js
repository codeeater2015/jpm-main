var ChangeUserPassword = function(){
    'use strict';

    var form_change_user_password;

    var initCore = function(){
        form_change_user_password = $('#form_change_user_password');
        $('input[name="password"]').focus();
    };

    var initEvent = function(){
        $('#save_changes').click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            form.find('div.has-error').removeClass('has-error');
            form.find('span.help-block').html('');

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'POST',
                beforeSend: function(){
                    $.show_loading('Processing request...');
                }
            }).done(function(response, status, xhr, $form) {
                console.log(response);
                showAlertMsg('#form_user_alert',"info",response.message)
            }).fail(function(response, status, xhr){
                console.log(response);
                if(response.status === 400){
                    showAlertMsg('#form_user_alert',"danger",response.responseJSON.message)
                    $.each( response.responseJSON.validation_error, function( key, value ) {
                        form.find('[name="'+key+'"]').closest('.form-group').addClass("has-error");
                        form.find('[name="'+key+'"]').next().html(value);
                    });
                }else{
                    $.error_notify(response);
                }

            }).always(function(){
                $.hide_loading();
            });

        });
    };

    var showAlertMsg = function(container, type, msg) {
        var alert = $('<div class="alert note note-' + type + ' alert-dismissible">\
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\
                <span></span>\
            </div>');

        $(container).find('.note').remove();
        $(container).append(alert);
        alert.addClass('fadeIn animated');
        alert.find('span').html(msg);
    };

    return {
        init : function(){
            initCore();
            initEvent() ;
        }
    }
}();