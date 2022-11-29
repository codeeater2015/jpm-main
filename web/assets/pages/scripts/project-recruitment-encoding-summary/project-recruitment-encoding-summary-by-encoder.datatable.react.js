var ProjectRecruitmentEncodingSummaryByEncoderDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showRecruitsModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            selectedDate : moment(new Date()).format('YYYY-MM-DD'),
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

        $("#voter_table #province_select2").on("change", function () {
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

                self.initDatatable();
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

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var project_recruitment_summary_table = $("#project_recruitment_summary_table");
        var grid_project_recruitment = new Datatable();
        var url = Routing.generate("ajax_get_datatable_project_recruitment_encoding_summary_by_encoder", {}, true);

        grid_project_recruitment.init({
            src: project_recruitment_summary_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.electId = $('#handler_component #election_select2').val();
                        d.provinceCode = $('#handler_component #province_select2').val();
                        d.proId = $('#handler_component #project_select2').val();
                        d.createdAt = self.state.selectedDate;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
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
                        "data": "created_by"
                    },
                    {
                        "data" : "start_time",
                        "width" : 70,
                        "className" : 'text-center',
                        "render" : function(data){
                            return moment(data).format('hh:mm A');
                        }
                    },
                    {
                        "data" : "end_time",
                        "width" : 70,
                        "className" : "text-center",
                        "render" : function(data){
                            return moment(data).format('hh:mm A');
                        }
                    },
                    {
                        "data" : "start_time",
                        "width" : 60,
                        "className" : "text-center",
                        "render" : function(data,type,row){
                            var end = moment(row.end_time);
                            var start = moment(row.start_time);
                            var duration = moment.duration(end.diff(start));

                            return duration.asHours().toFixed(2) + ' Hrs';
                        }
                    },
                    {
                        "data" : "start_time",
                        "width" : 80,
                        "className" : "text-center",
                        "render" : function(data,type,row){
                            var end = moment(row.end_time);
                            var start = moment(row.start_time);
                            var duration = moment.duration(end.diff(start));

                            return  (row.total_recruits / duration.asHours().toFixed()).toFixed() + ' <sub>/ hour</sub>';
                        }
                    },
                    { 
                        "data": "total_recruits" , 
                        "className": "text-center", 
                        width : 80
                    },
                    {
                        "data": "total_with_cellphone",
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "data": "total_members",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data,type,row){
                            return ((row.total_with_cellphone / row.total_recruits) * 100).toFixed(2);
                        }
                    },
                    {
                        "data": "total_with_cellphone",
                        "className": "text-center",
                        "width": 80,
                        "render" : function(data,type,row){
                            return row.total_recruits - row.total_with_cellphone;
                        }
                    },
                    {
                        "data": "total_with_cellphone",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data,type,row){
                            return (((row.total_recruits - row.total_with_cellphone) / row.total_recruits) * 100).toFixed(2);
                        }
                    },
                    { 
                        "data": "total_work_days" , 
                        "className": "text-center", 
                        "render" : function(data){
                            return data + " days";
                        },
                        width : 80
                    },
                    { 
                        "data": "total_work_result" , 
                        "className": "text-center", 
                        width : 50
                    },
                    { 
                        "data": "total_work_result" , 
                        "className": "text-center", 
                        width : 50,
                        "render" : function(data,type,row){
                            return (row.total_work_result - (row.total_work_days * 500));   
                        }
                    }

                ],
            }
        });

        project_recruitment_summary_table.on('click', '.recruits-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showRecruitsModal: true, target: data.rec_id });
        });

        project_recruitment_summary_table.on('click', '.delete-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.rec_id);
        });

        self.grid = grid_project_recruitment;
    },


    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    closeRecruitsModal: function () {
        this.setState({ showRecruitsModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    delete: function (recId) {
        var self = this;

        if (confirm("Are you sure you want to delete this record ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_project_recruitment_header", { recId : recId }),
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

    onDateChange : function(e){
        this.setState({ "selectedDate" : e.target.value },this.reload);
    },

    render: function () {
        return (
            <div>
                <div className="row" id="handler_component">
                    <div className="col-md-6 col-md-offset-6">
                        <form onSubmit={this.onApplyCode}>
                            <div className="col-md-3">
                                <select id="election_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-3">
                                <select id="province_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-3">
                                <select id="project_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-3">
                               <input type='date' className="form-control input-sm" value={this.state.selectedDate} onChange={this.onDateChange} name="encoding_date_picker" />
                            </div>
                        </form>
                    </div>
                </div>

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="project_recruitment_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Encoder</th>
                                <th>Started</th>
                                <th>Ended</th>
                                <th>Duration</th>
                                <th>Speed</th>
                                <th className="text-center">Total</th>
                                <th className="text-center">With CP</th>
                                <th className="text-center">%</th>
                                <th className="text-center">No CP</th>
                                <th className="text-center">%</th>
                                <th>No of Days</th>
                                <th>Overall</th>
                                <th>Credits</th>                                
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
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

window.ProjectRecruitmentEncodingSummaryByEncoderDatatable = ProjectRecruitmentEncodingSummaryByEncoderDatatable;