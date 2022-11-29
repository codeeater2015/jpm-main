var SpecialOptDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEditModal: false,
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
            src: $("#special_opt_detail_datatable"),
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
                    "url": Routing.generate('ajax_get_special_opt_detail_datatable'), // ajax source
                    "type": 'GET',
                    "data": function (d) {
                        d.hdrId = self.props.hdrId;
                        d.voterName = $('#special_opt_detail_datatable input[name="voterName"]').val();
                        d.barangayName = $('#special_opt_detail_datatable input[name="barangayName"]').val();
                        d.hasPhoto = $('#special_opt_detail_datatable select[name="hasPhotoFilter"]').val();
                        d.hasId = $('#special_opt_detail_datatable select[name="hasIdFilter"]').val();
                        d.status = $('#special_opt_detail_datatable select[name="statusFilter"]').val();
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
                        "data": "voter_name",
                        "className": "text-center"
                    },
                    {
                        "data": 'barangay_name',
                        "width": 100
                    },
                    {
                        "data": 'voter_group',
                        "width": 50,
                        "className": 'text-center'
                    },
                   
                    {
                        "data": 'created_at',
                        "className": "text-center",
                        "width": 150,
                        "render": function (data) {
                            return moment(data).format("MMM DD, YYYY hh:mm A")
                        }
                    },
                    {
                        "data": 'created_by',
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "width": 120,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs edit-btn btn-primary"> Edit </button>';
                            var deleteBtn = '<button class="btn btn-xs delete-btn btn-danger"> Remove </button>';

                            return editBtn + deleteBtn;
                        }
                    },
                ]
            }
        });


        $('#special_opt_detail_datatable tbody').on('click', '.edit-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openEditModal(data.voter_id);
        });

        $('#special_opt_detail_datatable tbody').on('click', '.delete-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.remove(data.dtl_id);
        });

        self.grid = grid;
    },

    openEditModal: function (id) {
        this.setState({ showEditModal: true, target: id });
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    remove : function(dtlId){
        var self = this;

       if(confirm("Are you sure you want to remove this item?")){
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_special_opt_detail", { dtlId: dtlId }),
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
                    // this.state.showEditModal &&
                    // <VoterEditModal
                    //     show={this.state.showEditModal}
                    //     onHide={this.closeEditModal}
                    //     notify={this.props.notify}
                    //     voterId={this.state.target}
                    //     proId={this.props.proId}
                    // />
                }
                <table id="special_opt_detail_datatable" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>#</td>
                            <td>Voter Name</td>
                            <td>Barangay</td>
                            <td>Position</td>
                            <td>Created At</td>
                            <td>Added By</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="voterName" />
                            </td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="barangayName" />
                            </td>
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


window.SpecialOptDetailDatatable = SpecialOptDetailDatatable;