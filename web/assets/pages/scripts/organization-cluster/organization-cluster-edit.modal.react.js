var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var OrganizationClusterEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    voterName: "",
                    municipalityNo: "",
                    clusterNo: 1,
                    barangays: []
                },
                errors: []
            },
            barangayList: [],
            unselected: []
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} dialogClassName="modal-custom-85" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit Cluster Head</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="cluster-form" >
                        <div className="row">
                            <div className="col-md-6">

                                <div className="row">
                                    <div className="col-md-12">
                                        <FormGroup controlId="formVoterId" validationState={this.getValidationState('voterName')}>
                                            <ControlLabel > Recruitment Leader : </ControlLabel>
                                            <select id="lgc-select2" className="form-control input-sm">
                                            </select>
                                            <HelpBlock>{this.getError('voterName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-6    ">
                                        <div className="form-group">
                                            <label className="control-label">City/Municipality</label>
                                            <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <FormGroup controlId="formClusterNo" validationState={this.getValidationState('clusterNo')}>
                                            <ControlLabel> Cluster No : </ControlLabel>
                                            <FormControl type="number" bsClass="form-control input-sm" name="clusterNo" value={this.state.form.data.clusterNo} onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('clusterNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-12">
                                        <FormGroup controlId="formBarangays" validationState={this.getValidationState('barangays')} >
                                            <select multiple ref="selectBox" className="searchable" id="barangays" name="barangays[]">
                                                {this.state.barangayList.map(function (item) {
                                                    return (<option key={item.brgy_no} value={item.brgy_no}>{item.name}</option>)
                                                })}
                                            </select>
                                            <div className="text-right">
                                                <HelpBlock>{this.getError('barangays')}</HelpBlock>
                                            </div>
                                        </FormGroup>
                                    </div>
                                </div>

                            </div>

                            <div className="col-md-6">
                                <div className="row">
                                    <div className="col-md-12">
                                        {this.state.form.data.proIdCode != null ? <LocationAssignmentDatatable
                                            proIdCode={this.state.form.data.proIdCode}
                                            ref="locationDatatable"
                                        /> : ""}
                                    </div>
                                </div>
                            </div>

                        </div>





                        <div className="text-right" >
                            <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            <button type="button" className="btn  btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.initMultiSelect();
    },

    initSelect2: function () {
        var self = this;

        $("#cluster-form #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.state.provinceCode
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

        $("#cluster-form #lgc-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_cluster_lgc'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: self.props.electId,
                        proId: self.props.proId,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasId = parseInt(item.has_id) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - ID : ' + hasId;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#cluster-form #municipality_select2").on("change", function () {
            self.setMunicipality($(this).val());
        });


        $("#cluster-form #lgc-select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });


        $("#cluster-form #lgc-select2").empty()
            .append($("<option/>")
                .val(self.props.proVoterId)
                .text(self.props.voterName))
            .trigger("change");
    },


    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            form.data.proVoterId = res.proVoterId;
            form.data.voterGroup = res.voterGroup;
            form.data.position = res.position;
            form.data.voteName = res.voterName;
            form.data.proIdCode = res.proIdCode;
            form.data.clusterNo = res.clusterNo;

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.cellphone = '';
        form.data.position = '';
        form.data.voterGroup = '';
        form.data.remarks = '';

        self.setState({ form: form })
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
                self.setBarangays($(self.refs.selectBox).val());
            },

            afterDeselect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setBarangays($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });
    },

    loadBarangays: function () {
        var self = this;
        var data = self.state.form.data;

        console.log("data");
        console.log(data);

        self.requestBarangays = $.ajax({
            url: Routing.generate('ajax_location_assignment_multiselect_municipality', data),
            type: "GET"
        }).done(function (res) {
            self.setState({ barangayList: res, unselected: res });
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

    setBarangays: function (selected) {
        var form = this.state.form;
        var unselected = [];

        if (selected != null) {
            form.data.barangays = selected;
            unselected = this.state.barangayList.filter(function (item) {
                return selected.indexOf(item.brgy_no) == -1;
            });
        } else {
            form.data.barangays = [];
            unselected = this.state.barangayList;
        }

        this.setState({ form: form, unselected: unselected });
    },


    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setMunicipality(municipalityNo) {
        var form = this.state.form;
        form.data.municipalityNo = municipalityNo;

        this.setState({ form: form }, this.loadBarangays);
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

    reset: function () {
        var form = this.state.form;
        form.errors = [];

        this.setState({ form: form });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.barangays = $(self.refs.selectBox).val();

        console.log("form data");
        console.log(data);

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_organization_cluster", { proIdCode: data.proIdCode }),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.OrganizationClusterEditModal = OrganizationClusterEditModal;