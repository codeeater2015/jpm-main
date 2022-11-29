var VoterHistoryDatatable = React.createClass({

    getInitialState : function(){
      return {
          typingTimer : null,
          doneTypingInterval : 1500
      }
    },

    componentDidMount : function(){
        this.gridTable();
    },
   
    gridTable : function(){
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_history_table");

        grid.init({
            src: voter_table,
            dataTable : {
                "bState" : true,
                "autoWidth": true,
                "serverSide": true,
                "processing" : true,
                "deferRender" : true,
                "dom": '<"top">rt<"bottom"lp><"clear">',
                "ajax" : {
                    "url" : Routing.generate('ajax_datatable_voter_history',{ voterId : self.props.voterId }),
                    "type" : "GET"
                },
                columnDefs : [
                    {
                        'className': 'text-center valign-middle',
                        'orderable' : false,
                        'targets' : [0,1,2,3,4,5,6,7,8]
                    }
                ],
                "order": [
                    [9, "desc"]
                ],
                "columns" : [
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data" : "precinct_no",
                        "className" : "text-center",
                        "width" : "80px"
                    },
                    {
                        "data" : "municipality_name",
                        "width" : "150px"
                    },
                    {
                        "data" : "barangay_name",
                        "className" : "text-center",
                        "width" : "100px"
                    },
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : "30px",
                        "render" : function(data,type,row){
                            var voterStatus  = '';
                            
                            voterStatus += row.has_ast == 1 ? "*" : "";
                            voterStatus += row.has_a == 1 ? "A" : "";
                            voterStatus += row.has_b == 1 ? "B" : "";
                            voterStatus += row.has_c == 1 ? "C" : "";

                            return voterStatus;
                        }
                    },
                    {
                        "data" : null,
                        "className" : "text-center",
                        "width" : "10px",
                        "render" : function(data,type,row){
                            var voterClass  = '';
                            
                            voterClass += row.is_1 == 1 ?  "1," : "";
                            voterClass += row.is_2 == 1 ?  "2," : "";
                            voterClass += row.is_3 == 1 ?  "3," : "";
                            voterClass += row.is_4 == 1 ?  "4," : "";
                            voterClass += row.is_5 == 1 ?  "5," : "";
                            voterClass += row.is_6 == 1 ?  "6," : "";
                            voterClass += row.is_7 == 1 ?  "7,"  : "";
                            voterClass = voterClass.slice(0,voterClass.lastIndexOf(","));

                            return voterClass;
                        }
                    },
                    
                    {
                        "data" : "voted_2017",
                        "className" : "text-center",
                        "width" : "10px",
                        "render" : function(data,type,row){
                            return data == 1 ? "YES" : "NO";
                        }
                    },
                    {
                        "data" : "cellphone_no",
                        "className" : "text-center",
                        "width" : "90px"
                    },
                    {
                        "data" : "created_by",
                        "width" : "140px",
                        "className" : "text-center"
                    },
                    {
                        "data" : "created_at",
                        "width" : "140px",
                        "className" : "text-center",
                        "render" : function(data){
                            return moment(data).format('MM/DD/YYYY hh:mm A')
                        }
                    }
                ]
            }

        });

        voter_table.on( 'click', '.edit-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.edit(data.voter_id);
        });

        voter_table.on( 'click', '.view-btn', function () {
            var data =  grid.getDataTable().row($(this).parents('tr') ).data();
            self.view(data.voter_id);
        });


        self.grid = grid;
    },

    reload : function(){
        this.grid.getDataTable().ajax.reload();
    },

    render : function(){
        return (
            <div className="col-md-12">
                <div className="table-container">
                    <div className="table-actions-wrapper">
                        <span> </span>
                    </div>
                    <table id="voter_history_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Precinct</th>
                                <th>Mun</th>
                                <th>Brgy</th>
                                <th>Stat</th>
                                <th>Tag</th>
                                <th>16</th>
                                <th>CP</th>
                                <th>Updated by</th>
                                <th>Updated At</th>
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

window.VoterHistoryDatatable = VoterHistoryDatatable;