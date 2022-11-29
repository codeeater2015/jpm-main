var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var PhotoUploadItemsModal = React.createClass({
    getInitialState: function () {
        return {
            proIdCode: null,
            member: null,
            showAttendeeModal: false,
            showAttendeeBatchModal: false,
            events: [],
            selectedEvent: null,
            showItemEditModal: false,
            targetId: null,
            municipalityName: null,
            baragayName: null,
            brgyNo: null,
            showItemEditModal: false,
            uploadFilter: "UNLINKED"
        }
    },

    render: function () {
        var self = this;

        if (this.state.member != null) {
            var generatedIdNo = this.state.member.generated_id_no;
            var photoUrl = window.imgUrl + this.props.proId + '_' + generatedIdNo + "?" + new Date().getTime();
        }

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-full" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Uploaded Items</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="row">
                        {
                            this.state.showItemEditModal && this.state.targetId != null &&
                            (
                                <PhotoUploadItemEditModal
                                    show={this.state.showItemEditModal}
                                    onHide={this.closeItemEditModal}
                                    itemId={this.state.targetId}
                                    municipalityName={this.state.municipalityName}
                                    barangayName={this.state.barangayName}
                                    brgyNo={this.state.brgyNo}
                                    onSuccess={this.openCropModal}
                                />
                            )
                        }

                        {
                            this.state.showCropModal &&
                            (
                                <VoterCropModal
                                    proId="3"
                                    proVoterId={this.state.data.proVoterId}
                                    itemId={this.state.data.id}
                                    generatedIdNo={this.state.data.generatedIdNo}
                                    show={this.state.showCropModal}
                                    onHide={this.closeCropModal}
                                    onSuccess={this.reloadDatatable}
                                />
                            )
                        }

                        <div className="col-md-12">
                            <div className="mt-radio-inline">
                                <label className="mt-radio">
                                    <input type="radio" name="uploadFilter" onChange={this.setUploadFilter} value="LINKED" checked={this.state.uploadFilter == "LINKED"} /> Linked
                                    <span></span>
                                </label>
                                <label className="mt-radio">
                                    <input type="radio" name="uploadFilter" onChange={this.setUploadFilter} value="UNLINKED" checked={this.state.uploadFilter == "UNLINKED"} /> Unlinked
                                    <span></span>
                                </label>
                               
                                <label className="mt-radio">
                                    <input type="radio" name="uploadFilter" onChange={this.setUploadFilter} value="NOT_FOUND" checked={this.state.uploadFilter == "NOT_FOUND"} />Not FOUND
                                    <span></span>
                                </label>

                                <label className="mt-radio">
                                    <input type="radio" name="uploadFilter" onChange={this.setUploadFilter} value="ALL" checked={this.state.uploadFilter == "ALL"} /> All
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        
                        <div className="col-md-12">

                            <div className="table-container">
                                <div className="table-actions-wrapper">
                                </div>
                                <table id="photo_upload_items_datatable" className="table table-striped table-bordered" width="100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Uploaded</th>
                                            <th>Cropped</th>
                                            <th>filename</th>
                                            <th>Municipality</th>
                                            <th>Barangay</th>
                                            <th>Position</th>
                                            <th>Actions</th>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <select className="form-control form-filter input-sm" name="is_linked" >
                                                    <option value="-1">---</option>
                                                    <option value="0">NO</option>
                                                    <option value="1">YES</option>
                                                </select>
                                            </td>
                                            <td style={{ padding: "10px 5px" }}>
                                                <input type="text" className="form-control form-filter input-sm" name="filename" onChange={this.handleFilterChange} />
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
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

                    </div>
                </Modal.Body>
            </Modal>
        );
    },


    componentDidMount: function () {
        this.initDatatable();
    },


    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        console.log("init datatable");

        var photo_upload_items_datatable = $("#photo_upload_items_datatable");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_datatable_field_upload_items", { id: this.props.id }, true);

        grid_project_event.init({
            src: photo_upload_items_datatable,
            dataTable: {
                "bState": true,
                "autoWidth": true,
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.hdrId = '53';
                        d.filename = $('#photo_upload_items_datatable input[name="filename"]').val();
                        d.hasGeneratedIdNo = $('#photo_upload_items_datatable select[name="is_linked"]').val();
                        d.uploadFilter = self.state.uploadFilter;
                    }
                },
                pageLength: 10,
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
                        "width": 20,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "id",
                        "className": "text-center",
                        "render": function (data, type, row) {
                            let imgUrl = Routing.generate("ajax_get_field_upload_photo", { id: data });
                            return '<img src="' + imgUrl + '" style="width:150px;height:auto;"/><strong style="margin-top:10px;">';
                        }
                    },
                    {
                        "data": "id",
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var photoUrl = window.imgUrl + 3 + '_' + row.generated_id_no + "?" + new Date().getTime();
                            return '<img src="' + photoUrl + '" style="width:150px;height:auto;"/><strong style="margin-top:10px;">';
                        }
                    },
                    {
                        "data": "filename",
                        "className": "text-center",
                        "width": 250
                    },
                    {
                        "data": "municipality_name",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "width": 100,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var cropBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green crop-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-crop' ></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs btn-danger delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";

                            return editBtn + cropBtn + deleteBtn;
                        }
                    }
                ],
            }

        });


        photo_upload_items_datatable.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.edit(data.id, data.municipality_name, data.barangay_name, data.brgy_no);
        });

        photo_upload_items_datatable.on('click', '.crop-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({
                data: {
                    id: data.id,
                    proVoterId: data.pro_voter_id,
                    generatedIdNo: data.generated_id_no
                },
                showCropModal: true
            })
        });

        photo_upload_items_datatable.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },

    edit: function (id, municipalityName, barangayName, brgyNo) {
        this.setState({
            showItemEditModal: true,
            targetId: id,
            municipalityName: municipalityName,
            barangayName: barangayName,
            brgyNo: brgyNo
        });
    },

    delete: function (id) {
        var self = this;

        if (confirm("continue delete?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_field_upload_item", { id: id }),
                type: "DELETE"
            }).done(function (res) {
                self.reloadDatatable();
            });
        }
    },

    closeItemEditModal: function () {
        this.setState({
            showItemEditModal: false,
            targetId: null,
            municipalityName: null,
            barangayName: null,
            brgyNo: null
        },this.reloadDatatable());
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    reloadDatatable: function () {
        this.grid.getDataTable().ajax.reload(null, false);
    },

    openAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: true });
    },

    openCropModal: function (data) {
        this.setState({ showCropModal: true, data: data })
    },

    closeCropModal: function () {
        this.setState({ showCropModal: false, data: null });
    },

    closeAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: false });
    },

    setUploadFilter: function (e) {
        console.log('change upload filter');

        if (e.target.checked) {
            this.setState({ 'uploadFilter': e.target.value }, this.reloadDatatable);
        }
    },
});


window.PhotoUploadItemsModal = PhotoUploadItemsModal;