var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectRecruitmentAddMemberModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    voterId: null,
                    cellphone: "",
                    voterGroup: "",
                    assignedPrecinct: "",
                    precinctNo: "",
                    remarks: "",
                    inputVoterGroup : "KFC"
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

        $("#municipality_select2").select2({
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
                        provinceCode: 53
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

        $("#barangay_select2").select2({
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
                        municipalityNo: $("#municipality_select2").val(),
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

        $("#form-voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 3000,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: "custom-option",
            ajax: {
                url: Routing.generate('ajax_select2_project_voters_strict'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        proId : self.props.proId,
                        electId : self.props.electId,
                        provinceCode : self.props.provinceCode, 
                        municipalityNo : $("#municipality_select2").val(),
                        brgyNo : $("#barangay_select2").val()
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

        $("#voter-group-select2").select2({
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
                        searchText: params.term,
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

        $("#form-voter-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#voter-group-select2").on("change", function () {
            self.setFieldValue("voterGroup", $(this).val());
        });


        $("#municipality_select2").empty()
            .append($("<option/>")
                .val(this.props.municipalityNo)
                .text(this.props.municipalityName))
            .trigger("change");
        
        $("#barangay_select2").empty()
            .append($("<option/>")
                .val(this.props.brgyNo)
                .text(this.props.barangayName))
            .trigger("change");

    },

    loadVoter: function (proId, voterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, voterId: voterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            form.data.proVoterId = res.proVoterId;
            form.data.voterId = res.voterId;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? self.state.form.data.inputVoterGroup : res.voterGroup;
            form.data.assignedPrecinct = self.isEmpty(res.assignedPrecinct) ? '' : res.assignedPrecinct;
            form.data.precinctNo = self.isEmpty(res.precinctNo) ? '' : res.precinctNo;

            form.data.remarks = res.remarks;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(form.data.voterGroup)
                    .text(form.data.voterGroup))
                .trigger("change");

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.cellphone = '';
        form.data.voterGroup = '';
        form.data.remarks = '';

        self.setState({ form: form })
    },

    reset: function () {
        var form = this.state.form;
        form.data.proVoterId = "";
        form.data.cellphone = "";
        form.data.remarks = "";

        form.errors = [];

        $("#form-voter-select2").empty().trigger("change");

        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setFieldValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
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

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        
        data.recId = self.props.recId;
        data.proId = self.props.proId;

        self.requestAddAttendee = $.ajax({
            url: Routing.generate("ajax_post_project_recruitment_member"),
            type: "POST",
            data: data
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
            self.props.notify("Member has been added.", "teal");
        }).fail(function (err) {
            if (err.status == '401') {
                self.props.notify("You dont have the permission to update this record.", "ruby");
            } else {
                self.props.notify("Form Validation Failed.", "ruby");
            }
            self.setErrors(err.responseJSON);
        });
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-custom-40" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Member Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-node-form" onSubmit={this.submit}>
                        <div className="row">

                            <div className="col-md-12">
                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formMunicipalityNo">
                                        <ControlLabel > Municipality : </ControlLabel>
                                        <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingRight: "0" }}>
                                    <FormGroup controlId="formBrgyNo">
                                        <ControlLabel > Barangay : </ControlLabel>
                                        <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <FormGroup controlId="formProVoterId" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel > Voter Name : </ControlLabel>
                                    <select id="form-voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                        <ControlLabel > Cellphone No : </ControlLabel>
                                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                        <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-3" style={{ paddingRight: "0" }}>
                                    <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                        <ControlLabel> Position : </ControlLabel>
                                        <select id="voter-group-select2" className="form-control input-sm">
                                            <option value=""> </option>
                                        </select>
                                        <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-3" style={{ paddingRight: "0" }}>
                                    <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('inputVoterGroup')}>
                                        <ControlLabel> Default Position : </ControlLabel>
                                        <input type="text" value={this.state.form.data.inputVoterGroup} 
                                         className="input-sm form-control" onChange={this.setFormProp} name="inputVoterGroup" />
                                    </FormGroup>
                                </div>

                                <div className="clearfix"/>

                                <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                    <ControlLabel > Remarks : </ControlLabel>
                                    <textarea rows="5" value={this.state.form.data.remarks} className="input-sm form-control" onChange={this.setFormProp} name="remarks">
                                    </textarea>
                                    <HelpBlock>{this.getError('remarks')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" disabled={this.isEmpty(this.state.form.data.proVoterId)} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.ProjectRecruitmentAddMemberModal = ProjectRecruitmentAddMemberModal;