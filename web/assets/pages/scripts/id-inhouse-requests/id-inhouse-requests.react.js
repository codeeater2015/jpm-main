var IdInhouseRequests = React.createClass({
    getInitialState: function () {
        return {
            showNewRequestModal: false,
            user : null,
            status : {
                total_count : 0
            },
            filters : {
                proId : null,
                electId : null,
                provinceCode : null
            }
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
    
    componentDidMount: function () {
        this.initSelect2();
        this.loadUser(window.userId);
        this.loadPrintingStatus();
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

    loadPrintingStatus : function () {
        var self = this;

        self.requestPrintingStatus = $.ajax({
            url: Routing.generate("ajax_get_id_request_status"),
            type: "GET"
        }).done(function (res) {
            self.setState({ status : res });
        });
    },

    initSelect2: function () {
        var self = this;

        $("#id_inhouse_requests_component #election_select2").select2({
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

        $("#id_inhouse_requests_component #project_select2").select2({
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

        $("#id_inhouse_requests_component #province_select2").select2({
            casesentitive: false,
            placeholder: "Enter Province...",
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
       
        $("#id_inhouse_requests_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();
            self.setState({ filters : filters });
        });

        $("#id_inhouse_requests_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters });
        });

        $("#id_inhouse_requests_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters: filters });
        });
    },

    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user.project)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#id_inhouse_requests_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {

                $("#id_inhouse_requests_component #project_select2").empty()
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
            $("#id_inhouse_requests_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#id_inhouse_requests_component #election_select2").attr('disabled', 'disabled');
            $("#id_inhouse_requests_component #province_select2").attr('disabled', 'disabled');
            $("#id_inhouse_requests_component #project_select2").attr('disabled', 'disabled');
        }

        //self.gridTable();
    },

    openNewRequestModal: function () {
        console.log("opening new new  request modal !");
        this.setState({ showNewRequestModal: true });
    },

    closeNewRequestModal: function () {
        this.setState({ showNewRequestModal: false });
    },
    
    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    reloadDatatable : function(){
        this.refs.idRequestsDatatable.reload();
    },
    
    render: function () {
        return (
            <div className="portlet light portlet-fit bordered" id="id_inhouse_requests_component">
                <div className="portlet-body">
                    {/* Header Start */}
                    <div className="row">
                        <div className="col-md-5">
                            <div><h3>ID Inhouse Requests <small><strong><em>Today's Total Printed ID: { this.state.status.total_count }</em></strong></small></h3> </div>
                            <button type="button" onClick={this.openNewRequestModal} className="btn btn-sm green">New Request</button>
                            {
                                this.state.showNewRequestModal &&
                                <NewIdRequestModal
                                    notify={this.notify}
                                    show={this.state.showNewRequestModal}
                                    onHide={this.closeNewRequestModal}
                                    proId={this.state.filters.proId}
                                    electId={this.state.filters.electId}
                                    provinceCode={this.state.filters.provinceCode}
                                    onSuccess={this.reloadDatatable}
                                />
                            }
                        </div>

                        <div className="col-md-7">
                            <form>
                                <div className="col-md-3 col-md-offset-1">
                                    <select id="election_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <select id="province_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                                <div className="col-md-4">
                                    <select id="project_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    { /* Header End */ }

                    <br/>

                    { /* DataTable Start */ }
                    <div className="row">
                        <div className="col-md-12">
                            <InhouseRequestsDatatable
                                proId={this.state.filters.proId}
                                electId={this.state.filters.electId}
                                provinceCode={this.state.filters.provinceCode}
                                ref="idRequestsDatatable"
                                notify={this.notify}
                            />
                        </div>
                    </div>
                    { /* DataTable End */ }
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <IdInhouseRequests />,
        document.getElementById('component-container')
    );
}, 500);
