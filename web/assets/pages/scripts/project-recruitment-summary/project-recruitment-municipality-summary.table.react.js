var ProjectRecruitmentMunicipalitySummaryTable = React.createClass({

    getInitialState: function () {
        return {
            data: [],
            municipality: {
                name: ""
            },
            summary: {
                totalBarangay: 0,
                totalPrecincts: 0,
                totalRegistered: 0,
                totalRecruits: 0,
                totalCH: 0,
                totalKCL: 0,
                totalKCL0: 0,
                totalKCL1: 0,
                totalKCL2: 0,
                totalVoted: 0,
                totalDAO: 0,
                totalOthers: 0,
                totalHasCellphone: 0,
                percentage: 0,
                deep: 1
            },
            targetBarangay: null,
            targetVoterGroup: null,
            showItemDetail: false,
            loading: false,
            refreshInterval: 300000,
            refreshCounter: 0,
            refreshId: null,
            counterId: null,
            mode: 'all',
            updating: false,
            updateCount : 0
        }
    },

    componentDidMount: function () {
        var self = this;

        self.loadData();
        self.initShortcuts();

        var refreshId = setInterval(function () {
            self.loadData();
        }, self.state.refreshInterval);


        self.setState({ refreshId: refreshId });
    },

    initShortcuts: function () {
        var self = this;
        $("body").keydown(function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode == 121) {
                e.preventDefault();
                self.loadData();
            }
        });
    },

    componentWillUnmount: function () {
        clearInterval(this.state.refreshId);
        clearInterval(this.state.counterId);
    },

    initCounter: function () {
        var self = this;
        var counterId = self.state.counterId;

        if (counterId != null)
            clearInterval(counterId);

        var counterId = setInterval(function () {
            var refreshCounter = self.state.refreshCounter;
            refreshCounter++;
            self.setState({ refreshCounter: refreshCounter });
        }, 1000);

        self.setState({ counterId: counterId, refreshCounter: 0 });
    },

    loadData: function () {
        this.loadMunicipality(this.props.provinceCode, this.props.municipalityNo);
        this.loadMunicipalityData(this.props.electId, this.props.proId, this.props.provinceCode, this.props.municipalityNo);
        this.initCounter();
    },

    recalculate: function () {
        var self = this;

        if (self.state.mode == 'all') {
            var data = {
                electId: self.props.electId,
                proId: self.props.proId,
                provinceCode: self.props.provinceCode,
                municipalityNo: self.props.municipalityNo
            };

            self.setState({ updating: true });
            self.requestUpdateSummary = $.ajax({
                url: Routing.generate("ajax_update_recruitment_summary_by_municipality", data),
                type: 'GET'
            }).done(function () {
                self.loadData();
            }).always(function () {
                self.setState({ updating: false });
                alert("Re-computation has been completed...");
            });

        } else if (self.state.mode == 'my_favorites') {

            self.setState({ updateCount : 0 });

            var counter = 0;

            self.state.data.map(function (item) {

                if (item.is_favorite == 1) {

                    var data = {
                        electId: self.props.electId,
                        proId: self.props.proId,
                        provinceCode: self.props.provinceCode,
                        municipalityNo: self.props.municipalityNo,
                        brgyNo: item.brgy_no
                    };

                    self.requestUpdateSummary = $.ajax({
                        url: Routing.generate("ajax_update_project_voter_summary", data),
                        type: 'GET'
                    }).done(function () {
                        self.loadData();
                    }).always(function () {
                        var updateCount = self.state.updateCount - 1;

                        self.setState({updateCount :  updateCount});
                    });

                    counter++;
                }

            });

            self.setState({  updateCount : counter })
        }
    },

    loadMunicipalityData: function (electId, proId, provinceCode, municipalityNo) {
        var self = this;

        self.requestMunicipalityData = $.ajax({
            url: Routing.generate("ajax_get_municipality_recruitment_summary", {
                electId: electId,
                proId: proId,
                provinceCode: provinceCode,
                municipalityNo: municipalityNo
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ data: res });
            self.setSummary(res);
        });
    },

    setSummary: function (data) {
        var totalBarangay = 0;
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalRecruits = 0;
        var totalIs1 = 0;
        var totalIs2 = 0;
        var totalIs3 = 0;
        var totalIs4 = 0;
        var totalIs5 = 0;
        var totalIs6 = 0;
        var totalIs7 = 0;
        var totalVoted = 0;
        var totalVotedRecruits = 0;

        var percentage = 0;

        data.map(function (item) {
            totalBarangay++;
            totalPrecincts += parseInt(item.total_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalIs1 += parseInt(item.total_is_1);
            totalIs2 += parseInt(item.total_is_2);
            totalIs3 += parseInt(item.total_is_3);
            totalIs4 += parseInt(item.total_is_4);
            totalIs5 += parseInt(item.total_is_5);
            totalIs6 += parseInt(item.total_is_6);
            totalIs7 += parseInt(item.total_is_7);
        });

        if (totalRecruits > 0)
            percentage = (totalRecruits / totalRegistered * 100).toFixed(2);

        var summary = {
            totalBarangay: totalBarangay,
            totalPrecincts: totalPrecincts,
            totalRegistered: totalRegistered,
            totalRecruits: totalRecruits,
            totalIs1: totalIs1,
            totalIs2: totalIs2,
            totalIs3: totalIs3,
            totalIs4: totalIs4,
            totalIs5: totalIs5,
            totalIs6: totalIs6,
            totalIs7: totalIs7,
            percentage: percentage
        };

        this.setState({ summary: summary });
    },

    loadMunicipality: function (provinceCode, municipalityNo) {
        var self = this;

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_municipality", { provinceCode: provinceCode, municipalityNo: municipalityNo }),
            type: "GET"
        }).done(function (res) {
            self.setState({ municipality: res });
        });
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render: function () {
        var self = this;
        var counter = 0;
        var filteredVoter = 0;
        var filteredVoted = 0;
        var filteredRecruits = 0;
        var filteredWithIdRecruits = 0;
        var filteredWithIdRecruitsPercentage = 0;
        var filteredVotedRecruits = 0;
        var filteredRecruitsPercentage = 0;
        var filteredCellphone = 0;
        var filteredHasCellphonePercentage = 0;
        var filteredBarangay = 0;
        var filteredPrecincts = 0;
        var filteredRegistered = 0;
        var filteredIs1 = 0;
        var filteredIs2 = 0;
        var filteredIs3 = 0;
        var filteredIs4 = 0;
        var filteredIs5 = 0;
        var filteredIs6 = 0;
        var filteredIs7 = 0;

        self.state.data.map(function (item) {
            if (self.display(item)) {

                filteredBarangay++;
                filteredPrecincts += parseInt(item.total_precincts);
                filteredRegistered += parseInt(item.total_voters);

                filteredIs1 += parseInt(item.total_is_1);
                filteredIs2 += parseInt(item.total_is_2);
                filteredIs3 += parseInt(item.total_is_3);
                filteredIs4 += parseInt(item.total_is_4);
                filteredIs5 += parseInt(item.total_is_5);
                filteredIs6 += parseInt(item.total_is_6);
                filteredIs7 += parseInt(item.total_is_7)

                filteredVoter += parseInt(item.total_voters);
                filteredRecruits += parseInt(item.total_recruits);
                filteredWithIdRecruits += parseInt(item.total_with_id_recruits);
                filteredCellphone += parseInt(item.total_has_cellphone);
            }
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);
        filteredWithIdRecruitsPercentage = ((filteredWithIdRecruits / filteredRecruits) * 100 ).toFixed(2);
        filteredHasCellphonePercentage = ((filteredCellphone / filteredRecruits) * 100).toFixed(2);

        return (
            <div>
                {
                    self.state.showItemDetail && !self.isEmpty(self.state.targetBarangay) &&
                    <OrganizationSummaryItemDetail
                        electId={self.props.electId}
                        proId={self.props.proId}
                        provinceCode={self.props.provinceCode}
                        municipalityNo={self.props.municipalityNo}
                        brgyNo={self.state.targetBarangay}
                        voterGroup={self.state.targetVoterGroup}
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                    >
                    </OrganizationSummaryItemDetail>
                }

                <div style={{ marginBottom: "5px" }}><strong>City/Municipality : {this.state.municipality.name}</strong></div>
                <div>
                    <div className="col-md-3" style={{ padding: 0 }}>
                        <div className="bold">Total Barangay : <span className="font-red-sunglo">{this.numberWithCommas(filteredBarangay)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(filteredPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(filteredRegistered)}</span></div>
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total 1 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs1)}</span></div>
                        <div className="bold">Total 2 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs2)}</span></div>
                        <div className="bold">Total 3 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs3)}</span></div>
                        <div className="bold">Total 4 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs4)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total 5 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs5)}</span></div>
                        <div className="bold">Total 6 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs6)}</span></div>
                        <div className="bold">Total 7 : <span className="font-red-sunglo">{this.numberWithCommas(filteredIs7)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-4">
                        <div className="bold">Total : <span className="font-red-sunglo"> {this.numberWithCommas(filteredRecruits)} </span> <small><em>( {this.numberWithCommas(filteredRecruitsPercentage)} % )</em></small> </div>
                        <div className="bold">With Cellphone : <span className="font-red-sunglo"> {this.numberWithCommas(filteredCellphone)} </span> <small><em>( {this.numberWithCommas(filteredHasCellphonePercentage)} % )</em></small></div>
                    </div>
                </div>
                <div className="clearfix" />
                <div className="col-md-6" style={{ padding: "0" }}>
                    <select value={this.state.mode} onChange={self.setMode} style={{ marginTop: "8px" }}>
                        <option value="all"> All</option>
                        <option value="has_entry">Has Entry</option>
                        <option value="no_entry">No Entry</option>
                        <option value="my_favorites">Favorites</option>
                    </select>
                </div>
                <div className="col-md-6 text-right" style={{ marginTop: "10px", marginBottom: "5px", padding: "0" }}>
                    <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000) - this.state.refreshCounter}s</span> </small>
                    <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                    <button style={{ marginLeft: "10px" }} className="btn btn-danger btn-xs" disabled={this.state.mode == 'all' ? this.state.updating : this.state.updateCount > 0 } onClick={this.recalculate}>
                        {!this.state.updating || this.state.updateCount <= 0 ? "Recalculate" : "Please wait this may take a while.."}
                    </button>
                </div>
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2">Brgy</th>
                                <th className="text-center" rowSpan="2">Prec</th>
                                <th className="text-center" rowSpan="2">Reg</th>
                                <th className="text-center" colSpan="11">Organization</th>
                            </tr>
                            <tr>
                                <th className="text-center">1</th>
                                <th className="text-center">2</th>
                                <th className="text-center">3</th>
                                <th className="text-center">4</th>
                                <th className="text-center">5</th>
                                <th className="text-center">6</th>
                                <th className="text-center">7</th>
                                <th className="text-center">8</th>
                                <th className="text-center">Total</th>
                                <th className="text-center">CP</th>
                                <th className="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.data.map(function (item, index) {
                                if (self.display(item)) {
                                    counter += 1;

                                    return (
                                        <tr>
                                            <td className="text-center">{counter}</td>
                                            <td className="text-left">
                                                <label className="mt-checkbox status-checkbox">
                                                    <input onClick={self.toggleFavorite.bind(self, item)} type="checkbox" checked={parseInt(item.is_favorite) ? 1 : 0} name="is7"></input><span></span>
                                                </label>
                                                {item.name}
                                            </td>
                                            <td className="text-center">{item.total_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_precincts)}</td>
                                            <td className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                            <td className="text-center font-grey-gallery" >
                                                {item.total_is_1 == 0 ? "- - -" : self.numberWithCommas(item.total_is_1)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_2 == 0 ? "- - -" : self.numberWithCommas(item.total_is_2)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_3 == 0 ? "- - -" : self.numberWithCommas(item.total_is_3)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_4 == 0 ? "- - -" : self.numberWithCommas(item.total_is_4)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_5 == 0 ? "- - -" : self.numberWithCommas(item.total_is_5)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_6 == 0 ? "- - -" : self.numberWithCommas(item.total_is_6)}
                                            </td>
                                            <td className="text-center font-grey-gallery" >
                                                {item.total_is_7 == 0 ? "- - -" : self.numberWithCommas(item.total_is_7)}
                                            </td>
                                            <td className="text-center">
                                            
                                            </td>
                                            <td className="text-center">
                                                {item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}
                                            </td>
                                            <td className="text-center">{item.total_has_cellphone == 0 ? "- - -" : self.numberWithCommas(item.total_has_cellphone)}</td>
                                            <td className="text-center">{item.total_has_cellphone == 0 ? "- - -" : (item.total_has_cellphone / item.total_recruits * 100).toFixed(2) + "%"}</td>
                                        </tr>
                                    )
                                }
                            })}

                            {this.state.data.length == 0 && !this.state.loading &&
                                <tr>
                                    <td colSpan="14" className="text-center">No records was found...</td>
                                </tr>
                            }

                            {this.state.loading &&
                                <tr>
                                    <td colSpan="14" className="text-center">Data is being processed. Please wait for a while...</td>
                                </tr>
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    },

    setMode: function (e) {
        this.setState({ mode: e.target.value });
    },

    showDetail: function (item, voterGroup) {
        var self = this;
        self.setState({
            targetVoterGroup: voterGroup,
            targetBarangay: item.brgy_no,
            showItemDetail: true
        });
    },

    closeDetailModal: function () {
        this.setState({ showItemDetail: false, targetMunicipality: null, targetVoterGroup: null });
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    },

    toggleFavorite: function (item) {
        var self = this;

        self.requestToggleFavorite = $.ajax({
            url: Routing.generate("ajax_patch_barangay_status"),
            type: "PATCH",
            data: {
                isFavorite: item.is_favorite != 1 ? 1 : 0,
                municipalityCode: item.municipality_code,
                brgyNo: item.brgy_no
            }
        }).done(function (res) {
            self.loadMunicipalityData(self.props.electId, self.props.proId, self.props.provinceCode, self.props.municipalityNo);
        }).fail(function (res) {
            //something went wrong
        });
    },

    display: function (item) {
        var mode = this.state.mode;

        if (mode == 'all') {
            return true;
        } else if (mode == 'no_entry') {
            return item.total_recruits == 0;
        } else if (mode == 'has_entry') {
            return item.total_recruits > 0;
        } else if (mode == 'my_favorites') {
            return parseInt(item.is_favorite) == 1;
        }

        return false;
    },

    isActive: function (updatedAt) {
        if (this.isEmpty(updatedAt))
            return false;

        var today = new Date();
        var lastUpdate = new Date(updatedAt);
        var diffMs = (today - lastUpdate);
        var diffDays = Math.floor(diffMs / 86400000); // days
        var diffHrs = Math.floor((diffMs % 86400000) / 3600000);
        var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes

        if (today.getTime() < lastUpdate.getTime())
            return true;

        return diffDays == 0 && diffHrs == 0 && diffMins <= 10;
    }
});

window.ProjectRecruitmentMunicipalitySummaryTable = ProjectRecruitmentMunicipalitySummaryTable;