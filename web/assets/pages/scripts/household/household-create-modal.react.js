var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HouseholdCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    electId: 3,
                    proVoterId: null,
                    isTagalog: 0,
                    isBisaya: 0,
                    isCuyonon: 0,
                    isIlonggo: 0,
                    isCatholic: 0,
                    isInc: 0,
                    isIslam: 0
                },
                errors: []
            },
            provinceCode: 53,
            showNewVoterCreateModal: false
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Household Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showNewVoterCreateModal &&
                        <VoterTemporaryCreateModal
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            show={this.state.showNewVoterCreateModal}
                            notify={this.props.notify}
                            onHide={this.closeNewVoterCreateModal}
                        />
                    }

                    <form id="household-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                                    <label className="control-label">Barangay</label>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-6">
                                <FormGroup controlId="formVoterName" validationState={this.getValidationState('voterName')}>
                                    <ControlLabel > Household Leader : </ControlLabel>
                                    <select id="voter-recruit-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterName')}</HelpBlock>
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

                            <div className="col-md-2">
                                <FormGroup controlId="formGender" validationState={this.getValidationState('gender')}>
                                    <ControlLabel > Kasarian : </ControlLabel>
                                    <select className="input-sm form-control" onChange={this.setFormProp} value={data.gender} name="gender">
                                        <option value="">- Select -</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                    <HelpBlock>{this.getError('gender')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-2">
                                <div className="form-group">
                                    <label className="control-label">Civil Status</label>
                                    <select id="civil_status_select2" className="form-control form-filter input-sm" name="civilStatus">
                                    </select>
                                </div>
                            </div>

                            <div className="col-md-1">
                                <div className="form-group">
                                    <label className="control-label">Blood</label>
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
                                    <select id="other_position_select2" className="form-control form-filter input-sm" name="position">
                                    </select>
                                </div>
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


                        <div className="row" style={{ marginTop : "15px"}}>
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

        $("#household-create-form #municipality_select2").select2({
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

        $("#household-create-form #barangay_select2").select2({
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
                        municipalityNo: $("#household-create-form #municipality_select2").val(),
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

        $("#household-create-form #voter-recruit-select2").select2({
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
                        municipalityNo: $("#household-create-form #municipality_select2").val(),
                        brgyNo: self.props.brgyNo
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasId = parseInt(item.has_id) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - ID : ' + hasId;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#household-create-form #civil_status_select2").select2({
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

        $("#household-create-form #bloodtype_select2").select2({
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

        $("#household-create-form #occupation_select2").select2({
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

        $("#household-create-form #religion_select2").select2({
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

        $("#household-create-form #dialect_select2").select2({
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


        $("#household-create-form #ip_group_select2").select2({
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

        $("#household-create-form #voter_group_select2").select2({
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

        $("#household-create-form #other_position_select2").select2({
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

        $("#form-voter-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#household-create-form #civil_status_select2").on("change", function () {
            console.log("civil status changed");
            self.setFormPropValue("civilStatus", $(this).val());
        });

        $("#household-create-form #bloodtype_select2").on("change", function () {
            self.setFormPropValue("bloodtype", $(this).val());
        });

        $("#household-create-form #occupation_select2").on("change", function () {
            self.setFormPropValue("occupation", $(this).val());
        });

        $("#household-create-form #religion_select2").on("change", function () {
            self.setFormPropValue("religion", $(this).val());
        });

        $("#household-create-form #dialect_select2").on("change", function () {
            self.setFormPropValue("dialect", $(this).val());
        });

        $("#household-create-form #ip_group_select2").on("change", function () {
            self.setFormPropValue("ipGroup", $(this).val());
        });

        $("#household-create-form #voter_group_select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });

        $("#household-create-form #other_position_select2").on("change", function () {
            self.setFormPropValue("position", $(this).val());
        });

        $("#household-create-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
        });

        $("#household-create-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
        });

        $("#household-create-form #voter-recruit-select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });
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
            form.data.cellphoneNo = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.birthdate = !self.isEmpty(res.birthdate) ? moment(res.birthdate).format('YYYY-MM-DD') : '';
            form.data.gender = res.gender;
            form.data.firstname = self.isEmpty(res.firstname) ? firstname.trim() : res.firstname;
            form.data.middlename = self.isEmpty(res.middlename) ? middlename.trim() : res.middlename;
            form.data.lastname = self.isEmpty(res.lastname) ? lastname.trim() : res.lastname;
            form.data.extName = res.extname;
            form.data.civilStatus = res.civilStatus;
            form.data.bloodtype = res.bloodtype;
            form.data.occupation = res.occupation;
            form.data.religion = res.religion;
            form.data.dialect = res.dialect;
            form.data.ipGroup = res.ipGroup;
            form.data.isTagalog = self.isEmpty(res.isTagalog) ? 0 : res.isTagalog;
            form.data.isCuyonon = self.isEmpty(res.isCuyonon) ? 0 : res.isCuyonon;
            form.data.isBisaya = self.isEmpty(res.isBisaya) ? 0 : res.isBisaya;
            form.data.isIlonggo = self.isEmpty(res.isIlonggo) ? 0 : res.isIlonggo;
            form.data.isCatholic = self.isEmpty(res.isCatholic) ? 0 : res.isCatholic;
            form.data.isInc = self.isEmpty(res.isInc) ? 0 : res.isInc;
            form.data.isIslam = self.isEmpty(res.isIslam) ? 0 : res.isIslam;
            form.data.position = res.position;

            $("#household-create-form #civil_status_select2").empty()
                .append($("<option/>")
                    .val(res.civilStatus)
                    .text(res.civilStatus))
                .trigger("change");


            $("#household-create-form #bloodtype_select2").empty()
                .append($("<option/>")
                    .val(res.bloodtype)
                    .text(res.bloodtype))
                .trigger("change");


            $("#household-create-form #occupation_select2").empty()
                .append($("<option/>")
                    .val(res.occupation)
                    .text(res.occupation))
                .trigger("change");

            $("#household-create-form #religion_select2").empty()
                .append($("<option/>")
                    .val(res.religion)
                    .text(res.religion))
                .trigger("change");

            $("#household-create-form #dialect_select2").empty()
                .append($("<option/>")
                    .val(res.dialect)
                    .text(res.dialect))
                .trigger("change");

            $("#household-create-form #ip_group_select2").empty()
                .append($("<option/>")
                    .val(res.ipGroup)
                    .text(res.ipGroup))
                .trigger("change");

            $("#household-create-form #voter_group_select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");

            $("#household-create-form #other_position_select2").empty()
                .append($("<option/>")
                    .val(res.position)
                    .text(res.position))
                .trigger("change");


            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.cellphone = '';
        form.data.gender = '';
        form.data.remarks = '';

        self.setState({ form: form })
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

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form })
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

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    openNewVoterCreateModal: function () {
        console.log('opening modal');
        this.setState({ showNewVoterCreateModal: true })
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
        data.proId = self.props.proId;
        data.electId = self.props.electId;

        data.voterGroup = $("#household-create-form #voter_group_select2").val();
        data.position = $("#household-create-form #other_position_select2").val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_household_header"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.reload();
            self.props.onHide();
            self.props.onSuccess(res.id);
            self.notify("New household has been created.", 'teal');
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
            self.notify("Validation failed !", 'ruby');
        });
    }
});


window.HouseholdCreateModal = HouseholdCreateModal;