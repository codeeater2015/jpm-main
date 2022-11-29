var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FinancialAssistanceEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    trnNo: "",
                    municipalityNo: "",
                    municipalityName: "",
                    barangayNo: "",
                    barangayName: "",
                    applicantName: "",
                    applicantProVoterId: "",
                    beneficiaryName: "",
                    jpmIdNo: "",
                    contactNo: "",
                    typeOfAsst: "",
                    trnDate: "",
                    endorsedBy: "",
                    projectedAmt: 0,
                    grantedAmt: 0,
                    receivedBy: "",
                    releaseDate: "",
                    releasingOffice: "",
                    hospitalName: "",
                    personnel: "",
                    remarks: "",
                    status: "",

                    reqType: 0,
                    hasReqLetter: 0,
                    hasBrgyClearance: 0,
                    hasPatientId: 0,
                    hasMedCert: 0,
                    hasMedAbst: 0,
                    hasPromisoryNote: 0,
                    hasBillStatement: 0,
                    hasPriceQuot: 0,
                    hasReqOfPhysician: 0,
                    hasReseta: 0,
                    hasSocialCastReport: 0,
                    hasPoliceReport: 0,
                    hasDeathCert: 0,
                    isDswdMedical: 0,
                    isDswdOpd: 0,
                    isDohMaipMedical: 0,
                    isDohMaipOpd: 0
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit Financial Assistance Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="fa-form" >

                        <div className="row">
                            <div className="col-md-12">
                                <div><h3><strong>Request Information : </strong></h3></div>
                                <div className="row">
                                    <div className="col-md-4">
                                        <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                            <ControlLabel> Municipality : </ControlLabel>
                                            <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-4">
                                        <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('barangayNo')}>
                                            <ControlLabel> Barangay : </ControlLabel>
                                            <select id="barangay_select2" className="form-control form-filter input-sm" name="barangayNo">
                                            </select>
                                            <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">

                                    <div className="col-md-8">
                                        <FormGroup controlId="formVoterName" validationState={this.getValidationState('voterName')}>
                                            <ControlLabel > Applicant Name : </ControlLabel>
                                            <select id="voter-select2" className="form-control input-sm">
                                            </select>
                                            <HelpBlock>{this.getError('voterName')}</HelpBlock>
                                        </FormGroup>
                                    </div>


                                    {/* <div className="col-md-4">
                                        <FormGroup controlId="formApplicantName" validationState={this.getValidationState('applicantName')}>
                                            <ControlLabel> Applicant Name : </ControlLabel>
                                            <select id="applicant_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('applicantName')}</HelpBlock>
                                        </FormGroup>
                                    </div> */}

                                    <div className="col-md-3">
                                        <FormGroup controlId="formJpmIdNo" validationState={this.getValidationState('jpmIdNo')}>
                                            <ControlLabel> JPM ID No. : </ControlLabel>
                                            <FormControl bsClass="form-control input-sm" name="jpmIdNo" value={this.state.form.data.jpmIdNo} onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('jpmIdNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                    <div className="col-md-4">
                                        <FormGroup controlId="formBeneficiaryName" validationState={this.getValidationState('beneficiaryName')}>
                                            <ControlLabel> Beneficiary Name : </ControlLabel>
                                            <select id="beneficiary_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('beneficiaryName')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                </div>

                                <div className="row">
                                    <div className="col-md-3">
                                        <FormGroup controlId="formContactNo" validationState={this.getValidationState('contactNo')}>
                                            <ControlLabel> Contact No : </ControlLabel>
                                            <FormControl bsClass="form-control input-sm" name="contactNo" value={this.state.form.data.contactNo} onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('contactNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-7">
                                        <FormGroup controlId="formHospitalName" validationState={this.getValidationState('hospitalName')}>
                                            <ControlLabel> Hospital : </ControlLabel>
                                            <select id="hospital_select2" className="form-control form-filter input-sm" name="hospitalName">
                                            </select>
                                            <HelpBlock>{this.getError('hospitalName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-3">
                                        <FormGroup controlId="formEventName" validationState={this.getValidationState('typeOfAsst')}>
                                            <ControlLabel> Type of Assistance : </ControlLabel>
                                            <select id="type_of_assistance_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('typeOfAsst')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3">
                                        <FormGroup controlId="formEventName" validationState={this.getValidationState('trnDate')}>
                                            <ControlLabel> Date : </ControlLabel>
                                            <input type="date" value={this.state.form.data.trnDate} className="input-sm form-control" onChange={this.setFormProp} name="trnDate" />
                                            <HelpBlock>{this.getError('trnDate')}</HelpBlock>
                                        </FormGroup>
                                    </div>

                                    <div className="col-md-4">
                                        <FormGroup controlId="formEventName" validationState={this.getValidationState('endorsedBy')}>
                                            <ControlLabel> Endorsed By : </ControlLabel>
                                            <select id="endorser_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('endorsedBy')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div><h3><strong>Medical Assistance Requirements <i><small>(checklist)</small></i></strong></h3></div>
                                <div className="row">
                                    <div className="col-md-6">
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasReqLetter" checked={data.hasReqLetter == 1} onChange={this.setFormCheckProp} />
                                            Request Letter
                                            <span></span>
                                        </label>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="col-md-3">DSWD</div>
                                        <div className="col-md-3">
                                            <label className="mt-checkbox">
                                                <input type="checkbox" name="isDswdMedical" checked={data.isDswdMedical == 1} onChange={this.setFormCheckProp} />
                                                MEDICAL
                                                <span></span>
                                            </label>
                                        </div>
                                        <div className="col-md-3">
                                            <label className="mt-checkbox">
                                                <input type="checkbox" name="isDswdOpd" checked={data.isDswdOpd == 1} onChange={this.setFormCheckProp} />
                                                OPD
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div className="col-md-6">
                                        <div>
                                            ADDRESS TO : <strong>CONG. JOSE CH. ALVAREZ</strong><br />
                                            <small>Congressman, 2nd District of Palawan</small>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="col-md-3">DOH-MAIP</div>
                                        <div className="col-md-3">
                                            <label className="mt-checkbox">
                                                <input type="checkbox" name="isDohMaipMedical" checked={data.isDohMaipMedical == 1} onChange={this.setFormCheckProp} />
                                                MEDICAL
                                                <span></span>
                                            </label>
                                        </div>
                                        <div className="col-md-3">
                                            <label className="mt-checkbox">
                                                <input type="checkbox" name="isDohMaipOpd" checked={data.isDohMaipOpd == 1} onChange={this.setFormCheckProp} />
                                                OPD
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <br />
                                <br />
                                <div className="row">
                                    <div className="col-md-6">
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasBrgyClearance" checked={data.hasBrgyClearance == 1} onChange={this.setFormCheckProp} />
                                            Barangay Certificate <i><small>(*MED/OPD)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasPatientId" checked={data.hasPatientId == 1} onChange={this.setFormCheckProp} />
                                            VALID ID - PATIENT <i><small>(*MED/OPD) (W/PHOTOCOPY)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasMedCert" checked={data.hasMedCert == 1} onChange={this.setFormCheckProp} />
                                            MEDICAL CERTIFICATE <i><small>(*MED/OPD)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasMedAbst" checked={data.hasMedAbst == 1} onChange={this.setFormCheckProp} />
                                            MEDICAL ABSTRACT <i><small>(*MED)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasPromisoryNote" checked={data.hasPromisoryNote == 1} onChange={this.setFormCheckProp} />
                                            PROMISORY NOTE <i><small>(*MED)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasBillStatement" checked={data.hasBillStatement == 1} onChange={this.setFormCheckProp} />
                                            UPDATED BILLING STATEMENT <i><small>(*MED)</small></i>
                                            <span></span>
                                        </label>
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasPriceQuot" checked={data.hasPriceQuot == 1} onChange={this.setFormCheckProp} />
                                            PRICE QUOTATION<i><small>(*OPD)</small></i>
                                            <span></span>
                                        </label>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasReqOfPhysician" checked={data.hasReqOfPhysician == 1} onChange={this.setFormCheckProp} />
                                            REQUEST OF PHYSICIAN<i><small>(*DOH-MAIP) (*OPD)</small></i>
                                            <span></span>
                                        </label>
                                        <br />
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasReseta" checked={data.hasReseta == 1} onChange={this.setFormCheckProp} />
                                            RESETA <i><small>(*DSWD OPD)</small></i>
                                            <span></span>
                                        </label>
                                        <br />
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasSocialCastReport" checked={data.hasSocialCastReport == 1} onChange={this.setFormCheckProp} />
                                            SOCIAL CAST STUDY REPORT <i><small>(*DSWD MEDICAL)</small></i>
                                            <span></span>
                                        </label>
                                        <br />
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasPoliceReport" checked={data.hasPoliceReport == 1} onChange={this.setFormCheckProp} />
                                            POLICE REPORT <i><small>(*DSWD, in case of accident)</small></i>
                                            <span></span>
                                        </label>
                                        <br />
                                        <label className="mt-checkbox">
                                            <input type="checkbox" name="hasDeathCert" checked={data.hasDeathCert == 1} onChange={this.setFormCheckProp} />
                                            REGISTERED DEATH CERTIFICATE <i><small>(*DSWD, patient expires)</small></i>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-12">
                                        <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                                            <ControlLabel> Remarks : </ControlLabel>
                                            <FormControl componentClass="textarea" rows="6" bsClass="form-control input-sm" name="remarks" value={this.state.form.data.remarks} onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('remarks')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div className="clearfix" />

                        <div className="text-right" >
                            <button type="button" className="btn-lg btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                            <button type="button" className="btn-lg btn-primary" onClick={this.submit}>Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadData(this.props.trnId);
        this.initSelect2();
    },


    loadData: function (trnId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_financial_assistance_full", { trnId: trnId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            console.log("data has been reiceved");
            console.log(res);

            // form.data.proVoterId = res.pro_voter_id;
            // form.data.cellphoneNo = self.isEmpty(res.cellphone) ? '' : res.cellphone;
            // form.data.birthdate = !self.isEmpty(res.birthdate) ? moment(res.birthdate).format('YYYY-MM-DD') : '';
            // form.data.gender = res.gender;
            // form.data.firstname = self.isEmpty(res.firstname) ? firstname.trim() : res.firstname;
            // form.data.middlename = self.isEmpty(res.middlename) ? middlename.trim() : res.middlename;
            // form.data.lastname = self.isEmpty(res.lastname) ? lastname.trim() : res.lastname;
            // form.data.extName = res.ext_name;
            // form.data.civilStatus = res.civil_status;
            // form.data.bloodtype = res.bloodtype;
            // form.data.occupation = res.occupation;
            // form.data.religion = res.religion;
            // form.data.dialect = res.dialect;
            // form.data.ipGroup = res.ip_group;

            // form.data.municipalityName = res.municipality_name;
            // form.data.municipalityNo = res.municipality_no;
            // form.data.barangayName = res.barangay_name;
            // form.data.barangayNo = res.barangay_no;
            // form.data.voterName = res.voter_name;
            // form.data.voterGroup = res.voter_group;

            //self.setState({ form: form }, self.reinitSelect2);

            form.data.trnNo = res.trn_no;
            form.data.municipalityNo = res.municipality_no;
            form.data.municipalityName = res.municipality_name;
            form.data.barangayNo = res.barangay_no;
            form.data.barangayName = res.barangay_name;
            form.data.applicantName = res.applicant_name;
            form.data.applicantProVoterId = res.applicant_pro_voter_id;
            form.data.beneficiaryName = res.beneficiary_name;
            form.data.jpmIdNo = res.jpm_id_no;
            form.data.contactNo = res.contact_no;
            form.data.typeOfAsst = res.type_of_asst;
            form.data.trnDate = res.trn_date;
            form.data.endorsedBy = res.endorsed_by;
            form.data.projectedAmt = res.projected_amt;
            form.data.grantedAmt = res.granted_amt;
            form.data.receivedBy = res.received_by;
            form.data.releaseDate = res.release_date;
            form.data.releasingOffice = res.releasing_office;
            form.data.personnel = res.personnel;
            form.data.remarks = res.remarks;
            form.data.status = res.status;
            form.data.hospitalName = res.hospital_name;

            form.data.reqType = res.req_type;
            form.data.hasReqLetter = res.has_req_letter;
            form.data.hasBrgyClearance = res.has_brgy_clearance;
            form.data.hasPatientId = res.has_patient_id;
            form.data.hasMedCert = res.has_med_cert;
            form.data.hasMedAbst = res.has_med_abst;
            form.data.hasPromisoryNote = res.has_promisory_note;
            form.data.hasBillStatement = res.has_bill_statement;
            form.data.hasPriceQuot = res.has_price_quot;
            form.data.hasReqOfPhysician = res.has_req_of_physician;
            form.data.hasReseta = res.has_reseta;
            form.data.hasSocialCastReport = res.has_social_cast_report;
            form.data.hasPoliceReport = res.has_police_report;
            form.data.hasDeathCert = res.has_death_cert;
            form.data.isDswdMedical = res.is_dswd_medical;
            form.data.isDswdOpd = res.is_dswd_opd;
            form.data.isDohMaipMedical = res.is_doh_maip_medical;
            form.data.isDohMaipOpd = res.is_doh_maip_opd;

            self.setState({ form: form }, self.reinitSelect2);
        });
    },

    initSelect2: function () {
        var self = this;

        $("#fa-form #municipality_select2").select2({
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
                        provinceCode: 53
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

        $("#fa-form #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#fa-form #municipality_select2").val(),
                        provinceCode: 53
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

        // $("#fa-form #applicant_select2").select2({
        //     casesentitive: false,
        //     placeholder: "Select Applicant Name",
        //     allowClear: true,
        //     width: '100%',
        //     containerCssClass: ':all:',
        //     tags: true,
        //     createTag: function (params) {
        //         return {
        //             id: params.term,
        //             text: params.term,
        //             newOption: true
        //         }
        //     },
        //     ajax: {
        //         url: Routing.generate('ajax_select2_fa_applicant'),
        //         data: function (params) {
        //             return {
        //                 searchText: params.term,
        //             };
        //         },
        //         processResults: function (data, params) {
        //             return {
        //                 results: data.map(function (item) {
        //                     return { id: item.applicant_name, text: item.applicant_name };
        //                 })
        //             };
        //         },
        //     }
        // });

        $("#fa-form #beneficiary_select2").select2({
            casesentitive: false,
            placeholder: "Select Beneficiary Name",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_fa_beneficiary'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.beneficiary_name, text: item.beneficiary_name };
                        })
                    };
                },
            }
        });

        $("#fa-form #type_of_assistance_select2").select2({
            casesentitive: false,
            placeholder: "Select Type Of Assistance",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_fa_type_of_assistance'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.type_of_asst, text: item.type_of_asst };
                        })
                    };
                },
            }
        });

        $("#fa-form #endorser_select2").select2({
            casesentitive: false,
            placeholder: "Select Endorser",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_fa_endorser'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.endorsed_by, text: item.endorsed_by };
                        })
                    };
                },
            }
        });

        $("#fa-form #receiver_select2").select2({
            casesentitive: false,
            placeholder: "Select Receiver",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },

            ajax: {
                url: Routing.generate('ajax_select2_fa_receiver'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.received_by, text: item.received_by };
                        })
                    };
                },
            }
        });

        $("#fa-form #personnel_select2").select2({
            casesentitive: false,
            placeholder: "Select Releasing Personnel",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_fa_personnel'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.personnel, text: item.personnel };
                        })
                    };
                },
            }
        });


        $("#fa-form #office_select2").select2({
            casesentitive: false,
            placeholder: "Select Releasing Office",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },

            ajax: {
                url: Routing.generate('ajax_select2_fa_office'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.releasing_office, text: item.releasing_office };
                        })
                    };
                },
            }
        });

        $("#fa-form #voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: 4,
                        proId: 3,
                        provinceCode: 53,
                        municipalityNo: $("#fa-form #municipality_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasPhoto = parseInt(item.has_photo) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - PHOTO : ' + hasPhoto;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#fa-form #hospital_select2").select2({
            casesentitive: false,
            placeholder: "Select Hospital",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },

            ajax: {
                url: Routing.generate('ajax_select2_fa_hospital'),
                data: function (params) {
                    return {
                        searchText: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.hospital_name, text: item.hospital_name };
                        })
                    };
                },
            }
        });

        $("#fa-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
        });

        $("#fa-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
        });

        // $("#fa-form #applicant_select2").on("change", function () {
        //     self.setFormPropValue('applicantName', $(this).val());
        // });

        $("#fa-form #beneficiary_select2").on("change", function () {
            self.setFormPropValue('beneficiaryName', $(this).val());
        });

        $("#fa-form #type_of_assistance_select2").on("change", function () {
            self.setFormPropValue('typeOfAsst', $(this).val());
        });

        $("#fa-form #endorser_select2").on("change", function () {
            self.setFormPropValue('endorsedBy', $(this).val());
        });

        $("#fa-form #receiver_select2").on("change", function () {
            self.setFormPropValue('receivedBy', $(this).val());
        });

        $("#fa-form #personnel_select2").on("change", function () {
            self.setFormPropValue('personnel', $(this).val());
        });

        $("#fa-form #office_select2").on("change", function () {
            self.setFormPropValue('releasingOffice', $(this).val());
        });

        $("#fa-form #hospital_select2").on("change", function () {
            self.setFormPropValue('hospitalName', $(this).val());
        });

        $("#fa-form #voter-select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var form = self.state.form;
            form.data.applicantName = res.voterName;
            form.data.jpmIdNo = res.generatedIdNo;
            form.data.applicantProVoterId = res.proVoterId;

            // $("#fa-form #applicant_select2").empty()
            //     .append($("<option/>")
            //         .val(form.data.applicantName)
            //         .text(form.data.applicantName))
            //     .trigger("change");

            console.log("voter");
            console.log(form.data);

            self.setState({ form: form });
        });

        var form = self.state.form;

        // form.data.applicantName = "";
        // form.data.jpmIdNo = "";

        self.setState({ form: form })
    },

    reinitSelect2: function () {
        var self = this;
        var data = this.state.form.data;

        $("#fa-form #municipality_select2").empty()
            .append($("<option/>")
                .val(data.municipalityNo)
                .text(data.municipalityName))
            .trigger("change");

        $("#fa-form #barangay_select2").empty()
            .append($("<option/>")
                .val(data.barangayNo)
                .text(data.barangayName))
            .trigger("change");

        $("#fa-form #hospital_select2").empty()
            .append($("<option/>")
                .val(data.hospitalName)
                .text(data.hospitalName))
            .trigger("change");

        // $("#fa-form #applicant_select2").empty()
        //     .append($("<option/>")
        //         .val(data.applicantName)
        //         .text(data.applicantName))
        //     .trigger("change");

        $("#fa-form #voter-select2").empty()
            .append($("<option/>")
                .val(data.applicantProVoterId)
                .text(data.applicantName))
            .trigger("change");

        $("#fa-form #beneficiary_select2").empty()
            .append($("<option/>")
                .val(data.beneficiaryName)
                .text(data.beneficiaryName))
            .trigger("change");


        $("#fa-form #type_of_assistance_select2").empty()
            .append($("<option/>")
                .val(data.typeOfAsst)
                .text(data.typeOfAsst))
            .trigger("change");


        $("#fa-form #endorser_select2").empty()
            .append($("<option/>")
                .val(data.endorsedBy)
                .text(data.endorsedBy))
            .trigger("change");

        $("#fa-form #receiver_select2").empty()
            .append($("<option/>")
                .val(data.receivedBy)
                .text(data.receivedBy))
            .trigger("change");

        $("#fa-form #personnel_select2").empty()
            .append($("<option/>")
                .val(data.personnel)
                .text(data.personnel))
            .trigger("change");

        $("#fa-form #office_select2").empty()
            .append($("<option/>")
                .val(data.releasingOffice)
                .text(data.releasingOffice))
            .trigger("change");

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

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form })
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
        data.trnId = self.props.trnId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_financial_assistance_full", { trnId: self.props.trnId }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.FinancialAssistanceEditModal = FinancialAssistanceEditModal;