var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var LocationAssignmentCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    provinceCode: 53,
                    municipalityNo: "",
                    brgyNo: "",
                    userId: null,
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
            <Modal style={{ marginTop: "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Add Location</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="access-form" >
                        <div class="row">
                            <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                <ControlLabel > City / Municipality : </ControlLabel>
                                <select id="form-municipality-select2" className="form-control input-sm">
                                </select>
                                <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                            </FormGroup>
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

                        <div class="row">
                            <div className="text-right col-md-12" >
                                <button type="button" className="btn  btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                                <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadBarangays();
    },

    initSelect2: function () {
        var self = this;

        $("#form-municipality-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name ...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.state.form.data.provinceCode
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

        $("#form-municipality-select2").on("change", function () {
            var form = self.state.form;
            form.data.municipalityNo = $(this).val();

            self.setState({ form: form }, self.loadBarangays);
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
        form.data.brgyNo = "";
        form.errors = [];

        $("#form-barangay-select2").empty().trigger("change");

        this.setState({ form: form });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.barangays = $(self.refs.selectBox).val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_location_assignment", { proIdCode: self.props.proIdCode }),
            data: data,
            type: 'POST',
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
        }).fail(function (err) {
            self.props.notify("Form Validation Failed.", "ruby");
            self.setErrors(err.responseJSON);
        });
    }
});


window.LocationAssignmentCreateModal = LocationAssignmentCreateModal;