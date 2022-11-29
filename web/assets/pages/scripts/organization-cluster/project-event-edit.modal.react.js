var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectEventEditModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    eventName : "",
                    eventDate : null,
                    status : ""
                },
                errors : []
            }
        };
    },

    render : function(){
        var self = this;

        return (
            <Modal style={{ marginTop : "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit Event</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="event-form" >
                        <FormGroup controlId="formEventName" validationState={this.getValidationState('eventName')}>
                            <ControlLabel> Event Name : </ControlLabel>
                            <FormControl bsClass="form-control input-sm" name="eventName" value={this.state.form.data.eventName} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('eventName')}</HelpBlock>
                        </FormGroup>

                        <div className="col-md-4" style={{ paddingRight : "0" , paddingLeft : "0" }}>
                            <FormGroup controlId="formEventDate" validationState={this.getValidationState('eventDate')}>
                                <ControlLabel> Event Date : </ControlLabel>
                                <FormControl type="date" bsClass="form-control input-sm" name="eventDate" value={this.state.form.data.eventDate} onChange={this.setFormProp}/>
                                <HelpBlock>{this.getError('eventDate')}</HelpBlock>
                            </FormGroup>
                        </div>

                        <div className="col-md-3" >
                        <FormGroup controlId="formStatus" validationState={this.getValidationState('status')}>
                            <ControlLabel> Status : </ControlLabel>
                            <FormControl componentClass="select" bsClass="form-control input-sm" name="status" value={this.state.form.data.status} onChange={this.setFormProp}>
                                <option value="A"> Active </option>
                                <option value="I"> Inactive </option>
                            </FormControl>
                            <HelpBlock>{this.getError('status')}</HelpBlock>
                        </FormGroup>
                    </div>
                        
                        <div className="clearfix"/>

                        <FormGroup controlId="formEventDesc" validationState={this.getValidationState('eventDesc')}>
                            <ControlLabel> Purpose : </ControlLabel>
                            <FormControl componentClass="textarea" rows="6" bsClass="form-control input-sm" name="eventDesc" value={this.state.form.data.eventDesc} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('eventDesc')}</HelpBlock>
                        </FormGroup>

                        <div className="text-right" >
                            <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            <button type="button" className="btn  btn-default" style={{marginRight : "5px"}}  onClick={this.props.onHide}>Close</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount : function(){
        console.log("event id");
        console.log(this.props.eventId);
        this.loadEvent(this.props.eventId);
    },

    loadEvent : function(eventId){
        var self = this;

        self.requestEvent = $.ajax({
            url : Routing.generate("ajax_get_project_event_header", { eventId : eventId }),
            type : "GET"
        }).done(function(res){
            var form  = self.state.form;
            form.data = res;
            form.data.eventDate = self.isEmpty(res.eventDate) ? null : moment(res.eventDate).format('YYYY-MM-DD');

            self.setState({ form : form });
        });
    },

    setFormPropValue : function(field,value){
        var form = this.state.form;
        form.data[field] = value;
        this.setState({form : form});
    },

    setFormProp : function(e){
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({form : form});
    },

    setErrors : function(errors){
        var form = this.state.form;
        form.errors = errors;
        this.setState({form : form});
    },

    getError : function(field){
        var errors = this.state.form.errors;
        for(var errorField in errors){
            if(errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState : function(field){
        return this.getError(field) != null ? 'error' : '';
    },

    isEmpty : function(value){
        return value == null || value == '';
    },

    reset : function(){
      var form = this.state.form;
      form.errors = [];

      this.setState({form : form});
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        
        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_project_event_header",{ eventId : self.props.eventId }),
            data: data,
            type: 'PATCH'
        }).done(function(res){
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function(err){
             self.setErrors(err.responseJSON);
        });
    }
});


window.ProjectEventEditModal = ProjectEventEditModal;