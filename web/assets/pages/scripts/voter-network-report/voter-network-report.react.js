var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;


var VoterNetworkReportComponent = React.createClass({

    getInitialState : function(){
        return {
            provinceCode : null,
            municipalityNo : null,
            brgyNo : null,
            data : [],
            barangay : {
                total_voter : 0 ,
                total_recruits : 0,
                percentage : 0
            },
            displayMode : "HIERARCHY_LIST"
        }
    },

    notify : function(message,color){
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    
   
    render : function(){
        var self = this;
        var exportUrl =  Routing.generate("ajax_export_network_report_nodes",{
            municipalityNo : self.state.municipalityNo,
            brgyNo : self.state.brgyNo
        });

        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <div className="row">
                        <div className="col-md-3">
                            <form>
                                <FormGroup controlId="formProvinceCode" >
                                    <ControlLabel > Province : </ControlLabel>
                                    <select  id="form-province-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>
                                <FormGroup controlId="formMunicipalityNo" >
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select  id="form-municipality-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>
                                <FormGroup controlId="formBarangayNo">
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="form-barangay-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>
                                <input 
                                    type="checkbox" 
                                    ref="bsSwitch" 
                                    id="display_mode" 
                                    data-handle-width="120" 
                                    data-on-text="Hierarchy List" 
                                    data-off-text="By Precinct" 
                                    data-size="small"
                                    className="make-switch" 
                                    data-on-color="success" 
                                    data-off-color="danger"
                                />
                            </form>
                            <button className="btn btn-primary btn-sm" style={{width : "100%", marginTop:"35px"}} onClick={this.apply}>Apply</button>
                        </div>
                        <div className="col-md-9">
                               
                            {
                                this.state.showTable && 
                                this.state.displayMode == 'HIERARCHY_LIST' &&
                                <VoterNetworkReportHierarchyList 
                                    provinceCode={this.state.provinceCode}
                                    municipalityNo={this.state.municipalityNo} 
                                    brgyNo={this.state.brgyNo}/>
                            }
                            {
                                this.state.showTable && 
                                this.state.displayMode == 'BY_PRECINCT' &&
                                <VoterNetworkReportDatatable 
                                    provinceCode={this.state.provinceCode}
                                    municipalityNo={this.state.municipalityNo} 
                                    brgyNo={this.state.brgyNo}/>
                            }
                        </div>
                    </div>
                </div>
            </div>
        );
    },

    componentDidMount : function(){
        this.loadUser(window.userId);
        this.initSelect2();
        this.initSwitch();
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

        $("#form-province-select2").select2({
            casesentitive : false,
            placeholder : "Enter Name...",
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

        $("#form-municipality-select2").select2({
            casesentitive : false,
            placeholder : "Enter Name...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_municipality_strict'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        provinceCode : $("#form-province-select2").val()
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

        $("#form-barangay-select2").select2({
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
                        provinceCode : $("#form-province-select2").val(),
                        municipalityNo : $("#form-municipality-select2").val()
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

          
        $("#form-province-select2").on("change", function() {
            $("#form-municipality-select2").empty().trigger('change');
            $("#form-barangay-select2").empty().trigger('change');
            self.setState({ "provinceCode" : $(this).val() });
        });

        $("#form-municipality-select2").on("change", function() {
            $("#form-barangay-select2").empty().trigger('change');
            self.setState({ "municipalityNo" : $(this).val() });
        });

        $("#form-barangay-select2").on("change", function() {
            self.setState({ "brgyNo" : $(this).val() });
        });
    },

    reinitSelect2 : function(){
        var self = this;
        var provinceCode = self.state.user.province.provinceCode;

        self.requestProvince = $.ajax({
            url : Routing.generate("ajax_get_province", {provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            $("#form-province-select2").empty()
            .append($("<option/>")
                .val(res.province_code)
                .text(res.name))
            .trigger("change");
        });

        if(!self.state.user.isAdmin)
            $("#form-province-select2").attr('disabled','disabled');
    },

    initSwitch : function(){
        var self = this;
        $("#display_mode").bootstrapSwitch({state : true});
        $("#display_mode").on('switchChange.bootstrapSwitch', function(event, state) {
            var displayMode = state ? "HIERARCHY_LIST" : "BY_PRECINCT"; 
            self.setState({displayMode : displayMode});
        });
    },

    isEmpty : function(value){
        return value == "" || value == null || value == 'undefined';
    },
    
    apply  : function(){
        var provinceCode = this.state.provinceCode;
        var municipalityNo = this.state.municipalityNo;
        var brgyNo = this.state.brgyNo;
        var self = this;
        if(!this.isEmpty(municipalityNo) && !this.isEmpty(brgyNo) && !this.isEmpty(provinceCode)){
            self.setState({ showTable : false});            
            setTimeout(function(){
                self.setState({showTable : true});
            },500);
        }else{
            alert("Barangay cannot be empty.");
        }
    }
});

setTimeout(function(){
    ReactDOM.render(
    <VoterNetworkReportComponent />,
        document.getElementById('voter-container')
    );
},500);
