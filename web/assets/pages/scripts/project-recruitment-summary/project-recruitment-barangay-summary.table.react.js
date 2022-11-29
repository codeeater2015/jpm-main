var ProjectRecruitmentBarangaySummaryTable = React.createClass({

    getInitialState: function () {
        return {
            data: [],
            summary: {
                totalPrecincts : 0,
                totalRegistered : 0,
                totalRecruits : 0,
                totalIs1 : 0,
                totalIs2 : 0,
                totalIs3 : 0,
                totalIs4 : 0,
                totalIs5 : 0,
                totalIs6 : 0,
                totalIs7 : 0,
                totalIs8 : 0,
                totalHasCellphone : 0,
                totalWithIdRecruits : 0,
                percentage: 0
            },
            barangay: {
                name: ""
            },
            targetProvince: null,
            targetMunicipality: null,
            targetBarangay: null,
            targetPrecinct: null,
            targetVoterGroup: null,
            showItemDetail: false,
            loading: false,
            refreshInterval: 300000,
            refreshCounter: 0,
            refreshId: null,
            counterId: null,
            updating: false
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
    },

    loadData: function () {
        this.loadBarangayData(
            this.props.electId,
            this.props.proId,
            this.props.provinceCode,
            this.props.municipalityNo,
            this.props.brgyNo
        );
        this.loadBarangay(this.props.provinceCode, this.props.municipalityNo, this.props.brgyNo);
        this.initCounter();
    },

    recalculate: function () {
        var self = this;
        var data = {
            electId: self.props.electId,
            proId: self.props.proId,
            provinceCode: self.props.provinceCode,
            municipalityNo: self.props.municipalityNo,
            brgyNo: self.props.brgyNo
        };

        self.setState({ updating: true });
        self.setState({ updating: true });
        self.requestUpdateSummary = $.ajax({
            url: Routing.generate("ajax_update_recruitment_summary",data),
            type: 'GET'
        }).done(function () {
            self.loadData();
        }).always(function () {
            self.setState({ updating: false });
        });
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

    loadBarangayData: function (electId, proId, provinceCode, municipalityNo, brgyNo) {
        var self = this;

        self.requestProvinceData = $.ajax({
            url: Routing.generate("ajax_get_barangay_recruitment_summary", {
                electId: electId,
                proId: proId,
                provinceCode: provinceCode,
                municipalityNo: municipalityNo,
                brgyNo: brgyNo
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ data: res });
            self.setSummary(res);
        });
    },

    loadBarangay: function (provinceCode, municipalityNo, brgyNo) {
        var self = this;

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_barangay", {
                provinceCode: provinceCode,
                municipalityNo: municipalityNo,
                brgyNo: brgyNo
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ barangay: res });
        });
    },

    setSummary: function (data) {
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalRecruits = 0;
        var percentage = 0;
        var totalIs1 = 0;
        var totalIs2 = 0;
        var totalIs3 = 0;
        var totalIs4 = 0;
        var totalIs5 = 0;
        var totalIs6 = 0;
        var totalIs7 = 0;
        var totalIs8 = 0;
        var totalHasCellphone = 0;

        data.map(function (item) {
            totalPrecincts++;
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalIs1 += parseInt(item.total_is_1);
            totalIs2 += parseInt(item.total_is_2);
            totalIs3 += parseInt(item.total_is_3);
            totalIs4 += parseInt(item.total_is_4);
            totalIs5 += parseInt(item.total_is_5);
            totalIs6 += parseInt(item.total_is_6);
            totalIs7 += parseInt(item.total_is_7);
            totalHasCellphone += parseInt(item.total_has_cellphone);
        });

        if (totalRecruits > 0)
            percentage = (totalRecruits / totalRegistered * 100).toFixed(2);

        var summary = {
            totalPrecincts: totalPrecincts,
            totalRegistered: totalRegistered,
            totalRecruits: totalRecruits,
            percentage: percentage,
            totalIs1: totalIs1,
            totalIs2: totalIs2,
            totalIs3: totalIs3,
            totalIs4: totalIs4,
            totalIs5: totalIs5,
            totalIs6: totalIs6,
            totalIs7: totalIs7,
            totalIs8: totalIs8,
            totalHasCellphone: totalHasCellphone
        };

        this.setState({ summary: summary });
    },

    numberWithCommas: function (x) {
        if (this.isEmpty(x) || isNaN(x))
            return 0;

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
        var filteredVotedRecruits = 0;
        var filteredRecruitsPercentage = 0;
        var filteredCellphone = 0;
        var filteredHasCellphonePercentage = 0;

        self.state.data.map(function (item) {
            filteredVoter += parseInt(item.total_voters);
            filteredRecruits += parseInt(item.total_recruits);
            filteredCellphone += parseInt(item.total_has_cellphone);
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);
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
                        precinctNo={self.state.targetPrecinct}
                        voterGroup={self.state.targetVoterGroup}
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                    >
                    </OrganizationSummaryItemDetail>
                }


                <div style={{ marginBottom: "5px" }}><strong>City/Municipality : </strong></div>
                <div>
                    <div className="col-md-3" style={{ padding: 0 }}>
                        <div className="bold">Total Barangay : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalBarangay)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRegistered)}</span></div>
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total 1 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs1)}</span></div>
                        <div className="bold">Total 2 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs2)}</span></div>
                        <div className="bold">Total 3 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs3)}</span></div>
                        <div className="bold">Total 4 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs4)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total 5 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs5)}</span></div>
                        <div className="bold">Total 6 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs6)}</span></div>
                        <div className="bold">Total 7 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs7)}</span></div>
                        <div className="bold">Total 8 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalIs8)}</span></div>
                        
                        <br />
                    </div>
                    <div className="col-md-5">
                        <div className="bold">Total : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.totalRecruits)} </span> <small><em>( {this.numberWithCommas(filteredRecruitsPercentage)} %  of total voters )</em></small> </div>
                        <div className="bold">With Cellphone : <span className="font-red-sunglo"> {this.numberWithCommas(filteredCellphone)} </span> <small><em>( {this.numberWithCommas(filteredHasCellphonePercentage)} %  of total members )</em></small></div>
                    </div>
                </div>

                <div>
                    <div className="col-md-6 col-md-offset-6 text-right" style={{ marginTop: "10px", marginBottom: "5px", padding: "0" }}>
                        <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000) - this.state.refreshCounter}s</span> </small>
                        <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                        <button style={{ marginLeft: "10px" }} className="btn btn-danger btn-xs" disabled={this.state.updating} onClick={this.recalculate}>
                            {!this.state.updating ? "Recalculate" : "Please wait this may take a while.."}
                        </button>
                    </div>
                </div>
                <div className="clearfix" />
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
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
                                            <td className="text-center">
                                                {item.precinct_no}
                                            </td>
                                            <td className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_1 == 0 ? "- - -" : self.numberWithCommas(item.total_is_1)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_2 == 0 ? "- - -" :  self.numberWithCommas(item.total_is_2)} 
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_3 == 0 ? "- - -" : self.numberWithCommas(item.total_is_3)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_4 == 0 ? "- - -" : self.numberWithCommas(item.total_is_4)}
                                            </td>
                                            <td className="text-center font-grey-gallery" >
                                                {item.total_is_5 == 0 ? "- - -" : self.numberWithCommas(item.total_is_5)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_6 == 0 ? "- - -" : self.numberWithCommas(item.total_is_6)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                {item.total_is_7 == 0 ? "- - -" : self.numberWithCommas(item.total_is_7)}
                                            </td>
                                            <td className="text-center font-grey-gallery">
                                                
                                            </td>
                                            <td className="text-center">
                                                {item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}
                                            </td>
                                            <td className="text-center">
                                                {item.total_has_cellphone == 0 ? "- - -" : self.numberWithCommas(item.total_has_cellphone)}
                                            </td>
                                            <td className="text-center">
                                                {item.total_has_cellphone == 0 ? "- - -" : (item.total_has_cellphone / item.total_recruits * 100).toFixed(2) + "%"}
                                            </td>
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

    display: function (item) {
        return true;

        var mode = this.state.mode;

        if (mode == 'all') {
            return true;
        } else if (mode == 'no_entry') {
            return item.total_recruits == 0;
        } else if (mode == 'has_entry') {
            return item.total_recruits > 0;
        }

        return false;
    },

    showDetail: function (item, voterGroup) {
        var self = this;
        self.setState(
            {
                targetProvince: self.props.provinceCode,
                targetMunicipality: self.props.municipalityNo,
                targetBarangay: self.props.brgyNo,
                targetPrecinct: item.precinct_no,
                targetVoterGroup: voterGroup,
                showItemDetail: true
            }
        );
    },

    closeDetailModal: function () {
        this.setState({
            showItemDetail: false,
            targetProvince: null,
            targetMunicipality: null,
            targetBarangay: null,
            targetPrecinct: null,
            targetVoterGroup: null
        });
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    }
});

window.ProjectRecruitmentBarangaySummaryTable = ProjectRecruitmentBarangaySummaryTable;