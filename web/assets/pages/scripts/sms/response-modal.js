var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ResponseModal = React.createClass({

    getInitialState: function () {
        return {
            unselected: [],
            options: [],
            form: {
                data: {
                    messageBody: ""
                },
                errors: []
            },
            maxChars : 160
        };
    },

    componentDidMount: function () {
        console.log('pro voter id');
        console.log(this.props.proVoterId);
    },

    render: function () {
        var self  = this;
        var data = self.state.form.data;

        return (
            <Modal enforceFocus={false} backdrop="static" bsSize="lg" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header closeButton>
                    <Modal.Title>Create Template</Modal.Title>
                </Modal.Header>

                <Modal.Body bsClass="modal-body overflow-auto">
                    <form onSubmit={this.submit}>

                        <FormGroup controlId="formMessageBody" validationState={this.getValidationState('messageBody')}>
                            <ControlLabel >Your Message : </ControlLabel>
                            <FormControl componentClass="textarea" rows="5" name="messageBody" className="form-control input-sm" value={data.messageBody} onChange={this.setMessageBody} />
                            <small style={{ fontSize: "12px" }}>
                                <span>
                                    {"You may use the ff. keywords({name1}, {name2}, {name3}, {precinctNo}, {voterNo}, {brgy}, {mun}) to add additional information on your message."}
                                </span>
                            </small>
                            <div className="text-right">
                                <label>Letter Count : {data.messageBody.length + " / " + self.state.maxChars} </label>
                            </div>
                            <HelpBlock>{this.getError('messageBody')}</HelpBlock>
                        </FormGroup>

                        <div className="clearfix"></div>
                        <div className="text-right m-t-md">
                            <button type="button" className="btn btn-default" onClick={this.props.onHide}>Cancel</button>
                            <button type="submit" className="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    setMessageBody : function(e){
        var form = this.state.form;

        if(e.target.value.length > this.state.maxChars)
            form.data.messageBody = e.target.value.substring(0,this.state.maxChars);
        else
            form.data.messageBody = e.target.value;

        this.setState({form : form});
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
        if (this.getError(field) != null)
            return "error";

        return null;
    },

    submit: function (e) {
        e.preventDefault();
        var self = this;
        var data = self.state.form.data;

        self.requestPostSms = $.ajax({
            url: Routing.generate('ajax_reply_sms', { proId: this.props.proId, proVoterId : this.props.proVoterId }),
            type: 'POST',
            data: (data)
        }).done(function (res) {
            self.props.onHide();
        }).fail(function (res) {
            self.setErrors(res.responseJSON);
        });
    },

    isEmpty: function (value) {
        return value == null || value == '';
    }
});


window.ResponseModal = ResponseModal;