
var SpecialOperationDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showEditModal: false
        }
    },

    componentDidMount: function () {
        this.initDatatable(this.props.recId);
        console.log('rec id');
        console.log(this.props.recId);
    },

    initDatatable: function (recId) {
        var self = this;
        var grid = new Datatable();

        var project_recruitment_detail_datatable = $("#project_recruitment_detail_datatable");
        var grid_project_event = new Datatable();

        var url = Routing.generate("ajax_get_datatable_special_operation_detail", { recId: recId }, true);

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
                    'targets': [0, 2, 3, 4, 5, 6,7]
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
                        "data": "id",
                        "className": "text-center",
                        "width" : 80,
                        "render": function (data, type, row) {
                            var photoUrl = window.imgUrl + 3 + '_' + row.generated_id_no + "?" + new Date().getTime();
                            return '<img src="' + photoUrl + '" style="width:80px;height:auto;"/><strong style="margin-top:10px;">';
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
                        "data": "voter_group",
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
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";

                            return editBtn + deleteBtn;
                        }
                    }
                ],
            }
        });


        project_recruitment_detail_datatable.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        project_recruitment_detail_datatable.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.edit(data.pro_voter_id);
        });

        self.grid = grid_project_event;
    },

    edit: function (proVoterId) {
        this.setState({ showEditModal: true, target: proVoterId })
    },

    closeEditModal: function () {
        this.reload();
        this.setState({ showEditModal: false, target: null });
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to remove recruit?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_special_operation_detail", { id: id }),
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

                {
                this.state.showEditModal &&
                    <VoterEditModal
                        show={this.state.showEditModal}
                        onHide={this.closeEditModal}
                        notify={this.props.notify}
                        proVoterId={this.state.target}
                        user={this.state.user}
                        proId={this.props.proId}
                        electId={this.props.electId}
                    />
                }


                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="project_recruitment_detail_datatable" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Birthdate</th>
                                <th>Position</th>
                                <th>Barangay</th>
                                <th>Cellphone #</th>
                                <th>Actions</th>
                            </tr>
                            <tr>
                                <td></td>
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

window.SpecialOperationDetailDatatable = SpecialOperationDetailDatatable;