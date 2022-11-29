var OrganizationPhotoSummaryComponent = React.createClass({

    getInitialState: function () {
        return {
            provinceCode: null,
            municipalityNo: null,
            brgyNo: null,
            electId: null,
            proId: null,
            photoDate : moment(new Date()).format("YYYY/MM/DD"),
            displayBarangayTable: false,
            displayMunicipalityTable: false,
            displayProvinceTable: false
        };
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    render: function () {
        return (
            <div id="voter_record_summary" className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <div className="row">
                        <div className="col-md-3">
                            <form>
                                <div className="form-group">
                                    <label className="control-label">Election</label>
                                    <select id="election_select2" className="form-control form-filter input-sm" name="electId">
                                    </select>
                                </div>

                                <div className="form-group">
                                    <label className="control-label">Province</label>
                                    <select id="province_select2" className="form-control form-filter input-sm" name="provinceCode">
                                    </select>
                                </div>

                                <div className="form-group">
                                    <label className="control-label">Project</label>
                                    <select id="project_select2" className="form-control form-filter input-sm" name="proId">
                                    </select>
                                </div>

                                <div className="form-group">
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label className="control-label">Date</label>
                                    <input type="date" onChange={this.setPhotoDate} className="form-control form-filter input-sm"/>
                                </div>
                                <div>
                                    <button type="button" style={{ width: "100%" }} className="btn btn-primary" onClick={this.displayTable}>Apply</button>
                                </div>
                            </form>
                        </div>
                        <div className="col-md-9" style={{ padding: "0px" }}>

                            {
                                this.state.displayMunicipalityTable &&
                                <OrganizationMunicipalityPhotoSummaryTable electId={this.state.electId} proId={this.state.proId} provinceCode={this.state.provinceCode} municipalityNo={this.state.municipalityNo} photoDate={this.state.photoDate} notify={this.notify} />
                            }

                            {
                                !this.state.displayProvinceTable && !this.state.displayMunicipalityTable && !this.state.displayBarangayTable
                                &&
                                (
                                    <div style={{ marginTop: "10px" }}>
                                        This area was intentionally left blank...
                                    </div>
                                )
                            }
                        </div>
                    </div>
                </div>
            </div>
        )
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadUser(window.userId);
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

    initSelect2: function () {
        var self = this;

        $("#voter_record_summary #election_select2").select2({
            casesentitive: false,
            placeholder: "Select Election",
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

        $("#voter_record_summary #province_select2").select2({
            casesentitive: false,
            placeholder: "Select Province",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_province_strict'),
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

        $("#voter_record_summary #project_select2").select2({
            casesentitive: false,
            placeholder: "Select Project",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_projects'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.pro_id, text: item.pro_name };
                        })
                    };
                },
            }
        });

        $("#voter_record_summary #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality_strict'),
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

        $("#voter_record_summary #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay_strict'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#voter_record_summary #municipality_select2").val(),
                        provinceCode: self.state.provinceCode
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


        $("#voter_record_summary #election_select2").on("change", function () {
            self.setState({ electId: $(this).val() });
        });

        $("#voter_record_summary #project_select2").on("change", function () {
            self.setState({ proId: $(this).val() });
        });

        $("#voter_record_summary #province_select2").on("change", function () {
            $("#voter_record_summary #municipality_select2").empty().trigger('change');
            $("#voter_record_summary #barangay_select2").empty().trigger('change');
            self.setState({ provinceCode: $(this).val() });
        });

        $("#voter_record_summary #municipality_select2").on("change", function () {
            $("#voter_record_summary #barangay_select2").empty().trigger('change');
            self.setState({ municipalityNo: $(this).val() });
        });

        $("#voter_record_summary #barangay_select2").on("change", function () {
            self.setState({ brgyNo: $(this).val() });
        });
    },


    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#voter_record_summary #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {

                $("#voter_record_summary #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#voter_record_summary #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });


        if (!self.state.user.isAdmin)
            $("#voter_record_summary #province_select2").attr('disabled', 'disabled');
    },

    displayTable: function () {
        var self = this;
        var provinceCode = this.state.provinceCode;
        var municipalityNo = this.state.municipalityNo;
        var brgyNo = this.state.brgyNo;

        var displayProvinceTable = false;
        var displayMunicipalityTable = false;
        var displayBarangayTable = false;

        if (this.isEmpty(municipalityNo) && this.isEmpty(brgyNo) && !this.isEmpty(provinceCode)) {
            displayProvinceTable = true;
            displayMunicipalityTable = false;
            displayBarangayTable = false;
        } else if (!this.isEmpty(municipalityNo) && this.isEmpty(brgyNo)) {
            displayProvinceTable = false;
            displayMunicipalityTable = true;
            displayBarangayTable = false;
        } else if (!this.isEmpty(municipalityNo) && !this.isEmpty(brgyNo)) {
            displayProvinceTable = false;
            displayMunicipalityTable = false;
            displayBarangayTable = true;
        }

        console.log(this.state);

        this.setState({
            displayProvinceTable: false,
            displayMunicipalityTable: false,
            displayBarangayTable: false
        })

        setTimeout(function () {
            self.setState({
                displayProvinceTable: displayProvinceTable,
                displayMunicipalityTable: displayMunicipalityTable,
                displayBarangayTable: displayBarangayTable
            });
        }, 1000)
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined";
    },

    setPhotoDate : function(e) {
        this.setState({photoDate : e.target.value});
    }

});

setTimeout(function () {
    ReactDOM.render(
        <OrganizationPhotoSummaryComponent />,
        document.getElementById('container')
    );
}, 500);
