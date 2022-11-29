var VoterAssistanceDatatable = React.createClass({

    getInitialState : function(){
      return {
          showCreateModal : false,
          typingTimer : null,
          doneTypingInterval : 1500
      }
    },

    componentDidMount : function(){
        this.gridTable();
    },
   
    gridTable : function(){
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_assistance_datatable");

        grid.init({
            src: voter_table,
            dataTable : {
                "bState" : true,
                "autoWidth": true,
                "serverSide": true,
                "processing" : true,
                "deferRender" : true,
                "dom": '<"top"i>rt<"bottom"lp><"clear">',
                "ajax" : {
                    "url" : Routing.generate('ajax_datatable_voter_assistance',{ voterId : self.props.voterId }),
                    "type" : "GET"
                },
                columnDefs : [
                    {
                        'className': 'text-center valign-middle',
                        'orderable' : false,
                        'targets' : [1,2,3,4,5]
                    }
                ],
                "order": [
                    [0, "desc"]
                ],
                "columns" : [
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data" : "description",
                        "className" : "text-left"
                    },
                    {
                        "data" : "category",
                        "width" : "150px"
                    },
                    {
                        "data" : "amount",
                        "className" : "text-right",
                        "width" : "100px",
                        "render" : function(data){
                            return self.numberWithCommas(data,2);
                        }
                    },
                    {
                        "data" : "issued_at",
                        "width" : "140px",
                        "className" : "text-center",
                        "render" : function(data){
                            return moment(data).format('MMM DD YYYY')
                        }
                    },
                    {
                        "className" : "text-center",
                        "width" : 20,
                        "render" : function(){
                            var btnGroup = '<button class="btn btn-xs btn-danger remove-btn"><i class="fa fa-trash"></i></button>';
                            return btnGroup;
                        }
                    }
                ]
            }

        });

        voter_table.on( 'click', '.remove-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.remove(data.ast_id);
        });

        self.grid = grid;
    },
    
    remove : function(astId){
        var self = this;

        if(confirm("Are you sure you want to removing this assistance?")){
            self.requestRemove = $.ajax({
                url : Routing.generate("ajax_delete_voter_assistance",{astId : astId}),
                type : "DELETE"
            }).done(function(res){
                self.reload();
                self.props.notify("Assistance has been removed.","teal");
            }).fail(function(err){
                if(err.status == '401'){
                    self.props.notify("You dont have the permission to perform this action.","ruby");
                }else if(err.status == '400'){
                    self.props.notify("Form Validation Failed.","ruby");
                    self.setErrors(err.responseJSON);
                }
            });
        }
    },

    reload : function(){
        this.grid.getDataTable().ajax.reload();
    },

    openCreateModal : function(){
        this.setState({showCreateModal : true});
    },

    closeCreateModal : function(){
        this.setState({showCreateModal : false});
        this.reload();
    },

    numberWithCommas : function(x,scale) {
        x = parseFloat(x).toFixed(scale);
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render : function(){
        return (
            <div className="col-md-12">
                <button className="btn btn-sm btn-primary" onClick={this.openCreateModal}>Add Assistance</button>
                {
                    this.state.showCreateModal &&
                    <VoterCreateAssistanceModal 
                        voterId={this.props.voterId}
                        show={this.state.showCreateModal}
                        onHide={this.closeCreateModal}
                        notify={this.props.notify}
                    />
                }
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="voter_assistance_datatable" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Issued Date</th>
                                <th></th>
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

window.VoterAssistanceDatatable = VoterAssistanceDatatable;