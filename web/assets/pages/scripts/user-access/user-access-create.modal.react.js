var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var UserAccessCreateModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                  provinceCode : "",
                  municipalityNo : "",
                  brgyNo : "",
                  userId : null
                },
                errors : []
            }
        };
    },

    render : function(){
        var self = this;

        return (
            <Modal style={{ marginTop : "10px" }} keyboard={false} enforceFocus={false} bsSize="sm" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Add Permission</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                <form id="access-form" >
                        <div class="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formProvinceCode" validationState={this.getValidationState('provinceCode')}>
                                    <ControlLabel > Province  : </ControlLabel>
                                    <select id="form-province-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('provinceCode')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-12">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > City / Municipality : </ControlLabel>
                                    <select id="form-municipality-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-12">
                                <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('brgyNo')}>
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="form-barangay-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('brgyNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div class="row">
                            <div className="text-right col-md-12" >
                                <button type="button" className="btn  btn-default" style={{marginRight : "5px"}}  onClick={this.props.onHide}>Close</button>
                                <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount : function(){
        this.initSelect2();
        this.loadUser(this.props.userId);
    },

    loadUser : function(userId){
        var self = this;

        self.requestUser = $.ajax({
            url : Routing.generate("ajax_get_user", {id : userId}),
            type : "GET"
        }).done(function(res){
            var form = self.state.form;
            form.data.provinceCode = res.province.provinceCode;
            self.setState({ form : form}, self.reinitSelect2);
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
            disabled : true,
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

        $("#form-municipality-select2").select2({
            casesentitive : false,
            placeholder : "Enter Name ...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_municipality'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        provinceCode : self.state.form.data.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.municipality_no , text: item.name};
                        })
                    };
                },
            }
        });

        $("#form-barangay-select2").select2({
            casesentitive : false,
            placeholder : "Enter name...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_barangay'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo : $("#form-municipality-select2").val(),
                        provinceCode : self.state.form.data.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.brgy_no , text: item.name};
                        })
                    };
                },
            }
        });

        $("#form-province-select2").on("change", function() {
            $("#form-municipality-select2").empty().trigger("change");
            $("#form-barangay-select2").empty().trigger("change");
            self.setFormPropValue("provinceCode",$(this).val());
        });

        $("#form-municipality-select2").on("change", function() {
            $("#form-barangay-select2").empty().trigger("change");
            self.setFormPropValue("municipalityNo",$(this).val());
        });

        $("#form-barangay-select2").on("change", function() {
            self.setFormPropValue("brgyNo",$(this).val());
        });
    },

    reinitSelect2 : function(){
        var self = this;
        var provinceCode = self.state.form.data.provinceCode;

        self.requestProvince = $.ajax({
            url : Routing.generate("ajax_get_province", {provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            console.log("setting default province");
            console.log(res);

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
      form.data.brgyNo = "";
      form.errors = [];

      $("#form-barangay-select2").empty().trigger("change");

      this.setState({form : form});
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
       
        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_user_access",{userId : self.props.userId}),
            data: data,
            type: 'POST',
        }).done(function(res){
            self.reset();
            self.props.reload();
        }).fail(function(err){
             self.props.notify("Form Validation Failed.","ruby");
             self.setErrors(err.responseJSON);
        });
    }
});


window.UserAccessCreateModal = UserAccessCreateModal;