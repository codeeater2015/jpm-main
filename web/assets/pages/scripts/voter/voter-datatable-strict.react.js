var VoterDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEntryModal: false,
            showEditModal: false,
            showViewModal: false,
            showUploadModal: false,
            showUploadVotingStatusModal: false,
            showNewVoterCreateModal: false,
            showUploadBdayModal: false,
            showSmsModal: false,
            showDswdSmsModal: false,
            showCapitolSmsModal: false,
            showJpmModal: false,
            target: null,
            typingTimer: null,
            user: null,
            doneTypingInterval: 1500,
            fiscalYears: [],
            summary: {
                recordsFiltered: 0,
                obrTotal: 0
            },
            filters: {
                proId: null,
                electId: null
            },
            user: null
        }
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadUser(window.userId);
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.reinitSelect2);
        });
    },

    initSelect2: function () {
        var self = this;

        $("#voter_component #election_select2").select2({
            casesentitive: false,
            placeholder: "Select Election...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_elections'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.elect_id, text: item.elect_name };
                        })
                    };
                },
            }
        });

        $("#voter_component #project_select2").select2({
            casesentitive: false,
            placeholder: "Select Project...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_projects'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.pro_id, text: item.pro_name };
                        })
                    };
                },
            }
        });

        $("#voter_component #province_select2").select2({
            casesentitive: false,
            placeholder: "Enter Province...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_province'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.province_code, text: item.name };
                        })
                    };
                },
            }
        });

        $("#voter_table #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $('#voter_component #province_select2').val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#voter_table #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Enter name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $('#voter_component #province_select2').val(),
                        municipalityNo: $("#voter_table #municipality_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });


        $("#voter_table #precinct_select2").select2({
            casesentitive: false,
            placeholder: "Enter Precinct...",
            allowClear: true,
            delay: 1500,
            width: '60',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_precinct_no'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.precinct_no, text: item.precinct_no };
                        })
                    };
                },
            }
        });

        $("#voter_table #organization_select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_organization'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.organization, text: item.organization };
                        })
                    };
                },
            }
        });

        $("#voter_table #position_select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_position'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.position, text: item.position };
                        })
                    };
                },
            }
        });

        $("#voter_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();
            self.setState({ filters: filters });

            self.handleFilterChange();
        });

        $("#voter_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters });
        });

        $("#voter_table #province_select2").on("change", function () {
            self.handleFilterChange();
        });

        $("#voter_table #municipality_select2").on("change", function () {
            self.handleFilterChange();
        });

        $("#voter_table #barangay_select2").on("change", function () {
            self.handleFilterChange();
        });

        $("#voter_table #precinct_select2").on("change", function () {
            self.handleFilterChange();
        });

        $("#voter_table #organization_select2").on("change", function () {
            self.handleFilterChange();
        });

        $("#voter_table #category_select2").on("change", function () {
            self.handleFilterChange();
        });
    },

    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user.project)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#voter_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {

                $("#voter_component #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#voter_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#voter_component #election_select2").attr('disabled', 'disabled');
            $("#voter_component #province_select2").attr('disabled', 'disabled');
            $("#voter_component #project_select2").attr('disabled', 'disabled');
            $("#voter_table #municipality_select2").attr('disabled', 'disabled');
        }

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_municipality_alt", { name : self.state.user.description }),
            type: "GET"
        }).done(function (res) {
            $("#voter_table #municipality_select2").empty()
                .append($("<option/>")
                    .val(res.municipality_no)
                    .text(res.name))
                .trigger("change");
        });

        self.gridTable();
    },

    gridTable: function () {
        var self = this;
        var grid = new Datatable();

        var voter_table = $("#voter_table");

        grid.init({
            src: voter_table,
            onSuccess: function (grid, response) {
                var summary = self.state.summary;
                summary.recordsFiltered = response.recordsFiltered;
                summary.obrTotal = response.obrTotal;
                self.setState({ summary: summary });
            },
            dataTable: {
                "bState": true,
                "autoWidth": true,
                "serverSide": true,
                "processing": true,
                "deferRender": true,
                "deferLoading": 0,
                "ajax": {
                    "url": Routing.generate('ajax_datatable_voter'),
                    "type": "GET",
                    "data": function (d) {
                        d.provinceCode = $('#voter_component #province_select2').val();
                        d.municipalityNo = $('#voter_table #municipality_select2').val();
                        d.brgyNo = $('#voter_table #barangay_select2').val();
                        d.precinctNo = $('#voter_table #precinct_select2').val();
                        d.voterName = $('#voter_table input[name="voter_name"]').val();
                        d.birthdate = $('#voter_table input[name="birthdate"]').val();
                        d.cellphone = $('#voter_table input[name="cellphone"]').val();
                        d.voterGroup = $('#voter_table input[name="voter_group"]').val();
                        d.brgyCluster = $('#voter_table input[name="brgy_cluster"]').val();
                        d.electId = $('#voter_component #election_select2').val();
                        d.proId = $('#voter_component #project_select2').val();
                    }
                },
                columnDefs: [
                    {
                        'className': 'text-center valign-middle',
                        'orderable': false,
                        'targets': [0, 4, 5, 6, 7, 8, 9]
                    }
                ],
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
                            return (row.voted_2017 == 1 ? "*" : "") + data;
                        }
                    },
                    {
                        "data": "birthdate",
                        "className": "text-center",
                        "width": "80px",
                        "render": function (data, type, row) {
                            return self.isEmpty(data) ? "" : data.split(" ")[0];
                        }
                    },
                    {
                        "data": "municipality_name",
                        "width": "150px"
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": "100px"
                    },
                    {
                        "data": "brgy_cluster",
                        "className": "text-center",
                        "width": 20
                    },
                    {
                        "data": "precinct_no",
                        "className": "text-center",
                        "width": "30px"
                    },
                    {
                        "data": "cellphone_no",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "width": 130,
                        "render": function (data, type, row) {
                            //var btnGroup = '<button class="btn btn-xs blue-madison  edit-btn"><i class="fa fa-edit"></i></button>';

                            var viewBtn = '<button class="btn btn-xs default edit2-btn"><i class="fa fa-eye"></i></button>';
                            var editBtn = '<button class="btn btn-xs green edit-btn"><i class="fa fa-edit"></i></button>';
                            var assignBtn = '<button class="btn btn-xs blue assign-btn"><i class="fa fa-edit"></i>Assign</button>';
                            var deleteBtn = '<button class="btn btn-xs red-sunglo delete-btn"><i class="fa fa-trash"></i></button>';

                            // if (self.state.user.isAdmin == 1) {
                            //     if (row.status == 'A' || row.status == '' || row.status == null) {
                            //         btnGroup += '<button class="btn btn-xs red-sunglo block-btn">Block</button>';
                            //         btnGroup += '<button class="btn btn-xs grey-gallery deactivate-btn">Detactivate</button>';
                            //     } else if (row.status == 'B') {
                            //         btnGroup += '<button class="btn btn-xs green-jungle unblock-btn">Unblock</button>';
                            //     } else if (row.status == 'I') {
                            //         btnGroup += '<button class="btn btn-xs red-sunglo block-btn">Block</button>';
                            //         btnGroup += '<button class="btn btn-xs green-jungle activate-btn">Activate</button>';
                            //     }

                            //     if (row.has_photo == 1) {
                            //         btnGroup += '<button class="btn btn-xs purple-sharp reset-image-btn">Reset Image</button>';
                            //     }
                            // }

                            var btnGroup = '';
                            btnGroup += viewBtn;
                            //btnGroup += editBtn;
                      
                            return btnGroup;
                        },
                        "className": "text-center"
                    }
                ]
            }

        });


        voter_table.on('click', '.edit2-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.edit2(data.pro_voter_id);
        9});

        voter_table.on('click', '.edit-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.edit(data.pro_voter_id);
        });

        voter_table.on('click', '.assign-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.assign(data.pro_id_code);
        });

        voter_table.on('click', '.delete-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.pro_voter_id);
        });


        voter_table.on('keypress', '.form-filter', function (e) {
            if (e.charCode == 13)
                self.reload();
        });

        // voter_table.on('click', '.block-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.block(data.voter_id);
        // });

        // voter_table.on('click', '.unblock-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.unblock(data.voter_id);
        // });

        // voter_table.on('click', '.activate-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.activate(data.voter_id);
        // });

        // voter_table.on('click', '.deactivate-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.deactivate(data.voter_id);
        // });

        // voter_table.on('click', '.view-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.view(data.voter_id);
        // });

        // voter_table.on('click', '.reset-image-btn', function () {
        //     var data = grid.getDataTable().row($(this).parents('tr')).data();
        //     self.resetImage(data.voter_id);
        // });

        // voter_table.on('keydown', 'input', function (e) {
        //     if (e.keyCode == 13)
        //         self.reload();
        // });

        self.grid = grid;
    },

    openEntryModal: function () {
        this.setState({ showEntryModal: true });
    },

    openNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: true });
    },

    openUploadModal: function () {
        this.setState({ showUploadModal: true });
    },

    openUploadBdayModal: function () {
        this.setState({ showUploadBdayModal: true });
    },

    openUploadVotingStatusModal: function () {
        this.setState({ showUploadVotingStatusModal: true });
    },

    openSmsModal: function () {
        this.setState({ showSmsModal: true });
    },

    openDswdSmsModal: function () {
        console.log("show dswd");
        this.setState({ showDswdSmsModal: true });
    },

    openCapitolSmsModal: function () {
        console.log("show dswd");
        this.setState({ showCapitolSmsModal: true });
    },

    openJpmModal: function () {
        this.setState({ showJpmModal: true });
    },

    edit: function (target) {
        this.setState({ showNewVoterEditModal: true, target: target });
    },

    edit2: function (target) {

        console.log("pro voter id");
        console.log(target);

        this.setState({ showEditModal: true, target: target });
    },

    assign: function (target) {
        console.log("assign location");
        this.setState({ showLocationAssignmentModal: true, target: target });
    },

    delete: function (target) {
        var self = this;

        if (confirm("Are you sure you want to delete this record?")) {
            self.requestDeleteVoter = $.ajax({
                url: Routing.generate("ajax_delete_temporary_voter", { proVoterId: target }),
                type: "DELETE"
            }).done(function (res) {
                console.log('Record has been deleted.');
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },

    closeNewVoterEditModal() {
        this.setState({ showNewVoterEditModal: false, target: null });
    },

    block: function (target) {
        var self = this;
        var reason = prompt("Please indicate the reason for blocking : ", "");

        if (self.isEmpty(reason)) {
            alert("Reason cannot be empty... Please try again.");
        } else {
            console.log("block voter");
            self.requestBlockUser = $.ajax({
                url: Routing.generate("ajax_project_voter_block", { voterId: target, reason: reason }),
                type: "GET"
            }).done(function (res) {
                console.log('voter has been blocked');
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },

    unblock: function (target) {
        var self = this;
        var reason = prompt("Please indicate the reason for unblocking : ", "");

        if (self.isEmpty(reason)) {
            alert("Reason cannot be empty... Please try again.");
        } else {
            self.requestBlockUser = $.ajax({
                url: Routing.generate("ajax_project_voter_unblock", { voterId: target, reason: reason }),
                type: "GET"
            }).done(function (res) {
                console.log("voter has been unblocked");
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },

    activate: function (target) {
        var self = this;
        var reason = prompt("Please indicate the reason for activation : ", "");

        if (self.isEmpty(reason)) {
            alert("Reason cannot be empty... Please try again.");
        } else {
            self.requestBlockUser = $.ajax({
                url: Routing.generate("ajax_project_voter_activate", { voterId: target, reason: reason }),
                type: "GET"
            }).done(function (res) {
                console.log('block user');
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },

    deactivate: function (target) {
        var self = this;
        var reason = prompt("Please indicate the reason for deactivation : ", "");

        if (self.isEmpty(reason)) {
            alert("Reason cannot be empty... Please try again.");
        } else {
            self.requestBlockUser = $.ajax({
                url: Routing.generate("ajax_project_voter_deactivate", { voterId: target, reason: reason }),
                type: "GET"
            }).done(function (res) {
                console.log('block user');
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },


    resetImage: function (target) {
        var self = this;

        if (confirm("Are you sure you want to reset this voter's picture?")) {
            self.requestBlockUser = $.ajax({
                url: Routing.generate("ajax_project_voter_reset_image", { voterId: target }),
                type: "GET"
            }).done(function (res) {
                console.log('voter image has been reset');
                self.reload();
            }).fail(function (res) {
                console.log("opps something went wrong");
            });
        }
    },

    closeEntryModal: function () {
        this.setState({ showEntryModal: false });
        this.reload();
    },

    closeViewModal: function () {
        this.setState({ showViewModal: false, target: null });
    },

    closeUploadModal: function () {
        this.setState({ showUploadModal: false });
        this.reload();
    },

    closeUploadVotingStatusModal: function () {
        this.setState({ showUploadVotingStatusModal: false });
        this.reload();
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
        this.reload();
    },

    closeSmsModal: function () {
        this.setState({ showSmsModal: false });
    },

    closeDswdSmsModal: function () {
        this.setState({ showDswdSmsModal: false });
    },

    closeUploadBdayModal: function () {
        this.setState({ showUploadBdayModal: false });
    },

    closeJpmModal: function () {
        this.setState({ showJpmModal: false });
    },

    closeCloseCapitolSmsModal: function () {
        this.setState({ showCapitolSmsModal: false });
    },

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    closeLocationAssignmentModal: function () {
        this.setState({ showLocationAssignmentModal: false });
    },

    closeViewModal: function () {
        this.setState({ showViewModal: false, target: null });
        this.reload();
    },

    handleFilterChange: function () {
        // var self = this;
        // clearTimeout(this.state.typingTimer);
        // this.state.typingTimer = setTimeout(function(){
        //     self.reload();
        // },this.state.doneTypingInterval);
    },


    reload: function () {
        this.grid.getDataTable().ajax.reload();
    },

    render: function () {
        return (
            <div>

                {
                    this.state.showNewVoterCreateModal &&
                    <VoterTemporaryCreateModal
                        show={this.state.showNewVoterCreateModal}
                        onHide={this.closeNewVoterCreateModal}
                        notify={this.props.notify}
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                    />
                }

                {
                    this.state.showNewVoterEditModal &&
                    <VoterTemporaryEditModal
                        show={this.state.showNewVoterEditModal}
                        onHide={this.closeNewVoterEditModal}
                        proVoterId={this.state.target}
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                    />
                }

                {
                    this.state.showEntryModal &&
                    <VoterCreateModal
                        show={this.state.showEntryModal}
                        onHide={this.closeEntryModal}
                        notify={this.props.notify}
                    />
                }
                {
                    this.state.showUploadModal &&
                    <VoterUploadModal
                        show={this.state.showUploadModal}
                        onHide={this.closeUploadModal}
                        notify={this.props.notify}
                    />
                }

                {
                    this.state.showUploadVotingStatusModal &&
                    <VoterUpload2016VotingStatusModal
                        show={this.state.showUploadVotingStatusModal}
                        onHide={this.closeUploadVotingStatusModal}
                        notify={this.props.notify}
                    />
                }

                {this.state.showUploadBdayModal &&
                    <VoterUploadBdayModal
                        show={this.state.showUploadBdayModal}
                        onHide={this.closeUploadBdayModal}
                        notify={this.props.notify}
                    />
                }

                {this.state.showEditModal &&
                    <VoterEditModal
                        show={this.state.showEditModal}
                        onHide={this.closeEditModal}
                        notify={this.props.notify}
                        proVoterId={this.state.target}
                        user={this.state.user}
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                    />
                }

                {this.state.showViewModal &&
                    <VoterViewModal
                        show={this.state.showViewModal}
                        onHide={this.closeViewModal}
                        notify={this.props.notify}
                        voterId={this.state.target}
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                    />
                }

                {this.state.showSmsModal &&
                    <SmsModal
                        show={this.state.showSmsModal}
                        onHide={this.closeSmsModal}
                        notify={this.props.notify}
                    />
                }

                {this.state.showDswdSmsModal &&
                    <DswdSmsModal
                        show={this.state.showDswdSmsModal}
                        onHide={this.closeDswdSmsModal}
                        notify={this.props.notify}
                    />
                }



                {this.state.showCapitolSmsModal &&
                    <CapitolSmsModal
                        show={this.state.showCapitolSmsModal}
                        onHide={this.closeCapitolSmsModal}
                        notify={this.props.notify}
                    />
                }

                {this.state.showJpmModal &&
                    <VoterJpmModal
                        show={this.state.showJpmModal}
                        onHide={this.closeJpmModal}
                        notify={this.props.notify}
                    />
                }


                {
                    this.state.showLocationAssignmentModal &&
                    <LocationAssignmentModal
                        show={this.state.showLocationAssignmentModal}
                        onHide={this.closeLocationAssignmentModal}
                        proIdCode={this.state.target}
                        notify={this.props.notify}
                    />
                }


                <div className="row" id="voter_component">
                    <div className="col-md-7">
                        <form onSubmit={this.onApplyCode}>
                            <div className="col-md-3">
                                <select id="election_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-4">
                                <select id="province_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-4">
                                <select id="project_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="table-container">
                    <div className="table-actions-wrapper">
                    </div>
                    <table id="voter_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th className="text-center">No</th>
                                <th>Name</th>
                                <th className="text-center">Birthdate</th>
                                <th className="text-center">Municipality</th>
                                <th className="text-center">Brgy</th>
                                <th className="text-center">CL</th>
                                <th className="text-center">Prec No.</th>
                                <th className="text-center">CP No.</th>
                                <th className="text-center">POS</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="birthdate" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select id="barangay_select2" className="form-control form-filter input-sm">
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="brgy_cluster" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px", "width": "10px" }}>
                                    <select id="precinct_select2" className="form-control form-filter input-sm" >
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="cellphone" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_group" onChange={this.handleFilterChange} />
                                </td>
                                <td className="text-center">
                                    <button style={{ marginTop: "5px", marginBottom: "5px" }} className="btn btn-xs green btn-outline filter-submit">
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
        )
    },

    setAccessCode: function (e) {
        this.setState({ "accessCode": e.target.value });
    },

    onApplyCode: function (e) {
        e.preventDefault();

        var self = this;

        self.requestApplyCode = $.ajax({
            url: Routing.generate("ajax_apply_access_code", { accessCode: this.state.accessCode }),
            type: "GET"
        }).done(function (res) {
            self.reload();
            alert("Code has been applied.Granting data access...");
        }).fail(function () {
            alert("Opps! Invalid Code");
        });
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    onKeyDown: function (e) {
        console.log("test");
    }
});

window.VoterDatatable = VoterDatatable;