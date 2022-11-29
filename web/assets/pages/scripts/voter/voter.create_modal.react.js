var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterCreateModal = React.createClass({

    getInitialState : function(){
        return {
            showDetailViewModal : false,
            form : {
                data : {
                    fyCode : 2017,
                    idxDate : moment(new Date()).format('YYYY-MM-DDThh:mm'),
                    recvCode : '',
                    paxCount : 1,
                    dvNo : '',
                    idxNo : "",
                    remarks : ""
                },
                errors : []
            },
            accIdxFiscalYears : [],
            details : null,
            showIdxNoModal : false
        };
    },

    getDefaultProps : function(){
        return {
            create : true
        }
    },

    componentDidMount : function(){
        // this.initSelect2();
    },

    initSelect2 : function(){
        var self = this;

        $("#idx-user-select2").select2({
            casesentitive : false,
            placeholder : "Enter Code or Name",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('indexing_ajax_select2_user'),
                data :  function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.user_code , text: item.user_name};
                        })
                    };
                },
            }
        });


        $("#idx-user-select2").on("change", function() {
            
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

    numberWithCommas : function(x) {
        x = parseFloat(x).toFixed(2);
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    isEmpty : function(value){
        return value == null || value == '';
    },

    reset : function(){
      var form = this.state.form;
      form.data.idxDate = moment(new Date()).format('YYYY-MM-DDThh:mm');
      form.data.remarks = "";
      form.data.paxCount =  1;
      form.errors = [];

      $("#idx-obr-select2").empty().trigger("change");

      this.setState({details : null , form : form});
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        // self.requestPost = $.ajax({
        //     url : Routing.generate('indexing_ajax_post_idx_hdr'),
        //     type : "POST",
        //     data : (data)
        // }).done(function(res){
        //     var form  = self.state.form;
        //     form.data.idxNo = res.idxNo;
        //     self.setState({form : form});
        //     self.props.notify("Transaction has been completed.","teal");
        //     self.openIdxNoModal();
        // }).fail(function(err){
        //     self.props.notify("Form Validation Failed.","ruby");
        //     self.setErrors(err.responseJSON);
        // });
    },

    render : function(){
        var self = this;

        return (
            <Modal  keyboard={false} enforceFocus={false} dialogClassName="modal-custom-85" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Voter Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="carding-entry-form">
                        <div class="row" style={{padding:0}}>
                        
                            <div className="col-md-5">
                                <FormGroup controlId="formIdxDate" validationState={this.getValidationState('idxDate')}>
                                    <ControlLabel> Name : </ControlLabel>
                                    <FormControl type="datetime-local" bsClass="form-control input-sm" name="idxDate" value={this.state.form.data.idxDate} onChange={this.setFormProp}/>
                                    <HelpBlock>{this.getError('idxDate')}</HelpBlock>
                                </FormGroup>
                            </div>
                                
                            <div className="col-md-2" style={{paddingRight:0}}>
                                <FormGroup controlId="formFyCode" validationState={this.getValidationState('fyCode')}>
                                    <ControlLabel >FY : </ControlLabel>
                                    <select name="fyCode" className="form-control input-sm" value={this.state.form.data.fyCode} onChange={this.setFormProp}>
                                        <option value=""> -- Select Year --</option>
                                        {this.state.accIdxFiscalYears.map(function(item){
                                            return (<option key={"acc" + item.fy_code} value={item.fy_code}>{item.fy_code}</option>);
                                        })}
                                    </select>
                                    <HelpBlock>{this.getError('fyCode')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-5">
                                <FormGroup controlId="formIdxDate" validationState={this.getValidationState('idxDate')}>
                                    <ControlLabel> Date Indexed : </ControlLabel>
                                    <FormControl type="datetime-local" bsClass="form-control input-sm" name="idxDate" value={this.state.form.data.idxDate} onChange={this.setFormProp}/>
                                    <HelpBlock>{this.getError('idxDate')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-5">
                                <FormGroup controlId="formUserCode" validationState={this.getValidationState('userCode')}>
                                    <ControlLabel >Indexed By : </ControlLabel>
                                    <select id="idx-user-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('userCode')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="clearfix"></div>

                            <div className="col-md-9">
                                <FormGroup controlId="formRecvCode" validationState={this.getValidationState('recvCode')}>
                                    <ControlLabel >OBR : </ControlLabel>
                                    <select id="idx-obr-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('recvCode')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formPaxCount" validationState={this.getValidationState('paxCount')}>
                                    <ControlLabel> Pax Count : </ControlLabel>
                                    <FormControl type="number" bsClass="form-control input-sm" name="paxCount" value={this.state.form.data.paxCount} onChange={this.setFormProp}/>
                                    <HelpBlock>{this.getError('paxCount')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-12">
                                <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                    <ControlLabel> Remarks : </ControlLabel>
                                    <FormControl componentClass="textarea" rows="9" value={this.state.form.data.remarks} bsClass="form-control input-sm" name="remarks" onChange={this.setFormProp}/>
                                    <HelpBlock>{this.getError('remarks')}</HelpBlock>
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
    }
});


window.VoterCreateModal = VoterCreateModal;