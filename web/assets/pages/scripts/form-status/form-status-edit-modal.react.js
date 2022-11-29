var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FormStatusEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    electId: 3,
                    proVoterId: null,
                    proIdCode: "",
                    recFormSub: 0,
                    houseFormSub: 0,
                    recFormSubCount: 0,
                    houseFormSubCount: 0,
                    recFormSubDate: "",
                    houseFormSubDate: "",
                    municipalityNo: "",
                    municipalityName: "",
                    barangayNo: "",
                    barangayName: ""
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
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Member Status</Modal.Title>
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

                            onSuccess={this.setNewProfile}

                            municipalityNo={data.municipalityNo}
                            municipalityName={data.municipalityName}
                            barangayNo={data.barangayNo}
                            barangayName={data.barangayName}
                        />
                    }

                    <form id="member-status-edit-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </div>
                            </div>

                            <div className="col-md-3">
                                <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                                    <label className="control-label">Barangay</label>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-8">
                                <FormGroup controlId="formVoterName" validationState={this.getValidationState('voterName')}>
                                    <ControlLabel >Name: </ControlLabel>
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
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">JPM Position</label>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div className="row">

                            <div className="col-md-4">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="houseFormSub" checked={data.houseFormSub == 1} onChange={this.setFormCheckProp} />
                                        Household Form Submitted
                                    <span></span>
                                </label>
                                <FormGroup controlId="formHouseFormSubCount" validationState={this.getValidationState('houseFormSubCount')}>
                                    <input type="text" placeholder="Total number of household members" value={this.state.form.data.houseFormSubCount} className="input-sm form-control" onChange={this.setFormProp} name="houseFormSubCount" />
                                    <HelpBlock>{this.getError('houseFormSubCount')}</HelpBlock>
                                </FormGroup>
                                <FormGroup controlId="formHouseFormSubDate" validationState={this.getValidationState('houseFormSubDate')}>
                                    <input type="date" value={this.state.form.data.houseFormSubDate} className="input-sm form-control" onChange={this.setFormProp} name="houseFormSubDate" />
                                    <HelpBlock>{this.getError('houseFormSubDate')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-4">
                                <label className="mt-checkbox">
                                    <input type="checkbox" name="recFormSub" checked={data.recFormSub == 1} onChange={this.setFormCheckProp} />
                                    Recruitment Form Submitted
                                    <span></span>
                                </label>
                                <FormGroup controlId="formRecFormSubCount" validationState={this.getValidationState('recFormSubCount')}>
                                    <input type="text" placeholder="Total number of recruits" value={this.state.form.data.recFormSubCount} className="input-sm form-control" onChange={this.setFormProp} name="recFormSubCount" />
                                    <HelpBlock>{this.getError('recFormSubCount')}</HelpBlock>
                                </FormGroup>
                                <FormGroup controlId="FormRecFormSubDate" validationState={this.getValidationState('recFormSubDate')}>
                                    <input type="date" value={this.state.form.data.recFormSubDate} className="input-sm form-control" onChange={this.setFormProp} name="recFormSubDate" />
                                    <HelpBlock>{this.getError('recFormSubDate')}</HelpBlock>
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
        this.loadData(this.props.id);
        console.log("edit mounted");
    },

    loadData: function (id) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_form_status", { id: id }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data = res;
            self.setState({ form: form }, self.reinitSelect2);
        });
    },

    initSelect2: function () {
        var self = this;

        $("#member-status-edit-form #municipality_select2").select2({
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

        $("#member-status-edit-form #barangay_select2").select2({
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
                        municipalityNo: $("#member-status-edit-form #municipality_select2").val(),
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

        $("#member-status-edit-form #voter-recruit-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            disabled: true,
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_form_status_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: self.props.electId,
                        proId: self.props.proId,
                        provinceCode: self.props.provinceCode,
                        municipalityNo: $("#member-status-edit-form #municipality_select2").val(),
                        brgyNo: $("#member-status-edit-form #barangay_select2").val()
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

        $("#member-status-edit-form #voter_group_select2").select2({
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

        $("#member-status-edit-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
            self.loadMunicipality($(this).val());
        });

        $("#member-status-edit-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
            self.loadBarangay($("#member-status-edit-form #municipality_select2").val(), $(this).val());
        });

        $("#member-status-edit-form #voter-recruit-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });
    },

    reinitSelect2: function () {
        var data = this.state.form.data;

        $("#member-status-edit-form #municipality_select2").empty()
            .append($("<option/>")
                .val(data.municipalityNo)
                .text(data.municipalityName))
            .trigger("change");

        $("#member-status-edit-form #barangay_select2").empty()
            .append($("<option/>")
                .val(data.barangayNo)
                .text(data.barangayName))
            .trigger("change");

        $("#member-status-edit-form #voter-recruit-select2").empty()
            .append($("<option/>")
                .val(data.proVoterId)
                .text(data.voterName))
            .trigger("change");

        $("#member-status-edit-form #voter_group_select2").empty()
            .append($("<option/>")
                .val(data.voterGroup)
                .text(data.voterGroup))
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

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.gender = '';
        form.data.remarks = '';

        self.setState({ form: form })
    },

    loadMunicipality: function (municipalityNo) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_municipality_loc", { municipalityNo: municipalityNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.municipalityName = res.name;
            self.setState({ form: form });
        });
    },


    loadBarangay: function (municipalityNo, brgyNo) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_barangay_loc", { municipalityNo: municipalityNo, brgyNo: brgyNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.barangayName = res.name;
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

        form.data.proVoterId = null;
        form.data.proIdCode = "";
        form.data.recFormSub = 0;
        form.data.houseFormSub = 0;
        form.data.recFormSubCount = 0;
        form.data.houseFormSubCount = 0;
        form.data.firstname = "";
        form.data.middlename = "";
        form.data.lastname = "";
        form.data.extName = "";

        $("#member-status-edit-form #voter-recruit-select2")
            .empty()
            .trigger("change");

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

    setNewProfile: function (data) {
        var self = this;

        $("#member-status-edit-form #voter-recruit-select2").empty()
            .append($("<option/>")
                .val(data.proVoterId)
                .text(data.voterName))
            .trigger("change")
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        data.electId = self.props.electId;
        data.voterGroup = $("#member-status-edit-form #voter_group_select2").val();

        self.requestPatch = $.ajax({
            url: Routing.generate("ajax_patch_form_status", { id: self.props.id }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.props.reload();
            self.props.onHide();
            self.notify("Status has been updated.", 'teal');
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
            self.notify("Validation failed !", 'ruby');
        });
    }

});


window.FormStatusEditModal = FormStatusEditModal;