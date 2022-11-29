var PGPIS;
PGPIS = function () {
    var Main = function () {

        $.enable_loading = function (param) {
            $("#" + param + "-loading-layer").show();
            $("#" + param + "-loading-box").show();
        };

        $.disable_loading = function (param) {
            $("#" + param + "-loading-layer").hide();
            $("#" + param + "-loading-box").hide();
        };

        $.show_loading = function (message) {
            var loading_box = $("#loading-box");
            loading_box.find(".message").html(message);
            $("#loading-layer").show();
            loading_box.show();
        };

        $.hide_loading = function () {
            var loading_box = $("#loading-box");
            loading_box.find(".message").html("");
            $("#loading-layer").hide();
            loading_box.hide();
        };

        $.notification = function (message, type) {
            var animation_open = "", animation_close = "";
            var layout = "topCenter";

            if (type === "success") {
                animation_open = "bounceIn";
                animation_close = "flipOutX";
            } else if (type === "error") {
                animation_open = "tada";
                animation_close = "flipOutX";
            } else if (type === "warning") {
                animation_open = "swing";
                animation_close = "flipOutX";
            } else if (type === "information") {
                layout = "topRight";
                animation_open = "fadeInRight";
                animation_close = "fadeOutRight";
            } else if (type === "confirm") {
                animation_open = "fadeInDown";
                animation_close = "flipOutX";
            } else {
                animation_open = "flipInX";
            }

            noty({
                layout: layout,
                text: message,
                theme: 'relax',
                type: type,
                animation: {
                    open: 'animated ' + animation_open, // Animate.css class names
                    close: 'animated ' + animation_close, // Animate.css class names
                    easing: 'swing', // easing
                    speed: 500 // opening & closing animation speed
                },
                timeout: 3000,
                force: true
            });
        };

        $.notify = function (message, color, heading) {
            if (heading === undefined) {
                heading = "System Message";
            }

            $.notific8('zindex', 11500);
            $.notific8(message, {
                heading: heading,
                theme: color,
                life: 5000,
                verticalEdge: 'right',
                horizontalEdge: 'top',
            });
        };

        $.error_notify = function (response) {
            var message = (!response.responseJSON) ? response.statusText : response.responseJSON.message;
            $.notify(message, "ruby", "Error " + response.status);
        };

        $.fixedHeader = function (table) {
            $(window).resize(function () {
                if ($(window).width() < 992) {
                    table.DataTable().fixedHeader.disable();
                } else {
                    table.DataTable().fixedHeader.enable();
                }
            });
        };

        $.PopupCenter = function (url, title, w, h) {
            // Fixes dual-screen position                         Most browsers      Firefox
            var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
            var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

            var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

            var left = ((width / 2) - (w / 2)) + dualScreenLeft;
            var top = ((height / 2) - (h / 2)) + dualScreenTop;
            var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

            // Puts focus on the newWindow
            if (window.focus) {
                newWindow.focus();
            }
        }

    };

    var Notification = function () {
        $('.view_notification').on("click", function () {
            var id = $(this).attr('data-id');
            var url = Routing.generate("load_suspension_details", {}, true);

            $("#notification").load(url, {id: id}, function () {
                console.log("Load was performed.");
            });

        });

        $('#notification').on('show.bs.modal', function () {
            $(this).removeData('bs.modal').find(".modal-content").empty();
            $(this).html('<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-body">' +
                '<img src="' + appUrlConfig.baseUrl + '/assets/global/img/loading-spinner-grey.gif" alt="" class="loading">' +
                '<span> &nbsp;&nbsp;Loading... </span>' +
                '</div>' +
                '</div>' +
                '</div>');

        });

    };


    return {
        init: function () {
            Main();
            Notification();
        }
    }

}();

jQuery(document).ready(function() {
    PGPIS.init();
});