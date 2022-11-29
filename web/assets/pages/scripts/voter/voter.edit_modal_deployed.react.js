var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterEditModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    voterId  : 0,
                    voterName : "",
                    address : "",
                    voterClass : 0
                },
                errors : []
            }
        };
    },

    render : function(){
        return (
            <Modal  keyboard={false} enforceFocus={false} bsSize="sm" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Update Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-updated-form">
                        <div class="row" style={{padding:0}}>
                            <div className="col-md-12">
                                <FormGroup controlId="formVoterClass" validationState={this.getValidationState('voterClass')}>
                                    <ControlLabel> Tag : </ControlLabel>
                                    <FormControl componentClass="select" bsClass="form-control input-sm" name="voterClass" value={this.state.form.data.voterClass} onChange={this.setFormProp}>
                                        <option value=""> -- Select --</option>
                                        <option value="1"> 1 </option>
                                        <option value="2"> 2 </option>
                                        <option value="3"> 3 </option>
                                        <option value="4"> 4 </option>
                                        <option value="5"> 5 </option>
                                        <option value="6"> 6 </option>
                                        <option value="7"> 7 </option>
                                        <option value="8"> 8 </option>
                                        <option value="9"> 9 </option>
                                    </FormControl>
                                    <HelpBlock>{this.getError('voterClass')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-12">
                            <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                                    <ControlLabel> Cellphone : </ControlLabel>
                                    <FormControl bsClass="form-control input-sm" name="cellphoneNo" value={this.state.form.data.cellphoneNo} onChange={this.setFormProp}/>
                                    <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="clearfix"></div>

                        <div className="text-right col-md-12" >
                            <button type="button" className="btn  btn-default" style={{marginRight : "5px"}}  onClick={this.props.onHide}>Close</button>
                            <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount : function(){
        this.loadVoter(this.props.voterId);
    },
    
    loadVoter : function(voterId){
        var self = this;

        self.requestVoter = $.ajax({
            url : Routing.generate("ajax_get_voter", {voterId : voterId}),
            type : "GET"
        }).done(function(res){
            var form = self.state.form;
            form.data.voterClass = res.voterClass;
            form.data.cellphoneNo = res.cellphoneNo;
            form.data.voted2017 = res.voted2017;
            form.data.hasAst = res.hasAst;
            form.data.hasA = res.hasA;
            form.data.hasB = res.hasB;
            form.data.hasC = res.hasC;

            self.setState({form : form});
        });
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

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        self.requestPost = $.ajax({
            url : Routing.generate('ajax_patch_voter',{voterId : this.props.voterId}),
            type : "PATCH",
            data : (data)
        }).done(function(res){
            self.props.notify("Record has been updated. Waiting for admin's approval for changes to take effect.","ruby");
            self.props.onHide();
        }).fail(function(err){
            if(err.status == '401'){
                self.props.notify("You dont have the permission to update this record.","ruby");
            }else{
                self.props.notify("Form Validation Failed.","ruby");
            }
            self.setErrors(err.responseJSON);
        });
    }
});

window.VoterEditModal = VoterEditModal;