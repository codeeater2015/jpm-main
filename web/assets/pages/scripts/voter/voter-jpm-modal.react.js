var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterJpmModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                  municipality_name : "",
                  barangay_name : ""
                },
                errors : []
            },
            loadingText : ""
        };
    },

    getDefaultProps : function(){
        return {
            create : true
        }
    },

    componentDidMount : function(){
        this.initSelect2();
    },

    initSelect2 : function(){
        var self = this;

        $("#form-municipality-select2").select2({
            casesentitive : false,
            placeholder : "Enter Name...",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('ajax_select2_jpm_municipality'),
                data :  function (params) {
                    return {
                        searchText : params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.municipality_name , text: item.municipality_name};
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
                url : Routing.generate('ajax_select2_jpm_barangay'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        municipalityName : $("#form-municipality-select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.barangay_name , text: item.barangay_name};
                        })
                    };
                },
            }
        });

        $("#form-municipality-select2").on("change", function() {
            $("#form-barangay-select2").empty().trigger('change');
            self.setFormPropValue("municipality_name", $(this).val());
        });

        $("#form-barangay-select2").on("change", function() {
            self.setFormPropValue("barangay_name", $(this).val());
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
      form.data.brgyNo = "";
      form.errors = [];

      $("#form-barangay-select2").empty().trigger("change");
      this.setState({details : null , form : form});
    },

    submit : function(e){
        e.preventDefault();
        var data = this.state.form.data;
        var url = 'http://' + window.hostIp + ':8100/voter-report/web/voter/jpm/index.php?municipality_name='+ data.municipality_name + '&barangay_name=' + data.barangay_name;

        this.popupCenter(url, 'JPM LIST', 900, 600);
    },

    popupCenter: function (url, title, w, h) {
        // Fixes dual-screen position                         Most browsers      Firefox  
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
        var width = 0;
        var height = 0;

        width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow  
        if (window.focus) {
            newWindow.focus();
        }
    },
    
    render : function(){
        var self = this;
        return (
            <Modal  keyboard={false} enforceFocus={false}  backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Voter Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-form" enctype="multipart/form-data">
                        <div class="row">
                            <div className="col-md-6">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="form-municipality-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-6">
                                <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('brgyNo')}>
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="form-barangay-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('brgyNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="clearfix"></div>

                        <div class="row" style={{marginTop:"20px"}}>
                            <div className="text-right col-md-12">
                                <button type="button" className="btn  btn-default" style={{marginRight : "5px"}}  onClick={this.props.onHide}>Close</button>
                                <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.VoterJpmModal = VoterJpmModal;