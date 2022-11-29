var ReceivedSmsDatatable = React.createClass({

    getInitialState : function(){
        return {
            showResponseModal : false,
            proVoterId : null
        }
    },

    render: function () {
        return (
            <div>
                {this.state.showResponseModal && 
                    <ResponseModal 
                        show={this.state.showResponseModal} 
                        onHide={this.closeResponseModal} 
                        proId={this.props.proId} 
                        proVoterId={this.state.proVoterId}/> 
                }
                <table id="sms_received_datatable" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>Id</td>
                            <td>Mobile #</td>
                            <td>Content</td>
                            <td>Municipality</td>
                            <td>Barangay</td>
                            <td>Received At</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        );
    },

    componentDidMount: function () {
        this.initGrid();
    },

    initGrid: function () {
        var grid = new Datatable();
        var self = this;
        grid.init({
            src: $("#sms_received_datatable"),
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
                    "url": Routing.generate('ajax_get_received_sms'), // ajax source
                    "type": 'GET'
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
                        "data": 'MessageFrom',
                        "className": "dt-body-center",
                        "width": 70
                    },
                    {
                        "data": 'MessageText',
                        "className": "text-left",
                        "render" : function(data, type, row){
                            var content = data;

                            if(row.voter_name != '- - - -')
                                content = '<strong>' + row.VoterName + '</strong> : '  + data;

                            return content;
                        }
                    },
                    {
                        "data": 'Municipality',
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": 'Barangay',
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": 'SendTime',
                        "className": "text-center",
                        "render" : function(data){
                            return moment(data).format('lll');
                        }
                    },
                    {
                        "width": 30,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var replyBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark reply-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-commenting' ></i></a>";
                            return (row.ProVoterId != '' && row.ProVoterId != null) ? replyBtn : "";
                        }
                    },
                ]
            }
        });


        $('#sms_received_datatable tbody').on('click', '.reply-button', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.openResponseModal(data.ProVoterId);
        });
     
        self.grid = grid;
    },

    openResponseModal : function(proVoterId){
        this.setState({ showResponseModal : true , proVoterId : proVoterId});
    },

    closeResponseModal : function(){
        this.setState({ showResponseModal : false, proVoterId : null });
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
    }

});


window.ReceivedSmsDatatable = ReceivedSmsDatatable;