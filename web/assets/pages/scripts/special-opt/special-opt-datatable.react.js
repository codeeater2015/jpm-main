var SpecialOptDatatableComponent = React.createClass({

    getInitialState: function () {
        return {
            showViewModal: false,
            showReleaseModal: false,
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
            src: $("#special_opt_datatable"),
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
                    "url": Routing.generate('ajax_get_special_opt_datatable'), // ajax source
                    "type": 'GET',
                    "data": function (d) {
                        d.electId = self.props.electId;
                        d.proId = self.props.proId;
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
                        "width": 40,
                        "className" : "text-center"
                    },
                    {
                        "data": 'barangay_name',
                        "width": 80,
                        "className": 'text-center'
                    },
                    {
                        "data": 'cellphone',
                        "width": 60,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_members',
                        "width": 60,
                        "className": 'text-center'
                    },
                    {
                        "data": 'updated_at',
                        "width": 150,
                        "className": 'text-center',
                        "render": function (data) {
                            return moment(data).format("MMM DD, YYYY hh:mm A")
                        }
                    },
                    {
                        "data": 'updated_by',
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "width": 120,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs btn-view btn-primary"> View </button>';
                            var deleteBtn = '<button class="btn btn-xs delete-btn btn-danger"> Remove </button>';

                            return editBtn +  deleteBtn;
                        }
                    }
                ]
            }
        });

        $('#special_opt_datatable tbody').on('click', '.btn-view', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openViewModal(data.hdr_id);
        });

        $('#special_opt_datatable tbody').on('click', '.release-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openReleaseModal(data.hdr_id);
        });

        $('#special_opt_datatable tbody').on('click', '.delete-btn', function () {
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

    openReleaseModal: function (id) {
        this.setState({ showReleaseModal: true, target: id });
    },

    closeReleaseModal: function () {
        this.setState({ showReleaseModal: false, target: null });
    },

    remove : function(hdrId){
        var self = this;

       if(confirm("Are you sure you want to remove this item?")){
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_special_opt_header", { hdrId : hdrId }),
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
                    this.state.showViewModal && (
                    <SpecialOptDetailViewModal 
                        show={this.state.showViewModal} 
                        onHide={this.closeViewModal} 
                        hdrId={this.state.target} 
                        provinceCode={this.props.provinceCode}
                        electId={this.props.electId}
                        proId={this.props.proId}
                        notify={this.props.notify}
                    />
                    )
                }

                {
                    // this.state.showReleaseModal && (
                    //     <IdInhouseRequestReleaseModal
                    //         show={this.state.showReleaseModal} 
                    //         onHide={this.closeReleaseModal} 
                    //         hdrId={this.state.target} 
                    //         provinceCode={this.props.provinceCode}
                    //         electId={this.props.electId}
                    //         proId={this.props.proId}
                    //         notify={this.props.notify}
                    //     />
                    // )
                }

                <table id="special_opt_datatable" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>#</td>
                            <td>Name</td>
                            <td>Position</td>
                            <td>Brgy</td>
                            <td>Cellphone</td>
                            <td>Members</td>
                            <td>Last Update</td>
                            <td>Updated By</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="text" className="form-control form-filter input-sm" name="submitted_by" />
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


window.SpecialOptDatatableComponent = SpecialOptDatatableComponent;