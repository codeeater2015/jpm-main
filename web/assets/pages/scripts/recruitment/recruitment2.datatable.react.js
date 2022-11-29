var Recruitment2Datatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showRecruitsModal: false,
            showHouseholdMemberModal : false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,

            header: {
                voterName: "",
                voterGroup: "",
                barangayName: "",
                position: "",
                municipalityName: "",
                cellphone: "",
                lgc: {
                    voter_name: "",
                    cellphone: ""
                }
            },
            otherInfoLoaded: false,
            loadingInfo: false,
            otherInfo: {
                householdInfo: {
                    totalMembers: 0,
                    totalVoter: 0,
                    totalNonVoter: 0,
                    totalWithCp: 0
                },
                recInfo: {
                    totalMembers: 0,
                    totalVoter: 0,
                    totalNonVoter: 0,
                    totalWithCp: 0
                }
            }
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var recruitment_table = $("#recruitment_table");
        var grid_project_recruitment = new Datatable();
        var url = Routing.generate("ajax_get_datatable_recruitment_header", {}, true);

        grid_project_recruitment.init({
            src: recruitment_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "dom": "pit",
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.voterName = $('#recruitment_table input[name="voter_name"]').val();
                        d.municipalityName = $('#recruitment_table input[name="municipality_name"]').val();
                        d.barangayName = $('#recruitment_table input[name="barangay_name"]').val();
                        d.voterGroup = $('#recruitment_table input[name="voter_group"]').val();
                        d.electId = self.props.electId;
                        d.provinceCode = self.props.provinceCode;
                        d.municipalityNo = self.props.municipalityNo;
                        d.brgyNo = self.props.brgyNo;

                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0,2]
                }, {
                    'className': 'align-center',
                    'targets': [2]
                }],
                "order": [
                    [1, "asc"]
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
                        "render": function (data, type, row) {
                            return "<a class='hover-button' href='javascript:void(0)'>" + data + "</a>";
                        }
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 30
                    }
                ],
            }
        });


        recruitment_table.on('click', '.recruits-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showRecruitsModal: true, target: data.id });
        });

        recruitment_table.on('click', '.delete-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        recruitment_table.on('click', '.hover-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            console.log("mouse entering");
            self.displayPreview(data.id);
        });

        self.grid = grid_project_recruitment;
    },

    initHouseholdDatatable: function () {
        var self = this;
        var grid2 = new Datatable();

        var household_table = $("#household_table");
        var grid_project_household = new Datatable();
        var url = Routing.generate("ajax_get_datatable_household_header_no_recruitment", {}, true);

        grid_project_household.init({
            src: household_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "dom": "pit",
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.voterName = $('#household_table input[name="voter_name"]').val();
                        d.municipalityName = $('#household_table input[name="municipality_name"]').val();
                        d.barangayName = $('#household_table input[name="barangay_name"]').val();
                        d.voterGroup = $('#household_table input[name="voter_group"]').val();
                        d.electId = self.props.electId;
                        d.provinceCode = self.props.provinceCode;
                        d.municipalityNo = self.props.municipalityNo;
                        d.brgyNo = self.props.brgyNo;

                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0]
                }],
                "order": [
                    [1, "asc"]
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
                        "render": function (data, type, row) {
                            return "<a class='hover-button' href='javascript:void(0)'>" + data + "</a>";
                        }
                    }
                ],
            }
        });

        
        household_table.on('click', '.hover-button', function () {
            var data = grid_project_household.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showHouseholdMemberModal: true, householdTarget: data.id });
        });

        self.grid2 = grid_project_household;
    },

    displayPreview: function (id) {
        console.log("displaying preview");
        console.log(id);

        this.setState({ showPreview: true, target: id });
        this.loadHeader(id);
    },

    loadHeader: function (id) {
        var self = this;

        self.requestRecruiter = $.ajax({
            url: Routing.generate("ajax_get_recruitment_header_full", { recId: id }),
            type: "GET"
        }).done(function (res) {
            self.setState({ header: res, otherInfoLoaded: false, loadingInfo: false });
        });
    },

    loadOtherInfo: function () {
        var self = this;

        self.requestRecruitOtherInfo = $.ajax({
            url: Routing.generate("ajax_get_recruitment_other_info", { proIdCode: self.state.header.proIdCode }),
            type: "GET"
        }).done(function (res) {
            self.setState({ otherInfo: res, otherInfoLoaded: true, loadingInfo: false });
        });

        this.setState({ loadingInfo: true });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    openCreateKCL1Modal: function () {
        this.setState({ showCreateKCL1Modal: true });
    },

    closeCreateKCL1Modal: function () {
        this.setState({ showCreateKCL1Modal: false, target: null });
    },

    closeRecruitsModal: function () {
        this.setState({ showRecruitsModal: false, target: null });
    },

    closeHouseholdMemberModal: function () {
        this.setState({ showHouseholdMemberModal: false, householdTarget: null });
    },

    delete: function (recId) {
        var self = this;

        if (confirm("Are you sure you want to delete this record ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_recruitment_header", { recId: recId }),
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


    handleFilter2Change: function () {
        var self = this;

        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reloadHousehold();
        }, this.state.doneTypingInterval);
    },

    reload: function () {
        if (this.grid != null) {
            console.log("i am reloading");
            this.grid.getDataTable().ajax.reload();
        }
    },

    reloadHousehold: function () {
        if (this.grid2 != null) {
            console.log("i am reloading");
            this.grid2.getDataTable().ajax.reload();
        }
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        var header = this.state.header;
        var otherInfo = this.state.otherInfo;

        var self = this;

        if (this.state.target != null) {
            var generatedIdNo = this.state.header.generatedIdNo;
            var photoUrl = window.imgUrl + 3 + '_' + generatedIdNo + "?" + new Date().getTime();
        }

        return (
            <div >

                <div className="row">
                    <div className="col-md-4">
                        <div className="portlet light portlet-fit bordered">
                            <div className="table-container">
                                <table id="recruitment_table" className="table table-striped table-bordered" width="100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Recruiter Name</th>
                                            <th>Pos</th>
                                            
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td style={{ padding: "10px 5px" }}>
                                                <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                            </td>
                                            <td style={{ padding: "10px 5px" }}>
                                                <input type="text" className="form-control form-filter input-sm" name="voter_group" onChange={this.handleFilterChange} />
                                            </td>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div className="col-md-4">
                        <div className="portlet light profile-sidebar-portlet ">
                            <div className="profile-userpic">
                                <div className="row">
                                    <div className="col-md-6 col-md-offset-3">
                                        <img src={photoUrl} className="img-responsive" alt="" />
                                    </div>
                                </div>
                            </div>
                            <div className="profile-usertitle">
                                <div className="profile-usertitle-name text-center">  <strong>{header.voterName}</strong></div>
                                <div className="profile-usertitle-job  text-center"> <em>{header.voterGroup}</em> </div>
                            </div>

                            <div className="profile-usermenu">
                                <br />
                                <div><strong>Cellphone : </strong>{self.isEmpty(header.cellphone) ? "No contact #" : header.cellphone}</div>
                                <div><strong>Barangay Position : </strong>{self.isEmpty(header.position) ? "N/A" : header.position}</div>
                            </div>

                            <div>
                                <div className="portlet-body">
                                    <div><strong>Municipality : </strong>{header.municipalityName}</div>
                                    <div><strong>Barangay : </strong>{header.barangayName}</div>
                                    <div><strong>Precinct # : </strong>{header.precinctNo}</div>
                                    <div><strong>Is Voter : </strong>{header.isNonVoter == 1 ? "NO" : "YES"}</div>

                                    <br />

                                    <div><strong>LGC : </strong>{header.lgc.voter_name}</div>
                                    <div><strong>LGC CP # : </strong>{header.lgc.cellphone}</div>

                                    <br />

                                    <div><strong>Gender : </strong>{header.gender} </div>
                                    <div><strong>Birthdate : </strong>{header.birthdate}</div>
                                    <div><strong>Dialect : </strong>{header.dialect}</div>
                                    <div><strong>Religion : </strong>{header.religion}</div>

                                    <br />
                                    {!this.state.otherInfoLoaded && <button disabled={this.state.loadingInfo} onClick={this.loadOtherInfo} className="btn btn-sm btn-primary ">Load Household & Recruitment</button>}
                                    {this.state.otherInfoLoaded &&
                                        (
                                            <div>
                                                <div><strong>Household Members : </strong>
                                                    {otherInfo.householdInfo.totalMembers}
                                                        (Voter :{otherInfo.householdInfo.totalVoter},
                                                        With CP : {otherInfo.householdInfo.totalWithCp} )
                                                    </div>
                                                <div>
                                                    <strong>Recruits : </strong>
                                                    {otherInfo.recInfo.totalMembers}
                                                        (Voter :{otherInfo.recInfo.totalVoter},
                                                            With CP : {otherInfo.recInfo.totalWithCp} )
                                                    </div>
                                            </div>
                                        )
                                    }
                                </div>
                            </div>
                        </div>


                    </div>

                    <div className='col-md-4'>

                    {
                        this.state.showHouseholdMemberModal &&
                        <HouseholdMemberModal
                            id={this.state.householdTarget}
                            show={this.state.showHouseholdMemberModal}
                            reload={this.reloadHousehold}
                            onHide={this.closeHouseholdMemberModal}
                            proId={this.props.proId}
                            electId={this.props.electId}
                        />
                    }
    

                        <div className="portlet light portlet-fit bordered">
                            <table id="household_table" className="table table-striped table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Household Leader</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style={{ padding: "10px 5px" }}>
                                            <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilter2Change} />
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        )
    }
});

window.Recruitment2Datatable = Recruitment2Datatable;