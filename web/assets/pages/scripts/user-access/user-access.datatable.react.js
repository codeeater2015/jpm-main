var UserAccessDatatable = React.createClass({

    getInitialState : function(){
      return {
            showCreateModal : false,          
            target : null,
            typingTimer : null,
            doneTypingInterval : 1500
      }
    },

    componentDidMount : function(){
        this.initDatatable();
        this.initSelect2();
    },


    initSelect2 : function(){
        var self = this;
        
          
        $("#user_access_table #province_select2").select2({
            casesentitive : false,
            placeholder : "Enter Name..",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_province'),
                data :  function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.province_code , text: item.name};
                        })
                    };
                },
            }
        });

        $("#user_access_table #municipality_select2").select2({
            casesentitive : false,
            placeholder : "Enter Name...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_municipality'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        provinceCode : $("#user_access_table #province_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.municipality_no , text: item.name};
                        })
                    };
                },
            }
        });

        $("#user_access_table #barangay_select2").select2({
            casesentitive : false,
            placeholder : "Enter name...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_barangay'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo : $("#user_access_table #municipality_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.brgy_no , text: item.name};
                        })
                    };
                },
            }
        });

        $("#user_access_table #province_select2").on("change", function() {
            self.handleFilterChange();
        });

        $("#user_access_table #municipality_select2").on("change", function() {
            self.handleFilterChange();
        });

        $("#user_access_table #barangay_select2").on("change", function() {
            self.handleFilterChange();
        });
    },

    initDatatable : function(){
        var self = this;
    
        var access_table = $("#user_access_table");
        var grid_table = new Datatable();
        var url = Routing.generate("ajax_get_datatable_users_access_list",{userId : self.props.userId}, true);
        
        grid_table.init({
            src: access_table,
            loadingMessage: 'Loading...',
            "dataTable" : {
                "bState" : true,
                "autoWidth": true,
                "deferRender": true,
                "ajax" : {
                    "url" : url,
                    "type" : 'GET',
                    "data" : function(d){
                        d.provinceCode = $('#user_access_table #province_select2').val();
                        d.municipalityNo = $('#user_access_table #municipality_select2').val();
                        d.brgyNo = $('#user_access_table #barangay_select2').val();
                    }
                },
                "columnDefs" : [{
                    'orderable' : false,
                    'targets' : [0,3,4,5]
                }],
                "order" : [
                    [0, "desc"]
                ],
                "columns": [
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "province_name"
                    },
                    {
                        "data": "municipality_name"
                    },
                    { "data": "brgy_name"},
                    { 
                        "data": "valid_until",
                        "render" : function(data,type,row){
                            return moment(data).format("MMM DD, YYYY hh:mm A")
                        }
                    },
                    { 
                        "width" : 20,
                        "render" : function(data,type,row){
                            var changeBtn = "<a href='javascript:void(0);' class='btn btn-xs btn-danger user-permission-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return (row.isDefault === "YES") ? "<span class='badge badge-info'>Default</span>" : changeBtn;
                        }
                    }
                ],
            }
        });

        access_table.on( 'click', '.user-permission-button', function () {
            var data =  grid_table.getDataTable().row($(this).parents('tr') ).data();
            self.delete(data.access_id);
        });

        self.grid = grid_table;
    },

    delete : function(accessId){
        var self = this;

        if(confirm("Are you sure you want to delete this access?")){
            self.requestDelete = $.ajax({
                url : Routing.generate("ajax_delete_user_access",{accessId : accessId}),
                type : 'DELETE'
            }).done(function(){
                self.reload();
            });
        }
    },

    handleFilterChange : function(){
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function(){
            self.reload();
        },this.state.doneTypingInterval);
    },

    reload : function(){
        this.grid.getDataTable().ajax.reload();
    },

    openCreateModal : function(){
        this.setState({showCreateModal : true});
    },

    closeCreateModal : function(){
        this.setState({showCreateModal : false});
    },

    render : function(){
        return (
            <div style={{marginTop:"10px"}}>
                <button type="button" className="btn btn-primary btn-xs" onClick={this.openCreateModal}>Add Access</button>
                {this.state.showCreateModal &&
                    <UserAccessCreateModal 
                        show={this.state.showCreateModal}
                        onHide={this.closeCreateModal}
                        userId={this.props.userId}
                        notify={this.props.notify}
                        reload={this.reload}
                    />
                }
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="user_access_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Barangay</th>
                            <th>Valid Until</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <td></td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="province_select2" className="form-control form-filter input-sm" name="provinceCode">
                                </select>
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                </select>
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                </select>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.UserAccessDatatable = UserAccessDatatable;