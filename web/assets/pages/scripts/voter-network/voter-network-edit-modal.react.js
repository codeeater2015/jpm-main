var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterNetworkEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    nodeId: null,
                    voterId: null,
                    nodeLabel: "",
                    parentId: null,
                    municipalityNo: "",
                    brgyNo: "",
                    cellphoneNo: "",
                    remarks: ""
                },
                errors: []
            },
            parentNode: {
                municipalityNo: null,
                brgyNo: null
            },
            member: null
        };
    },

    getDefaultProps: function () {
        return {
            nodeId: null
        }
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadNode(this.props.nodeId);
    },

    initUploader: function () {
        var self = this;

        console.log("initializing uploader");

        $('#network-photo-upload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                console.log("image has been uploaded");
                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo(document.body);
                });
                self.loadVoter(self.props.proId, self.state.form.data.voterId);
            },
            progressall: function (e, data) {
                console.log("uploading");
                var progress = parseInt(data.loaded / data.total * 100, 10);
            }
        });
    },

    initSelect2: function () {
        var self = this;

        $("#form-voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: "custom-option",
            ajax: {
                url: Routing.generate('ajax_select2_groupless_voter'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.state.parentNode.provinceCode,
                        municipalityNo: self.state.parentNode.municipalityNo,
                        brgyNo: self.state.parentNode.brgyNo
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var text = "";
                            var disabled = false;
                            var voted = item.voted_2017 == 1 ? "*" : "";

                            if (item.in_network == true) {
                                text = voted + item.voter_name + " (" + (item.is_leader ? "LEADER" : "MEMBER") + ") - " + item.precinct_no;
                                disabled = true;
                            } else {
                                text = voted + item.voter_name + ' - ' + item.precinct_no;
                            }

                            return { id: item.voter_id, text: text, disabled: disabled };
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

        $("#voter-group-select2").on("change", function () {
            self.setFieldValue("voterGroup", $(this).val());
        });
    },

    loadNode: function (nodeId) {
        var self = this;
        self.requestNode = $.ajax({
            url: Routing.generate("ajax_get_network_node", { nodeId: nodeId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ parentNode: res }, self.loadVoter.bind(self, res.proId, res.voterId));
        });
    },

    loadVoter: function (proId, voterId) {
        var self = this;
        console.log("loading voter");
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
            form.data.voterGroup = res.voterGroup;
            form.data.cellphoneNo = self.isEmpty(res.cellphoneNo) ? "" : res.cellphoneNo;
            form.data.remarks = self.isEmpty(res.remarks) ? "" : res.remarks;

            $("#form-voter-select2").empty()
                .append($("<option/>")
                    .val(res.voterId)
                    .text(res.voterName))
                .trigger("change");


            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");

            $("#form-voter-select2").select2('enable', false);

            self.setState({ form: form, member: res }, self.initUploader);
        });
    },

    reset: function () {
        var form = this.state.form;
        form.data.voterId = "";
        form.data.nodeLabel = "";
        form.data.nodeOrder = 1;
        form.data.cellphoneNo = "";
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

        self.requestNode = $.ajax({
            url: Routing.generate("ajax_patch_network_node", { nodeId: self.props.nodeId }),
            type: "PATCH",
            data: data
        }).done(function (res) {
            self.props.onHide();
            self.props.notify("Record has been successfully updated.", "teal");
            self.props.onSuccess();
        }).fail(function (err) {
            if (err.status == '401') {
                self.props.notify("You dont have the permission to update this record.", "ruby");
            } else {
                self.props.notify("Form Validation Failed.", "ruby");
            }
            self.setErrors(err.responseJSON);
        });
    },

    generateIdNo: function () {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_generate_id_no", {
                voterId: self.state.form.data.voterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("ID NO has been created : " + res.proIdCode);
            self.loadVoter(self.props.proId, self.state.form.data.voterId);
        });
    },

    resetId: function () {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_reset_id", {
                voterId: self.state.form.data.voterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("You can now re-print this member ID");
            self.loadVoter(self.props.proId, self.state.form.data.voterId);
        });
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        if (this.state.member != null) {
            var proIdCode = this.state.member.proIdCode != null ? this.state.member.proIdCode.replace('KFC', '') : "no_id";
            var photoUrl = 'http://' + window.hostIp + '/voter-2018/web/app.php' + '/voter/photo/' + this.props.proId + '_' + proIdCode + "?" + new Date().getTime();
        }

        return (
            <Modal keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Node Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-node-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                {this.state.member != null && (
                                    <div className="row voter-view-header">
                                        <div className="col-md-12">
                                            <div>
                                                <a href={photoUrl} data-lightbox="Profile Photo" data-title="Profile Photo" >
                                                    <img src={photoUrl} className="img-responsive" alt="" />
                                                </a>
                                            </div>
                                            <div className="profile-userbuttons" style={{ marginTop: "10px" }}>
                                                <span className="btn col-md-12 green btn-sm fileinput-button ">
                                                    <span> Change Photo</span>
                                                    <input id="network-photo-upload" type="file" name="files[]" data-url={Routing.generate('ajax_upload_project_voter_photo', { proId: this.props.proId, voterId: this.state.form.data.voterId })} multiple={false} />
                                                </span>
                                            </div>
                                            {this.isEmpty(this.state.member.proIdCode) && (
                                                <div>
                                                    <button className="btn btn-primary btn-sm col-md-12" type="button" onClick={this.generateIdNo} style={{ "marginTop": "10px" }}>Generate ID No</button>
                                                </div>
                                            )}
                                            {this.state.hasId && (
                                                <div>
                                                    <button className="btn btn-info btn-sm col-md-12" type="button" onClick={this.resetId} style={{ "marginTop": "10px" }}>Reprint ID</button>
                                                </div>
                                            )}
                                            <br/>
                                            <div style={{ marginTop: "10px" }}>
                                                <div><strong>Birthdate : </strong> {this.state.member.birthdate == "" ? "- - - -" : this.state.member.birthdate} </div>
                                                <div><strong>Municipality :</strong> {this.state.member.municipalityName} </div>
                                                <div><strong>Barangay :</strong> {this.state.member.barangayName} </div>
                                                <div><strong>Precinct No :</strong> {this.state.member.precinctNo} </div>
                                                <div><strong>Cellphone No :</strong> {this.state.member.cellphoneNo} </div>
                                                <div><strong>Position : </strong> {this.state.member.voterGroup == "" ? "- - - -" : this.state.member.voterGroup} </div>
                                                <div><strong>ID No : </strong> {this.state.member.proIdCode == "" ? "- - - -" : this.state.member.proIdCode} </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                {this.state.member == null && (
                                    <div className="row voter-view-header">
                                        <div className="col-md-12">
                                            <h4 className="text-center">Member not found...</h4>
                                        </div>
                                    </div>
                                )}

                            </div>

                            <div className="col-md-9">
                                <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterId')}>
                                    <ControlLabel > Voter Name : </ControlLabel>
                                    <select id="form-voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterId')}</HelpBlock>
                                </FormGroup>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                                        <ControlLabel > CellphoneNo : </ControlLabel>
                                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                                        <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingRight: "0" }}>
                                    <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                        <ControlLabel >Position : </ControlLabel>
                                        <select id="voter-group-select2" className="form-control input-sm">
                                            <option value=""> </option>
                                        </select>
                                        <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                    </FormGroup>
                                </div>

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
                                <button className="btn btn-primary btn-sm" disabled={this.isEmpty(this.state.form.data.voterId)} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body >
            </Modal >
        );
    }
});


window.VoterNetworkEditModal = VoterNetworkEditModal;