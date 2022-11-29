var ProjectRecruitmentDetailDatatable = React.createClass({

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
    },

    initDatatable: function (recId) {
        var self = this;
        var grid = new Datatable();

        var project_recruitment_detail_datatable = $("#project_recruitment_detail_datatable");
        var grid_project_event = new Datatable();

        var url = Routing.generate("ajax_datatable_recruitment_member", { recId : recId }, true);

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
                        d.proId = self.props.proId;
                        d.voterName = $('#project_recruitment_detail_datatable input[name="voterName"]').val();
                        d.voterGroup = $('#project_recruitment_detail_datatable input[name="voterGroup"]').val();
                        d.barangayName = $('#project_recruitment_detail_datatable input[name="barangayName"]').val();
                        d.precinctNo = $('#project_recruitment_detail_datatable input[name="precinctNo"]').val();
                        d.assignedPrecinct = $('#project_recruitment_detail_datatable input[name="assignedPrecinct"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
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
                        "width": 20 ,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "voter_name",
                        "className": "text-center",
                        "width": 150 ,
                        "render": function (data, type, row) {
                            var photoUrl = window.imgUrl + self.props.proId + '_' + row.pro_id_code + "?" + new Date().getTime();
                            return '<img src="' + photoUrl + '" style="width:80px;height:auto;"/><strong style="margin-top:10px;"><br/>' + data + '</strong>';
                        }
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 30,
                    },
                    {
                        "data": "is_1",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is1" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_2",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is2" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_3",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is3" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_4",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is4" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_5",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is5" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_6",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is6" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_7",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="is7" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.rec_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "precinct_no",
                        "className": "text-center",
                        "width": "10px",
                        "width": 60
                    },
                    {
                        "data": "assigned_precinct",
                        "className": "text-center",
                        "width": "10px",
                        "width": 60
                    },
                    {
                        "data": "cellphone",
                        "className": "text-center",
                        "width": 100,
                    },

                    {
                        "width": 60,
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";

                            return editBtn + deleteBtn;
                        }
                    }
                ],
            }
        });


        project_recruitment_detail_datatable.on('click','.status-checkbox',function(e){
            var recDetailId = e.target.value;
            var checked = e.target.checked;
            var fieldName = e.target.name;
            var newValue = checked ? 1 : 0;

            if(recDetailId != null && checked != null){
                self.patchStatus(recDetailId,fieldName,newValue);
            }
        });

        project_recruitment_detail_datatable.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.rec_detail_id);
        });

        project_recruitment_detail_datatable.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.edit(data.voter_id);
        });

        self.grid = grid_project_event;
    },

    patchStatus: function (recDetailId, fieldName, value) {
        var self = this;
        var data = {};

        data[fieldName] = value;
        self.requestToggleRequirement = $.ajax({
            url: Routing.generate("ajax_patch_recruitment_detail_status", { recDetailId: recDetailId }),
            type: "PATCH",
            data: (data)
        }).done(function (res) {
            console.log("requirement patched");
        });
    },


    edit: function (voterId) {
        this.setState({ showEditModal: true, target: voterId })
    },

    closeEditModal: function () {
        this.reload();
        this.setState({ showEditModal: false, target: null });
    },

    delete: function (recDetailId) {
        var self = this;

        if (confirm("Are you sure you want to remove this member ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_project_recruitment_detail", { recDetailId : recDetailId }),
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
        var self =  this;
        $('#project_recruitment_detail_datatable input[name="assignedPrecinct"]').val(precinctNo);

        setTimeout(function(){
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
                                <th>POS</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>Barangay</th>
                                <th>Precinct</th>
                                <th>Assigned</th>
                                <th>CP</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voterName" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="voterGroup" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="barangayName" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="precinctNo" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="assignedPrecinct" onChange={this.handleFilterChange} />
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

window.ProjectRecruitmentDetailDatatable = ProjectRecruitmentDetailDatatable;