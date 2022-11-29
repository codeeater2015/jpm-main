var DataImportDatatable = React.createClass({

    getInitialState: function () {
        return {
            showViewModal: false,
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
            src: $("#data-import-datatable"),
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options
                'ordering': false,
                "serverSide": true,
                "processing": true,
                "searching" : false,
                "deferRender": true,
                "autoWidth": true,
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
                    "url": Routing.generate('ajax_get_data_import_datatable'), // ajax source
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
                        "data": 'data_source',
                        "width": 40,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_count',
                        "width": 50,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_processed',
                        "width": 50,
                        "className": 'text-center'
                    },
                    {
                        "data": 'total_imported',
                        "width": 50,
                        "className": 'text-center'
                    },
                    {
                        "data": 'created_at',
                        "className": "text-center",
                        "width": 80,
                        "render" : function(data){
                            return moment(data).format("MMM DD, YYYY hh:mm A")
                        }
                    },
                    {
                        "data": 'created_by',
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "width": 40,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs btn-view btn-primary"> View </button>';
                            return editBtn;
                        }
                    },
                ]
            }
        });
      

        $('#data-import-datatable tbody').on('click', '.btn-view', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openViewModal(data.hdr_id);
        });

        self.grid = grid;
    },

    openViewModal : function (id) {
        console.log("open view modal");
        this.setState({ showViewModal : true, target : id });        
    },

    closeViewModal : function(){
        this.setState({ showViewModal : false, target : null });
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
                    <DataImportViewModal show={this.state.showViewModal} onHide={this.closeViewModal} hdrId={this.state.target}/>
                )}
                <table id="data-import-datatable" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>#</td>
                            <td>Data Source</td>
                            <td>Total Records</td>
                            <td>Processed</td>
                            <td>Imported</td>
                            <td>Date</td>
                            <td>User</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody> 
                    </tbody>
                </table>
            </div>
        );
    }
});


window.DataImportDatatable = DataImportDatatable;