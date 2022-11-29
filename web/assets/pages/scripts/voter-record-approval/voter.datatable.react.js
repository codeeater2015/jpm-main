var VoterDatatable = React.createClass({

    getInitialState : function(){
      return {
          showApprovalModal : false,
          showViewModal : false,
          target : null,
          typingTimer : null,
          doneTypingInterval : 1500,
          fiscalYears : [],
          summary : {
              recordsFiltered : 0,
              obrTotal : 0
          }
      }
    },

    componentDidMount : function(){
        // this.initDatePicker();
        this.gridTable();
        this.initSelect2();
        // this.loadFiscalYears();
    },

    initSelect2 : function(){
        var self = this;

        $("#voter_table #precinct_select2").select2({
            casesentitive : false,
            placeholder : "Enter Precinct...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_precinct_no'),
                data :  function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.precinct_no , text: item.precinct_no};
                        })
                    };
                },
            }
        });
        
        $("#voter_table #municipality_select2").select2({
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
                        searchText: params.term
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

        $("#voter_table #barangay_select2").select2({
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
                        municipalityNo : $("#voter_table #municipality_select2").val()
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

        $("#voter_table #municipality_select2").on("change", function() {
            self.handleFilterChange();
        });

        $("#voter_table #barangay_select2").on("change", function() {
            self.handleFilterChange();
        });

        $("#voter_table #precinct_select2").on("change", function() {
            self.handleFilterChange();
        });
    },
    
    gridTable : function(){
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_table");

        grid.init({
            src: voter_table,
            onSuccess : function(grid, response){
                var summary = self.state.summary;
                summary.recordsFiltered = response.recordsFiltered;
                summary.obrTotal  = response.obrTotal;
                console.log(response);
                self.setState({summary : summary});
            },
            dataTable : {
                "bState" : true,
                "autoWidth": true,
                "serverSide": true,
                "processing" : true,
                "deferRender" : true,
                "ajax" : {
                    "url" : Routing.generate('ajax_datatable_voter_approval'),
                    "type" : "GET",
                    "data" : function(d){
                        d.municipalityNo = $('#voter_table #municipality_select2').val();
                        d.brgyNo = $('#voter_table #barangay_select2').val();
                        d.precinctNo = $('#voter_table #precinct_select2').val();
                        d.voterName = $('#voter_table input[name="voter_name"]').val();
                    }
                },
                columnDefs : [
                    {
                        'className': 'text-center valign-middle',
                        'orderable' : false,
                        'targets' : [0,3,4,5,6,7,8]
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
                    },
                    {
                        "data" : "precinct_no",
                        "className" : "text-center",
                        "width" : "80px"
                    },
                    {
                        "data" : "municipality_name",
                        "width" : "150px"
                    },
                    {
                        "data" : "barangay_name",
                        "className" : "text-center",
                        "width" : "100px"
                    },
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : "30px",
                        "render" : function(data,type,row){
                            var voterStatus  = '';
                            
                            voterStatus += row.has_ast == 1 ? "*" : "";
                            voterStatus += row.has_a == 1 ? "A" : "";
                            voterStatus += row.has_b == 1 ? "B" : "";
                            voterStatus += row.has_c == 1 ? "C" : "";

                            return voterStatus;
                        }
                    },
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : "10px",
                        "render" : function(data,type,row){
                            var voterClass  = '';
                            
                            voterClass += row.is_1 == 1 ?  "1," : "";
                            voterClass += row.is_2 == 1 ?  "2," : "";
                            voterClass += row.is_3 == 1 ?  "3," : "";
                            voterClass += row.is_4 == 1 ?  "4," : "";
                            voterClass += row.is_5 == 1 ?  "5," : "";
                            voterClass += row.is_6 == 1 ?  "6," : "";
                            voterClass += row.is_7 == 1 ?  "7,"  : "";
                            voterClass = voterClass.slice(0,voterClass.lastIndexOf(","));

                            return voterClass;
                        }
                    },
                    {
                        "data" : "updated_at",
                        "width" : "140px",
                        "className" : "text-center",
                        "render" : function(data){
                            return moment(data).format('MM/DD/YYYY hh:mm A')
                        }
                    },
                    {
                        "width" : 100,
                        "render" : function(){
                            var btnGroup = '<button class="btn btn-xs blue-madison  approve-btn">Approve</button>';
                            btnGroup += '<button class="btn btn-xs green view-btn"><i class="fa fa-search"></i></button>';
                            return btnGroup;
                        },
                        "className" : "dt-body-center"
                    }
                ]
            }

        });

        voter_table.on( 'click', '.edit-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.approve(data.voter_id);
        });

        voter_table.on( 'click', '.view-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.view(data.voter_id);
        });


        self.grid = grid;
    },

    openEntryModal : function(){
        console.log("open create modal");
        this.setState({showEntryModal : true});
    },

    openUploadModal : function(){
        this.setState({showUploadModal : true});
    },

    approve : function(voterId){
        //approve here
    },

    view : function(target){
        this.setState({showViewModal : true , target : target});
    },

    openApprovalModal : function(){
        this.setState({showApprovalModal : true});
    },

    closeApprovalModal : function(){
        this.setState({showApprovalModal : false});
        this.reload();
    },

    closeViewModal : function(){
        this.setState({showViewModal : false, target : null});
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
                <div className="row">
                    <div className="col-md-6">
                        <button className="btn btn-primary btn-sm" onClick={this.openApprovalModal}>Batch Approval</button>
                    </div>
                    <div className="col-md-6 text-right">
                        <form onSubmit={this.onApplyCode}>
                            <div className="col-md-4 col-md-offset-5" style={{paddingRight : "0px"}}>
                                <input type="text" id="input_access_code" onChange={this.setAccessCode} className="form-control input-sm"/>
                            </div>
                            <div className="col-md-3">
                                <button type="submit" className="btn btn-primary btn-sm">Apply Code</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                {
                    this.state.showApprovalModal &&
                    <VoterApprovalModal
                        show={this.state.showApprovalModal}
                        onHide={this.closeApprovalModal}
                        notify={this.props.notify}
                    />
                }

                {this.state.showViewModal &&
                    <VoterViewModal
                        show={this.state.showViewModal}
                        onHide={this.closeViewModal}
                        notify={this.props.notify}
                        voterId={this.state.target}
                    />
                }

                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="voter_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Precinct No</th>
                            <th>Municipality</th>
                            <th>Brgy</th>
                            <th>Status</th>
                            <th>Tag</th>
                            <th>Last Update</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td style={{padding: "10px 5px"}}>
                                <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="precinct_select2" className="form-control form-filter input-sm" name="precinct_no">
                                </select>
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="municipality_select2" className="form-control form-filter input-sm" name="precinct_no">
                                </select>
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="barangay_select2" className="form-control form-filter input-sm" name="precinct_no">
                                </select>
                            </td>
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
    },

    setAccessCode : function(e){
        this.setState({"accessCode" : e.target.value});
    },

    onApplyCode : function(e){
        console.log("apply code");
        e.preventDefault();

        var self = this;

        self.requestApplyCode = $.ajax({
            url : Routing.generate("ajax_apply_access_code",{ accessCode : this.state.accessCode }),
            type : "GET"
        }).done(function(res){
            self.reload();
            console.log("codes has been applied");
        }).fail(function(){
            console.log('invalid code');
        });
    }
});

window.VoterDatatable = VoterDatatable;