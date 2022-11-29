var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOptNewMemberModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null,
                    proIdCode: "",
                    cellphone : "",
                    is1 : 1,
                    is2 : 0,
                    is9 : 0
                },
                errors: []
            }
        };
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;
        
        $("#special_opt_member_form #municipality_select2").select2({
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

        $("#special_opt_member_form #barangay_select2").select2({
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
                        municipalityNo: $("#special_opt_member_form #municipality_select2").val(),
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

        $("#special_opt_member_form #voter-recruit-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
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
                        municipalityNo :  $("#special_opt_member_form #municipality_select2").val(),
                        brgyNo :  $("#special_opt_member_form #barangay_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var text = item.voter_name + ' - ' + item.precinct_no + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' )';
                            return { id: item.voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#special_opt_member_form #voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

        $("#special_opt_member_form #voter-recruit-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#special_opt_member_form #voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
    },
    
    loadVoter: function (proId, voterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, voterId: voterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
         
            form.data.voterId = res.voterId;
            form.data.proVoterId = res.proVoterId;
            form.data.proIdCode = res.proIdCode;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? "" : res.cellphoneNo;
            form.data.precinctNo = res.precinctNo;
            form.data.assignedPrecinct = self.isEmpty(res.assignedPrecinct) ? res.precinctNo : res.assignedPrecinct;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? "KFC" : res.voterGroup;
            form.data.remarks = self.isEmpty(res.remarks) ? "" : res.remarks;

            console.log("opt type");
            console.log(self.props.optType);

            if(self.props.optType == "REGULAR"){
                form.data.is2 = 0;
                form.data.is9 = 1;
            }else if(self.props.optType == "PULAHAN" ){
                form.data.is2 = 1;
                form.data.is9 = 0;
            }

            form.data.is3 = res.is3;
            form.data.is4 = res.is4;
            form.data.is5 = res.is5;
            form.data.is6 = res.is6;
            form.data.is7 = res.is7;
            form.data.is8 = res.is8;
            form.data.is10 = res.is10;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(form.data.voterGroup)
                    .text(form.data.voterGroup))
                .trigger("change");

            self.setState({ form: form }, self.initUploader);
            
        });
    },

    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setErrors: function (errors) {
        var form = this.state.form;
        form.errors = errors;
        this.setState({ form: form });
    },

    getError: function (field) {
        var errors = this.state.form.errors;
        for (var errorField in errors) {
            if (errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState: function (field) {
        return this.getError(field) != null ? 'error' : '';
    },

    isEmpty: function (value) {
        return value == null || value == '';
    },

    reset: function () {
        var form = this.state.form;
        form.errors = [];

        $("#special_opt_member_form #voter-recruit-select2").empty().trigger("change");
        $("#special_opt_member_form #voter-group-select2").empty().trigger("change");

        this.setState(this.getInitialState());
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.hdrId = self.props.hdrId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_special_opt_detail"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    },

    render: function () {
        var self = this;
        var photoUrl = window.imgUrl + this.props.proId + '_' + this.state.form.data.proIdCode + "?" + new Date().getTime();

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="special_opt_member_form" onSubmit={this.submit}>
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
                                    <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterId')}>
                                        <ControlLabel > Voter Name : </ControlLabel>
                                        <select id="voter-recruit-select2" className="form-control input-sm">
                                        </select>
                                        <HelpBlock>{this.getError('voterId')}</HelpBlock>
                                    </FormGroup>
                                </div>
                                
                                <div className="col-md-3">
                                    <img src={photoUrl} className="img-responsive" alt="" />
                                </div>

                                <div className="col-md-9" >
                                    <div className="col-md-4">
                                        <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                            <ControlLabel > CellphoneNo : </ControlLabel>
                                            <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                            <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                    <div className="col-md-3">
                                        <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                            <ControlLabel >Position : </ControlLabel>
                                            <select id="voter-group-select2" className="form-control input-sm">
                                                <option value=""> </option>
                                            </select>
                                            <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                    <div className="col-md-4">
                                        <FormGroup controlId="formOptType" validationState={this.getValidationState('optType')}>
                                            <ControlLabel >Type : </ControlLabel>
                                            <select name="optType" onChange={this.setOptType}  value={this.state.form.data.optType} className="form-control input-sm">
                                                <option value="REGULAR">Regular</option>
                                                <option value="PULAHAN">Pulahan</option>
                                            </select>
                                            <HelpBlock>{this.getError('optType')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                    {/* Newline */}
                                    <div className="clearfix"/>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            1
                                            <input type="checkbox" checked={this.state.form.data.is1 == 1} onChange={this.handleCheckbox} name="is1"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            2
                                            <input type="checkbox" checked={this.state.form.data.is2 == 1} onChange={this.handleCheckbox} name="is2"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            3
                                        <input type="checkbox" checked={this.state.form.data.is3 == 1} onChange={this.handleCheckbox} name="is3"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            4
                                        <input type="checkbox" checked={this.state.form.data.is4 == 1} onChange={this.handleCheckbox} name="is4"></input>
                                            <span></span>
                                        </label>
                                    </div>
                                    
                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            5
                                        <input type="checkbox" checked={this.state.form.data.is5 == 1} onChange={this.handleCheckbox} name="is5"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    {/* Next line */} 
                                    <div className="clearfix"/>
                                    
                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            6
                                        <input type="checkbox" checked={this.state.form.data.is6 == 1} onChange={this.handleCheckbox} name="is6"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            7
                                        <input type="checkbox" checked={this.state.form.data.is7 == 1} onChange={this.handleCheckbox} name="is7"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            8
                                        <input type="checkbox" checked={this.state.form.data.is8 == 1} onChange={this.handleCheckbox} name="is8"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            9
                                        <input type="checkbox" checked={this.state.form.data.is9 == 1} onChange={this.handleCheckbox} name="is9"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-2">
                                        <label className="mt-checkbox status-checkbox">
                                            10
                                        <input type="checkbox" checked={this.state.form.data.is10 == 1} onChange={this.handleCheckbox} name="is10"></input>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-12">
                                        <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                            <ControlLabel > Remarks : </ControlLabel>
                                            <textarea rows="2" value={this.state.form.data.remarks} className="input-sm form-control" onChange={this.setFormProp} name="remarks">
                                            </textarea>
                                            <HelpBlock>{this.getError('remarks')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>
                              
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" style={{ marginRight : "10px" }} disabled={this.isEmpty(this.state.form.data.voterId)} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }

});

window.SpecialOptNewMemberModal = SpecialOptNewMemberModal;