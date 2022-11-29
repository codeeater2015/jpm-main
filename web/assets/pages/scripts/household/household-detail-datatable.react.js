
var HouseholdDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showEditModal: false
        }
    },

    componentDidMount: function () {
        this.initDatatable(this.props.householdId);
    },

    initDatatable: function (householdId) {
        var self = this;
        var grid = new Datatable();

        var project_recruitment_detail_datatable = $("#project_recruitment_detail_datatable");
        var grid_project_event = new Datatable();

        var url = Routing.generate("ajax_get_datatable_household_detail", { householdId: householdId }, true);

        grid_project_event.init({
            src: project_recruitment_detail_datatable,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.provinceCode = '53';
                        d.voterName = $('#project_recruitment_detail_datatable input[name="voterName"]').val();
                        d.barangayName = $('#project_recruitment_detail_datatable input[name="barangayName"]').val();
                        d.householdId = self.props.householdId;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3]
                }],
                "order": [
                    [1, "desc"]
                ],
                "columns": [
                    {
                        "data": null,
                        "className": "text-center",
                        "width": 20,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "voter_name",
                        "className": "text-left"
                    },
                    {
                        "data": "birthdate",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "relationship",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "cellphone",
                        "className": "text-center",
                        "width": 100,
                    },

                    {
                        "width": 60,
                        "className" : "text-center",
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";

                            return deleteBtn;
                        }
                    }
                ],
            }
        });


        project_recruitment_detail_datatable.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        // project_recruitment_detail_datatable.on('click', '.edit-button', function () {
        //     var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
        //     self.edit(data.voter_id);
        // });

        self.grid = grid_project_event;
    },

    edit: function (voterId) {
        this.setState({ showEditModal: true, target: voterId })
    },

    closeEditModal: function () {
        this.reload();
        this.setState({ showEditModal: false, target: null });
    },

    delete: function (householdDetailId) {
        var self = this;

        if (confirm("Are you sure you want to remove this member ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_household_detail", { householdDetailId: householdDetailId }),
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
        this.grid.getDataTable().ajax.reload();
    },

    reloadFiltered: function (precinctNo) {
        var self = this;
        $('#project_recruitment_detail_datatable input[name="assignedPrecinct"]').val(precinctNo);

        setTimeout(function () {
            self.grid.getDataTable().ajax.reload();
        });
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },


    handleFilterChange: function () {
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reload();
        }, this.state.doneTypingInterval);
    },

    render: function () {
        return (
            <div>

                {this.state.showEditModal &&
                    <VoterEditModal
                        show={this.state.showEditModal}
                        onHide={this.closeEditModal}
                        notify={this.props.notify}
                        voterId={this.state.target}
                        proId={this.props.proId}
                    />
                }

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="project_recruitment_detail_datatable" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Birthdate</th>
                                <th>Relationship</th>
                                <th>Barangay</th>
                                <th>Cellphone #</th>
                                <th>Actions</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voterName" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="birthdate" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="relationship" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="barangayName" onChange={this.handleFilterChange} />
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

window.HouseholdDetailDatatable = HouseholdDetailDatatable;