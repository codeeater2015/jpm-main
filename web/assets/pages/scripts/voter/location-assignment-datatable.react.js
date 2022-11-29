var LocationAssignmentDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500
        }
    },

    componentDidMount: function () {
        this.initDatatable();
    },

    initDatatable: function () {
        var self = this;

        var location_table = $("#location_assignment_table");
        var grid_table = new Datatable();
        var url = Routing.generate("ajax_get_datatable_location_assignment", { proIdCode: self.props.proIdCode }, true);

        grid_table.init({
            src: location_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "dom": "pit",
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.municipalityName = $('#location_assignment_table input[name="municipalityName"]').val();
                        d.barangayName = $('#location_assignment_table input[name="barangayName"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 1, 2, 3]
                }],
                "order": [
                    [2, "ASC"]
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
                        "data": "municipality_name"
                    },
                    { "data": "barangay_name" },
                    {
                        "width": 20,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs btn-danger delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return deleteBtn;
                        }
                    }
                ],
            }
        });

        location_table.on('click', '.delete-button', function () {
            var data = grid_table.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_table;
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this location?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_location_assignment", { id: id }),
                type: 'DELETE'
            }).done(function () {
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
        this.grid.getDataTable().ajax.reload();
    },

    render: function () {
        return (
            <div style={{ marginTop: "10px" }}>
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="location_assignment_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Action</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="municipalityName" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="barangayName" onChange={this.handleFilterChange} />
                                </td>
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

window.LocationAssignmentDatatable = LocationAssignmentDatatable;