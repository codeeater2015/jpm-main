var UserDatatable = React.createClass({

    getInitialState : function(){
      return {
          showAccessModal : false,
          target : null,
          typingTimer : null,
          doneTypingInterval : 1500
      }
    },

    componentDidMount : function(){
        this.initDatatable();
    },

    initDatatable : function(){
        var self = this;
        var grid = new Datatable();
        
        var user_table = $("#user_table");
        var grid_user = new Datatable();
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
                    'targets' : [0,2,3,4,5,6,7]
                }, {
                    'className': 'align-center',
                    'targets': [0,3,4,5,6,7]
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
                        "data" : "provinceName",
                        "width" : 100
                    },
                    {
                        "data": "groupId",
                        "className" : "text-center",
                        "render" : function(data,type,row){
                            return data == 1 ? 'END OF TIME' : (row.validUntil == null ? "NOT SET" : moment(row.validUntil).format("MMM DD YYYY hh:mm:ss A"));
                        }
                    },
                    { "render" : function(data,type,row){
                        var changeBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark user-permission-button' data-toggle='tooltip' data-title='Change Password'><i class='fa fa-lock' ></i></a>";
                        return (row.isDefault === "YES") ? "<span class='badge badge-info'>Default</span>" : changeBtn;
                    }}
                ],
            }
        });

        user_table.on( 'click', '.user-permission-button', function () {
            var data =  grid_user.getDataTable().row($(this).parents('tr') ).data();
            self.setState({showAccessModal : true, target : data.id});
        });

        self.grid = grid_user;
    },


    closeAccessModal : function(){
        this.setState({ showAccessModal : false, target : null});
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

    render : function(){
        return (
            <div>
                {this.state.showAccessModal &&
                    <UserAccessModal 
                        show={this.state.showAccessModal}
                        onHide={this.closeAccessModal}
                        userId={this.state.target}
                        notify={this.props.notify}
                    />
                }
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="user_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Gender</th>
                            <th>Group</th>
                            <th>Province</th>
                            <th>Valid Until</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td style={{padding: "10px 5px"}}>
                                <input type="text" className="form-control form-filter input-sm" name="idx_no" onChange={this.handleFilterChange} />
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
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

window.UserDatatable = UserDatatable;