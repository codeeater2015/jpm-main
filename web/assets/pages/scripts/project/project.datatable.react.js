var ProjectDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
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
        var grid = new Datatable();

        var project_table = $("#project_table");
        var grid_project = new Datatable();
        var url = Routing.generate("ajax_get_datatable_project", {}, true);

        grid_project.init({
            src: project_table,
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
                    'targets': [0, 2, 3, 4]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3, 4]
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
                    { "data": "pro_name" },
                    { "data": "province_name" },
                    { "data": "pro_desc" },
                    {
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark project-edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo project-delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return editBtn + deleteBtn;
                        }
                    }
                ],
            }
        });

        project_table.on('click', '.project-edit-button', function () {
            var data = grid_project.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.pro_id });
        });


        project_table.on('click', '.project-delete-button', function () {
            var data = grid_project.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.pro_id);
        });

        self.grid = grid_project;
    },

    closeEditModal : function(){
        this.setState({ showEditModal : false, target : null });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    delete: function (proId) {
        var self = this;

        if (confirm("Are you sure you want to delete this project ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_project", { proId: proId }),
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

    render: function () {
        return (
            <div>
                {
                    this.state.showCreateModal &&
                    <ProjectCreateModal show={this.state.showCreateModal} notify={this.props.notify} reload={this.reload} onHide={this.closeCreateModal} />
                }

                {
                    this.state.showEditModal &&
                    <ProjectEditModal proId={this.state.target} show={this.state.showEditModal} notify={this.props.notify} reload={this.reload} onHide={this.closeEditModal} />
                }

                <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>Add Project</button>
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="project_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Province</th>
                                <th>Description</th>
                                <th width="80px"></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="idx_no" onChange={this.handleFilterChange} />
                                </td>
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

window.ProjectDatatable = ProjectDatatable;