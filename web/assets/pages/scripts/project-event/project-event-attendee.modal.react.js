var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectEventAttendeeModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    voterId: null,
                    cellphone: "",
                    voterGroup: "",
                    position: "",
                    assignedPrecinct: "",
                    precinctNo: "",
                    remarks: ""
                },
                errors: []
            },
            tempVoter: {
                hasPhoto: "",
                hasId: "",
                lastEvent: {
                    event_name: ""
                }
            }
        };
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#form-voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 3000,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: "custom-option",
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        proId: self.props.proId,
                        electId: self.props.electId,
                        provinceCode: self.props.provinceCode,
                        municipalityNo: $("#municipality-select2").val()

                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var text = item.voter_name + ' - ' + item.precinct_no + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' )';
                            // var disabled = false;

                            // if(item.status != 'A'){
                            //     disabled = true;
                            //     text += " Opps! Voter either blocked or deactivated... Please notify the system administrator...";
                            // }


                            return { id: item.pro_voter_id, text: text };
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

        $("#municipality-select2").select2({
            casesentitive: false,
            placeholder: "Enter municipality...",
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

        $("#position-select2").select2({
            casesentitive: false,
            placeholder: "Enter Barangay Position",
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
                url: Routing.generate('ajax_select2_voter_position'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.position, text: item.position };
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

        $("#position-select2").on("change", function () {
            self.setFieldValue("position", $(this).val());
        });


        $("#municipality-select2").empty()
            .append($("<option/>")
                .val("11")
                .text("DUMARAN"))
            .trigger("change");
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.proVoterId = res.proVoterId;
            form.data.voterId = res.voterId;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? '' : res.voterGroup;
            form.data.position = self.isEmpty(res.position) ? '' : res.position;
            form.data.assignedPrecinct = self.isEmpty(res.assignedPrecinct) ? '' : res.assignedPrecinct;
            form.data.precinctNo = self.isEmpty(res.precinctNo) ? '' : res.precinctNo;
            form.data.is1 = res.is1;
            form.data.is2 = res.is2;
            form.data.is3 = res.is3;
            form.data.is4 = res.is4;
            form.data.is5 = res.is5;
            form.data.is6 = res.is6;
            form.data.is7 = res.is7;
            form.data.is8 = res.is8;
            form.data.is9 = res.is9;
            form.data.is10 = res.is10;
            form.data.remarks = res.remarks;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");


            $("#position-select2").empty()
                .append($("<option/>")
                    .val(res.position)
                    .text(res.position))
                .trigger("change");


            self.setState({ form: form, tempVoter: res });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.cellphone = '';
        form.data.voterGroup = '';
        form.data.position = '';
        form.data.is1 = null;
        form.data.is2 = null;
        form.data.is3 = null;
        form.data.is4 = null;
        form.data.is5 = null;
        form.data.is6 = null;
        form.data.is7 = null;
        form.data.is8 = null;
        form.data.is9 = null;
        form.data.is10 = null;

        form.data.remarks = '';

        self.setState({
            form: form, tempVoter: {
                hasPhoto: "",
                hasId: "",
                lastEvent: {
                    event_name: ""
                }
            }
        });
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


    handleCheckbox: function (e) {
        var form = this.state.form;

        form.data[e.target.name] = e.target.checked ? 1 : 0;

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
        data.eventId = self.props.eventId;
        data.proId = self.props.proId;

        self.requestAddAttendee = $.ajax({
            url: Routing.generate("ajax_post_project_event_attendee"),
            type: "POST",
            data: data
        }).done(function (res) {
            self.props.onSuccess("");
            self.props.notify("Attendee has been added.", "teal");
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
                    <Modal.Title>Attendee Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-node-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="municipality-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>

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

                                <div className="col-md-6" style={{ paddingRight: "0" }}>
                                    <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                        <ControlLabel >Position: </ControlLabel>
                                        <select id="voter-group-select2" className="form-control input-sm">
                                            <option value=""> </option>
                                        </select>
                                        <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formPosition" validationState={this.getValidationState('position')}>
                                        <ControlLabel >Barangay Position: </ControlLabel>
                                        <select id="position-select2" className="form-control input-sm">
                                            <option value=""> </option>
                                        </select>
                                        <HelpBlock>{this.getError('position')}</HelpBlock>
                                    </FormGroup>
                                </div>

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


window.ProjectEventAttendeeModal = ProjectEventAttendeeModal;