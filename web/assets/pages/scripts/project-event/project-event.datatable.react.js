var ProjectEventDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showAttendanceModal : false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            filters : {
                electId : null,
                provinceCode : null,
                proId : null
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

        $("#voter_component #election_select2").select2({
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

        $("#voter_component #project_select2").select2({
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

        $("#voter_component #province_select2").select2({
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

        $("#voter_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();
            
            self.setState({ filters : filters },self.reload);
        });
        
        $("#voter_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters : filters },self.reload);
        });

        $("#voter_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters : filters },self.reload);
        });

    },

    reinitSelect2: function () {
        var self = this;
        
        if(!self.isEmpty(self.state.user.project)){
            var provinceCode = self.state.user.project.provinceCode;
           
            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#voter_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId : self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#voter_component #project_select2").empty()
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
            $("#voter_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin){
            $("#voter_component #election_select2").attr('disabled', 'disabled');
            $("#voter_component #province_select2").attr('disabled', 'disabled');
            $("#voter_component #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var project_event_table = $("#project_event_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_project_event", {}, true);

        grid_project_event.init({
            src: project_event_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.electId = $('#voter_component #election_select2').val();
                        d.provinceCode = $('#voter_component #province_select2').val();
                        d.proId = $('#voter_component #project_select2').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0,2,3,4,5,6,7]
                }, {
                    'className': 'align-center',
                    'targets': [0,3]
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
                    { "data" : "event_name" },
                    {
                        "data" : "event_date",
                        "width" : 100,
                        "className" : "text-center",
                        "render" : function(data,type,row){
                            return moment(data).format('MMM DD, YYYY');
                        }
                    },
                    { 
                        "data": "total_expected",
                        "className" : "text-center",
                        "width" : 30
                    },
                    { 
                        "data": "total_attended",
                        "className" : "text-center",
                        "width" : 30
                    },
                    { 
                        "data": "total_new_id",
                        "className" : "text-center",
                        "width" : 30
                    },
                    { 
                        "data": "total_claimed",
                        "className" : "text-center",
                        "width" : 30
                    },
                    {
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var attendanceBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green attendance-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-calendar'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return editBtn + attendanceBtn + deleteBtn;
                        }
                    }
                ],
            }
        });

        project_event_table.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.event_id });
        });

        project_event_table.on('click', '.attendance-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showAttendanceModal: true, target: data.event_id });
        });

        project_event_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.event_id);
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    closeAttendanceModal: function () {
        this.setState({ showAttendanceModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    delete: function (eventId) {
        var self = this;

        if (confirm("Are you sure you want to delete this event?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_project_event_header", { eventId : eventId }),
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
        if(this.grid != null){
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
                    <ProjectEventCreateModal 
                        proId={this.state.filters.proId} 
                        show={this.state.showCreateModal} 
                        notify={this.props.notify} 
                        reload={this.reload} 
                        onHide={this.closeCreateModal} 
                    />
                }

                {
                    this.state.showEditModal &&
                    <ProjectEventEditModal 
                        proId={this.state.filters.proId}
                        eventId={this.state.target} 
                        show={this.state.showEditModal} 
                        notify={this.props.notify} 
                        reload={this.reload} 
                        onHide={this.closeEditModal} 
                    />
                } 

                {
                    this.state.showAttendanceModal &&
                    <ProjectEventAttendanceModal 
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        provinceCode={this.state.filters.provinceCode}
                        eventId={this.state.target} 
                        show={this.state.showAttendanceModal} 
                        notify={this.props.notify} 
                        reload={this.reload} 
                        onHide={this.closeAttendanceModal} 
                    />
                } 

                <div className="row" id="voter_component">
                    <div className="col-md-5">
                        <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Event</button>
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

                <div className="table-container" style={{ marginTop : "20px" }}>
                    <table id="project_event_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Event Name</th>
                                <th>Event Date </th>
                                <th>Expected</th>
                                <th>Attended</th>
                                <th>New</th>
                                <th>Claimed</th>
                                <th width="90px"></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="idx_no" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
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

window.ProjectEventDatatable = ProjectEventDatatable;