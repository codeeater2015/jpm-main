var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var PulahanCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-recruit-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterId')}>
                                    <ControlLabel > Voter Name : </ControlLabel>
                                    <select id="voter-recruit-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterId')}</HelpBlock>
                                </FormGroup>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphone')}>
                                        <ControlLabel > CellphoneNo : </ControlLabel>
                                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                        <HelpBlock>{this.getError('cellphone')}</HelpBlock>
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

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        1
                                        <input type="checkbox" checked={this.state.form.data.is1 == 1} onChange={this.handleCheckbox} name="is1"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        6
                                    <input type="checkbox" checked={this.state.form.data.is6 == 1} onChange={this.handleCheckbox} name="is6"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        2
                                        <input type="checkbox" checked={this.state.form.data.is2 == 1} onChange={this.handleCheckbox} name="is2"></input>
                                        <span></span>
                                    </label>
                                </div>
                             
                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        7
                                    <input type="checkbox" checked={this.state.form.data.is7 == 1} onChange={this.handleCheckbox} name="is7"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        3
                                    <input type="checkbox" checked={this.state.form.data.is3 == 1} onChange={this.handleCheckbox} name="is3"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        8
                                    <input type="checkbox" checked={this.state.form.data.is8 == 1} onChange={this.handleCheckbox} name="is8"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        4
                                    <input type="checkbox" checked={this.state.form.data.is4 == 1} onChange={this.handleCheckbox} name="is4"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        9
                                    <input type="checkbox" checked={this.state.form.data.is9 == 1} onChange={this.handleCheckbox} name="is9"></input>
                                        <span></span>
                                    </label>
                                </div>

                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        5
                                    <input type="checkbox" checked={this.state.form.data.is5 == 1} onChange={this.handleCheckbox} name="is5"></input>
                                        <span></span>
                                    </label>
                                </div>
                            
                                <div className="col-md-6">
                                    <label className="mt-checkbox status-checkbox">
                                        10
                                    <input type="checkbox" checked={this.state.form.data.is10 == 1} onChange={this.handleCheckbox} name="is10"></input>
                                        <span></span>
                                    </label>
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
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#voter-recruit-select2").select2({
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

        $("#voter-recruit-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
    },

    loadVoter: function (proId, voterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, voterId: voterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            form.data.proVoterId = res.proVoterId;
            form.data.voterId = res.voterId;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? 'KFC' : res.voterGroup;
            form.data.assignedPrecinct = self.isEmpty(res.assignedPrecinct) ? '' : res.assignedPrecinct;
            form.data.precinctNo = self.isEmpty(res.precinctNo) ? '' : res.precinctNo;

            form.data.is1 = 1;
            form.data.is2 = res.is2;
            form.data.is3 = res.is3;
            form.data.is4 = res.is4;
            form.data.is5 = res.is5;
            form.data.is6 = res.is6;
            form.data.is7 = res.is7;
            form.data.is8 = res.is8;    
            form.data.is9 = res.is9;
            form.data.is10 = res.is10;
            form.data.is11 = res.is11;
            form.data.is12 = res.is12;
            form.data.is13 = res.is13;
            form.data.is14 = res.is14;            

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
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },
    
    handleCheckbox: function (e) {
        var form = this.state.form;

        form.data[e.target.name] = e.target.checked ? 1 :0;

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

        form.data.proVoterId = "";
        form.data.voterId = "";
        form.data.voterGroup = "";
        form.data.cellphone = "";
        form.data.is1 = 0;
        form.data.is2 = 0;
        form.data.is3 = 0;
        form.data.is4 = 0;
        form.data.is5 = 0;
        form.data.is6 = 0;
        form.data.is6 = 0;
        form.data.is7 = 0;
        form.data.is8 = 0;
        form.data.is9 = 0;
        form.data.is10 = 0;

        form.errors = [];

        $("#voter-recruit-select2").empty().trigger("change");
        $("#voter-group-select2").empty().trigger("change");

        this.setState({ form: form });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        data.proId = self.props.proId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_project_voter_alt"),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.reset();
            self.props.reload();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.PulahanCreateModal = PulahanCreateModal;