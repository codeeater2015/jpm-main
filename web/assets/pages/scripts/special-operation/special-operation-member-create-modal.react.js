var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOperationMemberCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    voterId: null,
                    birthdate: null,
                    cellphone: "",
                    voterGroup: "",
                    dialect: "",
                    religion: "",
                    isTagalog: 0,
                    isCuyonon: 0,
                    isBisaya: 0,
                    isIlonggo: 0,
                    isCatholic: 0,
                    isIslam: 0,
                    isInc: 0,
                    remarks: ""
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

        $("#recruitment-member-create-form #municipality_select2").select2({
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

        $("#recruitment-member-create-form #barangay_select2").select2({
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

        $("#recruitment-member-create-form #form-voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1000,
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
                        provinceCode: 53,
                        municipalityNo: $("#municipality_select2").val(),
                        brgyNo: $("#barangay_select2").val()
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

        $("#recruitment-member-create-form #religion_select2").select2({
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

        $("#recruitment-member-create-form #dialect_select2").select2({
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

        $("#recruitment-member-create-form #voter_group_select2").select2({
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

        $("#recruitment-member-create-form #other_position_select2").select2({
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

        $("#recruitment-member-create-form #form-voter-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#recruitment-member-create-form #municipality_select2").on("change", function () {
            self.setFieldValue("municipalityNo", $(this).val());
        });

        $("#recruitment-member-create-form #barangay_select2").on("change", function () {
            self.setFieldValue("barangayNo", $(this).val());
        });

        $("#recruitment-member-create-form #dialect_select2").on("change", function () {
            self.setFieldValue("dialect", $(this).val());
        });

        $("#recruitment-member-create-form #religion_select2").on("change", function () {
            self.setFieldValue("religion", $(this).val());
        });

        $("#recruitment-member-create-form #voter_group_select2").on("change", function () {
            self.setFieldValue("voterGroup", $(this).val());
        });

        $("#recruitment-member-create-form #other_position_select2").on("change", function () {
            self.setFieldValue("position", $(this).val());
        });

        $("#recruitment-member-create-form #municipality_select2").empty()
            .append($("<option/>")
                .val(this.props.municipalityNo)
                .text(this.props.municipalityName))
            .trigger("change");

        $("#recruitment-member-create-form #barangay_select2").empty()
            .append($("<option/>")
                .val(this.props.barangayNo)
                .text(this.props.barangayName))
            .trigger("change");
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var chunks = res.voterName.split(",");
            var firstname = '';
            var middlename = '';
            var lastname = '';

            if (chunks.length > 1) {
                chunks = chunks[1].trim().split(" ");
                lastname = res.voterName.split(",")[0];
                middlename = chunks.length > 1 ? chunks[chunks.length - 1] : '';
                firstname = res.voterName.split(",")[1].replace(middlename, '');;
            } else {
                chunks = res.voterName.trim().split(" ");
                lastname = chunks[0];
                firstname = chunks[1];
                middlename = chunks.length > 2 ? chunks[2] : '';
            }

            var form = self.state.form;
            form.data.proVoterId = res.proVoterId;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.birthdate = !self.isEmpty(res.birthdate) ? moment(res.birthdate).format('YYYY-MM-DD') : '';
            form.data.gender = res.gender;
            form.data.firstname = self.isEmpty(res.firstname) ? firstname.trim() : res.firstname;
            form.data.middlename = self.isEmpty(res.middlename) ? middlename.trim() : res.middlename;
            form.data.lastname = self.isEmpty(res.lastname) ? lastname.trim() : res.lastname;
            form.data.extName = res.extname;
            form.data.dialect = self.isEmpty(res.dialect) ? self.props.defaultDialect : res.dialect;
            form.data.religion = self.isEmpty(res.religion) ? self.props.defaultReligion : res.religion;
            form.data.voterGroup = res.voterGroup;
            form.data.isTagalog = self.isEmpty(res.isTagalog) ? 0 : res.isTagalog;
            form.data.isCuyonon = self.isEmpty(res.isCuyonon) ? 0 : res.isCuyonon;
            form.data.isBisaya = self.isEmpty(res.isBisaya) ? 0 : res.isBisaya;
            form.data.isIlonggo = self.isEmpty(res.isIlonggo) ? 0 : res.isIlonggo;
            form.data.isCatholic = self.isEmpty(res.isCatholic) ? 0 : res.isCatholic;
            form.data.isInc = self.isEmpty(res.isInc) ? 0 : res.isInc;
            form.data.isIslam = self.isEmpty(res.isIslam) ? 0 : res.isIslam;
            form.data.position = res.position;

            $("#recruitment-member-create-form #dialect_select2").empty()
                .append($("<option/>")
                    .val(form.data.dialect)
                    .text(form.data.dialect))
                .trigger("change");

            console.log('default dialect');
            console.log(self.props.defaultDialect);

            $("#recruitment-member-create-form #religion_select2").empty()
                .append($("<option/>")
                    .val(form.data.religion)
                    .text(form.data.religion))
                .trigger("change");


            var voterGroup = self.isEmpty(res.voterGroup) ? "JPM" : res.voterGroup;

            $("#recruitment-member-create-form #voter_group_select2").empty()
                .append($("<option/>")
                    .val(voterGroup)
                    .text(voterGroup))
                .trigger("change")

            $("#recruitment-member-create-form #other_position_select2").empty()
                .append($("<option/>")
                    .val(res.position)
                    .text(res.position))
                .trigger("change")

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.cellphone = '';
        form.data.gender = '';
        form.data.remarks = '';
        form.data.position = '';

        self.setState({ form: form })
    },

    reset: function () {
        var form = this.state.form;
        form.data.proVoterId = "";
        form.data.cellphone = "";
        form.data.firstname = "";
        form.data.lastname = "";
        form.data.middlename = "";
        form.data.extName = "";
        form.data.gender = "";
        form.data.birthdate = "";
        //form.data.religion = "";
        //form.data.dialect = "";
        form.data.isTagalog = 0;
        form.data.isBisaya = 0;
        form.data.isCuyonon = 0;
        form.data.Ilonggo = 0;
        form.data.isCatholic = 0;
        form.data.isInc = 0;
        form.data.isIslam = 0;
        form.data.voterGroup = "";
        form.data.remarks = "";
        form.data.position = "";

        form.errors = [];

        $("#recruitment-member-create-form #form-voter-select2").empty().trigger("change");
        //$("#recruitment-member-create-form #dialect_select2").empty().trigger("change");
        //$("#recruitment-member-create-form #religion_select2").empty().trigger("change");
        $("#recruitment-member-create-form #voter_group_select2").empty().trigger("change");

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

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form })
    },

    setNewProfile: function (data) {
        var self = this;

        $("#recruitment-member-create-form #form-voter-select2").empty()
            .append($("<option/>")
                .val(data.proVoterId)
                .text(data.voterName))
            .trigger("change")
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

        data.recId = self.props.recId;
        data.proId = self.props.proId;

        data.voterGroup = $("#recruitment-member-create-form #voter_group_select2").val();
        data.position = $("#recruitment-member-create-form #other_position_select2").val();

        self.requestAddAttendee = $.ajax({
            url: Routing.generate("ajax_post_special_operation_detail"),
            type: "POST",
            data: data
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
            self.notify("Member has been added.", "teal");
        }).fail(function (err) {
            self.notify("Form Validation Failed.", "ruby");
            self.setErrors(err.responseJSON);
        });
    },

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    openNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: true })
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-custom-85" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Leader : {this.props.leader.voterName} | {this.props.leader.voterGroup}</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showNewVoterCreateModal &&
                        <VoterTemporaryCreateModal
                            proId={self.props.proId}
                            electId={self.props.electId}
                            provinceCode={53}
                            show={this.state.showNewVoterCreateModal}
                            onHide={this.closeNewVoterCreateModal}
                            onSuccess={this.setNewProfile}

                            municipalityNo={this.props.municipalityNo}
                            municipalityName={this.props.municipalityName}
                            barangayNo={this.props.barangayNo}
                            barangayName={this.props.barangayName}
                        />
                    }

                    <form id="recruitment-member-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-2">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2">
                                <FormGroup controlId="formBrgyNo" validationState={this.getValidationState('barangayNo')}>
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-6">
                                <FormGroup controlId="formProVoterId" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel > Voter Name : </ControlLabel>
                                    <select id="form-voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3">
                                <button style={{ marginTop: "25px" }} onClick={this.openNewVoterCreateModal} className="btn btn-primary btn-sm" type="button"> New Voter </button>
                            </div>
                        </div>

                        <div className="row">

                            <div className="col-md-2" >
                                <FormGroup controlId="formLastname" validationState={this.getValidationState('lastname')}>
                                    <ControlLabel > Apelyido : </ControlLabel>
                                    <input type="text" value={this.state.form.data.lastname} className="input-sm form-control" onChange={this.setFormProp} name="lastname" />
                                    <HelpBlock>{this.getError('lastname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
                                <FormGroup controlId="formFirstname" validationState={this.getValidationState('firstname')}>
                                    <ControlLabel > Pangalan : </ControlLabel>
                                    <input type="text" value={this.state.form.data.firstname} className="input-sm form-control" onChange={this.setFormProp} name="firstname" />
                                    <HelpBlock>{this.getError('firstname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
                                <FormGroup controlId="formMiddlename" validationState={this.getValidationState('middlename')}>
                                    <ControlLabel > Gitnang Pangalan : </ControlLabel>
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

                            <div className="col-md-2" >
                                <FormGroup controlId="formBirthdate" validationState={this.getValidationState('birthdate')}>
                                    <ControlLabel > Birthdate : </ControlLabel>
                                    <input type="date" value={this.state.form.data.birthdate} className="input-sm form-control" onChange={this.setFormProp} name="birthdate" />
                                    <HelpBlock>{this.getError('birthdate')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2">
                                <FormGroup controlId="formGender" validationState={this.getValidationState('gender')}>
                                    <ControlLabel > Gender : </ControlLabel>
                                    <select className="input-sm form-control" onChange={this.setFormProp} value={data.gender} name="gender">
                                        <option value="">- Select -</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                    <HelpBlock>{this.getError('gender')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2" >
                                <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                    <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-2">
                                <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                    <ControlLabel > JPM Position : </ControlLabel>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                    <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2">
                                <FormGroup controlId="formOtherPosition" validationState={this.getValidationState('position')}>
                                    <ControlLabel > Barangay Position : </ControlLabel>
                                    <select id="other_position_select2" className="form-control form-filter input-sm" name="position">
                                    </select>
                                    <HelpBlock>{this.getError('position')}</HelpBlock>
                                </FormGroup>
                            </div>

                        </div>
                        <div className="row">
                            <div className="col-md-2">

                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isTagalog" checked={data.isTagalog == 1} onChange={this.setFormCheckProp} />
                                        Is Tagalog
                                    <span></span>
                                </label>
                                <br />
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isCuyonon" checked={data.isCuyonon == 1} onChange={this.setFormCheckProp} />
                                        Is Cuyonon
                                        <span></span>
                                </label>
                                <br />
                            </div>

                            <div className="col-md-2">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isIlonggo" checked={data.isIlonggo == 1} onChange={this.setFormCheckProp} />
                                        Is Ilonggo
                                    <span></span>
                                </label>
                                <br />
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isBisaya" checked={data.isBisaya == 1} onChange={this.setFormCheckProp} />
                                         Is Bisaya
                                    <span></span>
                                </label>
                            </div>

                            <div className="col-md-2">
                                <FormGroup controlId="formDialect" validationState={this.getValidationState('dialect')}>
                                    <ControlLabel > Other Dialect : </ControlLabel>
                                    <select id="dialect_select2" className="form-control form-filter input-sm" name="dialect">
                                    </select>
                                    <HelpBlock>{this.getError('dialect')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row" style={{ marginTop: "15px" }}>

                            <div className="col-md-2">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isCatholic" checked={data.isCatholic == 1} onChange={this.setFormCheckProp} />
                                    Is Catholic
                                    <span></span>
                                </label>
                                <br />
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isInc" checked={data.isInc == 1} onChange={this.setFormCheckProp} />
                                    Is INC
                                    <span></span>
                                </label>
                            </div>

                            <div className="col-md-2">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="isIslam" checked={data.isIslam == 1} onChange={this.setFormCheckProp} />
                                    Is Islam
                                    <span></span>
                                </label>
                            </div>
                            <div className="col-md-2">
                                <FormGroup controlId="formReligion" validationState={this.getValidationState('religion')}>
                                    <ControlLabel > Other Religion : </ControlLabel>
                                    <select id="religion_select2" className="form-control form-filter input-sm" name="religion">
                                    </select>
                                    <HelpBlock>{this.getError('religion')}</HelpBlock>
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


window.SpecialOperationMemberCreateModal = SpecialOperationMemberCreateModal;