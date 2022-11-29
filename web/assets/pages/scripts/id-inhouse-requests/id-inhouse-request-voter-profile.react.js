var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var IdInhouseRequestVoterProfile = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null,
                    proIdCode: "",
                    cellphoneNo : ""
                },
                errors: []
            },
            voter : null,
            showCropModal : false
        };
    },
    
    componentDidMount : function(){
        console.log("voter profile did load");
        this.loadVoter(this.props.proId,this.props.voterId);
        this.initSelect2();
    },

    render: function () {
        var self = this;
        var photoUrl = window.imgUrl + this.props.proId + '_' + this.state.form.data.proIdCode + "?" + new Date().getTime();
        var dataUrl = Routing.generate('ajax_upload_project_voter_photo', { proId: this.props.proId, voterId: this.state.form.data.voterId }) + "?" + new Date().getTime();

        return (
            <form id="id_inhouse_request_voter_profile" onSubmit={this.submit}>
                
                <div className="col-md-4">
                    <div onClick={this.openCropModal}>
                        <img src={photoUrl} className="img-responsive" alt="" />
                    </div>

                    {
                        this.state.showCropModal && 
                        (
                            <VoterCropModal 
                                proId={this.props.proId}
                                voterId={this.state.voter.voterId}
                                proIdCode={this.state.voter.proIdCode}
                                show={this.state.showCropModal}
                                onHide={this.closeCropModal}
                                onSuccess={this.refresh}
                            />
                        )
                    }
                    
                    <div className="profile-userbuttons" style={{ marginTop: "10px" }}>
                        <span className="btn col-md-12 green btn-sm fileinput-button ">
                            <span> Change Photo</span>
                            <input id="voter-photo-upload" type="file" name="files[]" data-url={dataUrl} multiple={false} />
                        </span>
                    </div>
                </div>

                <div className="col-md-8" style={{ paddingLeft: "0" }}>
                    <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                        <ControlLabel > CellphoneNo : </ControlLabel>
                        <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                        <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                    </FormGroup>

                    <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                        <ControlLabel >Position : </ControlLabel>
                        <select id="voter-group-select2" className="form-control input-sm">
                            <option value=""> </option>
                        </select>
                        <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                    </FormGroup>

                    <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                        <ControlLabel > Remarks : </ControlLabel>
                        <textarea rows="5" value={this.state.form.data.remarks} className="input-sm form-control" onChange={this.setFormProp} name="remarks">
                        </textarea>
                        <HelpBlock>{this.getError('remarks')}</HelpBlock>
                    </FormGroup>
                    
                    <HelpBlock>{this.getError('voterId')}</HelpBlock>

                    <div className="text-right">
                        <button className="btn btn-default btn-md " style={{ marginRight : "15px", width : "150px" }} type="button"  onClick={this.props.reset} > Reset </button>
                        <button className="btn btn-primary btn-md " style={{ width : "250px" }} disabled={this.isEmpty(this.state.form.data.voterId)} type="submit"> Submit </button>
                    </div>
                </div>
            </form>         
        );
    },

    initSelect2: function () {
        var self = this;

        $("#id_inhouse_request_voter_profile #voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

        $("#id_inhouse_request_voter_profile #voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
    },

    loadVoter: function (proId, voterId) {
        var self = this;

        console.log("loading voter");
        
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, voterId: voterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            console.log("voter has been loaded");

            form.data.municipalityNo = res.municipalityNo;
            form.data.brgyNo = res.brgyNo;
            form.data.voterId = res.voterId;
            form.data.proVoterId = res.proVoterId;
            form.data.proIdCode = res.proIdCode;
            form.data.cellphoneNo = self.isEmpty(res.cellphoneNo) ? "" : res.cellphoneNo;
            form.data.precinctNo = res.precinctNo;
            form.data.assignedPrecinct = self.isEmpty(res.assignedPrecinct) ? res.precinctNo : res.assignedPrecinct;
            form.data.voterGroup = self.isEmpty(res.voterGroup) ? "KCL3" : res.voterGroup;
            form.data.remarks = self.isEmpty(res.remarks) ? "" : res.remarks;

            $("#voter-group-select2").empty()
                .append($("<option/>")
                    .val(form.data.voterGroup)
                    .text(form.data.voterGroup))
                .trigger("change");

            self.setState({ form: form , voter : res }, self.initUploader);
        });
    },

    initUploader: function () {
        var self = this;

        $('#inhouse_id_request_item_form #voter-photo-upload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo(document.body);
                });

                self.refresh();
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
            }
        });
    },
    
    openCropModal : function(){
        this.setState({ showCropModal : true });
    },

    closeCropModal : function(){
        this.setState({ showCropModal : false });
    },

    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
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

    isEmpty: function (value) {
        return value == null || value == '';
    },

    refresh : function(){
        this.loadVoter(this.props.proId, this.state.voter.voterId);
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.hdrId = self.props.hdrId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_id_request_detail"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.props.reset();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});

window.IdInhouseRequestVoterProfile = IdInhouseRequestVoterProfile;