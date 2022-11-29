var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectEditModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                  provinceCode : "",
                  proDesc : "",
                  proDesc : ""
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
                    <Modal.Title>Create Project</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="project-form" >
                        <FormGroup controlId="formProvinceCode" validationState={this.getValidationState('provinceCode')}>
                            <ControlLabel > Province  : </ControlLabel>
                            <select id="form-province-select2" className="form-control input-sm">
                            </select>
                            <HelpBlock>{this.getError('provinceCode')}</HelpBlock>
                        </FormGroup>
                        <FormGroup controlId="formProName" validationState={this.getValidationState('proName')}>
                            <ControlLabel> Name : </ControlLabel>
                            <FormControl bsClass="form-control input-sm" name="proName" value={this.state.form.data.proName} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('proName')}</HelpBlock>
                        </FormGroup>

                        <FormGroup controlId="formproDesc" validationState={this.getValidationState('proDesc')}>
                            <ControlLabel> Description : </ControlLabel>
                            <FormControl componentClass="textarea" rows="6" bsClass="form-control input-sm" name="proDesc" value={this.state.form.data.proDesc} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('proDesc')}</HelpBlock>
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
        this.initSelect2();
        this.loadProject(this.props.proId);
    },

    loadProject : function(proId){
        var self = this;

        self.requestProject = $.ajax({
            url : Routing.generate("ajax_get_project",{proId : proId}),
            type : "GET"
        }).done(function(res){
            var form = self.state.form;
            form.data.proName = res.proName;
            form.data.proDesc = res.proDesc;
            form.data.provinceCode = res.provinceCode;

            self.setState({form : form}, self.reinitSelect2);
        });
    },

    initSelect2 : function(){
        var self = this;

        $("#form-province-select2").select2({
            casesentitive : false,
            placeholder : "Enter Province ...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_province'),
                data :  function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.province_code , text: item.name};
                        })
                    };
                },
            }
        });

        $("#form-province-select2").on("change", function() {
            self.setFormPropValue("provinceCode",$(this).val());
        });
    },

    reinitSelect2 : function(){
        var self = this;
        var provinceCode = self.state.form.data.provinceCode;

        self.requestProvince = $.ajax({
            url : Routing.generate("ajax_get_province", {provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            $("#form-province-select2").empty()
            .append($("<option/>")
                .val(res.province_code)
                .text(res.name))
            .trigger("change");
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
       
        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_project", { proId : this.props.proId }),
            data: data,
            type: 'PATCH',
        }).done(function(res){
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function(err){
             self.props.notify("Form Validation Failed.","ruby");
             self.setErrors(err.responseJSON);
        });
    }
});


window.ProjectEditModal = ProjectEditModal;