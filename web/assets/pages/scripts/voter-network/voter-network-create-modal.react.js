var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterNetworkCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
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
            parentNode: null
        };
    },

    getDefaultProps: function () {
        return {
            nodeId: null
        }
    },

    componentDidMount: function () {
        this.loadNode(this.props.nodeId);
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
                        electId: self.state.parentNode.electId,
                        proId: self.state.parentNode.proId,
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

        $("#form-voter-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
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
            self.setState({ parentNode: res });
            self.initSelect2();
        });
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
            form.data.cellphoneNo = self.isEmpty(res.cellphoneNo) ? "" : res.cellphoneNo;
            form.data.voterGroup = res.voterGroup;
            form.data.remarks = self.isEmpty(res.remarks) ? "" : res.remarks;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");

            self.setState({ form: form });
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
        data.parentId = self.props.nodeId;

        console.log("node information");
        console.log(data);

        self.requestNode = $.ajax({
            url: Routing.generate("ajax_post_network_node"),
            type: "POST",
            data: data
        }).done(function (res) {
            //self.props.onHide();
            self.reset();
            self.props.onSuccess();
            self.props.notify("Member branch has been added.", "teal");
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
                    <Modal.Title>Node Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-node-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterId')}>
                                    <ControlLabel > Voter Name : </ControlLabel>
                                    <select id="form-voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterId')}</HelpBlock>
                                </FormGroup>

                                <div className="col-md-6" style={{ paddingLeft : "0" }}>
                                    <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                                        <ControlLabel > CellphoneNo : </ControlLabel>
                                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                                        <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingRight : "0" }}>
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
                </Modal.Body>
            </Modal>
        );
    }
});


window.VoterNetworkCreateModal = VoterNetworkCreateModal;