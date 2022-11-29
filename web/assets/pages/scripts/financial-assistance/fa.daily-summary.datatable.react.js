var FinancialAssistanceDailySummaryDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showReleasedListModal : false,
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
            self.setState({ user: res });
        });
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var financial_assistance_daily_summary = $("#financial_assistance_daily_summary");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_financial_assistance_daily_summary", {}, true);

        grid_project_event.init({
            src: financial_assistance_daily_summary,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET'
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8]
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
                        "data": "closing_date",
                        "width": 50,
                        "className": "text-center"
                    },
                    {
                        "data": "total_released",
                        "width": 50,
                        "className": "text-center"
                    },
                    {
                        "data": "released_amt",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_pending",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "pending_amt",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "created_by",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "created_at",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "width": 30,
                        "className" : "text-center",
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var releaseBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green release-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-list-ol'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return releaseBtn + deleteBtn;
                        }
                    }
                ],
            }
        });


        financial_assistance_daily_summary.on('click', '.release-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showReleasedListModal: true, target: data.id });
        });

        financial_assistance_daily_summary.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this summary?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_financial_assistance_daily_summary", { id: id }),
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
    
    closeReleasedListModal: function () {
        this.setState({ showReleasedListModal: false });
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render: function () {
        return (
            <div>
                
                {
                    this.state.showReleasedListModal &&
                    <FinancialAssistanceReleasedListModal
                        proId={3}
                        show={this.state.showReleasedListModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        id={this.state.target}
                        onHide={this.closeReleasedListModal}
                    />
                }

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="financial_assistance_daily_summary" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Closing Date</th>
                                <th>Total Released</th>
                                <th>Total Released Amount</th>
                                <th>Total Pending</th>
                                <th>Total Pending Amt</th>
                                <th>Closed By</th>
                                <th>Closed At</th>
                                <th width="50px"></th>
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

window.FinancialAssistanceDailySummaryDatatable = FinancialAssistanceDailySummaryDatatable;