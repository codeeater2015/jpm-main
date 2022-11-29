var VoterDatatable = React.createClass({

    getInitialState : function(){
      return {
          showEntryModal : false,
          showEditModal : false,
          showUploadModal : false,
          showViewModal : false,
          target : null,
          typingTimer : null,
          doneTypingInterval : 1500,
          fiscalYears : [],
          summary : {
              recordsFiltered : 0,
              obrTotal : 0
          },
          user : null
      }
    },

    componentDidMount : function(){
        this.loadUser(window.userId);
        this.initSelect2();
    },

    loadUser : function(userId){
        var self = this;

        self.requestUser = $.ajax({
            url : Routing.generate("ajax_get_user",{id : userId}),
            type : "GET"
        }).done(function(res){
            self.setState({user : res},self.reinitSelect2);
        });
    },

    initSelect2 : function(){
        var self = this;

        
        $("#voter_component #province_select2").select2({
            casesentitive : false,
            placeholder : "Enter Province...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_province_strict'),
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
        
        $("#voter_table #municipality_select2").select2({
            casesentitive : false,
            placeholder : "Enter Name...",
            allowClear : true,
            delay : 1500,
            width : 100,
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_municipality_strict'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        provinceCode : $('#voter_component #province_select2').val(),
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
                url : Routing.generate('ajax_select2_barangay_strict'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        provinceCode : $('#voter_component #province_select2').val(),
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

        $("#voter_table #precinct_select2").select2({
            casesentitive : false,
            placeholder : "Enter Precinct...",
            allowClear : true,
            delay : 1500,
            width : 70,
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

    
    reinitSelect2 : function(){
        var self = this;
        var provinceCode = self.state.user.province.provinceCode;

        self.requestProvince = $.ajax({
            url : Routing.generate("ajax_get_province", {provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            console.log("province loaded.");
            $("#voter_component #province_select2").empty()
            .append($("<option/>")
                .val(res.province_code)
                .text(res.name))
            .trigger("change");
        });

        if(!self.state.user.isAdmin)
            $("#voter_component #province_select2").attr('disabled','disabled');

        self.gridTable();
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
                "deferLoading" : 0,
                "ajax" : {
                    "url" : Routing.generate('ajax_datatable_voter_update'),
                    "type" : "GET",
                    "data" : function(d){
                        d.provinceCode = $('#voter_component #province_select2').val();
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
                        'targets' : [0,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]
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
                        "width" : "30px"
                    },
                    {
                        "data" : "municipality_name",
                        "width" : "100px"
                    },
                    {
                        "data" : "barangay_name",
                        "className" : "text-center",
                        "width" : "100px"
                    },
                    {
                        "data" : "has_ast",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="hasAst" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "has_a",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="hasA" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "has_b",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="hasB" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "has_c",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="hasC" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "is_1",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is1" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },

                    {
                        "data" : "is_2",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is2" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "is_3",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is3" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "is_4",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is4" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },

                    {
                        "data" : "is_5",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is5" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "is_6",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is6" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "is_7",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="is7" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data" : "voted_2017",
                        "className" : "dt-body-center",
                        "width" : 20,
                        "render" : function(data,type,row){
                            return '<label class="mt-checkbox req-checkbox"><input type="checkbox" name="voted2017" ' + ( (parseInt(data) == 1) ? ' checked="checked" ' : '')  + ' value="' + row.voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "width" : 60,
                        "render" : function(){
                            var btnGroup = '<button class="btn btn-xs blue-madison  edit-btn"><i class="fa fa-mobile-phone"></i></button>';
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
            self.edit(data.voter_id);
        });

        voter_table.on( 'click', '.view-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.view(data.voter_id);
        });

        voter_table.on('click','.req-checkbox',function(e){
            var voterId = e.target.value;
            var checked = e.target.checked;
            var fieldName = e.target.name;
            var newValue = checked ? 1 : 0;

            console.log("voter id");
            console.log(voterId);
            console.log("field name");
            console.log(fieldName);
            console.log("current value");
            console.log(checked);
            console.log("new value");
            console.log(newValue);
            
            if(voterId != null && checked != null){
                self.patch(voterId,fieldName,newValue);
            }
        });

        self.grid = grid;
    },

    patch : function(voterId,fieldName,value){
        var self = this;
        var data = {};

        data[fieldName] = value;
        self.requestToggle = $.ajax({
            url : Routing.generate("ajax_patch_voter",{voterId : voterId}),
            type : "PATCH",
            data : (data)
        }).done(function(res){
            console.log("voter has been patched");
        });
    },

    openEntryModal : function(){
        console.log("open create modal");
        this.setState({showEntryModal : true});
    },

    openUploadModal : function(){
        this.setState({showUploadModal : true});
    },

    edit : function(target){
        this.setState({showEditModal : true, target : target});
    },

    view : function(target){
        this.setState({showViewModal : true , target : target});
    },

    closeEntryModal : function(){
        this.setState({showEntryModal : false});
        this.reload();
    },

    closeViewModal : function(){
        this.setState({showViewModal : false, target : null});
    },

    closeUploadModal : function(){
        this.setState({showUploadModal : false});
        this.reload();
    },

    closeEditModal : function(){
        this.setState({showEditModal : false, target : null});
        this.reload();
    },

    handleFilterChange : function(){
        // var self = this;
        // clearTimeout(this.state.typingTimer);
        // this.state.typingTimer = setTimeout(function(){
        //     self.reload();
        // },this.state.doneTypingInterval);
    },


    reload : function(){
        this.grid.getDataTable().ajax.reload();
    },

    render : function(){
        return (
            <div>
                <div className="row" id="voter_component">
                    <div className="col-md-2 col-md-offset-10">
                        <form >
                            <select id="province_select2" className="form-control form-filter input-sm" >
                            </select>
                        </form>
                    </div>
                </div>

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
                            <th>Precinct</th>
                            <th>Municipality</th>
                            <th>Brgy</th>
                            <th>*</th>
                            <th>a</th>
                            <th>b</th>
                            <th>c</th>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                            <th>4</th>
                            <th>5</th>
                            <th>6</th>
                            <th>7</th>
                            <th>16</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td style={{padding: "10px 5px"}}>
                                <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                            </td>
                            <td style={{padding: "10px 5px"}}>
                                <select id="precinct_select2" style={{width:"30px"}} className="form-control form-filter input-sm" name="precinct_no">
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <button style={{ marginTop : "5px", marginBottom : "5px" }}className="btn btn-xs green btn-outline filter-submit">
                                    <i className="fa fa-search"/>Search 
                                </button>
                            </td>
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