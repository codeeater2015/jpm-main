var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectEventAttendeeBatchModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    provinceCode: "",
                    electId : "",
                    municipalityNo: "",
                    brgyNo: "",
                    voterGroup: "",
                    project: 2,
                    withId: 0,
                    withNoId: 1,
                    is1 : 1,
                    is2 : 0,
                    is3 : 0,
                    is4 : 0,
                    is5 : 0,
                    is6 : 0,
                    is7 : 0,
                    is8 : 0,
                    isCh : 0,
                    isKcl : 0,
                    isKcl0 : 0,
                    isKcl1 : 0,
                    isKcl2 : 0,
                    isKcl3 : 0,
                    isKfc : 0,
                    isDao : 0,
                    sop : 0,
                    notSop : 0,
                    voters: []
                },
                errors: []
            },
            categories: [],
            votersList: [],
            unselected: []
        };
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-full" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white bold" closeButton>
                    <Modal.Title>Add Attendees</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="sms-form" onSubmit={this.submit}>
                        <div className="col-md-3">
                            <FormGroup controlId="formProvinceCode" >
                                <ControlLabel > Province : </ControlLabel>
                                <select id="form-province-select2" className="form-control input-sm">
                                </select>
                            </FormGroup>
                            <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                <ControlLabel > Municipality : </ControlLabel>
                                <select id="form-municipality-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                            </FormGroup>
                            <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('brgyNo')}>
                                <ControlLabel > Barangay : </ControlLabel>
                                <select id="form-barangay-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('brgyNo')}</HelpBlock>
                            </FormGroup>
                            {
                                /*
                                <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                    <ControlLabel > Position : </ControlLabel>
                                    <select id="form-voter-group-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                </FormGroup>
                                */
                            }
                            <div className="col-md-3" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="withId" checked={data.withId} onChange={this.setFormCheckProp} />
                                        ID
                                        <span></span>
                                    </label>

                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="withNoId" checked={data.withNoId} onChange={this.setFormCheckProp} />
                                        No ID
                                        <span></span>
                                    </label>

                                </div>
                            </div>

                            <div className="col-md-3" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="notSop" checked={data.notSop} onChange={this.setFormCheckProp} />
                                         !SOP
                                        <span></span>
                                    </label>

                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="sop" checked={data.sop} onChange={this.setFormCheckProp} />
                                        SOP
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isCh" checked={data.isCh} onChange={this.setFormCheckProp} />
                                        CH
                                        <span></span>
                                    </label>

                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKcl" checked={data.isKcl} onChange={this.setFormCheckProp} />
                                        KCL
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKcl0" checked={data.isKcl0} onChange={this.setFormCheckProp} />
                                        KCL0
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div className="col-md-4" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKcl1" checked={data.isKcl1} onChange={this.setFormCheckProp} />
                                        KCL1
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKcl2" checked={data.isKcl2} onChange={this.setFormCheckProp} />
                                        KCL2
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKcl3" checked={data.isKcl3} onChange={this.setFormCheckProp} />
                                        KCL3
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div className="col-md-4" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKjr" checked={data.isKjr} onChange={this.setFormCheckProp} />
                                        KJR
                                        <span></span>
                                    </label>

                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isKfc" checked={data.isKfc} onChange={this.setFormCheckProp} />
                                        KFC
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="isDao" checked={data.isDao} onChange={this.setFormCheckProp} />
                                        DAO
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div className="clearfix" />

                            <div className="col-md-2" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is1" checked={data.is1} onChange={this.setFormCheckProp} />
                                        1
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is2" checked={data.is2} onChange={this.setFormCheckProp} />
                                        2
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                            <div className="col-md-2" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is3" checked={data.is3} onChange={this.setFormCheckProp} />
                                        3
                                            <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is4" checked={data.is4} onChange={this.setFormCheckProp} />
                                        4
                                            <span></span>
                                    </label>
                                </div>
                            </div>
                            <div className="col-md-2" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is5" checked={data.is5} onChange={this.setFormCheckProp} />
                                        5
                                        <span></span>
                                    </label>

                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is6" checked={data.is5} onChange={this.setFormCheckProp} />
                                        6
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div className="col-md-2" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is7" checked={data.is7} onChange={this.setFormCheckProp} />
                                        7
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is8" checked={data.is8} onChange={this.setFormCheckProp} />
                                        8
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div className="col-md-2" style={{ padding: 0 }}>
                                <div className="mt-checkbox-list">
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is9" checked={data.is9} onChange={this.setFormCheckProp} />
                                        9
                                        <span></span>
                                    </label>
                                    <label className="mt-checkbox">
                                        <input type="checkbox" name="is10" checked={data.is10} onChange={this.setFormCheckProp} />
                                        10
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <button type="button" className="btn btn-sm btn-primary" style={{ width: "100%" }} onClick={this.loadVoters}>Apply</button>
                        </div>
                        <div className="col-md-9">
                            <div style={{ margin: "5px 0 20px 0 " }}>
                                <span style={{ marginRight: "10px" }}> Members : </span>
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
                                        var withoutNumber = self.isEmpty(item.cellphone);
                                        return (<option key={item.pro_voter_id} value={item.pro_voter_id}>{item.voter_name} - {item.voter_group} ({item.cellphone}) </option>)
                                    })}
                                </select>
                                <div className="text-right">
                                    <HelpBlock>{this.getError('voters')}</HelpBlock>
                                </div>
                            </FormGroup>
                        </div>

                        <div className="text-right col-md-12">
                            <button type="submit" style={{ marginRight: "5px" }} className="btn blue-madison btn-sm">Submit</button>
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
        this.initFormData();
        this.loadUser(window.userId);
    },

    initFormData : function(){
        var form = this.state.form;
        form.data.electId = this.props.electId;
        this.setState({form : form});
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
            url: Routing.generate('ajax_project_event_attendee_multiselect', data),
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

        $("#form-province-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_province'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.province_code, text: item.name };
                        })
                    };
                },
            }
        });

        $("#form-municipality-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $('#form-province-select2').val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#form-barangay-select2").select2({
            casesentitive: false,
            placeholder: "Enter name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $('#form-province-select2').val(),
                        municipalityNo: $("#form-municipality-select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#form-voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Category",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term
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

        $("#form-province-select2").on("change", function () {
            $("#form-municipality-select2").empty().trigger('change');
            $("#form-barangay-select2").empty().trigger('change');
            self.setFormPropValue("provinceCode", $(this).val());
        });

        $("#form-municipality-select2").on("change", function () {
            $("#form-barangay-select2").empty().trigger('change');
            self.setFormPropValue("municipalityNo", $(this).val());
        });

        $("#form-barangay-select2").on("change", function () {
            var provinceCode = $("#form-province-select2").val();
            var municipalityNo = $("#form-municipality-select2").val();
            var brgyNo = $("#form-barangay-select2").val();

            self.setFormPropValue("brgyNo", $(this).val());
        });

        $("#form-voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
    },

    reinitSelect2: function () {
        var self = this;
        var provinceCode = self.state.user.province.provinceCode;

        self.requestProvince = $.ajax({
            url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
            type: "GET"
        }).done(function (res) {
            console.log("province loaded.");
            $("#form-province-select2").empty()
                .append($("<option/>")
                    .val(res.province_code)
                    .text(res.name))
                .trigger("change");
        });
    },

    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;

        this.setState({ form: form });
    },

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
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
        data.eventId = self.props.eventId;
        data.proId = self.props.proId;

        self.requestValidation = $.ajax({
            url: Routing.generate("ajax_post_project_event_batch_attendee"),
            type: "POST",
            data: data
        }).done(function (data) {
            self.props.notify("Attendees has been added.", "ruby");
            self.props.onSuccess();
            self.reset();
        }).fail(function (err) {
            if (err.status == '401') {
                self.props.notify("You dont have the permission to perform this action.", "ruby");
            } else if (err.status == '400') {
                self.props.notify("Form Validation Failed.", "ruby");
            }
            self.setErrors(err.responseJSON)
        }).always(function () {

        });
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


window.ProjectEventAttendeeBatchModal = ProjectEventAttendeeBatchModal;