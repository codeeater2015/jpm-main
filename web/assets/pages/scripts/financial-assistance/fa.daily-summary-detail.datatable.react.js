var FinancialDailySummaryDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEditModal: false,
            showReleaseModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            filters: {
                electId: null,
                provinceCode: null,
                proId: null
            }
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        this.initDatatable();
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

        $("#fa_component #election_select2").select2({
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

        $("#fa_component #project_select2").select2({
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

        $("#fa_component #province_select2").select2({
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

        $("#fa_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#fa_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#fa_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters: filters }, self.reload);
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
                $("#fa_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#fa_component #project_select2").empty()
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
            $("#fa_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#fa_component #election_select2").attr('disabled', 'disabled');
            $("#fa_component #province_select2").attr('disabled', 'disabled');
            $("#fa_component #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var financial_assistance_released_table = $("#financial_assistance_released_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_financial_assistance_daily_summary_detail", {}, true);

        grid_project_event.init({
            src: financial_assistance_released_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.id = self.props.id;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3]
                }],
                "order": [
                    [0, "desc"]
                ],
                "columns": [
                    {
                        "data": null,
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "trn_no",
                        "width": 50,
                        "className": "text-center"
                    },
                    {
                        "data": "trn_date",
                        "width": 50,
                        "className": "text-center"
                    },
                    {
                        "data": "applicant_name",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "contact_no",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "beneficiary_name",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "endorsed_by",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "municipality_name",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "granted_amt",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "width": 50,
                        "className" : "text-center",
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var releaseBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green release-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-calendar'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return deleteBtn;
                        }
                    }
                ],
            }
        });

        financial_assistance_released_table.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.trn_id });
        });

        financial_assistance_released_table.on('click', '.release-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showReleaseModal: true, target: data.trn_id });
        });

        financial_assistance_released_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.dtl_id);
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    closeReleaseModal: function () {
        this.setState({ showReleaseModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    openClosingModal: function () {
        this.setState({ showClosingModal: true });
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this item?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_financial_assistance_daily_summary_detail", { id: id }),
                type: "DELETE"
            }).done(function (res) {
                self.reload();
            });
        }
    },

    handleFilterChange: function () {
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reload();
        }, this.state.doneTypingInterval);
    },

    reload: function () {
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render: function () {

        console.log("active table");
        console.log(this.state.activeTable);

        return (
            <div>
               

                {
                    this.state.showReleaseModal &&
                    <FinancialAssistanceReleaseModal
                        proId={this.state.filters.proId}
                        trnId={this.state.target}
                        show={this.state.showReleaseModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeReleaseModal}
                    />
                }

                {
                    this.state.showEditModal &&
                    <FinancialAssistanceEditModal
                        proId={this.state.filters.proId}
                        trnId={this.state.target}
                        show={this.state.showEditModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                    />
                }

                <div className="row" id="fa_component">
                    {/* <div className="col-md-10">
                        <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Assistance</button>
                        <button type="button" className="btn btn-primary" style={{ marginLeft : "10px" }} onClick={this.openClosingModal}>Close Transactions</button>
                    </div>
                    <div className="col-md-2">
                        <select className="form-control" onChange={this.setActiveTable} name="activeTable">
                            <option value="TRANSACTIONS">Transactions</option>
                            <option value="DAILY_SUMMARY">Daily Summary</option>
                        </select>
                    </div> */}
                </div>

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="financial_assistance_released_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Trn No</th>
                                <th>Trn Date</th>
                                <th>Applicant</th>
                                <th>Contact No</th>
                                <th>Beneficiary</th>
                                <th>Endorser</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Granted Amt</th>
                                <th width="50px"></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="trn_no" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="trn_date" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="applicant_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="contact_no" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="beneficiary_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="endorsed_by" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="municipality_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="barangay_name" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.FinancialDailySummaryDetailDatatable = FinancialDailySummaryDetailDatatable;