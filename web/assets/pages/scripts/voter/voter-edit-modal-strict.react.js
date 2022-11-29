var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    category: "",
                    cellphone: "",
                    organization: "",
                    voterGroup: ""
                },
                errors: []
            },
            voter: {
                status : 'I'
            },
            showCropModal : false
        };
    },

    componentDidMount: function () {
        console.log('pro id');
        console.log(this.props.proId);
        console.log('pro voter id');
        console.log(this.props.proVoterId);
        
        this.loadVoter(this.props.proId, this.props.proVoterId);
        this.initSelect2();
        this.initUploader();
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proVoterId: proVoterId, proId: proId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.cellphone = res.cellphoneNo;
            form.data.category = res.category;
            form.data.organization = res.organization;
            form.data.voterGroup = res.voterGroup;
            form.data.proIdCode = res.proIdCode;
            form.data.assignedPrecinct = res.assignedPrecinct;
            form.data.precinctNo = res.precinctNo;
            form.data.remarks = res.remarks;
            form.data.status = res.status;

            self.setState({ form: form, voter: res }, self.reinitSelect2);
        });
    },

    initSelect2: function () {

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

        var self = this;

        $("#voter-group-select2").on("change", function () {
            self.setFieldValue("voterGroup", $(this).val());
        });
    },

    reinitSelect2: function () {
        var voterGroup = this.state.form.data.voterGroup;

        $("#voter-group-select2").empty()
            .append($("<option />")
                .val(voterGroup)
                .text(voterGroup))
            .trigger("change");
    },


    initUploader: function () {
        var self = this;

        $('#voter-photo-upload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo(document.body);
                });
            
                self.refresh();
                self.openCropModal();
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
            }
        });
    },

    openCropModal : function(){
        this.setState({ showCropModal : true });
    },

    closeCropModal : function(){
        this.setState({ showCropModal : false });
    },

    generateIdNo: function () {
        var self = this;
        
        alert("generating id number");

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_generate_id_no", {
                proVoterId: self.props.proVoterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("ID NO has been created : " + res.proIdCode);
            self.refresh();
        });
    },

    resetId: function () {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_reset_id", {
                proVoterId: self.props.proVoterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("You can now re-print this member ID");
            self.refresh();
        });
    },

    refresh : function(){
        this.loadVoter(this.props.proId, this.props.proVoterId);
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
        data.proId = this.props.proId;

        self.requestPost = $.ajax({
            url: Routing.generate('ajax_patch_project_voter', {
                proVoterId: this.props.proVoterId,
                proId: this.props.proId
            }),
            type: "PATCH",
            data: (data)
        }).done(function (res) {
            self.props.notify("Record has been updated.", "ruby");
            self.props.onHide();
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
        if (!this.isEmpty(this.state.voter)) {
            var photoUrl = window.imgUrl + this.props.proId + '_' + this.state.voter.generatedIdNo + "?" + new Date().getTime();
        }

        return (
            <Modal keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Update Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="row">
                        <div className="col-md-4">
                            <div onClick={this.openCropModal}>
                                <img src={photoUrl} className="img-responsive" alt="" />
                            </div>
                            {
                                this.state.showCropModal && 
                                (
                                    <VoterCropModal 
                                        proId={this.props.proId}
                                        proVoterId={this.props.proVoterId}
                                        generatedIdNo={this.state.voter.generatedIdNo}
                                        show={this.state.showCropModal}
                                        onHide={this.closeCropModal}
                                        onSuccess={this.refresh}
                                    />
                                )
                            }
  
                            {!this.isEmpty(this.state.voter) &&
                                (
                                    <div style={{ marginTop: "90px" }} >
                                        <div><small><strong>Name : </strong> {this.state.voter.voterName} </small></div>
                                        <div><small><strong>Municipality :</strong> {this.state.voter.municipalityName}</small></div>
                                        <div><small><strong>Barangay :</strong> {this.state.voter.barangayName}</small></div>
                                        <div><small><strong>Precinct No :</strong> {this.state.voter.precinctNo}</small></div>
                                        <div><small><strong>Assigned Precinct :</strong> {this.state.voter.assignedPrecinct}</small></div>
                                    </div>
                                )
                            }
                        </div>
                        <div className="col-md-8">
                            <form id="voter-updated-form">

                                <div className="row" >
                                    <div className="col-md-12">
                                        <div className="col-md-6">
                                            <FormGroup controlId="formPrecinctNo" validationState={this.getValidationState('precinctNo')}>
                                                <ControlLabel> Precinct No : </ControlLabel>
                                                <FormControl bsClass="form-control input-sm" name="precinctNo" value={this.state.form.data.precinctNo} disabled={true} />
                                                <HelpBlock>{this.getError('precinctNo')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                        <div className="col-md-6">
                                            <FormGroup controlId="formAssignedPrecinct" validationState={this.getValidationState('assignedPrecinct')}>
                                                <ControlLabel> Assigned Precinct No : </ControlLabel>
                                                <FormControl bsClass="form-control input-sm" name="assignedPrecinct" value={this.state.form.data.assignedPrecinct} onChange={this.setFormProp} />
                                                <HelpBlock>{this.getError('assignedPrecinct')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                    </div>
                                </div>

                                <div className="clearfix"/>

                                <div className="row" >
                                    <div className="col-md-12">
                                        <div className="col-md-6">
                                            <FormGroup controlId="formProIdCode" validationState={this.getValidationState('proIdCode')}>
                                                <ControlLabel> ID No : </ControlLabel>
                                                <FormControl bsClass="form-control input-sm" disabled={true} name="proIdCode" value={this.state.form.data.proIdCode} onChange={this.setFormProp} />
                                                <HelpBlock>{this.getError('proIdCode')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                        <div className="col-md-6">
                                            <FormGroup controlId="formStatus" validationState={this.getValidationState('status')}>
                                                <ControlLabel> Status : </ControlLabel>
                                                <FormControl componentClass="select" bsClass="form-control input-sm" name="status" value={this.state.form.data.status} onChange={this.setFormProp}>
                                                    <option value=""> -- Select Status --</option>
                                                    <option value="A">Active</option>
                                                    <option value="I">Inactive</option>
                                                    <option value="B">Blocked</option>
                                                </FormControl>
                                                <HelpBlock>{this.getError('status')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                    </div>
                                </div>

                                <div className="clearfix"></div>

                                <div className="row" >
                                    <div className="col-md-12">
                                        <div className="col-md-6">
                                            <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                                <ControlLabel >Position : </ControlLabel>
                                                <select id="voter-group-select2" className="form-control input-sm">
                                                    <option value=""> </option>
                                                </select>
                                                <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                            </FormGroup>
                                        </div>

                                        <div className="col-md-6">
                                            <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                                <ControlLabel> Cellphone : </ControlLabel>
                                                <FormControl bsClass="form-control input-sm" name="cellphone" value={this.state.form.data.cellphone} onChange={this.setFormProp} />
                                                <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                    </div>
                                </div>
                                <div className="clearfix"></div>

                                <div className="row" >
                                    <div className="col-md-12">
                                        <div className="col-md-12">
                                            <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                                <ControlLabel> Remarks : </ControlLabel>
                                                <FormControl componentClass="textarea" rows="5" bsClass="form-control input-sm" name="remarks" value={this.state.form.data.remarks} onChange={this.setFormProp} />
                                                <HelpBlock>{this.getError('remarks')}</HelpBlock>
                                            </FormGroup>
                                        </div>
                                    </div>
                                </div>

                                <div className="text-right col-md-12" >
                                    <button type="button" className="btn btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                                </div>

                            </form>
                        </div>
                    </div>

                </Modal.Body>
            </Modal>
        );
    }

});

window.VoterEditModal = VoterEditModal;