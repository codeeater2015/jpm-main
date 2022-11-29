var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterCreateAssistanceModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    voterId  : 0,
                    description : "",
                    remarks : "",
                    amount : 0,
                    category : "",
                    status : "",
                    issuedAt : moment(new Date()).format('YYYY-MM-DD')
                },
                errors : []
            }
        };
    },

    render : function(){
     
        return (
            <Modal  keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Assisatance Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-updated-form">
                        <div className="col-md-4">
                            <FormGroup controlId="formIssuedAt" validationState={this.getValidationState('issuedAt')}>
                                <ControlLabel >Issued At : </ControlLabel>
                                <FormControl type='date' bsClass="form-control input-sm" name="issuedAt" value={this.state.form.data.issuedAt} onChange={this.setFormProp}/>
                                <HelpBlock>{this.getError('issuedAt')}</HelpBlock>
                            </FormGroup>
                        </div>
                        <div className="clearfix"/>
                        <div className="col-md-6">
                            <FormGroup controlId="formCategory" validationState={this.getValidationState('category')}>
                                <ControlLabel >Category : </ControlLabel>
                                <select id="category-select2" className="form-control input-sm">
                                    <option value=""> </option>
                                </select>
                                <HelpBlock>{this.getError('category')}</HelpBlock>
                            </FormGroup>
                        </div>
                        <div className="col-md-6">
                            <FormGroup controlId="formAmount" validationState={this.getValidationState('amount')}>
                                <ControlLabel> Amount : </ControlLabel>
                                <FormControl bsClass="form-control input-sm" name="amount" value={this.state.form.data.amount} onChange={this.setFormProp}/>
                                <HelpBlock>{this.getError('amount')}</HelpBlock>
                            </FormGroup>
                        </div>
                        
                        <div className="clearfix"></div>
                        
                        <div className="col-md-12">
                            <FormGroup controlId="formDescription" validationState={this.getValidationState('description')}>
                                <ControlLabel> Description : </ControlLabel>
                                <FormControl componentClass="textarea" rows="5" bsClass="form-control input-sm" name="description" value={this.state.form.data.description} onChange={this.setFormProp}/>
                                <HelpBlock>{this.getError('description')}</HelpBlock>
                            </FormGroup>
                        </div>
                        
                        <div className="clearfix"/>

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
       this.initSelect2();
    },
    
    initSelect2 : function(){
        $("#category-select2").select2({
            casesentitive : false,
            placeholder : "Enter Name",
            width : '100%',
            allowClear : true,
            tags : true,
            containerCssClass : ":all:",
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax : {
                url : Routing.generate('ajax_select2_assistance_category'),
                data :  function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return {id:item.category,text: item.category};
                        })
                    };
                },
            }
        });

        var self = this;
        
        $("#category-select2").on("change", function() {
            self.setFieldValue("category",$(this).val());
        });
    },

    reinitSelect2 : function(){
        var category = this.state.form.data.category;
        $("#category-select2").empty()
            .append($("<option/>")
                .val(category)
                .text(category))
            .trigger("change");
    },

    setFormProp : function(e){
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({form : form});
    },

    setFieldValue : function(field,value){
        var form = this.state.form;
        form.data[field] = value;
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
            url : Routing.generate('ajax_post_voter_assistance',{voterId : this.props.voterId}),
            type : "POST",
            data : (data)
        }).done(function(res){
            self.props.notify("Assistance record has been saved.","ruby");
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

window.VoterCreateAssistanceModal = VoterCreateAssistanceModal;