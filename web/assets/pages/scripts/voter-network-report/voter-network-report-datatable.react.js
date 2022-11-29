var VoterNetworkReportDatatable = React.createClass({
    
    getInitialState : function(){
      return {
          target : null,
          typingTimer : null,
          doneTypingInterval : 1500,
          barangay : {
            total_voter : 0 ,
            total_recruits : 0,
            percentage : 0,
            municipality : ''
         }
      }
    },

    getInitialProp : function(){
        return {
            municipalityNo : null,
            brgyNo : null
        }
    },

    componentDidMount : function(){
       this.gridTable();
       this.loadBarangay(this.props.provinceCode,this.props.municipalityNo, this.props.brgyNo);
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
                    "url" : Routing.generate('ajax_datatable_voter_summary_item_detail'),
                    "type" : "GET",
                    "data" : function(d){
                        d.provinceCode = self.props.provinceCode;
                        d.municipalityNo = self.props.municipalityNo;
                        d.brgyNo = self.props.brgyNo;
                        d.voterName = $('#voter_table input[name="voter_name"]').val();
                    }
                },
                columnDefs : [
                    {
                        'className': 'text-center valign-middle',
                        'orderable' : false,
                        'targets' : [0,4,5,6]
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
                        "render" : function(data,type,row){
                            var voted  = row.voted_2017 == 1;
                            return (voted ? "*" : "" ) + data;
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
                        "data" : "node_level",
                        "className" : 'text-center',
                        "width" : 30
                    },
                    {
                        "data" : "parent_id",
                        "className" : "text-center",
                        "width" : "80px",
                        "render" : function(data){
                            return data == 0 ? "YES" : "NO"
                        }
                    },
                    {
                        "data" : "parent_id",
                        "width" : 200,
                        "className" :"text-left",
                        "render" : function(data,type,row){
                            return row.parent.node_label;
                        }
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

    loadBarangay : function(provinceCode,municipalityNo,brgyNo){
        var self = this;
        self.requestItems = $.ajax({
            url : Routing.generate("ajax_get_baranagy_full",{
                provinceCode : provinceCode,
                municipalityNo : municipalityNo,
                brgyNo : brgyNo
            }),
            type : "GET"
        }).done(function(res){
            self.setState({barangay : res});
        });
    },

    render : function(){

        var data = this.isEmpty(this.grid) ?  {} : this.grid.getDataTable().ajax.params();

        var exportUrl =  Routing.generate("ajax_datatable_voter_summary_item_detail_download",{
            provinceCode : this.isEmpty(this.props.provinceCode) ? "" :  this.props.provinceCode,
            municipalityNo : this.isEmpty(this.props.municipalityNo) ? "" : this.props.municipalityNo,
            brgyNo : this.isEmpty(this.props.brgyNo) ? "" : this.props.brgyNo,
            precinctNo : this.isEmpty(this.props.precinctNo) ? "" : this.props.precinctNo,
            order : data.order,
            columns : data.columns
        });

        var exportUrl2 =  Routing.generate("ajax_export_network_report_nodes_option2",{
            provinceCode : this.isEmpty(this.props.provinceCode) ? "" :  this.props.provinceCode,
            municipalityNo : this.isEmpty(this.props.municipalityNo) ? "" : this.props.municipalityNo,
            brgyNo : this.isEmpty(this.props.brgyNo) ? "" : this.props.brgyNo,
            precinctNo : this.isEmpty(this.props.precinctNo) ? "" : this.props.precinctNo,
            order : data.order,
            columns : data.columns
        });

        return (
            <div className="row">
                <div className="col-md-8" style={{marginBottom:"10px"}}>
                    <div style={{marginBottom : "5px"}}>
                        <span className="bold">City/Municipality : <span className="font-red-sunglo">{this.state.barangay.municipality}</span></span>
                        <span className="bold" style={{marginLeft : "10px"}}>Barangay : <span className="font-red-sunglo">{this.state.barangay.name}</span> </span>
                    </div>
                    <div className="bold">
                        Registered : <span className="font-red-sunglo" style={{marginRight:"5px"}}>{this.state.barangay.total_voter}</span>
                        Recruited : <span className="font-red-sunglo" style={{marginRight:"5px"}}>{this.state.barangay.total_recruits}</span> 
                        Percentage : <span className="font-red-sunglo">{parseFloat(this.state.barangay.percentage).toFixed(2)} %</span>
                    </div>
                </div>
                <div className="col-md-4 text-right">
                    <a href={exportUrl} target="_self" className="btn btn-sm btn-danger" >Excel A</a>
                    <a href={exportUrl2} target="_self" className="btn btn-sm btn-danger" style={{marginLeft : "5px"}}>Excel B</a>
                </div>
                <div className="clearfix"/>
                <div className="col-md-12">
                    <table id="voter_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Barangay</th>
                                <th>Precinct</th>
                                <th>Level</th>
                                <th>Is Leader</th>
                                <th>Leader</th>
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
        )
    }
   
});

window.VoterNetworkReportDatatable = VoterNetworkReportDatatable;