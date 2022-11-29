var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FinancialAssistanceCreateNewProfileModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null
                },
                errors: []
            },
            provinceCode: 53
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "150px" }} keyboard={false} dialogClassName="modal-custom-55" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>New Profile Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="new-voter-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3">
                                <FormGroup controlId="formBrgyNo" validationState={this.getValidationState('brgyNo')}>
                                    <label className="control-label">Barangay</label>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('brgyNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">

                            <div className="col-md-3" >
                                <FormGroup controlId="formLastname" validationState={this.getValidationState('lastname')}>
                                    <ControlLabel > Lastname : </ControlLabel>
                                    <input type="text" value={this.state.form.data.lastname} className="input-sm form-control" onChange={this.setFormProp} name="lastname" />
                                    <HelpBlock>{this.getError('lastname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3" >
                                <FormGroup controlId="formFirstname" validationState={this.getValidationState('firstname')}>
                                    <ControlLabel > Firstname : </ControlLabel>
                                    <input type="text" value={this.state.form.data.firstname} className="input-sm form-control" onChange={this.setFormProp} name="firstname" />
                                    <HelpBlock>{this.getError('firstname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3" >
                                <FormGroup controlId="formMiddlename" validationState={this.getValidationState('middlename')}>
                                    <ControlLabel > Middlename : </ControlLabel>
                                    <input type="text" value={this.state.form.data.middlename} className="input-sm form-control" onChange={this.setFormProp} name="middlename" />
                                    <HelpBlock>{this.getError('middlename')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-1" >
                                <FormGroup controlId="formExtName" validationState={this.getValidationState('extName')}>
                                    <ControlLabel > Ext : </ControlLabel>
                                    <input type="text" value={this.state.form.data.extName} className="input-sm form-control" onChange={this.setFormProp} name="extName" />
                                    <HelpBlock>{this.getError('extName')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-2">
                                <FormGroup controlId="formGender" validationState={this.getValidationState('gender')}>
                                    <ControlLabel > Gender : </ControlLabel>
                                    <select className="input-sm form-control" onChange={this.setFormProp} name="gender">
                                        <option value="">- Select -</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                    <HelpBlock>{this.getError('gender')}</HelpBlock>
                                </FormGroup>
                            </div>


                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Civil Status</label>
                                    <select id="civil_status_select2" className="form-control form-filter input-sm" name="civilStatus">
                                    </select>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Blood Type</label>
                                    <select id="bloodtype_select2" className="form-control form-filter input-sm" name="bloodtype">
                                    </select>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Occupation</label>
                                    <select id="occupation_select2" className="form-control form-filter input-sm" name="occupation">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div className="row">

                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Religion</label>
                                    <select id="religion_select2" className="form-control form-filter input-sm" name="religion">
                                    </select>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Dialect</label>
                                    <select id="dialect_select2" className="form-control form-filter input-sm" name="dialect">
                                    </select>
                                </div>
                            </div>
                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Ip Group</label>
                                    <select id="ip_group_select2" className="form-control form-filter input-sm" name="ipGroup">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-2" >
                                <FormGroup controlId="formBirthdate" validationState={this.getValidationState('birthdate')}>
                                    <ControlLabel > Birthdate : </ControlLabel>
                                    <input type="date" value={this.state.form.data.birthdate} className="input-sm form-control" onChange={this.setFormProp} name="birthdate" />
                                    <HelpBlock>{this.getError('birthdate')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3" >
                                <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                                    <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" style={{ marginRight: "10px" }} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#new-voter-create-form #voter-recruit-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: self.props.electId,
                        proId: self.props.proId,
                        provinceCode: self.props.provinceCode,
                        municipalityNo: self.props.municipalityNo,
                        brgyNo: self.props.brgyNo
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


        $("#new-voter-create-form #municipality_select2").select2({
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
                        provinceCode: self.state.provinceCode
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

        $("#new-voter-create-form #barangay_select2").select2({
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
                        municipalityNo: $("#new-voter-create-form #municipality_select2").val(),
                        provinceCode: self.state.provinceCode
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

        $("#new-voter-create-form #civil_status_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_civil_status'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.civil_status, text: item.civil_status };
                        })
                    };
                },
            }
        });

        $("#new-voter-create-form #bloodtype_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bloodtype'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.bloodtype, text: item.bloodtype };
                        })
                    };
                },
            }
        });

        $("#new-voter-create-form #occupation_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_occupation'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.occupation, text: item.occupation };
                        })
                    };
                },
            }
        });

        $("#new-voter-create-form #religion_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_religion'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.religion, text: item.religion };
                        })
                    };
                },
            }
        });

        $("#new-voter-create-form #dialect_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_dialect'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.dialect, text: item.dialect };
                        })
                    };
                },
            }
        });


        $("#new-voter-create-form #ip_group_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_ip_group'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.ip_group, text: item.ip_group };
                        })
                    };
                },
            }
        });

        $("#new-voter-create-form #voter_group_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
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
                        searchText: params.term
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

        $("#new-voter-create-form #other_position_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
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
                        searchText: params.term
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


        $("#new-voter-create-form #civil_status_select2").on("change", function () {
            console.log("civil status changed");
            self.setFormPropValue("civilStatus", $(this).val());
        });

        $("#new-voter-create-form #bloodtype_select2").on("change", function () {
            self.setFormPropValue("bloodtype", $(this).val());
        });

        $("#new-voter-create-form #occupation_select2").on("change", function () {
            self.setFormPropValue("occupation", $(this).val());
        });

        $("#new-voter-create-form #religion_select2").on("change", function () {
            self.setFormPropValue("religion", $(this).val());
        });

        $("#new-voter-create-form #dialect_select2").on("change", function () {
            self.setFormPropValue("dialect", $(this).val());
        });

        $("#new-voter-create-form #ip_group_select2").on("change", function () {
            self.setFormPropValue("ipGroup", $(this).val());
        });

        $("#new-voter-create-form #municipality_select2").on("change", function () {
            self.setFormPropValue("municipalityNo", $(this).val());
        });

        $("#new-voter-create-form #barangay_select2").on("change", function () {
            self.setFormPropValue("brgyNo", $(this).val());
        });


        var municipalityNo = this.props.municipalityNo;
        var municipalityName = this.props.municipalityName;
        var barangayNo = this.props.barangayNo;
        var barangayName = this.props.barangayName;

        if (municipalityNo != null) {

            $("#new-voter-create-form #municipality_select2").empty()
                .append($("<option/>")
                    .val(municipalityNo)
                    .text(municipalityName))
                .trigger("change");

            $("#new-voter-create-form #barangay_select2").empty()
                .append($("<option/>")
                    .val(barangayNo)
                    .text(barangayName))
                .trigger("change");
        }

        if (!self.props.user.isAdmin) {
            $("#new-voter-create-form #municipality_select2").attr('disabled', 'disabled');
        }
    },

    loadVoter: function (proId, voterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, voterId: voterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            form.data.nodeLabel = res.voterName;
            form.data.nodeOrder = 10;
            form.data.municipalityNo = res.municipalityNo;
            form.data.brgyNo = res.brgyNo;
            form.data.voterId = res.voterId;
            form.data.proVoterId = res.proVoterId;
            form.data.cellphoneNo = self.isEmpty(res.cellphoneNo) ? "" : res.cellphoneNo;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? "KFC" : res.voterGroup;
            form.data.remarks = self.isEmpty(res.remarks) ? "" : res.remarks;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(form.data.voterGroup)
                    .text(form.data.voterGroup))
                .trigger("change");


            self.setState({ form: form });
        });
    },

    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value.toUpperCase();
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

        this.setState({ form: form });
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.civilStatus = $('#new-voter-create-form #civil_status_select2').val();
        data.bloodtype = $('#new-voter-create-form #bloodtype_select2').val();
        data.occupation = $('#new-voter-create-form #occupation_select2').val();
        data.religion = $('#new-voter-create-form #religion_select2').val();
        data.dialect = $('#new-voter-create-form #dialect_select2').val();
        data.ipGroup = $('#new-voter-create-form #ip_group_select2').val();
        data.voterGroup = $('#new-voter-create-form #voter_group_select2').val();
        data.position = $('#new-voter-create-form #other_position_select2').val();

        data.electId = self.props.electId;
        data.proId = self.props.proId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_project_temporary_voter"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.props.onHide();
            self.props.onSuccess(res);
            self.notify("New record has been saved.", 'teal');
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
            self.notify("Form validation failed!.", 'ruby');
        });
    }
});


window.FinancialAssistanceCreateNewProfileModal = FinancialAssistanceCreateNewProfileModal;