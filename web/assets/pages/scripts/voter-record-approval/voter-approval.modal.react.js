var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterApprovalModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    voters : []
                },
                errors : []
            },
            votersList : [],
            unselected : [],
            maxChars : 160
        };
    },

    getDefaultProps : function(){
        return {
            create : true
        }
    },

    componentDidMount : function(){
        this.initSelect2();
        this.initMultiSelect();
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
                url : Routing.generate('ajax_select2_municipality'),
                data :  function (params) {
                    return {
                        searchText: params.term
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
                        municipalityNo : $("#form-municipality-select2").val()
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

        $("#form-municipality-select2").on("change", function() {
            self.setFormPropValue("municipalityNo",$(this).val());
        });

        $("#form-barangay-select2").on("change", function() {
            self.setFormPropValue("brgyNo",$(this).val());
            self.loadVoters();
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

        console.log("setting errors");

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
      $("#voter-form #excel-file").val("");

      this.setState({details : null , form : form});
    },

    setFile : function(e){
    
        this.setState({file : $(this)[0].files[0]});
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        self.requestPostApproval = $.ajax({
            url : Routing.generate("ajax_post_voter_approval"),
            type : "POST",
            data : data
        }).done(function(res){
            self.props.onHide();
        }).fail(function(err){
            if(err.status == '401'){
                self.props.notify("You dont have the permission to update this record.","ruby");
            }else{
                self.props.notify("Form Validation Failed.","ruby");
            }
            self.setErrors(err.responseJSON);
        });
    },

    initMultiSelect : function(){

        var self = this;

        var selectBox = this.refs.selectBox;

        $(selectBox).multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input placeholder='Enter Name' type='text' class='form-control input-sm' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input placeholder='Enter Name' type='text' class='form-control input-sm' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function(ms){
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function(e){
                        if (e.which == 40){
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },

            afterSelect: function(values){
                this.qs1.cache();
                this.qs2.cache();
                self.setVoters($(self.refs.selectBox).val());
            },

            afterDeselect: function(values){
                this.qs1.cache();
                this.qs2.cache();
                self.setVoters($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });
    },

    loadVoters : function(){
      var self = this;
      var data = self.state.form.data;
      var municipalityNo =  $("#form-municipality-select2").val();
      var brgyNo =  $("#form-barangay-select2").val();

      if(!self.isEmpty(municipalityNo) && !self.isEmpty(brgyNo)){
          self.requestVoters = $.ajax({
              url : Routing.generate('ajax_multiselect_voter',data),
              type : "GET"
          }).done(function(res){
              var form = self.state.form;
              form.data.voters = [];

              self.setState({votersList : res, unselected : res, form : form });
              self.refreshSelectBox();
          });
      }
    },

    refreshSelectBox : function(){
        $(this.refs.selectBox).multiSelect('refresh');
    },

    deselectAll : function(){
        $(this.refs.selectBox).multiSelect('deselect_all');
    },

    selectAll : function(){
        $(this.refs.selectBox).multiSelect('select_all');
    },

    setVoters : function(selected){
        var form = this.state.form;
        var unselected = [];

        if(selected != null){
            form.data.voters = selected;
            unselected = this.state.votersList.filter(function(item){
                return selected.indexOf(item.voter_id) == -1;
            });
        }else{
            form.data.voters = [];
            unselected = this.state.votersList;
        }

        this.setState({form : form,unselected : unselected});
    },

    render : function(){
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal  keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Approval Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="form-municipality-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('brgyNo')}>
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="form-barangay-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('brgyNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                            
                            <div className="col-md-12 text-right">
                                <div style={{margin:"5px 0 20px 0 "}}>
                                    <span style={{ marginRight: "10px" }}> Voters : </span>
                                    <div className="btn-group" style={{ marginTop:"-6px" }}>
                                        <button type="button" onClick={this.deselectAll} className="btn btn-xs grey-steel">Deselect All</button>
                                        <button type="button" onClick={this.selectAll} className="btn btn-xs green-turquoise">Select All</button>
                                    </div>
                                </div>
                            </div>

                            <div className="col-md-12">
                                <div className="col-md-6" style={{marginLeft:"0",paddingLeft : "0"}}>
                                    <div> Available : { this.state.unselected.length }</div>
                                </div>
                                <div className="col-md-6 ">
                                    <div style={{marginLeft : '30px'}}> Selected :  { data.voters.length }</div>
                                </div>
                                <FormGroup controlId="formVoters" validationState={this.getValidationState('totalRecords')} >
                                    <select multiple ref="selectBox" className="searchable" id="voters" name="voters[]">
                                        {this.state.votersList.map(function(item){
                                            return (<option key={item.hist_id}  value={item.hist_id}>{item.voter_name}</option>)
                                        })}
                                    </select>
                                    <div className="text-right">
                                        <HelpBlock>{this.getError('totalRecords')}</HelpBlock>
                                    </div>
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
    },

    

});


window.VoterApprovalModal = VoterApprovalModal;