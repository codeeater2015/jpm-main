var ProjectEventDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showEditModal: false
        }
    },

    componentDidMount: function () {
        this.initDatatable(this.props.eventId);
    },

    initDatatable: function (eventId) {
        var self = this;
        var grid = new Datatable();

        var project_event_detail_datatable = $("#project_event_detail_datatable");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_datatable_event_member", { eventId: eventId }, true);

        grid_project_event.init({
            src: project_event_detail_datatable,
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
                        d.voterName = $('#project_event_detail_datatable input[name="voterName"]').val();
                        d.voterGroup = $('#project_event_detail_datatable input[name="voterGroup"]').val();
                        d.hasAttended = $('#project_event_detail_datatable select[name="hasAttendedFilter"]').val();
                        d.hasNewId = $('#project_event_detail_datatable select[name="hasNewIdFilter"]').val();
                        d.hasClaimed = $('#project_event_detail_datatable select[name="hasClaimedFilter"]').val();
                        d.barangayName = $('#project_event_detail_datatable input[name="barangayName"]').val();
                        d.precinctNo = $('#project_event_detail_datatable input[name="precinctNo"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
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
                            var photoUrl = window.imgUrl + self.props.proId + '_' + row.generated_id_no + "?" + new Date().getTime();
                            var lastEvent = "No Event";
                            if(row['last_event'] != null){
                                lastEvent = row['last_event'].event_name + " - Event Date : " + row['last_event'].event_date; 
                            }
                            return '<img src="' + photoUrl + '" style="width:80px;height:auto;"/><strong style="margin-top:10px;"><br/>' + data + '</strong> <br/><span>'+ lastEvent  +'</span>';
                        }
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 30,
                    },
                    {
                        "data": "has_attended",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="hasAttended" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.event_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "has_new_id",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="hasNewId" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.event_detail_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "has_claimed",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="hasClaimed" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.event_detail_id + '"></input><span></span></label>';
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
                        "data": "cellphone",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "is_1",
                        "className": "text-center",
                        "width": 70,
                        "render" : function(data,type,row){
                            
                            var is1 = parseInt(row.is_1) == 1 ? 1 : "";
                            var is2 = parseInt(row.is_2) == 1 ? 2 : "";
                            var is3 = parseInt(row.is_3) == 1 ? 3 : "";
                            var is4 = parseInt(row.is_4) == 1 ? 4 : "";
                            var is5 = parseInt(row.is_5) == 1 ? 5 : "";
                            var is6 = parseInt(row.is_6) == 1 ? 6 : "";
                            var is7 = parseInt(row.is_7) == 1 ? 7 : "";
                            var is8 = parseInt(row.is_8) == 1 ? 8 : "";
                            var is9 = parseInt(row.is_9) == 1 ? 9 : "";
                            var is10 = parseInt(row.is_10) == 1 ? 10 : ""; 
                            
                            return  is1 + " " + is2 + " " + is3 + " " + is4 + " " + is5 + " " + is6 + " " + is7 + " " + is8 + " " + is9 + " " + is10;
                        }
                    },

                    {
                        "data": "with_stub",
                        "className": "text-center",
                        "width": 20,
                        "render" : function(data,type,row){
                            return parseInt(data) == 1 ? "YES" : "NO";
                        }
                    },

                    {
                        "width": 100,
                        "className" : "text-center",
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";

                            return editBtn + deleteBtn;
                        }
                    }
                ],
            }
        });


        project_event_detail_datatable.on('click','.status-checkbox',function(e){
            var eventDetailId = e.target.value;
            var checked = e.target.checked;
            var fieldName = e.target.name;
            var newValue = checked ? 1 : 0;

            if(eventDetailId != null && checked != null){
                self.patchStatus(eventDetailId,fieldName,newValue);
            }
        });

        project_event_detail_datatable.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.event_detail_id);
        });

        project_event_detail_datatable.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.edit(data.pro_voter_id);
        });

        self.grid = grid_project_event;
    },

    patchStatus: function (eventDetailId, fieldName, value) {
        var self = this;
        var data = {};

        data[fieldName] = value;
        self.requestToggleRequirement = $.ajax({
            url: Routing.generate("ajax_patch_event_detail_status", { eventDetailId: eventDetailId }),
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

    delete: function (eventDetailId) {
        var self = this;

        if (confirm("Are you sure you want to remove this member ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_project_event_detail", { eventDetailId: eventDetailId }),
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
        $('#project_event_detail_datatable input[name="assignedPrecinct"]').val(precinctNo);

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
                        proVoterId={this.state.target}
                        proId={this.props.proId}
                    />
                }

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="project_event_detail_datatable" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>POS</th>
                                <th>ATD</th>
                                <th>PHOTO</th>
                                <th>DIST</th>
                                <th>Barangay</th>
                                <th>Precinct</th>
                                <th>CP</th>
                                <th>Tag</th>
                                <th>Stub</th>
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
                                <td>
                                    <select name="hasAttendedFilter" onChange={this.handleFilterChange} className="input-sm" style={{ marginTop: "2px" }}>
                                        <option value=''>All</option>
                                        <option value='1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="hasNewIdFilter" onChange={this.handleFilterChange} className="input-sm" style={{ marginTop: "2px" }}>
                                        <option value=''>All</option>
                                        <option value='1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="hasClaimedFilter" onChange={this.handleFilterChange} className="input-sm" style={{ marginTop: "2px" }}>
                                        <option value=''>All</option>
                                        <option value='1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="barangayName" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="precinctNo" onChange={this.handleFilterChange} />
                                </td>
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

window.ProjectEventDetailDatatable = ProjectEventDetailDatatable;