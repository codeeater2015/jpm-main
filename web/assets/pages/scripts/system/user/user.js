var User = function(){
    var user_table = $('#user_table');
    var grid_user = new Datatable();
    var btnCreate = $('#create');
    var btnBatchDelete = $('#batch_delete');
    var modalViewUser = $('#modal_view_user');
    var modalCreateNewUser = $('#modal_create_new_user');
    var modalEditUser = $('#modal_edit_user');
    var modalChangeUserPassword = $('#modal_change_user_password');

    var initCore = function(){
        initGridUser();
    };

    var initEvent = function(){
        btnCreate.on('click',function(){
            showModalCreateNewUser();
        });

        btnBatchDelete.on('click',function(){
            batchDeleteUser();
        });

        user_table.find('tbody').on('click','.user-view-btn',function(e){
            e.preventDefault();
            $(this).blur();

            var user =  grid_user.getDataTable().row($(this).parents('tr') ).data();
            showModalViewUser(user);
        });

        user_table.find('tbody').on('click','.user-edit-btn',function(e){
            e.preventDefault();
            $(this).blur();

            var user =  grid_user.getDataTable().row($(this).parents('tr') ).data();
            showModalEditUser(user);
        });

        user_table.find('tbody').on('click','.user-change-password-btn',function(e){
            e.preventDefault();
            $(this).blur();

            var user =  grid_user.getDataTable().row($(this).parents('tr') ).data();
            showModalChangeUserPassword(user);
        });

        user_table.find('tbody').on('click','.user-delete-btn',function(e){
            e.preventDefault();
            $(this).blur();

            var user =  grid_user.getDataTable().row($(this).parents('tr') ).data();
            deleteUser(user);
        });

        modalViewUser.on('show.bs.modal',function(e){
            if (e.namespace === 'bs.modal') {
                $.show_loading('Please wait...');
            }
        }).on('hidden.bs.modal',function(){
            $(this).removeData("bs.modal").find(".modal-content").empty();
        });

        modalCreateNewUser.on('show.bs.modal',function(e){
            if (e.namespace === 'bs.modal') {
                $.show_loading('Please wait...');
            }
        }).on('hidden.bs.modal',function(){
            $(this).removeData("bs.modal").find(".modal-content").empty();
        });

        modalEditUser.on('show.bs.modal',function(e){
            if (e.namespace === 'bs.modal') {
                $.show_loading('Please wait...');
            }
        }).on('hidden.bs.modal',function(){
            $(this).removeData("bs.modal").find(".modal-content").empty();
        });

        modalChangeUserPassword.on('show.bs.modal',function(e){
            if (e.namespace === 'bs.modal') {
                $.show_loading('Please wait...');
            }
        }).on('hidden.bs.modal',function(){
            $(this).removeData("bs.modal").find(".modal-content").empty();
        });

    };

    var initGridUser = function(){
        var url = Routing.generate("ajax_get_datatable_users_list",{}, true);
        grid_user.init({
            src: user_table,
            loadingMessage: 'Loading...',
            "dataTable" : {
                "bState" : true,
                "autoWidth": true,
                "deferRender": true,
                "ajax" : {
                    "url" : url,
                    "type" : 'GET'
                },
                "columnDefs" : [{
                    'orderable' : false,
                    'targets' : [0,6]
                }, {
                    'className': 'align-center',
                    'targets': [0,3,4,5,6]
                }],
                "order" : [
                    [0, "desc"]
                ],
                "columns": [
                    {
                        "data" : "id",
                        "render" : function(data,type,row){
                            return (row.isDefault === "YES") ? "" : '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'+data+'"/><span></span></label>';
                        }
                    },
                    { "data": "name"},
                    {
                        "data": "username",
                        "render" : function(data){
                            var url = Routing.generate("homepage",{},true);
                            return '<a href="'+url+'?_switch_user='+data+'">'+data+'</a>';
                        }
                    },
                    { "data": "gender"},
                    { "data": "groupName"},
                    {
                        "data": "isActive"
                    },
                    { "render" : function(data,type,row){
                        var viewBtn = "<a href='#' class='btn btn-xs btn-success user-view-btn' data-toggle='tooltip' data-title='View'><i class='glyphicon glyphicon-eye-open' ></i></a>";
                        var editBtn = "<a href='#' class='btn btn-xs btn-primary user-edit-btn' data-toggle='tooltip' data-title='Edit'><i class='glyphicon glyphicon-edit'></i></a>";
                        var changeBtn = "<a href='#' class='btn btn-xs font-white bg-green-dark user-change-password-btn' data-toggle='tooltip' data-title='Change Password'><i class='fa fa-lock' ></i></a>";
                        var deleteBtn = "<a href='#' class='btn btn-xs btn-danger user-delete-btn' data-toggle='tooltip' data-title='Delete'><i class='glyphicon glyphicon-trash'></i></a>";


                        return (row.isDefault === "YES") ? "<span class='badge badge-info'>Default</span>" : viewBtn + editBtn + changeBtn + deleteBtn ;
                    }}
                ],
            }
        });
    };

    var gridUserReload = function(){
        grid_user.getDataTable().ajax.reload();
    };

    var showModalViewUser = function(user){
        modalViewUser.modal("show");

        var url = Routing.generate("modal_view_user", {}, true);
        modalViewUser.load(url + "?id=" + user.id, function (response, status, xhr) {
            $.hide_loading();
            if (status === "error") {
                $(this).html('<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header bg-red-mint">' +
                    '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                    '<h3 style="color:#000;" class="modal-title bg-font-red-mint"> Error Response</h3>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<span> Sorry but there was an error : ' + xhr.status + ' ' + xhr.statusText + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
            }
        });
    };

    var showModalCreateNewUser = function(){
        modalCreateNewUser.modal("show");

        var url = Routing.generate("modal_create_new_user", {}, true);
        modalCreateNewUser.load(url, function (response, status, xhr) {
            $.hide_loading();
            if (status === "error") {
                $(this).html('<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header bg-red-mint">' +
                    '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                    '<h3 style="color:#000;" class="modal-title bg-font-red-mint"> Error Response</h3>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<span> Sorry but there was an error : ' + xhr.status + ' ' + xhr.statusText + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
            }else{
                CreateNewUser.init();
            }
        });
    };

    var showModalEditUser = function(user){
        modalEditUser.modal("show");

        var url = Routing.generate("modal_edit_user", {}, true);
        modalEditUser.load(url + "?id=" + user.id, function (response, status, xhr) {
            $.hide_loading();
            if (status === "error") {
                $(this).html('<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header bg-red-mint">' +
                    '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                    '<h3 style="color:#000;" class="modal-title bg-font-red-mint"> Error Response</h3>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<span> Sorry but there was an error : ' + xhr.status + ' ' + xhr.statusText + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
            }else{
                EditUser.init();
            }
        });
    };

    var showModalChangeUserPassword = function(user){
        modalChangeUserPassword.modal("show");

        var url = Routing.generate("modal_change_user_password", {}, true);
        modalChangeUserPassword.load(url + "?id=" + user.id, function (response, status, xhr) {
            $.hide_loading();
            if (status === "error") {
                $(this).html('<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header bg-red-mint">' +
                    '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                    '<h3 style="color:#000;" class="modal-title bg-font-red-mint"> Error Response</h3>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<span> Sorry but there was an error : ' + xhr.status + ' ' + xhr.statusText + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
            }else{
                ChangeUserPassword.init();
            }
        });
    };

    var deleteUser = function(user){
        bootbox.dialog({
            title: "System Message",
            message: "Are you sure you want to remove this user?",
            buttons: {
                'confirm': {
                    label: 'Yes, Delete',
                    className: 'btn-danger',
                    callback: function(){
                        var url = Routing.generate("ajax_delete_user",{ id : user.id },true);
                        $.ajax({
                            url : url,
                            type: 'DELETE',
                            beforeSend: function(){
                                $.show_loading('Processing request...');
                            }
                        }).done(function(response){
                            gridUserReload();
                            $.notify(response.message,'teal');
                        }).fail(function(response){
                            $.error_notify(response);
                        }).always(function(){
                            $.hide_loading();
                        });
                    }
                },
                'cancel': {
                    label: 'Cancel',
                    className: 'btn-default '
                }
            }
        });
    };

    var batchDeleteUser = function(){
        var selectedCount = grid_user.getSelectedRowsCount();
        var rowsSelected = grid_user.getSelectedRows();

        if(selectedCount === 0){
            bootbox.alert("Please select a user(s) you want to remove!");
            return false;
        }

        var count = (selectedCount > 1) ? selectedCount+" users" : selectedCount+" user";

        bootbox.dialog({
            title: "System Message",
            message: "You have selected "+count+". Are you sure you want to remove?",
            buttons: {
                'confirm': {
                    label: 'Yes, Delete',
                    className: 'btn-danger',
                    callback: function(){
                        var url = Routing.generate("ajax_batch_delete_user",{},true);
                        $.ajax({
                            url : url,
                            type: 'DELETE',
                            data:{
                                id : rowsSelected
                            },
                            beforeSend: function(){
                                $.show_loading('Processing request...');
                            }
                        }).done(function(response){
                            gridUserReload();
                            $.notify(response.message,'teal');
                        }).fail(function(response){
                            $.error_notify(response);
                        }).always(function(){
                            $.hide_loading();
                        });
                    }
                },
                'cancel': {
                    label: 'Cancel',
                    className: 'btn-default '
                }
            }
        });
    };

    return {
        init : function(){
            initCore();
            initEvent();
        },
        reloadUserGrid : function(){
            gridUserReload();
        }
    }
}();

jQuery(document).ready(function(){
    User.init();
});