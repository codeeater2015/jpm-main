var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var NewIdRequestModal = React.createClass({
    getInitialState: function () {
        return {
            form: {
                data: {
                   municipalityNo : null,
                   brgyNo : null,
                   submittedBy : "",
                   submittedAt : moment(new Date()).format("YYYY-MM-DD"),
                   totalReceived : 0,
                   remarks : ""
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

        $("#new_request_form #municipality_select2").select2({
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
                        provinceCode: self.props.provinceCode
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

        $("#new_request_form #barangay_select2").select2({
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
                        municipalityNo: $("#new_request_form #municipality_select2").val(),
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

        $("#new_request_form #municipality_select2").on("change", function () {
            self.setFieldValue("municipalityNo",$(this).val());
        });

        $("#new_request_form #barangay_select2").on("change", function () {
            self.setFieldValue("brgyNo",$(this).val());
        });
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

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        data.proId = self.props.proId;
        data.electId = self.props.electId;
        data.provinceCode = self.props.provinceCode;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_id_request_header"),
            data: data,
            type: 'POST'
        }).done(function(res){
            self.props.onSuccess();
            self.props.onHide();
        }).fail(function(err){
             self.setErrors(err.responseJSON);
        });
    },

    render: function () {
        return (
            <Modal enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header closeButton>
                    <Modal.Title>New Request</Modal.Title>
                </Modal.Header>

                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="new_request_form" onSubmit={this.submit}>
                        <div className="row">

                            <div className="col-md-12">
                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formMunicipalityNo">
                                        <ControlLabel > Municipality : </ControlLabel>
                                        <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formBrgyNo">
                                        <ControlLabel > Barangay : </ControlLabel>
                                        <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                        </select>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formSubmittedBy" validationState={this.getValidationState('submittedBy')}>
                                        <ControlLabel > Submitted By </ControlLabel>
                                        <input type="text" value={this.state.form.data.submittedBy} className="input-sm form-control" onChange={this.setFormProp} name="submittedBy" />
                                        <HelpBlock>{this.getError('submittedBy')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            
                                <div className="col-md-6" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                        <ControlLabel > Cellphone No : </ControlLabel>
                                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                        <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                    </FormGroup>
                                </div> 

                                <div className="col-md-4" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formSubmittedAt" validationState={this.getValidationState('submittedAt')}>
                                        <ControlLabel > Received At </ControlLabel>
                                        <input type="date" value={this.state.form.data.submittedAt} className="input-sm form-control" onChange={this.setFormProp} name="submittedAt" />
                                        <HelpBlock>{this.getError('submittedAt')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-2" style={{ paddingLeft: "0" }}>
                                    <FormGroup controlId="formTotalReceived" validationState={this.getValidationState('totalReceived')}>
                                        <ControlLabel > Received : </ControlLabel>
                                        <input type="number" value={this.state.form.data.totalReceived} className="input-sm form-control" onChange={this.setFormProp} name="totalReceived" />
                                        <HelpBlock>{this.getError('totalReceived')}</HelpBlock>
                                    </FormGroup>
                                </div> 

                                <div className="clearfix"/>

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
                                <button className="btn btn-primary btn-sm" type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.NewIdRequestModal = NewIdRequestModal;