var FormStatusChecklistDatatable = React.createClass({

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

        $("#checklist_component #election_select2").select2({
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

        $("#checklist_component #project_select2").select2({
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

        $("#checklist_component #province_select2").select2({
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


        $("#checklist_component #summary_date_select2").select2({
            casesentitive: false,
            placeholder: "Enter Date...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_member_summary_dates'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.generated_at, text: item.generated_at };
                        })
                    };
                },
            }
        });

        $("#checklist_table #municipality_select2").select2({
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
                        provinceCode: $('#checklist_component #province_select2').val()
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

        $("#checklist_table #barangay_select2").select2({
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
                        provinceCode: $('#checklist_component #province_select2').val(),
                        municipalityNo: $("#checklist_table #municipality_select2").val()
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


        $("#checklist_table #precinct_select2").select2({
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

        $("#checklist_table #organization_select2").select2({
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

        $("#checklist_table #position_select2").select2({
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

        $("#checklist_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();
            self.setState({ filters: filters });

        });

        $("#checklist_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters });
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
                $("#checklist_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {

                $("#checklist_component #project_select2").empty()
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
            $("#checklist_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#checklist_component #election_select2").attr('disabled', 'disabled');
            $("#checklist_component #province_select2").attr('disabled', 'disabled');
            $("#checklist_component #project_select2").attr('disabled', 'disabled');
        }

        self.gridTable();
    },

    gridTable: function () {
        var self = this;
        var grid = new Datatable();

        var checklist_table = $("#checklist_table");

        grid.init({
            src: checklist_table,
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
                        d.provinceCode = $('#checklist_component #province_select2').val();
                        d.municipalityNo = $('#checklist_table #municipality_select2').val();
                        d.brgyNo = $('#checklist_table #barangay_select2').val();
                        d.precinctNo = $('#checklist_table #precinct_select2').val();
                        d.voterName = $('#checklist_table input[name="voter_name"]').val();
                        d.birthdate = $('#checklist_table input[name="birthdate"]').val();
                        d.cellphone = $('#checklist_table input[name="cellphone"]').val();
                        d.voterGroup = $('#checklist_table input[name="voter_group"]').val();
                        d.electId = $('#checklist_component #election_select2').val();
                        d.recFormSub = $('#checklist_table select[name="rec_form_sub"]').val();
                        d.houseFormSub = $('#checklist_table select[name="house_form_sub"]').val();
                        d.isNonVoter = $('#checklist_table select[name="is_non_voter"]').val();
                        d.proId = $('#checklist_component #project_select2').val();
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
                        "data": "voter_group",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "rec_form_sub",
                        "className": "text-center",
                        "width": 60,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="recFormSub" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.pro_voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "house_form_sub",
                        "className": "text-center",
                        "width": 60,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="houseFormSub" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.pro_voter_id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "is_non_voter",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return parseInt(data) == 1 ? "YES" : "NO";
                        }
                    },
                    {
                        "data": "house_form_sub",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return "";
                        }
                    },
                  
                ]
            }

        });



        checklist_table.on('click', '.status-checkbox', function (e) {
            var proVoterId = e.target.value;
            var checked = e.target.checked;
            var fieldName = e.target.name;
            var newValue = checked ? 1 : 0;

            if (proVoterId != null && checked != null) {
                self.patchStatus(proVoterId, fieldName, newValue);
            }
        });

        self.grid = grid;
    },

    reload: function () {
        this.grid.getDataTable().ajax.reload();
    },

    patchStatus: function (proVoterId, fieldName, value) {
        var self = this;
        var data = {};

        data[fieldName] = value;
        self.requestToggleRequirement = $.ajax({
            url: Routing.generate("ajax_patch_form_status_tag", { proVoterId: proVoterId }),
            type: "PATCH",
            data: (data)
        }).done(function (res) {
            self.props.notify("Record has been updated.", 'teal');
        });
    },

    render: function () {
        return (
            <div>
                <div className="row" id="checklist_component">
                    <div className="col-md-2 ">
                        <select id="election_select2" className="form-control form-filter input-sm" >
                        </select>
                    </div>
                    <div className="col-md-2">
                        <select id="province_select2" className="form-control form-filter input-sm" >
                        </select>
                    </div>
                    <div className="col-md-2">
                        <select id="project_select2" className="form-control form-filter input-sm" >
                        </select>
                    </div>
                    <div className="col-md-1">
                        <button className="btn red-sunglo btn-sm" onClick={this.export}> <i className="fa fa-file-excel-o" /> Export List </button>
                    </div>

                    <div className="col-md-2 col-md-offset-1">
                        <select id="summary_date_select2" className="form-control form-filter input-sm" >
                        </select>
                    </div>

                    <div className="col-md-1">
                        <button className="btn red-sunglo btn-sm" onClick={this.exportSummary}> <i className="fa fa-file-excel-o" /> Export Province Summary </button>
                    </div>

                </div>

                <div className="table-container">
                    <div className="table-actions-wrapper">
                    </div>
                    <table id="checklist_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th className="text-center">No</th>
                                <th>Name</th>
                                <th className="text-center">Birthdate</th>
                                <th className="text-center">Municipality</th>
                                <th className="text-center">Brgy</th>
                                <th className="text-center">POS</th>
                                <th className="text-center">HH</th>
                                <th className="text-center">REC</th>
                                <th className="text-center">Non-voter</th>
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
                                    <input type="text" className="form-control form-filter input-sm" name="voter_group" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select className="form-control form-filter input-sm" name="house_form_sub" onChange={this.handleFilterChange}>
                                        <option value="">- - -</option>
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select className="form-control form-filter input-sm" name="rec_form_sub" onChange={this.handleFilterChange}>
                                        <option value="">- - -</option>
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                    </select>
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <select className="form-control form-filter input-sm" name="is_non_voter" onChange={this.handleFilterChange}>
                                        <option value="">- - -</option>
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                    </select>
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

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    export: function () {
        var params = this.grid.getDataTable().ajax.params();
        var user = this.state.user;

        console.log("user");
        console.log(user);
        params.generatedBy = user.name;

        console.log("parameters");
        console.log(params);

        this.showPrintout(params);
    },

    exportSummary: function () {
        var generatedAt = $("#checklist_component #summary_date_select2").val();
        var url = "http://" + window.hostIp + ":83/jpm/form-summary/index.php?generatedAt=" + generatedAt;
        this.popupCenter(url, 'Form Status Summary', 900, 600);
    },

    showPrintout: function (params) {
        var url = "http://" + window.hostIp + ":83/jpm/form-status/index.php?municipalityNo=" + params.municipalityNo;
        url += "&recFormSub=" + params.recFormSub;
        url += "&voterGroup=" + params.voterGroup;
        url += "&generatedBy=" + params.generatedBy;
        url += "&isNonVoter=" + params.isNonVoter;

        this.popupCenter(url, 'Member Form Status List', 900, 600);
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

window.FormStatusChecklistDatatable = FormStatusChecklistDatatable;