var OrganizationSummaryPhotoDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 3000,
            filters: {
                hasId: null,
                hasSubmitted: null,
                assignedPrecinct : null,
                hasAst: null,
                voterGroup: null
            }
        }
    },

    getInitialProp: function () {
        return {
            provinceCode: null,
            municipalityNo: null,
            brgyNo: null,
            precinctNo: null,
            voterGroup: null,
            hasAst : null
        }
    },

    componentDidMount: function () {

        $("#voter_table select[name='hasId']").val(this.props.hasId);
       
        var filters = this.state.filters;
    
        filters.voterGroup = this.props.voterGroup;
        filters.hasId = this.props.hasId;
        filters.precinctNo = this.props.precinctNo;

        if(this.props.assignedPrecinct){
            filters.precinctNo = null;
            filters.assignedPrecinct = this.props.precinctNo;
        }else{
            filters.assignedPrecinct = null;
        }

        this.setState({ filters: filters }, this.gridTable);
    },

    gridTable: function () {
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_table");

        grid.init({
            src: voter_table,
            dataTable: {
                "bState": true,
                "autoWidth": true,
                "serverSide": true,
                "processing": true,
                "deferRender": true,
                "ajax": {
                    "url": Routing.generate('ajax_datatable_organization_summary_item_detail'),
                    "type": "GET",
                    "data": function (d) {
                        d.electId = self.props.electId;
                        d.proId = self.props.proId;
                        d.provinceCode = self.props.provinceCode;
                        d.municipalityNo = self.props.municipalityNo
                        d.brgyNo = self.props.brgyNo;
                        d.precinctNo = self.state.filters.precinctNo;
                        d.assignedPrecinct = self.state.filters.assignedPrecinct;
                        d.voterGroup = self.state.filters.voterGroup;
                        d.voterName = self.state.filters.voterName;
                        d.hasId = self.state.filters.hasId;
                    }
                },
                columnDefs: [
                    {
                        'className': 'text-center valign-middle',
                        'orderable': false,
                        'targets': [0, 3, 4, 5, 6, 7, 8]
                    }
                ],
                "order": [
                    [2, "asc"]
                ],
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
                        "className": "text-center",
                        "width": 90,
                        "render": function (data, type, row) {
                            var photoUrl = window.imgUrl + self.props.proId + '_' + row.pro_id_code + "?" + new Date().getTime();
                            return '<img src="' + photoUrl + '" class="img-responsive"/><strong style="margin-top:10px;">' + data + '</strong>';
                        }
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "has_id",
                        "className": "text-center",
                        "width": 50,
                        "render": function (data) {
                            return data == 1 ? "YES" : "NO";
                        }
                    },
                    
                    {
                        "data": "municipality_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "precinct_no",
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "data": "assigned_precinct",
                        "className": "text-center",
                        "width": 80
                    },
                    {
                        "data": "cellphone",
                        "width": 100
                    }
                ]
            }

        });

        self.grid = grid;
    },

    reload: function () {
        this.grid.getDataTable().ajax.reload();
    },


    handleFilterChange: function (e) {
        var self = this;
        var fieldName = e.target.name;
        var fieldValue = e.target.value;

        self.state.typingTimer = setTimeout(function () {
            var filters = self.state.filters;

            filters[fieldName] = fieldValue;
            self.setState({ filters: filters }, self.reload);
        }, self.state.doneTypingInterval);
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined";
    },

    render: function () {

        var data = this.isEmpty(this.grid) ? {} : this.grid.getDataTable().ajax.params();
        var downloadUrl = Routing.generate("ajax_datatable_organization_download_excel", data);

        return (
            <div className="row">
                <div className="col-md-12">
                    <div className="text-right">
                        <a className="btn btn-primary btn-sm" href={downloadUrl} target="_blank"><i className="fa fa-download"></i> Download Excel File</a>
                    </div>
                    <table id="voter_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>ID</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Precinct</th>
                                <th>Assign Precinct</th>
                                <th>Cellphone No</th>
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
                                    <select name="hasId" onChange={this.handleFilterChange} className="input-sm" style={{ marginTop: "2px" }}>
                                        <option value=''>All</option>
                                        <option value='1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                </td>
                               
                                <td>
                                </td>
                                <td>
                                </td>
                                <td>
                                </td>
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

window.OrganizationSummaryPhotoDatatable = OrganizationSummaryPhotoDatatable;