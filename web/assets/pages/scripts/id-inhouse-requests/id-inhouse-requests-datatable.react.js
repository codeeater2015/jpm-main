var InhouseRequestsDatatable = React.createClass({

    getInitialState: function () {
        return {
            showViewModal: false,
            showPrintModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500
        }
    },

    componentDidMount: function () {
        this.initGrid();
    },

    initGrid: function () {
        var grid = new Datatable();
        var self = this;

        grid.init({
            src: $("#id_inhouse_request_datatable"),
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options
                'ordering': false,
                "serverSide": true,
                "processing": true,
                "searching": false,
                "deferRender": true,
                "autoWidth": false,
                "dom": '<"top"fpli>rt<"bottom"pli><"clear">',
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
                    "url": Routing.generate('ajax_get_id_request_datatable'), // ajax source
                    "type": 'GET',
                    "data": function (d) {
                        d.electId = self.props.electId;
                        d.proId = self.props.proId;
                        d.submittedBy = $('#id_inhouse_request_datatable input[name="submitted_by"]').val();
                        d.municipalityName = $('#id_inhouse_request_datatable input[name="municipality_name"]').val();
                        d.barangayName = $('#id_inhouse_request_datatable input[name="barangay_name"]').val();
                        d.submittedAt = $('#id_inhouse_request_datatable input[name="submitted_at"]').val();
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
                        "data": 'submitted_by'
                    },

                    {
                        "data": 'municipality_name',
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "data": 'barangay_name',
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "data": 'submitted_at',
                        "width": 100,
                        "className": 'text-center',
                        "render" : function(data){
                            return moment(data).format("MMM DD, YYYY");
                        }
                    },
                    {
                        "data": 'total_received',
                        "width": 30,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_photo',
                        "width": 30,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_id',
                        "width": 30,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_released',
                        "width": 30,
                        "className": 'text-center'
                    },
                    {
                        "data": 'cellphone',
                        "width": 80,
                        "className": 'text-center'
                    },
                    {
                        "width": 180,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs btn-view btn-primary"> View </button>';
                            var printBtn = '<button class="btn btn-xs print-btn btn-info"> Print </button>';
                            var deleteBtn = '<button class="btn btn-xs delete-btn btn-danger"> Remove </button>';

                            return editBtn + printBtn +  deleteBtn;
                        }
                    }
                ]
            }
        });

        $('#id_inhouse_request_datatable tbody').on('click', '.btn-view', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openViewModal(data.hdr_id);
        });

        $('#id_inhouse_request_datatable tbody').on('click', '.print-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openPrintModal(data.hdr_id);
        });

        $('#id_inhouse_request_datatable tbody').on('click', '.delete-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.remove(data.hdr_id);
        });


        self.grid = grid;
    },

    openViewModal: function (id) {
        this.setState({ showViewModal: true, target: id });
    },

    closeViewModal: function () {
        this.setState({ showViewModal: false, target: null });
    },

    openPrintModal: function (id) {
        this.setState({ showPrintModal: true, target: id });
    },

    closePrintModal: function () {
        this.setState({ showPrintModal: false, target: null });
    },

    remove : function(hdrId){
        var self = this;

       if(confirm("Are you sure you want to remove this item?")){
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_id_request_header", { hdrId : hdrId }),
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
                {this.state.showViewModal && (
                    <IdInhouseRequestDetailViewModal 
                        show={this.state.showViewModal} 
                        onHide={this.closeViewModal} 
                        hdrId={this.state.target} 
                        provinceCode={this.props.provinceCode}
                        electId={this.props.electId}
                        proId={this.props.proId}
                        notify={this.props.notify}
                    />
                )}

                {this.state.showPrintModal && (
                    <IdInhouseRequestPrintModal
                        show={this.state.showPrintModal} 
                        onHide={this.closePrintModal} 
                        hdrId={this.state.target} 
                        provinceCode={this.props.provinceCode}
                        electId={this.props.electId}
                        proId={this.props.proId}
                        notify={this.props.notify}
                    />
                )}

                <table id="id_inhouse_request_datatable" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>#</td>
                            <td>Submitted By</td>
                            <td>Mun</td>
                            <td>Brgy</td>
                            <td>Received At</td>
                            <td>RECV</td>
                            <td>PHOTO</td>
                            <td>ID</td>
                            <td>REL</td>
                            <td>Cellphone</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="submitted_by" />
                            </td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="municipality_name" />
                            </td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="barangay_name" />
                            </td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="submitted_at" />
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td className="text-right">
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
        );
    }
});


window.InhouseRequestsDatatable = InhouseRequestsDatatable;