var CreateNewUser = function(){
    'use strict';

    var form_create_user;

    var initCore = function(){
        $('input[name="name"]').focus();
        form_create_user = $("#form_create_user");
    };

    var initEvent = function(){
        $('#clear_form').click(function() {
            clearForm();
        });

        $('#submit_create').click(function(e) {
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
                User.reloadUserGrid();
                clearForm();
                showAlertMsg('#form_user_alert',"info",response.message)
            }).fail(function(response, status, xhr){
                console.log(response);
                if(response.status === 400){
                    showAlertMsg('#form_user_alert',"danger",response.responseJSON.message)

                    $.each( response.responseJSON.validation_error, function( key, value ) {
                        form.find('[name="'+key+'"]').closest('.form-group').addClass("has-error");
                        if(form.find('[name="'+key+'"]').hasClass("select2-hidden-accessible")){
                            form.find('[name="'+key+'"]').next().next().html(value);
                        }else if(form.find('[name="'+key+'"]').closest('.mt-radio-list').hasClass('mt-radio-list')){
                            form.find('[name="'+key+'"]').closest('.mt-radio-list').find('.help-block').html(value);
                        }else{
                            form.find('[name="'+key+'"]').next().html(value);
                        }
                    });
                }else{
                    $.error_notify(response);
                }

            }).always(function(response, status, xhr){
                $.hide_loading();
            });

        });

        $('input[name="status"]').bootstrapSwitch('state',true);
    };

    var showAlertMsg = function(container, type, msg) {
        var alert = $('<div style="margin: 0px;" class="alert note note-' + type + ' alert-dismissible">\
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\
                <span></span>\
            </div>');

        $(container).find('.note').remove();
        $(container).append(alert);
        alert.addClass('fadeIn animated');
        alert.find('span').html(msg);
    };

    var clearForm = function(){
        $('input[name="name"]').focus();
        form_create_user.find(".has-error").removeClass("has-error");
        form_create_user.find("span.help-block").html("");
        form_create_user.find('input:text:not([name="user_id"]), input:password, select, textarea').val('');
        form_create_user.find('input:radio[value="Male"]').prop('checked',true);
        form_create_user.find('input[name="status"]').bootstrapSwitch('state',true);
    };

    return {
        init : function(){
            initCore();
            initEvent();
        }
    }
}();