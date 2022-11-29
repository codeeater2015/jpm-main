var Recruitment2Component = React.createClass({
    getInitialState: function () {
        return {
            user: null,
            filters: {
                electId: 3,
                provinceCode: 53,
                proId: 3,
                municipalityNo : null,
                brgyNo : null
            }
        };
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

        $("#handler_component #election_select2").select2({
            casesentitive: false,
            placeholder: "Select Election...",
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

        $("#handler_component #project_select2").select2({
            casesentitive: false,
            placeholder: "Select Project...",
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

        $("#handler_component #province_select2").select2({
            casesentitive: false,
            placeholder: "Enter Province...",
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

        
        $("#handler_component #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Enter Municipality...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $('#handler_component #province_select2').val()
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
        
        $("#handler_component #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Enter barangay...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $('#handler_component #municipality_select2').val()
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


        $("#handler_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #municipality_select2").on("change", function () {
            var filters = self.state.filters;
            filters.municipalityNo = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });
        
        $("#handler_component #barangay_select2").on("change", function () {
            var filters = self.state.filters;
            filters.brgyNo = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });
    },

    reload : function(){
        console.log("reloading datatable");
        this.refs.RecruitDatatable.reload();
        this.refs.RecruitDatatable.reloadHousehold();
    },


    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user.project)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");

                self.refs.RecruitDatatable.initDatatable();
                self.refs.RecruitDatatable.initHouseholdDatatable();
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#handler_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#handler_component #election_select2").attr('disabled', 'disabled');
            $("#handler_component #province_select2").attr('disabled', 'disabled');
            $("#handler_component #project_select2").attr('disabled', 'disabled');
        }
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


    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>
                <div className="portlet light portlet-fit bordered" style={{ marginTop: "10px" }}>
                    <div className="portlet-body">
                        <div className="row" >
                            <div className="col-md-6">
                                <h4 className="bold">Recruitement Page</h4>
                            </div>
                        </div>

                        <div className="row" id="handler_component">
                            <form onSubmit={this.onApplyCode}>
                                <div className="col-md-2">
                                    <select id="project_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-2">
                                    <select id="election_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-2">
                                    <select id="province_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-2">
                                    <select id="municipality_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-2">
                                    <select id="barangay_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <Recruitment2NotSubmittedDatatable
                    electId={this.state.filters.electId}
                    proId={this.state.filters.proId}
                    provinceCode={this.state.filters.provinceCode}
                    municipalityNo={this.state.filters.municipalityNo}
                    brgyNo={this.state.filters.brgyNo}
                    ref="RecruitDatatable" />

            </div>
        )
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

});

setTimeout(function () {
    ReactDOM.render(
        <Recruitment2Component />,
        document.getElementById('page-container')
    );
}, 500);
