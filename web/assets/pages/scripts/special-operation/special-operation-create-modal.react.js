var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOperationCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    electId: 4,
                    proVoterId: null,
                    municipalityNo: null
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
                    <Modal.Title>Special Operation Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showNewVoterCreateModal &&
                        <VoterTemporaryCreateModal
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            municipalityName={this.props.user.description}
                            municipalityNo={this.state.form.data.municipalityNo}
                            show={this.state.showNewVoterCreateModal}
                            user={this.props.user}
                            notify={this.props.notify}
                            onHide={this.closeNewVoterCreateModal}
                        />
                    }

                    <form id="household-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </div>
                            </div>

                           {
                            //     <div className="col-md-3">
                            //     <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                            //         <label className="control-label">Barangay</label>
                            //         <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                            //         </select>
                            //         <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                            //     </FormGroup>
                            // </div>
                           }
                        </div>

                        <div className="row">
                            <div className="col-md-9">
                                <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterName')}>
                                    <ControlLabel > Leader : </ControlLabel>
                                    <select id="voter-recruit-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterName')}</HelpBlock>
                                </FormGroup>
                            </div>

                            {
                            //     <div className="col-md-3">
                            //     <button style={{ marginTop: "25px" }} onClick={this.openNewVoterCreateModal} className="btn btn-primary btn-sm" type="button"> New Voter </button>
                            // </div>
                            }
                        </div>

                        <div className="row">

                            <div className="col-md-3" >
                                <FormGroup controlId="formLastname" validationState={this.getValidationState('lastname')}>
                                    <ControlLabel > Apelyido : </ControlLabel>
                                    <input type="text" value={this.state.form.data.lastname} className="input-sm form-control" onChange={this.setFormProp} name="lastname" />
                                    <HelpBlock>{this.getError('lastname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3" >
                                <FormGroup controlId="formFirstname" validationState={this.getValidationState('firstname')}>
                                    <ControlLabel > Pangalan : </ControlLabel>
                                    <input type="text" value={this.state.form.data.firstname} className="input-sm form-control" onChange={this.setFormProp} name="firstname" />
                                    <HelpBlock>{this.getError('firstname')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3" >
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
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">JPM Position</label>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                </div>
                            </div>
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">Barangay Position</label>
                                    <select id="other_position_select2" className="form-control form-filter input-sm" name="position">
                                    </select>
                                </div>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formSpecialOpGroup" validationState={this.getValidationState('specialOpGroup')}>
                                    <label className="control-label">Organization Name</label>
                                    <select id="organization_select2" className="form-control form-filter input-sm" name="position">
                                    </select>
                                    <HelpBlock>{this.getError('specialOpGroup')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                           <div className="col-md-3">
                                <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone # : </ControlLabel>
                                    <input type="text" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
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
                        municipalityNo: self.props.user.isAdmin ? "" : $("#household-create-form #municipality_select2").val(),
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

        $("#household-create-form #organization_select2").select2({
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
                url: Routing.generate('ajax_select2_organizations'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.special_op_group, text: item.special_op_group };
                        })
                    };
                },
            }
        });

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_municipality_alt", { name: self.props.municipalityName }),
            type: "GET"
        }).done(function (res) {
            $("#household-create-form #municipality_select2").empty()
                .append($("<option/>")
                    .val(res.municipality_no)
                    .text(res.name))
                .trigger("change");
        });

        if (!self.props.user.isAdmin) {
            $("#household-create-form #municipality_select2").attr('disabled', 'disabled');
        }

        $("#household-create-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
        });

        $("#household-create-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
        });

        $("#household-create-form #voter_group_select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });

        $("#household-create-form #organization_select2").on("change", function () {
            self.setFormPropValue("specialOpGroup", $(this).val());
        });

        $("#household-create-form #other_position_select2").on("change", function () {
            self.setFormPropValue("position", $(this).val());
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
            form.data.firstname = self.isEmpty(res.firstname) ? firstname.trim() : res.firstname;
            form.data.middlename = self.isEmpty(res.middlename) ? middlename.trim() : res.middlename;
            form.data.lastname = self.isEmpty(res.lastname) ? lastname.trim() : res.lastname;
            form.data.extName = res.extname;
            form.data.voterGroup = res.voterGroup;
            form.data.position = res.position;
            form.data.cellphone = res.cellphone;

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
        form.data.position = '';

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
        data.specialOpGroup = $("#household-create-form #organization_select2").val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_special_operation_header"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.reload();
            self.props.onHide();
            self.props.onSuccess(res.id);
            self.notify("New recruitment has been created.", 'teal');
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
            self.notify("Validation failed !", 'ruby');
        });
    }
});


window.SpecialOperationCreateModal = SpecialOperationCreateModal;