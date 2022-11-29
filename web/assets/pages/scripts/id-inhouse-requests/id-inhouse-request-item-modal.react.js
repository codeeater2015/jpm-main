var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var IdInhouseRequestItemModal = React.createClass({

    getInitialState: function () {
        return {
            voterId : null
        };
    },
    
    render: function () {
        var self = this;
      
        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div id="inhouse_id_request_item_form">
                        <div className="row">
                            <div className="col-md-12">

                                <div className="col-md-4">
                                    <FormGroup controlId="formMunicipalityNo">
                                        <ControlLabel > Municipality : </ControlLabel>
                                        <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <div className="col-md-3" >
                                    <FormGroup controlId="formBrgyNo">
                                        <ControlLabel > Barangay : </ControlLabel>
                                        <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <div className="col-md-12">
                                    <FormGroup controlId="formVoterId" >
                                        <ControlLabel > Voter Name : </ControlLabel>
                                        <select id="voter-recruit-select2" className="form-control input-sm">
                                        </select>
                                    </FormGroup>
                                </div>
                                
                               {this.state.voterId != null &&
                                    <IdInhouseRequestVoterProfile
                                        electId={self.props.electId}
                                        proId= {self.props.proId}
                                        provinceCode= {self.props.provinceCode}
                                        voterId={self.state.voterId}
                                        hdrId={self.props.hdrId}
                                        reset={self.reset}
                                    />
                                }
                              
                            </div>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;
        
        $("#inhouse_id_request_item_form #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.props.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#inhouse_id_request_item_form #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#inhouse_id_request_item_form #municipality_select2").val(),
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#inhouse_id_request_item_form #voter-recruit-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            //allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters_strict'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: self.props.electId,
                        proId: self.props.proId,
                        provinceCode: self.props.provinceCode,
                        municipalityNo :  $("#inhouse_id_request_item_form #municipality_select2").val(),
                        brgyNo :  $("#inhouse_id_request_item_form #barangay_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var text = item.voter_name + ' - ' + item.precinct_no + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' )';
                            var disabled = (self.state.voterId != null || self.state.voterId == '' );

                            if(item.status != 'A'){
                                disabled = true;
                                text += " Opps! Voter either blocked or deactivated... Please notify the system administrator...";
                            }

                            return { id: item.voter_id, text: text , disabled : disabled };
                        })
                    };
                },
            }
        });

        $("#inhouse_id_request_item_form #voter-recruit-select2").on("change", function () {
            var voterId  = $(this).val();

            if(voterId == 'undefined' || voterId == '')
                voterId = null;

            self.setState({ voterId : voterId });
        });
               
    },

    reset : function(){
        $("#inhouse_id_request_item_form #voter-recruit-select2").empty().trigger("change");
        this.setState({voterId : null});
        this.props.onSuccess();
    }

});

window.IdInhouseRequestItemModal = IdInhouseRequestItemModal;