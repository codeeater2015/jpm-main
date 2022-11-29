var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;


var VoterNetworkComponent = React.createClass({

    getInitialState: function () {
        return {
            electId: null,
            proId: null,
            provinceCode: null,
            municipalityNo: null,
            brgyNo: null,
            barangay: {
                total_voter: 0,
                total_recruits: 0,
                percentage: 0
            },
            searchTimer: null,
            delay: 1000,
            searchText: "",
            target: null,
            orderBy: "entryNo",
            nodeLevel : 1,
            showRootCreateModal: false,
            showNodeCreateModal: false,
            showNodeEditModal: false,
            items: [],
            loading: false,
            activeNode: null,
            activeRoot: null,
            user: null
        }
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    render: function () {
        var self = this;
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <div className="row">
                        <div className="col-md-5">
                            {
                                this.state.showRootCreateModal &&
                                <VoterNetworkRootCreateModal
                                    show={this.state.showRootCreateModal}
                                    onHide={this.closeRootCreateModal}
                                    notify={this.notify}
                                    onSuccess={this.onSuccessRootCreate}
                                    electId={this.state.electId}
                                    provinceCode={this.state.provinceCode}
                                    proId={this.state.proId}
                                    municipalityNo={this.state.municipalityNo}
                                    brgyNo={this.state.brgyNo}
                                />
                            }
                            {
                                this.state.showNodeCreateModal &&
                                <VoterNetworkCreateModal
                                    show={this.state.showNodeCreateModal}
                                    onHide={this.closeCreateModal}
                                    onSuccess={this.onSuccessMemberCreate}
                                    nodeId={this.state.target}
                                    notify={this.notify}
                                    electId={this.state.electId}
                                    proId={this.state.proId}
                                />
                            }
                            {
                                this.state.showNodeEditModal &&
                                <VoterNetworkEditModal
                                    show={this.state.showNodeEditModal}
                                    onHide={this.closeEditModal}
                                    nodeId={this.state.target}
                                    notify={this.notify}
                                    onSuccess={this.onSuccessMemberEdit}
                                    electId={this.state.electId}
                                    proId={this.state.proId}
                                />
                            }

                            <form>
                                <FormGroup controlId="formElection" >
                                    <ControlLabel > Election : </ControlLabel>
                                    <select id="form-election-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>

                                <FormGroup controlId="formProjectCode" >
                                    <ControlLabel > Project : </ControlLabel>
                                    <select id="form-project-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>

                                <FormGroup controlId="formProvinceCode" >
                                    <ControlLabel > Province : </ControlLabel>
                                    <select id="form-province-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>

                                <FormGroup controlId="formMunicipalityNo" >
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="form-municipality-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>

                                <FormGroup controlId="formBarangayNo">
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="form-barangay-select2" className="form-control input-sm">
                                    </select>
                                </FormGroup>
                            </form>
                            {!this.state.loading && <button type="button" className="btn btn-primary btn-sm" style={{ width: "100%" }} onClick={this.apply}>Apply </button>}
                            {this.state.loading && <button type="button" disabled={true} className="btn red-sunglo btn-sm" style={{ width: "100%" }}> Loading <i className="fa fa-spinner fa-pulse fa-1x fa-fw"></i></button>}

                            <div style={{ marginTop: "20px" }}>
                                <div className="row" style={{ marginBottom : "10px" }}>
                                    <div className="col-md-6">
                                        <select name="nodeLevel" className="form-control input-sm" value={this.state.nodeLevel} onChange={this.setNodeLevel} >
                                            <option value="1">Level 1</option>
                                            <option value="2">Level 2</option>
                                            <option value="3">Level 3</option>
                                            <option value="4">Level 4</option>
                                            <option value="5">Level 5</option>
                                            <option value="6">Level 6</option>
                                        </select>
                                    </div>
                                    <div className="col-md-6">
                                        <select name="orderBy" className="form-control input-sm" value={this.state.orderBy} onChange={this.setOrderBy} >
                                            <option value="entryNo">Entry Date</option>
                                            <option value="name">Name</option>
                                        </select>
                                    </div>
                                </div>
                                <input className="form-control input-sm" type="text" placeholder="Search group leader..." onChange={this.setSearchText} />
                                <ul style={{ listStyleType: "none", padding: "5px", backgroundColor: '#E6E6E6', height: "180px", width: "100%", overflow: "scroll" }} >
                                    {
                                        this.state.items.map(function (item, index) {
                                            var className = item.node_id == self.state.activeNode ? "bg-blue-dark font-white" : "";
                                            return (
                                                <li
                                                    className={className}
                                                    style={{ padding: "5px", cursor: "pointer", fontSize: "12px" }}
                                                    key={"item" + item.node_id}
                                                    value={item.node_id}
                                                    onClick={self.setActiveNode.bind(self, item.root_node, item.node_id)}>
                                                    {++index}. {item.voted_2017 == 1 ? "*" : ""}{item.node_label} - {item.precinct_no} ({item.voter_group})
                                                        <i className="fa fa-trash font-red"
                                                        onClick={self.removeRootNode.bind(self, item.node_id)}
                                                    ></i>
                                                </li>
                                            );
                                        })
                                    }

                                    {
                                        this.state.items.length == 0 && !this.state.loading &&
                                        (
                                            <li style={{ padding: "5px" }} className="text-center bg-red-sunglo font-white">Opps! Your leader list is empty...</li>
                                        )
                                    }
                                </ul>
                            </div>
                            <button
                                type="button"
                                className="btn btn-primary btn-sm"
                                onClick={this.openRootCreateModal}
                                disabled={self.isEmpty(this.state.municipalityNo)}
                                style={{ width: "100%" }}>
                                Add Leader
                            </button>

                        </div>
                        <div className="col-md-7">
                            <div className="col-md-4">
                                <p><strong>Hierarchy View</strong></p>
                            </div>
                            <div className="col-md-8 text-right">
                                <div className="bold">
                                    Registered : <span className="font-red-sunglo" style={{ marginRight: "5px" }}>{this.state.barangay.total_voter}</span>
                                    Recruited : <span className="font-red-sunglo" style={{ marginRight: "5px" }}>{this.state.barangay.total_recruits}</span>
                                    Percentage : <span className="font-red-sunglo">{parseFloat(this.state.barangay.percentage).toFixed(2)} %</span>
                                </div>
                            </div>
                            <div className="col-md-12">
                                <div id="sample-tree"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    },

    componentDidMount: function () {
        this.catchHttpErrors();
        this.loadUser(window.userId);
        this.initSelect2();
        this.initNetwork();
    },

    catchHttpErrors: function () {
        var self = this;
        $(document).ajaxError(function (event, request, settings) {
            switch (request.status) {
                case 400:
                    self.notify("Form validation failed.", "ruby");
                    break;
                case 401:
                    self.notify("Action denied.You are not allowed to perform this action.", "ruby");
                    break;
                case 403:
                    self.notify("Action denied.You are not allowed to perform this action.", "ruby");
                    break;
                case 500:
                    self.notify("Opps. Something went wrong in the server. Please inform the system administrator.", "ruby");
                    break;
            }
        });
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

    initNetwork: function () {
        $.jstree.defaults.core.themes.variant = "large";
        var self = this;

        $('#sample-tree').jstree({
            "core": {
                "animation": 0,
                "check_callback": true,
                "themes": { "stripes": true },
                'data': {
                    'cache': false,
                    'dataType': 'json',
                    'url': function (node) {
                        return Routing.generate("ajax_get_network_nodes",
                            {
                                provinceCode: self.state.provinceCode,
                                municipalityNo: self.state.municipalityNo,
                                brgyNo: self.state.brgyNo,
                                rootId: self.state.activeRoot,
                                nodeId: self.state.activeNode
                            });
                    },
                    'data': function (node) {
                        return { 'id': node.id };
                    }
                }
            },

            "plugins": [
                "contextmenu", "wholerow"
            ],
            "contextmenu": {
                "select_node": false,
                "items": self.reportMenu
            }
        });
    },

    refreshTree: function () {
        $('#sample-tree').jstree(true).refresh();
    },

    setActiveNode: function (rootId, nodeId) {
        this.setState({ activeNode: nodeId, activeRoot: nodeId }, this.refreshTree);
    },

    clearItems: function () {
        this.setState({ activeNode: null, items: [] });
    },

    loadItems: function () {
        var self = this;
        self.requestItems = $.ajax({
            url: Routing.generate("ajax_get_root_nodes", {
                electId: self.state.electId,
                proId: self.state.proId,
                provinceCode: self.state.provinceCode,
                municipalityNo: self.state.municipalityNo,
                brgyNo: self.state.brgyNo,
                searchText: self.state.searchText,
                orderBy: self.state.orderBy,
                nodeLevel : self.state.nodeLevel
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ items: res });
        }).always(function () {
            self.setState({ loading: false });
        });

        self.setState({ loading: true });
    },

    openRootCreateModal: function () {
        this.setState({ showRootCreateModal: true });
        this.loadItems();
    },

    closeRootCreateModal: function (nodeId) {
        this.setState({ showRootCreateModal: false });
    },

    onSuccessRootCreate: function (nodeId) {
        var self = this;
        self.loadItems();
        self.loadBarangay();
        self.setState({ showNodeCreateModal: false, activeRoot: null });

        setTimeout(function () {
            self.setState({ showRootCreateModal: false, activeRoot: nodeId }, self.refreshTree);
        }, 500);
    },

    onSuccessMemberCreate: function () {
        this.loadItems();
        this.loadBarangay();
        this.refreshTree()
    },

    onSuccessMemberEdit : function () {
        this.loadItems();
        this.loadBarangay();
        this.refreshTree()
    },

    closeCreateModal: function () {
        this.setState({
            target: null,
            showNodeCreateModal: false
        });
    },

    openCreateModal: function (nodeId) {
        this.setState({
            target: nodeId,
            showNodeCreateModal: true
        });
    },

    openEditModal: function (nodeId) {
        this.setState({
            target: nodeId,
            showNodeEditModal: true
        });
    },

    closeEditModal: function () {
        this.setState({
            target: null,
            showNodeEditModal: false
        });
    },

    apply: function () {
        var municipalityNo = this.state.municipalityNo;
        var brgyNo = this.state.brgyNo;

        if (!this.isEmpty(municipalityNo)) {
            this.loadItems();
            this.loadBarangay();
        } else {
            alert("Please select a municipaity and barangay.");
        }
    },

    removeRootNode: function (nodeId) {
        var self = this;

        if (confirm("Are you sure you want to remove this item?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_network_node", { nodeId: nodeId }),
                type: "DELETE"
            }).done(function (res) {
                self.notify("Recruit/Recruits has been removed.", "teal");
                self.loadItems();
                self.loadBarangay();
                self.setState({ activeNode: null }, self.refreshTree);
            }).fail(function (err) {
                if (err.status == '401') {
                    self.notify("You dont have the permission to update this record.", "ruby");
                } else {
                    self.notify("Form Validation Failed.", "ruby");
                }
            });
        }
    },

    removeNode: function (node) {
        var self = this;
        if (confirm("Are you sure you want to remove this item?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_network_node", { nodeId: node.id }),
                type: "DELETE"
            }).done(function (res) {
                self.notify("Member has been removed.", "teal");
                self.loadItems();
                self.loadBarangay();
                self.refreshTree();
            }).fail(function (err) {
                if (err.status == '401') {
                    self.notify("You dont have the permission to update this record.", "ruby");
                } else {
                    self.notify("Form Validation Failed.", "ruby");
                }
            });
        }
    },

    initSelect2: function () {
        var self = this;

        $("#form-election-select2").select2({
            casesentitive: false,
            placeholder: "Select election....",
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


        $("#form-province-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
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

        $("#form-project-select2").select2({
            casesentitive: false,
            placeholder: "Select project....",
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

        $("#form-municipality-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality_strict'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $("#form-province-select2").val()
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

        $("#form-barangay-select2").select2({
            casesentitive: false,
            placeholder: "Enter name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay_strict'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: $("#form-province-select2").val(),
                        municipalityNo: $("#form-municipality-select2").val()
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


        $("#form-election-select2").on("change", function () {
            self.setState({ "electId": $(this).val() });
        });

        $("#form-project-select2").on("change", function () {
            self.setState({ "proId": $(this).val() });
        });

        $("#form-province-select2").on("change", function () {
            $("#form-municipality-select2").empty().trigger('change');
            $("#form-barangay-select2").empty().trigger('change');
            self.setState({ "provinceCode": $(this).val() });
        });

        $("#form-municipality-select2").on("change", function () {
            $("#form-barangay-select2").empty().trigger('change');
            self.setState({ "municipalityNo": $(this).val() });
        });

        $("#form-barangay-select2").on("change", function () {
            self.setState({ "brgyNo": $(this).val() });
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
                $("#form-province-select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {

                $("#form-project-select2").empty()
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
            $("#form-election-select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });


        if (!self.state.user.isAdmin)
            $("#form-province-select2").attr('disabled', 'disabled');
    },

    loadBarangay: function () {
        var self = this;
        self.requestItems = $.ajax({
            url: Routing.generate("ajax_get_baranagy_full", {
                provinceCode: self.state.provinceCode,
                municipalityNo: self.state.municipalityNo,
                brgyNo: self.state.brgyNo
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ barangay: res });
        });
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined";
    },

    reportMenu: function (node) {
        var self = this;

        return {
            createItem: {
                "label": "Add Member",
                "action": function (obj) {
                    self.openCreateModal(node.id);
                },
                "_class": "class"
            },
            editItem: {
                "label": "Edit",
                "action": function (obj) {
                    self.openEditModal(node.id);
                },
                "_class": "class"
            },
            deleteItem: {
                "label": "Remove",
                "action": function (obj) {
                    self.removeNode(node);
                }
            }
        };
    },

    setSearchText: function (e) {
        var self = this;
        var searchTimer = null;

        if (self.state.searchTimer == null) {
            searchTimer = setTimeout(function () {
                self.setState({ searchTimer: null }, self.loadItems);
            }, self.state.delay);

            self.setState({ searchTimer: searchTimer, searchText: e.target.value });
        } else {
            self.setState({ searchTimer: searchTimer, searchText: e.target.value });
        }


    },

    setOrderBy: function (e) {
        this.setState({ orderBy: e.target.value }, this.loadItems);
    },

    setNodeLevel : function (e) {
        this.setState({ nodeLevel : e.target.value }, this.loadItems);
    }
});

setTimeout(function () {
    ReactDOM.render(
        <VoterNetworkComponent />,
        document.getElementById('voter-container')
    );
}, 500);
