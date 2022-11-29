var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var DswdSmsModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    address: "",
                    sex: "",
                    remarks: "",
                    messageBody: "",
                    voters: []
                },
                errors: []
            },
            categories: [],
            votersList: [],
            unselected: [],
            maxChars: 160,
            messageSent: 0,
            messageTotal: 0,
            messageQueue: 0,
            sending: false,
            uploadedRecord: 0,
            totalRows: 0,
            percentage: 0,
            showTemplateModal: false,
            templates: []
        };
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-full" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white bold" closeButton>
                    <Modal.Title>DSWD SMS Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {this.state.showTemplateModal &&
                        <SmsTemplateModal
                            show={this.state.showTemplateModal}
                            onHide={this.closeTemplateModal}
                            templateContent={this.state.form.data.messageBody}
                            notify={this.props.notify}
                            onSuccess={this.loadTemplates}
                        />
                    }

                    <div className="text-right">
                        <span className="font-bold "> Total Messages : {this.state.totalRows} </span>
                        <span className="font-bold font-green-seagreen" style={{ margin: "0 10px" }}>Messages Sent : {this.state.uploadedRecord}</span>
                        <span className="font-bold font-red-sunglo" style={{ margin: "0 10px" }}>On-Queue : {this.state.totalRows - this.state.uploadedRecord}</span>
                    </div>
                    <form id="sms-form" onSubmit={this.submit}>
                        <div className="col-md-3">
                            <FormGroup controlId="formAddress" validationState={this.getValidationState('address')}>
                                <ControlLabel > Barangay : </ControlLabel>
                                <select id="form-address-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('address')}</HelpBlock>
                            </FormGroup>
                            <FormGroup controlId="formSex" validationState={this.getValidationState('sex')}>
                                <ControlLabel > Gender : </ControlLabel>
                                <select id="form-sex-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('sex')}</HelpBlock>
                            </FormGroup>
                            <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                <ControlLabel > Remarks : </ControlLabel>
                                <select id="form-remarks-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('remarks')}</HelpBlock>
                            </FormGroup>

                            <button type="button" className="btn btn-sm btn-primary" style={{ width: "100%" }} onClick={this.loadVoters}>Apply</button>
                        </div>
                        <div className="col-md-9">
                            <div style={{ margin: "5px 0 20px 0 " }}>
                                <span style={{ marginRight: "10px" }}> Voters : </span>
                                <div className="btn-group" style={{ marginTop: "-6px" }}>
                                    <button type="button" onClick={this.deselectAll} className="btn btn-xs grey-steel">Deselect All</button>
                                    <button type="button" onClick={this.selectAll} className="btn btn-xs green-turquoise">Select All</button>
                                </div>
                            </div>

                            <div className="clearfix"></div>

                            <div className="col-md-6 remove-padding">
                                <div> Available : {this.state.unselected.length}</div>
                            </div>
                            <div className="col-md-6 ">
                                <div style={{ marginLeft: '18px' }}> Selected :  {data.voters.length}</div>
                            </div>
                            <FormGroup controlId="formProfiles" validationState={this.getValidationState('students')} >
                                <select multiple ref="selectBox" className="searchable" id="voters" name="voters[]">
                                    {this.state.votersList.map(function (item) {
                                        var withoutNumber = self.isEmpty(item.contact_no);
                                        return (<option key={item.id} disabled={withoutNumber} value={item.id}>{item.firstname} {item.middlename} {item.lastname}- {item.remarks} ({item.contact_no}) </option>)
                                    })}
                                </select>
                                <div className="text-right">
                                    <HelpBlock>{this.getError('voters')}</HelpBlock>
                                </div>
                            </FormGroup>

                            <FormGroup controlId="formMessageBody" validationState={this.getValidationState('messageBody')}>
                                <div className="row" style={{ marginBottom: "10px" }}>
                                    <div className="col-md-9" >
                                        <ControlLabel >Your Message : </ControlLabel>
                                    </div>
                                    <div className="col-md-3 text-right">
                                        <select className="form-control input-sm" onChange={self.handleTemplateChange}>
                                            <option value=""> -- Select Template -- </option>
                                            {this.state.templates.map(function (item) {
                                                return (<option value={item.templateContent} key={"tempalte" + item.id} >{item.templateName}</option>);
                                            })}
                                        </select>
                                    </div>
                                </div>

                                <FormControl componentClass="textarea" disabled={this.state.sending} rows="5" name="messageBody" className="form-control input-sm" value={data.messageBody} onChange={this.setMessageBody} />
                                <small style={{ fontSize: "12px" }}>
                                    <span>
                                        {"You may use the ff. keywords({firstname},{middlename},{lastname}, {address}, {sex}, {age}, {remarks}) to add additional information on your message."}
                                    </span>
                                </small>
                                <div className="text-right">
                                    <label>Letter Count : {data.messageBody.length + " / " + self.state.maxChars} </label>
                                </div>
                                <HelpBlock>{this.getError('messageBody')}</HelpBlock>
                            </FormGroup>
                            <div>
                                <label>Sent Logs</label>
                                <div style={{ padding: "5px", fontSize: "14px", overflow: "scroll", resize: "none", width: "100%", height: "130px", backgroundColor: "#D4D4D4" }} id="message_logs">

                                </div>
                            </div>
                        </div>

                        <div className="text-right col-md-12" style={{ marginTop: "20px" }} >
                            <button type="button" style={{ marginRight: "5px" }} onClick={this.openTemplateModal} className="btn blue-madison btn-sm">Save Template</button>
                            {!this.state.sending && <button type="submit" style={{ marginRight: "5px" }} className="btn blue-madison btn-sm">Submit</button>}
                            {this.state.sending && <button type="submit" disabled={true} style={{ marginRight: "5px" }} className="btn blue-madison btn-sm"><i className="fa fa-spinner fa-pulse fa-1x fa-fw"></i> Sending Messages. Please wait...</button>}
                            <button type="button" className="btn btn-sm btn-default" onClick={this.props.onHide}>Close</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.initMultiSelect();
        this.loadUser(window.userId);
        this.loadTemplates();
    },

    handleTemplateChange: function (e) {
        var form = this.state.form;
        form.data.messageBody = e.target.value;
        this.setState({ form: form });
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.reinitSelect2);
        });
    },

    loadTemplates: function () {
        var self = this;

        self.requestTemplates = $.ajax({
            url: Routing.generate("ajax_get_sms_template"),
            type: "GET"
        }).done(function (res) {
            self.setState({ templates: res });
        });
    },

    initMultiSelect: function () {

        var self = this;

        var selectBox = this.refs.selectBox;

        $(selectBox).multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input placeholder='Enter Name' type='text' class='form-control input-sm' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input placeholder='Enter Name' type='text' class='form-control input-sm' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },

            afterSelect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setVoters($(self.refs.selectBox).val());
            },

            afterDeselect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setVoters($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });
    },

    loadVoters: function () {
        var self = this;
        var data = self.state.form.data;
        self.requestVoters = $.ajax({
            url: Routing.generate('ajax_sms_multiselect_dswd_member', data),
            type: "GET"
        }).done(function (res) {
            self.setState({ votersList: res, unselected: res });
            self.refreshSelectBox();
        });
    },

    refreshSelectBox: function () {
        $(this.refs.selectBox).multiSelect('refresh');
    },

    deselectAll: function () {
        $(this.refs.selectBox).multiSelect('deselect_all');
    },

    selectAll: function () {
        $(this.refs.selectBox).multiSelect('select_all');
    },

    setVoters: function (selected) {
        var form = this.state.form;
        var unselected = [];

        if (selected != null) {
            form.data.voters = selected;
            unselected = this.state.votersList.filter(function (item) {
                return selected.indexOf(item.voter_id) == -1;
            });
        } else {
            form.data.voters = [];
            unselected = this.state.votersList;
        }

        this.setState({ form: form, unselected: unselected });
    },

    initSelect2: function () {
        var self = this;

        $("#form-address-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_dswd_address'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.address, text: item.address };
                        })
                    };
                },
            }
        });

        $("#form-sex-select2").select2({
            casesentitive: false,
            placeholder: "Enter gender...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_dswd_sex'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.sex, text: item.sex };
                        })
                    };
                },
            }
        });

        $("#form-remarks-select2").select2({
            casesentitive: false,
            placeholder: "Enter remarks...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_dswd_remarks'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.remarks, text: item.remarks };
                        })
                    };
                },
            }
        });

        $("#form-address-select2").on("change", function () {
            self.setFormPropValue("address", $(this).val());
        });

        $("#form-sex-select2").on("change", function () {
            self.setFormPropValue("sex", $(this).val());
        });

        $("#form-remarks-select2").on("change", function () {
            self.setFormPropValue("remarks", $(this).val());
        });
    },

    setMessageBody: function (e) {
        var form = this.state.form;

        if (e.target.value.length > this.state.maxChars)
            form.data.messageBody = e.target.value.substring(0, this.state.maxChars);
        else
            form.data.messageBody = e.target.value;

        this.setState({ form: form });
    },

    openTemplateModal: function () {
        if (!this.isEmpty(this.state.form.data.messageBody))
            this.setState({ showTemplateModal: true });
        else
            alert("Opps! Cannot save an empty template...");
    },

    closeTemplateModal: function () {
        this.setState({ showTemplateModal: false });
    },

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form });
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

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        var lastResponseLength = false;

        self.requestValidation = $.ajax({
            url: Routing.generate("ajax_post_dswd_sms"),
            type: "POST",
            data: data,
            xhrFields: {
                onprogress: function (e) {
                    var progressResponse;
                    var response = e.currentTarget.response;

                    if (lastResponseLength === false) {
                        progressResponse = response;
                        lastResponseLength = response.length;
                    }
                    else {
                        progressResponse = response.substring(lastResponseLength);
                        lastResponseLength = response.length;
                    }

                    if (self.isJsonString(progressResponse)) {
                        progressResponse = JSON.parse(progressResponse);
                        var row = progressResponse.currentRow;

                        $('#message_logs').prepend("<div> " + progressResponse.currentRowIndex + ". Message to " + row.firstname + (progressResponse.status ? " was sent : " : " has failed : ") + progressResponse.message + "</div>")
                        self.setState({ uploadedRecord: progressResponse.currentRowIndex, totalRows: progressResponse.totalRows, percentage: progressResponse.percentage });
                    } else {
                        // invalid json data
                    }
                }
            }
        }).done(function () {
            self.reset();
        }).fail(function (err) {
            if (err.status == '401') {
                self.props.notify("You dont have the permission to perform this action.", "ruby");
            } else if (err.status == '400') {
                self.props.notify("Form Validation Failed.", "ruby");
            }
            self.setErrors(err.responseJSON)
        }).always(function () {
            self.setState({ sending: false });

        });

        $('#message_logs').empty();
        self.setState({ sending: true });
    },

    isJsonString: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },

    isEmpty: function (value) {
        return value == null || value == '';
    },

    reset: function () {
        var form = this.state.form;
        form.errors = [];

        this.deselectAll();
        this.setState({ form: form });
    }

});


window.DswdSmsModal = DswdSmsModal;