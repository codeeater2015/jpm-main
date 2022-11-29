var UserRcenter = function(){
    var multiSelectRCenter = $('#rcenter');
    var select2User = $('#user');
    var btnSaveChanges = $('#save_changes');
    var btnSelectAll = $('#select_all');
    var btnDeselectAll = $('#deselect_all');

    var initCore = function(){
        select2User.select2({
            width: "auto",
            placeholder: "Select...",
            allowClear : true
        });

        multiSelectRCenter.multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function(ms){
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function(){
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function(){
                this.qs1.cache();
                this.qs2.cache();
            },
            cssClass: "fluid-size"
        });

    };

    var initEvents = function(){
        select2User.on("change",function(){
            loadUserRCenter();
        });

        btnSaveChanges.on("click",function(){
            if(select2User.val() === null || select2User.val() === ""){
                swal({
                    text : "Please select user first",
                    type : "warning"
                });
            }else{
                saveChanges();
            }
        });

        btnSelectAll.on("click",function(){
            multiSelectRCenter.multiSelect('select_all');
        });

        btnDeselectAll.on("click",function(){
            multiSelectRCenter.multiSelect('deselect_all');
        });
    };

    var loadUserRCenter = function(){
        var user_rcenter = [];
        var url = Routing.generate("ajax_get_user_rcenter",{ userid : select2User.val()},true);

        $.get(url)
            .done(function(res){
                res.map(function(row){
                    user_rcenter.push(row.rc_code);
                });
                multiSelectRCenter.multiSelect('deselect_all');
                multiSelectRCenter.multiSelect('select', user_rcenter);
            })
            .fail(function(res){
                $.error_notify(res);
            })
            .always(function(){
            });
    };

    var saveChanges = function(){
        var userid = select2User.val();
        var rcenter = multiSelectRCenter.val();

        $.show_loading("Processing request...");
        var url = Routing.generate("ajax_save_user_rcenter",{},true);
        $.post(url,{userid : userid, rcenter : rcenter})
            .done(function(res){
                $.notify(res.message,"teal");
            })
            .fail(function(res){
                $.error_notify(res);
            })
            .always(function(){
                $.hide_loading();
            });

    };

    return {
        init : function(){
            initCore();
            initEvents();
        }
    }
}();

jQuery(document).ready(function() {
    UserRcenter.init();
});