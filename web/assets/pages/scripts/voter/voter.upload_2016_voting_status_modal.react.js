var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterUpload2016VotingStatusModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    provinceCode: "",
                    municipalityNo: "",
                    brgyNo: "",
                    electId: null
                },
                errors: []
            },
            file: null,
            isLoading: false,
            uploadedRecord: 0,
            targetName: "- - - -",
            totalRows: 0,
            percentage: 0,
            loadingText: ""
        };
    },

    getDefaultProps: function () {
        return {
            create: true
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#form-election-select2").select2({
            casesentitive: false,
            placeholder: "Enter election....",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_elections'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.elect_id, text: item.elect_name };
                        })
                    };
                },
            }
        });


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

        $("#form-election-select2").on("change", function () {
            self.setFormPropValue("electId", $(this).val());
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
            self.loadTargetName(provinceCode, municipalityNo, brgyNo);
        });
    },

    loadTargetName: function (provinceCode, municipalityNo, brgyNo) {
        var self = this;

        self.requestBarangay = $.ajax({
            url: Routing.generate("ajax_get_barangay", {
                provinceCode: provinceCode,
                municipalityNo: municipalityNo,
                brgyNo: brgyNo
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ targetName: res.name });
        });
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

    numberWithCommas: function (x) {
        x = parseFloat(x).toFixed(2);
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    isEmpty: function (value) {
        return value == null || value == '';
    },

    reset: function () {
        var form = this.state.form;
        form.data.brgyNo = "";
        form.errors = [];

        $("#form-barangay-select2").empty().trigger("change");
        $("#voter-form #excel-file").val("");

        this.setState({ details: null, form: form });
    },

    setFile: function (e) {

        this.setState({ file: $(this)[0].files[0] });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        var formData = new FormData();

        formData.append('electId', data.electId);
        formData.append('excelFile', $('#voter-form #excel-file')[0].files[0]);
        formData.append('provinceCode', data.provinceCode);
        formData.append('municipalityNo', data.municipalityNo);
        formData.append('brgyNo', data.brgyNo);

        self.setState({ isLoading: true, loadingText: "Loading " });
        var lastResponseLength = false;

        self.requestUpload = $.ajax({
            url: Routing.generate("ajax_upload_voters_voting_status"),
            data: formData,
            type: 'POST',
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false, // NEEDED, DON'T OMIT THIS
            xhrFields: {
                // Getting on progress streaming response
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
                        self.setState({ uploadedRecord: progressResponse.currentRow, totalRows: progressResponse.totalRows, percentage: progressResponse.percentage });
                    }
                }
            }
            // ... Other options like success and etc
        }).done(function (res) {
            self.setState({ loadingText: "Re-computing " });
            $.ajax({
                url: Routing.generate('ajax_update_voter_summary', {
                    provinceCode: data.provinceCode,
                    municipalityNo: data.municipalityNo,
                    brgyNo: data.brgyNo
                }),
                type: "GET"
            }).done(function (res) {
                self.props.notify("Record has been uploaded.", "ruby");
                self.reset();
            }).always(function () {
                self.setState({ isLoading: false });
            });
        }).fail(function (err) {
            if (err.status == '401') {
                self.props.notify("You dont have the permission to perform this action.", "ruby");
            } else if (err.status == '400') {
                self.props.notify("Form Validation Failed.", "ruby");
                self.setErrors(err.responseJSON);
            } else {
                self.props.notify("Record has been uploaded.", "ruby");
                self.reset();
            }

            self.setState({ isLoading: false });
        });
    },

    isJsonString: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },

    render: function () {
        var self = this;
        
        return (
            <Modal keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Update Voter Record</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-form" enctype="multipart/form-data">
                        <div class="row">

                            <div className="col-md-6">
                                <FormGroup controlId="formElectId" validationState={this.getValidationState('electId')}>
                                    <ControlLabel > Election : </ControlLabel>
                                    <select id="form-election-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('electId')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-6">
                                <FormGroup controlId="formProvinceCode" validationState={this.getValidationState('provinceCode')}>
                                    <ControlLabel > Province : </ControlLabel>
                                    <select id="form-province-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('provinceCode')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="clearfix" />

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

                            <div className="clearfix" />

                            <div className="col-md-6">
                                <FormGroup style={{ marginTop: "30px" }} controlId="formExcelFile" validationState={this.getValidationState('excelFile')}>
                                    <div>
                                        <input id="excel-file" onChange={this.setFile} type="file" accept=".xlxs, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" name="excel-file" />
                                        <HelpBlock>{this.getError('excel-file')}</HelpBlock>
                                    </div>
                                    <HelpBlock>{this.getError('excelFile')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="clearfix"></div>

                        <div class="row" style={{ marginTop: "20px" }}>
                            <div className="col-md-6 text-center" >
                                <div className="bold">{this.state.targetName}</div>
                                <div className="col-md-6">
                                    <div className="font-green-seagreen text-center bold" style={{ fontSize: "50px" }}>{this.state.totalRows}</div>
                                    <div style={{ textAlign: "center", fontWeight: "bold" }}>Total</div>
                                </div>
                                <div className="col-md-6">
                                    <div className="font-red-sunglo text-center bold" style={{ fontSize: "50px" }}>{this.state.uploadedRecord}</div>
                                    <div style={{ textAlign: "center", fontWeight: "bold" }}>Uploaded</div>
                                </div>
                            </div>
                            <div className="text-right col-md-6" style={{ marginTop: "70px" }}>
                                <button type="button" className="btn  btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                                {!this.state.isLoading && <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>}
                                {this.state.isLoading && <button type="button" disabled={true} className="btn red-sunglo"> {this.state.loadingText} {this.state.percentage} % <i className="fa fa-spinner fa-pulse fa-1x fa-fw"></i></button>}
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.VoterUpload2016VotingStatusModal = VoterUpload2016VotingStatusModal;