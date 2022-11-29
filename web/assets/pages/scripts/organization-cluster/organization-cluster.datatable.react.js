var OrganizationClusterDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showAttendanceModal: false,
            targetId: null,
            targetName : "",
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
        this.initSelect2();
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

        $("#organization_cluster_table #election_select2").select2({
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

        $("#organization_cluster_table #project_select2").select2({
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


        $("#custer_table #municipality_select2").select2({
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

        $("#custer_table #barangay_select2").select2({
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
                        provinceCode: 53,
                        municipalityNo: $("#custer_table #municipality_select2").val()
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

        $("#organization_cluster_table #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#organization_cluster_table #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#organization_cluster_table #province_select2").on("change", function () {
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
                $("#organization_cluster_table #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#organization_cluster_table #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");

                self.initDatatable();
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#organization_cluster_table #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#organization_cluster_table #election_select2").attr('disabled', 'disabled');
            $("#organization_cluster_table #province_select2").attr('disabled', 'disabled');
            $("#organization_cluster_table #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var custer_table = $("#custer_table");

        grid.init({
            src: custer_table,

            dataTable: {
                "bState": true,
                "autoWidth": true,
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": Routing.generate('ajax_datatable_organization_cluster'),
                    "type": "GET",
                    "data": function (d) {
                        d.provinceCode = 53;
                        d.municipalityNo = $('#custer_table #municipality_select2').val();
                        d.brgyNo = $('#custer_table #barangay_select2').val();
                        d.voterName = $('#custer_table input[name="voter_name"]').val();
                        d.cellphone = $('#custer_table input[name="cellphone"]').val();
                        d.voterGroup = 'LGC'
                        d.electId = self.props.elecId;
                        d.proId = self.props.proId;
                    }
                },
                columnDefs: [
                    {
                        'className': 'text-center valign-middle',
                        'orderable': false,
                        'targets': [0, 2, 3, 4, 5, 6, 7]
                    }
                ],
                "order": [
                    [1, "asc"]
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
                        "data": "voter_name",
                        "render": function (data, type, row) {
                            return (row.voted_2017 == 1 ? "*" : "") + data;
                        }
                    },
                    {
                        "data": "municipality_name",
                        "width": 150
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "cluster_no",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "total_barangay",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "cellphone_no",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "width": 70,
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs green edit-btn"><i class="fa fa-edit"></i></button>';
                            var deleteBtn = '<button class="btn btn-xs red-sunglo delete-btn"><i class="fa fa-trash"></i></button>';

                            var btnGroup = '';
                            btnGroup += editBtn;
                            btnGroup += deleteBtn;

                            return btnGroup;
                        },
                        "className": "text-center"
                    }
                ]
            }

        });


        custer_table.on('click', '.edit-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            console.log(data.voter_name);
            self.setState({ showEditModal : true, targetId : data.pro_voter_id, targetName : data.voter_name });
        });

        custer_table.on('click', '.delete-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.pro_voter_id);
        });

        self.grid = grid;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    delete: function (proVoterId) {
        var self = this;

        if (confirm("continue clear assigned barangays?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_organization_cluster", { proVoterId: proVoterId }),
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

    render: function () {
        return (
            <div>
                {
                    this.state.showCreateModal &&
                    <OrganizationClusterCreateModal
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        show={this.state.showCreateModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeCreateModal}
                    />
                }

                {
                    this.state.showEditModal &&
                    <OrganizationClusterEditModal
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        proVoterId={this.state.targetId}
                        voterName={this.state.targetName}
                        show={this.state.showEditModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                    />
                }

                <div className="row" id="organization_cluster_table">
                    <div className="col-md-5">
                        <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>Add CluterHead</button>
                    </div>

                    <div className="col-md-7">
                        <form onSubmit={this.onApplyCode}>
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

                <div className="table-container">
                    <div className="table-actions-wrapper">
                    </div>
                    <table id="custer_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th className="text-center">No</th>
                                <th>Name</th>
                                <th className="text-center">Municipality</th>
                                <th className="text-center">Brgy</th>
                                <th className="text-center">CL</th>
                                <th className="text-center">Total</th>
                                <th className="text-center">CP No.</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                </td>

                                <td style={{ padding: "10px 5px" }}>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select id="barangay_select2" className="form-control form-filter input-sm">
                                    </select>
                                </td>

                                <td style={{ padding: "10px 5px" }}>
                                
                                </td>

                                
                                <td style={{ padding: "10px 5px" }}>
                                
                                </td>

                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="cellphone" onChange={this.handleFilterChange} />
                                </td>

                                <td className="text-center">
                                    <button style={{ marginTop: "5px", marginBottom: "5px" }} className="btn btn-xs green btn-outline filter-submit">
                                        <i className="fa fa-search" />Search
                                    </button>
                                </td>
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

window.OrganizationClusterDatatable = OrganizationClusterDatatable;