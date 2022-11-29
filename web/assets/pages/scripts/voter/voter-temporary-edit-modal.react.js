var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterTemporaryEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    voterName: "test",
                    lgcName: "test"
                },
                errors: []
            },
            provinceCode: 53
        };
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        console.log("form data");
        console.log(data);

        return (
            <Modal style={{ marginTop: "10px" }} dialogClassName="modal-custom-85" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Voter Information : {data.voterName} | LGC : {data.lgcName}</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="new-voter-edit-form" onSubmit={this.submit}>
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

                            <div className="col-md-2" >
                                <FormGroup controlId="formLastname" validationState={this.getValidationState('lastname')}>
                                    <ControlLabel > Lastname : </ControlLabel>
                                    <input type="text" value={this.state.form.data.lastname} className="input-sm form-control" onChange={this.setFormProp} name="lastname" />
                                    <HelpBlock>{this.getError('lastname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
                                <FormGroup controlId="formFirstname" validationState={this.getValidationState('firstname')}>
                                    <ControlLabel > Firstname : </ControlLabel>
                                    <input type="text" value={this.state.form.data.firstname} className="input-sm form-control" onChange={this.setFormProp} name="firstname" />
                                    <HelpBlock>{this.getError('firstname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
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
                                        <option value="M" selected={this.state.form.data.gender == 'M'}>Male</option>
                                        <option value="F" selected={this.state.form.data.gender == 'F'}>Female</option>
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


                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Religion</label>
                                    <select id="religion_select2" className="form-control form-filter input-sm" name="religion">
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div className="row">
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
                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">JPM Position</label>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Barangay Position</label>
                                    <select id="other_position_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div className="row">
                            <div className="col-md-3" >
                                <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                                    <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
                                <FormGroup controlId="formBirthdate" validationState={this.getValidationState('birthdate')}>
                                    <ControlLabel > Birthdate : </ControlLabel>
                                    <input type="date" value={this.state.form.data.birthdate} className="input-sm form-control" onChange={this.setFormProp} name="birthdate" />
                                    <HelpBlock>{this.getError('birthdate')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-3">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isCatholic" checked={data.hasPhoto == 1} disabled="true" />
                                        With Photo
                                    <span></span>
                                </label>
                                <br />
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isInc" checked={data.hasId == 1} disabled="true" />
                                        With ID
                                    <span></span>
                                </label>
                                <br />

                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isNonVoter" checked={data.isNonVoter != 1} disabled="true" />
                                        Is Voter      
                                    <span></span>
                                </label>
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
        this.loadVoter(3, this.props.proVoterId);
    },

    initSelect2: function () {
        var self = this;

        $("#new-voter-edit-form #voter-recruit-select2").select2({
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
                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#new-voter-edit-form #municipality_select2").select2({
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

        $("#new-voter-edit-form #barangay_select2").select2({
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
                        municipalityNo: $("#new-voter-edit-form #municipality_select2").val(),
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

        $("#new-voter-edit-form #civil_status_select2").select2({
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

        $("#new-voter-edit-form #bloodtype_select2").select2({
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

        $("#new-voter-edit-form #occupation_select2").select2({
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

        $("#new-voter-edit-form #religion_select2").select2({
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

        $("#new-voter-edit-form #dialect_select2").select2({
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


        $("#new-voter-edit-form #ip_group_select2").select2({
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

        $("#new-voter-edit-form #voter_group_select2").select2({
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
        
        $("#new-voter-edit-form #other_position_select2").select2({
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

        $("#new-voter-edit-form #civil_status_select2").on("change", function () {
            console.log("civil status changed");
            self.setFormPropValue("civilStatus", $(this).val());
        });

        $("#new-voter-edit-form #bloodtype_select2").on("change", function () {
            self.setFormPropValue("bloodtype", $(this).val());
        });

        $("#new-voter-edit-form #occupation_select2").on("change", function () {
            self.setFormPropValue("occupation", $(this).val());
        });

        $("#new-voter-edit-form #religion_select2").on("change", function () {
            self.setFormPropValue("religion", $(this).val());
        });

        $("#new-voter-edit-form #dialect_select2").on("change", function () {
            self.setFormPropValue("dialect", $(this).val());
        });

        $("#new-voter-edit-form #ip_group_select2").on("change", function () {
            self.setFormPropValue("ipGroup", $(this).val());
        });

        $("#new-voter-edit-form #municipality_select2").on("change", function () {
            self.setFormPropValue("municipalityNo", $(this).val());
        });

        $("#new-voter-edit-form #barangay_select2").on("change", function () {
            self.setFormPropValue("brgyNo", $(this).val());
        });

        $("#new-voter-edit-form #other_position_select2").on("change", function () {
            self.setFormPropValue("position", $(this).val());
        });
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var form = self.state.form;
            form.data = res;
            form.data.proVoterId = res.proVoterId;
            form.data.cellphoneNo = res.cellphoneNo;
            form.data.birthdate = moment(res.birthdate).format('YYYY-MM-DD');
            form.data.gender = res.gender;
            form.data.firstname = res.firstname;
            form.data.middlename = res.middlename;
            form.data.lastname = res.lastname;
            form.data.extName = res.extname;
            form.data.civilStatus = res.civilStatus;
            form.data.bloodtype = res.bloodtype;
            form.data.occupation = res.occupation;
            form.data.religion = res.religion;
            form.data.dialect = res.dialect;
            form.data.ipGroup = res.ipGroup;
            form.data.position = res.position;

            $("#new-voter-edit-form #civil_status_select2").empty()
                .append($("<option/>")
                    .val(res.civilStatus)
                    .text(res.civilStatus))
                .trigger("change");


            $("#new-voter-edit-form #bloodtype_select2").empty()
                .append($("<option/>")
                    .val(res.bloodtype)
                    .text(res.bloodtype))
                .trigger("change");


            $("#new-voter-edit-form #occupation_select2").empty()
                .append($("<option/>")
                    .val(res.occupation)
                    .text(res.occupation))
                .trigger("change");

            $("#new-voter-edit-form #religion_select2").empty()
                .append($("<option/>")
                    .val(res.religion)
                    .text(res.religion))
                .trigger("change");

            $("#new-voter-edit-form #dialect_select2").empty()
                .append($("<option/>")
                    .val(res.dialect)
                    .text(res.dialect))
                .trigger("change");

            $("#new-voter-edit-form #ip_group_select2").empty()
                .append($("<option/>")
                    .val(res.ipGroup)
                    .text(res.ipGroup))
                .trigger("change");

            $("#new-voter-edit-form #voter_group_select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");


            $("#new-voter-edit-form #municipality_select2").empty()
                .append($("<option/>")
                    .val(res.municipalityNo)
                    .text(res.municipalityName))
                .trigger("change");

            $("#new-voter-edit-form #barangay_select2").empty()
                .append($("<option/>")
                    .val(res.brgyNo)
                    .text(res.barangayName))
                .trigger("change");


            $("#new-voter-edit-form #other_position_select2").empty()
            .append($("<option/>")
                .val(res.position)
                .text(res.position))
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
        data.civilStatus = $('#new-voter-edit-form #civil_status_select2').val();
        data.bloodtype = $('#new-voter-edit-form #bloodtype_select2').val();
        data.occupation = $('#new-voter-edit-form #occupation_select2').val();
        data.religion = $('#new-voter-edit-form #religion_select2').val();
        data.dialect = $('#new-voter-edit-form #dialect_select2').val();
        data.ipGroup = $('#new-voter-edit-form #ip_group_select2').val();
        data.voterGroup = $('#new-voter-edit-form #voter_group_select2').val();
        data.position = $('#new-voter-edit-form #other_position_select2').val();
        data.electId = self.props.electId;
        data.proId = self.props.proId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_project_temporary_voter", { proVoterId: this.props.proVoterId }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.props.onHide();
            self.notify("Record has been updated.", 'teal');
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
            self.notify("Form validation failed!.", 'ruby');
        });
    }
});

window.VoterTemporaryEditModal = VoterTemporaryEditModal;