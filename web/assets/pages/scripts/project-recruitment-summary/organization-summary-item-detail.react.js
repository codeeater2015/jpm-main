var Modal = ReactBootstrap.Modal;

var OrganizationSummaryItemDetail = React.createClass({
    
    getInitialState : function(){
      return {
          target : null,
          typingTimer : null,
          doneTypingInterval : 1500
      }
    },

    getInitialProp : function(){
        return {
            provinceCode : null,
            municipalityNo : null,
            brgyNo : null,
            precinctNo : null,
            voterGroup : null
        }
    },

    componentDidMount : function(){
       this.gridTable();
    },

    gridTable : function(){
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_table");
       
        grid.init({
            src: voter_table,
            dataTable : {
                "bState" : true,
                "autoWidth": true,
                "serverSide": true,
                "processing" : true,
                "deferRender" : true,
                "dom" : '<"top">rt<"bottom"p><"clear">',
                "ajax" : {
                    "url" : Routing.generate('ajax_datatable_organization_summary_item_detail'),
                    "type" : "GET",
                    "data" : function(d){
                        d.electId = self.props.electId;
                        d.proId = self.props.proId;
                        d.provinceCode = self.props.provinceCode;
                        d.municipalityNo = self.props.municipalityNo
                        d.brgyNo = self.props.brgyNo;
                        d.precinctNo = self.props.precinctNo;
                        d.voterGroup = self.props.voterGroup;
                        d.voterName = $('#voter_table input[name="voter_name"]').val();
                    }
                },
                columnDefs : [
                    {
                        'className': 'text-center valign-middle',
                        'orderable' : false,
                        'targets' : [0,5,6]
                    }
                ],
                "order": [
                    [1, "asc"]
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
                        "data" : "voter_name",
                        "className" : "text-center",
                        "width" : 90,
                        "render" : function(data,type,row){
                            var photoUrl = window.imgUrl + self.props.proId + '_' + row.pro_id_code + "?" + new Date().getTime();
                            return '<img src="' + photoUrl + '" class="img-responsive"/><strong style="margin-top:10px;">' + data + '</strong>';
                        }
                    },
                    {
                        "data" : "voted_2017",
                        "width" : 40,
                        "className" : "text-center",
                        "render" : function(data){
                            return data == 1 ? "YES" : "NO";
                        }
                    },
                    {
                        "data" : "barangay_name",
                        "className" : "text-center",
                        "width" : "150px"
                    },

                    {
                        "data" : "precinct_no",
                        "className" : "text-center",
                        "width" : "80px"
                    },
                    {
                        "data" : "voter_group",
                        "className" : "text-center"
                    },
                    {
                        "data" : "cellphone"                    
                    }
                ]
            }

        });

        self.grid = grid;
    },

    reload : function(){
        this.grid.getDataTable().ajax.reload();
    },

    
    handleFilterChange : function(){
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function(){
            self.reload();
        },this.state.doneTypingInterval);
    },

    isEmpty : function(value){
        return value == null || value == "" || value == "undefined";
    },

    render : function(){

        var data = this.isEmpty(this.grid) ?  {} : this.grid.getDataTable().ajax.params();


        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-custom-85" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Organization Summary Item Details</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body">
                    <div className="row">
                        <div className="col-md-12">
                            <table id="voter_table" className="table table-striped table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>2016</th>
                                        <th>Barangay</th>
                                        <th>Precinct</th>
                                        <th>Position</th>
                                        <th>Cellphone No</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style={{padding: "10px 5px"}}>
                                            <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                        </td>
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
                </Modal.Body>
            </Modal>
        )
    }
   
});

window.OrganizationSummaryItemDetail = OrganizationSummaryItemDetail;