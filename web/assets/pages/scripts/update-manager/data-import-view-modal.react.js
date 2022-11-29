var Modal = ReactBootstrap.Modal;

var DataImportViewModal = React.createClass({

    componentDidMount: function () {
        this.initGrid();
    },

    initGrid: function () {
        var grid = new Datatable();
        var self = this;

        grid.init({
            src: $("#data-import-details-datatable"),
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options
                "bState": true,
                "autoWidth": false,
                "serverSide": true,
                "processing": true,
                "deferRender": true,
                "ordering" : false,
               
                "searchDelay": 2000,
                "language": {
                    "processing": '<i class="fa fa-spinner fa-pulse fa-fw"></i><span > Loading...</span>.'
                },
                "lengthMenu": [
                    [10, 20, 50, 100, 150],
                    [10, 20, 50, 100, 150] // change per page values here
                ],
                "pageLength": 10, // default record count per page
                "ajax": {
                    "url": Routing.generate('ajax_get_data_import_detail_datatable'), // ajax source
                    "type": 'GET',
                    "data": function (d) {
                        d.hdrId = self.props.hdrId;
                        d.voterName = $('#data-import-details-datatable input[name="import_voter_name"]').val();
                        d.status = $('#data-import-details-datatable select[name="import_status"]').val();
                    }
                },
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
                        "data": 'voter_name'
                    },
                    {
                        "data": 'voter_group',
                        "width": 50,
                        "className": 'text-center'
                    },
                    {
                        "data": 'has_id',
                        "width": 30,
                        "className": 'text-center',
                        "render": function (data) {
                            return data == 1 ? "YES" : "NO";
                        }
                    },
                    {
                        "data": 'cellphone',
                        "width": 50,
                        "className": 'text-center'
                    },
                    {
                        "data": 'updated_at',
                        "className": "text-center",
                        "width": 150,
                        "render": function (data) {
                            return moment(data).format("MMM DD, YYYY hh:mm A")
                        }
                    },
                    {
                        "data": 'updated_by',
                        "className": "text-center",
                        "width": 90
                    },
                    {
                        "width": 30,
                        "data": 'status',
                        "className": "text-center",
                        "render": function (data) {
                            var statusText = "";

                            switch (data) {
                                case 'A':
                                    statusText = 'IMPORTED';
                                    break;
                                case 'C':
                                    statusText = 'SKIPPED';
                                    break;
                                default:
                                    statusText = 'UNKNOWN STATUS';
                            }

                            return statusText;
                        }
                    },
                ]
            }
        });

        self.grid = grid;
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-full" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Data Import Information</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    <div className="col-md-12">
                        <div className="table-container">
                            <div className="table-actions-wrapper">
                            </div>

                            <table id="data-import-details-datatable" className="table table-bordered" >
                                <thead>
                                    <tr className="text-center">
                                        <td>#</td>
                                        <td>Name</td>
                                        <td>Position</td>
                                        <td>ID</td>
                                        <td>Cellphone</td>
                                        <td>Updated At</td>
                                        <td>Updated By</td>
                                        <td>Status</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <input type="text" className="form-control form-filter input-sm" name="import_voter_name" />
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <select name="import_status" className="input-sm" style={{ marginTop: "2px" }}>
                                                <option value=''>All</option>
                                                <option value='A'>Imported</option>
                                                <option value='C'>Skipped</option>
                                            </select>
                                        </td>
                                        <td style={{ width : "30px" }}>
                                            <button className="btn btn-xs green btn-outline filter-submit">
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
                </Modal.Body>
            </Modal>
        );
    }
});


window.DataImportViewModal = DataImportViewModal;