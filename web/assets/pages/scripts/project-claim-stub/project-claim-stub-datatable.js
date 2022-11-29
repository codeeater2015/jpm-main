var PrintDatatable = React.createClass({

    render: function () {
        return (
            <div>
                <table id="print-table" className="table table-bordered" >
                    <thead>
                        <tr className="text-center">
                            <td>#</td>
                            <td>Template Description</td>
                            <td></td>
                            <td>Created By</td>
                            <td>Date Created</td>
                            <td>Counts</td>
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
            src: $("#print-table"),
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
                    "url": Routing.generate('ajax_get_project_claim_stub_datatable', { proId: this.props.proId }), // ajax source
                    "type": 'GET'
                },
                "columns": [
                    { "data": "batch_id", "className": "text-center", "width": 50 },
                    {
                        "data": 'template_desc',
                        "className": "dt-body-center"
                    },
                    {
                        "width": 100,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var btnGroup = '<div class="btn-group"><button type="button" class="btn btn-xs blue">Actions</button><button type="button" class="btn btn-xs blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"><i class="fa fa-angle-down"></i></button><ul class="dropdown-menu" role="menu">';
                            btnGroup += '<li><a href="javascript:;" class="btn-claim-stub-vcenter"><i class="fa fa-file-pdf-o"></i> Claim Stub By Voting Center  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-claim-stub-purok"><i class="fa fa-file-pdf-o"></i> Claim Stub By Purok  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-claim-stub-grouping"><i class="fa fa-file-pdf-o"></i> Claim Stub By Grouping  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-dist-list-vcenter"><i class="fa fa-file-pdf-o"></i> Dist List By Voting Center  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-dist-list-purok"><i class="fa fa-file-pdf-o"></i> Dist List By Purok  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-dist-list-grouping"><i class="fa fa-file-pdf-o"></i> Dist List By Grouping  </a></li>';
                            btnGroup += '<li><a href="javascript:;" class="btn-delete"><i class="fa fa-trash"></i> Delete </a></li>';
                            btnGroup += '</ul></div>';
                            return btnGroup;
                        }
                    },
                    {
                        "data": 'created_by',
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": 'created_at',
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": 'total_members',
                        "width": 80,
                        "className": 'text-center'
                    }
                ]
            }
        });


        $('#print-table tbody').on('click', '.btn-claim-stub-vcenter', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showClaimStubByVotingCenter(data.batch_id);
        });

        
        $('#print-table tbody').on('click', '.btn-claim-stub-purok', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showClaimStubByPurok(data.batch_id);
        });

        
        $('#print-table tbody').on('click', '.btn-claim-stub-grouping', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showClaimStubByGrouping(data.batch_id);
        });

        $('#print-table tbody').on('click', '.btn-dist-list-vcenter', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showDistListByVotingCenter(data.batch_id);
        });

        $('#print-table tbody').on('click', '.btn-dist-list-purok', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showDistListByPurok(data.batch_id);
        });

        $('#print-table tbody').on('click', '.btn-dist-list-grouping', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.showDistListByGrouping(data.batch_id);
        });

        $('#print-table tbody').on('click', '.btn-delete', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.deletePrintout(data.batch_id);
        });

        self.grid = grid;
    },

    initSelect2: function () {
        var self = this;
        $.fn.select2.defaults.set("theme", "bootstrap");

        $("#print-table .rcenter-select2").select2({
            casesentitive: false,
            placeholder: "Enter text...",
            allowClear: true,
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('cos_api_select2_rcenters'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.rc_code, text: item.rc_desc };
                        })
                    };
                },
            }
        });

        $('#transmittal-table .rcenter-select2').on('change', function () {
            self.handleFilterChange();
        });
    },


    deletePrintout: function (batchId) {
        var self = this;

        bootbox.confirm('Are you sure you want to delete this template?', function (result) {
            if (result) {
                self.requestDelete = $.ajax({
                    url: Routing.generate('ajax_delete_project_claim_stub', { batchId: batchId }),
                    type: "DELETE"
                }).done(function (res) {
                    self.reload();
                });
            }
        });
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

    showClaimStubByVotingCenter : function(batchId){
        var url = window.reportUrl + "jgg/claim-stub-by-voting-center/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Voting Center', 900, 600);
    },

    showClaimStubByPurok : function(batchId){
        var url = window.reportUrl + "jgg/claim-stub-by-purok/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Purok', 900, 600);
    },

    showClaimStubByGrouping : function(batchId){
        var url = window.reportUrl + "jgg/claim-stub-by-grouping/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Grouping', 900, 600);
    },

    showDistListByVotingCenter : function(batchId){
        var url = window.reportUrl + "jgg/dist-list-by-voting-center/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Voting Center', 900, 600);
    },

    showDistListByPurok : function(batchId){
        var url = window.reportUrl + "jgg/dist-list-by-purok/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Purok', 900, 600);
    },

    showDistListByGrouping : function(batchId){
        var url = window.reportUrl + "jgg/dist-list-by-grouping/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&batch_id=" + batchId;
        this.popupCenter(url, 'Claim Stub By Grouping', 900, 600);
    },

    popupCenter: function (url, title, w, h) {
        // Fixes dual-screen position                         Most browsers      Firefox  
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
        var width = 0;
        var height = 0;

        width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow  
        if (window.focus) {
            newWindow.focus();
        }
    },
});


window.PrintDatatable = PrintDatatable;