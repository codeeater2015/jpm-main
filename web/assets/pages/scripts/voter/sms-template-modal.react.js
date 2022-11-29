var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SmsTemplateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    templateName : ""
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white bold" closeButton>
                    <Modal.Title>Template Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="sms-form" onSubmit={this.submit}>
                        <div className="col-md-12">
                            <FormGroup controlId="formTemplateName" validationState={this.getValidationState('templateName')}>
                                <ControlLabel >Template Name : </ControlLabel>
                                <FormControl name="templateName" className="form-control input-sm" value={data.templateName} onChange={this.setFormProp} />
                                <HelpBlock>{this.getError('templateName')}</HelpBlock>
                            </FormGroup>
                        </div>

                        <div className="text-right col-md-12">
                            <button type="submit" style={{ marginRight: "5px" }} className="btn blue-madison btn-sm">Submit</button>
                            <button type="button" className="btn btn-sm btn-default" onClick={this.props.onHide}>Close</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
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

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        data.templateContent = this.props.templateContent;

        self.requestTemplate = $.ajax({
            url : Routing.generate("ajax_post_sms_template"),
            type : "POST",
            data : data
        }).done(function(res){
            
            self.props.notify("Template has been saved...", "teal");
            self.props.onSuccess();
            self.props.onHide();

        }).fail(function(err){
            self.setErrors(err.responseJSON);
            self.props.notify("Form validation failed...", "ruby");
        });
    },


    isEmpty: function (value) {
        return value == null || value == '';
    }
});


window.SmsTemplateModal = SmsTemplateModal;