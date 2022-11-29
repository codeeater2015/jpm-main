var OrganizationProvinceSummaryTable = React.createClass({

    getInitialState: function () {
        return {
            data: [],
            summary: {
                totalMunicipality: 0,
                totalBarangay: 0,
                totalPrecincts: 0,
                totalRegistered: 0,
                totalRecruits: 0,
                totalCH: 0,
                totalKCL: 0,
                totalKCL0: 0,
                totalKCL1: 0,
                totalKCL2: 0,
                totalDAO: 0,
                totalOthers: 0,
                totalHasCellphone: 0,
                percentage: 0
            },
            province: {
                name: ''
            },
            targetMunicipality: null,
            targetVoterGroup: null,
            targetHasId: null,
            targetHasSubmitted: null,
            targetHasAst : null,
            showItemDetail: false,
            loading: false,
            refreshInterval: 300000,
            refreshCounter: 0,
            refreshId: null,
            counterId: null,
            mode: 'all',
            displayMode: 'default'
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

    componentWillUnmount: function () {
        clearInterval(this.state.refreshId);
        clearInterval(this.state.counterId);
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
        this.loadProvince(this.props.provinceCode);
        this.loadProvinceData(this.props.electId, this.props.proId, this.props.provinceCode, this.props.createdAt);
        this.initCounter();
    },

    loadProvince: function (provinceCode) {
        var self = this;
        self.requestProvince = $.ajax({
            url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
            type: "GET"
        }).done(function (res) {
            self.setState({ province: res });
        });
    },

    loadProvinceData: function (electId, proId, provinceCode, createdAt) {
        var self = this;

        self.requestProvinceData = $.ajax({
            url: Routing.generate("ajax_get_province_organization_summary", 
            { 
                electId: electId, 
                proId: proId, 
                provinceCode: provinceCode,
                createdAt : createdAt
            }),
            type: "GET"
        }).done(function (res) {
            self.setState({ data: res });
            self.setSummary(res);
        }).always(function () {
            self.setState({ loading: false });
        });

        self.setState({ loading: true });
    },

    setSummary: function (data) {
        var totalBarangay = 0;
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalCH = 0;
        var totalKCL = 0;
        var totalKCL0 = 0;
        var totalKCL1 = 0;
        var totalKCL2 = 0;
        var totalDAO = 0;
        var totalOthers = 0;
        var totalRecruits = 0;
        var percentage = 0;

        data.map(function (item) {
            totalBarangay += parseInt(item.total_barangays);
            totalPrecincts += parseInt(item.total_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalCH += parseInt(item.total_ch);
            totalKCL += parseInt(item.total_kcl);
            totalKCL0 += parseInt(item.total_kcl0);
            totalKCL1 += parseInt(item.total_kcl1);
            totalKCL2 += parseInt(item.total_kcl2);
            totalDAO += parseInt(item.total_staff);
            totalOthers += parseInt(item.total_others);
        });

        if (totalRecruits > 0)
            percentage = (totalRecruits / totalRegistered * 100).toFixed(2);

        var summary = {
            totalBarangay: totalBarangay,
            totalPrecincts: totalPrecincts,
            totalRegistered: totalRegistered,
            totalRecruits: totalRecruits,
            totalCH: totalCH,
            totalKCL: totalKCL,
            totalKCL0: totalKCL0,
            totalKCL1: totalKCL1,
            totalKCL2: totalKCL2,
            totalDAO: totalDAO,
            totalOthers: totalOthers,
            percentage: percentage
        };

        this.setState({ summary: summary });
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
        var filteredRecruits = 0;
        var filteredWithIdRecruits = 0;
        var filteredWithIdRecruitsPercentage = 0;
        var filteredRecruitsPercentage = 0;
        var filteredCellphone = 0;
        var filteredHasCellphonePercentage = 0;
        var filteredMunicipality = 0;
        var filteredClusteredPrecincts = 0;
        var filteredPrecincts = 0;
        var filteredRegistered = 0;
        var filteredCH = 0;
        var filteredKCL = 0;
        var filteredKCL0 = 0;
        var filteredKCL1 = 0;
        var filteredKCL2 = 0;
        var filteredKCL3 = 0;
        var filteredDAO = 0;
        var filteredKJR = 0;
        var filteredOthers = 0;

        self.state.data.map(function (item) {
            if (self.display(item)) {
                var displayMode = self.state.displayMode;

                filteredMunicipality++;
                filteredPrecincts += parseInt(item.total_precincts);
                filteredClusteredPrecincts += parseInt(item.total_clustered_precincts);
                filteredRegistered += parseInt(item.total_voters);
                filteredVoter += parseInt(item.total_voters);

                if (displayMode == "default") {

                    filteredCH += parseInt(item.total_ch);
                    filteredKCL += parseInt(item.total_kcl);
                    filteredKCL0 += parseInt(item.total_kcl0);
                    filteredKCL1 += parseInt(item.total_kcl1);
                    filteredKCL2 += parseInt(item.total_kcl2);
                    filteredKCL3 += parseInt(item.total_kcl3);
                    filteredKJR += parseInt(item.total_kjr);
                    filteredDAO += parseInt(item.total_staff);
                    filteredOthers += parseInt(item.total_others);
                    filteredRecruits += parseInt(item.total_recruits);
                    filteredCellphone += parseInt(item.total_has_cellphone);

                } else if (displayMode == "with_id" || displayMode == "with_id_percentage") {

                    filteredCH += parseInt(item.total_with_id_ch);
                    filteredKCL += parseInt(item.total_with_id_kcl);
                    filteredKCL0 += parseInt(item.total_with_id_kcl0);
                    filteredKCL1 += parseInt(item.total_with_id_kcl1);
                    filteredKCL2 += parseInt(item.total_with_id_kcl2);
                    filteredKCL3 += parseInt(item.total_with_id_kcl3);
                    filteredKJR += parseInt(item.total_with_id_kjr);
                    filteredDAO += parseInt(item.total_with_id_staff);
                    filteredOthers += parseInt(item.total_with_id_others);
                    filteredRecruits += parseInt(item.total_with_id_recruits);
                    filteredCellphone += parseInt(item.total_with_id_cellphone);

                } else if (displayMode == "no_id" || displayMode == "no_id_percentage") {

                    filteredCH += parseInt(item.total_no_id_ch);
                    filteredKCL += parseInt(item.total_no_id_kcl);
                    filteredKCL0 += parseInt(item.total_no_id_kcl0);
                    filteredKCL1 += parseInt(item.total_no_id_kcl1);
                    filteredKCL2 += parseInt(item.total_no_id_kcl2);
                    filteredKCL3 += parseInt(item.total_no_id_kcl3);
                    filteredDAO += parseInt(item.total_no_id_staff);
                    filteredKJR += parseInt(item.total_no_id_kjr);
                    filteredOthers += parseInt(item.total_no_id_others);
                    filteredRecruits += parseInt(item.total_no_id_recruits);
                    filteredCellphone += parseInt(item.total_no_id_cellphone);

                } else if (displayMode == "has_submitted" || displayMode == "has_submitted_percentage") {

                    filteredCH += parseInt(item.total_has_submitted_ch);
                    filteredKCL += parseInt(item.total_has_submitted_kcl);
                    filteredKCL0 += parseInt(item.total_has_submitted_kcl0);
                    filteredKCL1 += parseInt(item.total_has_submitted_kcl1);
                    filteredKCL2 += parseInt(item.total_has_submitted_kcl2);
                    filteredKCL3 += parseInt(item.total_has_submitted_kcl3);
                    filteredKJR += parseInt(item.total_has_submitted_kjr);
                    filteredOthers += parseInt(item.total_has_submitted_others);
                    filteredRecruits += parseInt(item.total_submitted);
                    filteredCellphone += 0;

                } else if (displayMode == "not_submitted" || displayMode == "not_submitted_percentage") {

                    filteredCH += parseInt(item.total_not_submitted_ch);
                    filteredKCL += parseInt(item.total_not_submitted_kcl);
                    filteredKCL0 += parseInt(item.total_not_submitted_kcl0);
                    filteredKCL1 += parseInt(item.total_not_submitted_kcl1);
                    filteredKCL2 += parseInt(item.total_not_submitted_kcl2);
                    filteredKCL3 += parseInt(item.total_not_submitted_kcl3);
                    filteredKJR += parseInt(item.total_not_submitted_kjr);
                    filteredOthers += parseInt(item.total_not_submitted_others);
                    filteredRecruits += parseInt(item.total_not_submitted_recruits);
                    filteredCellphone += 0;

                } else if (displayMode == "is_junior" || displayMode == "is_junior_percentage"){

                    filteredCH += parseInt(item.total_has_ast_ch);
                    filteredKCL += parseInt(item.total_has_ast_kcl);
                    filteredKCL0 += parseInt(item.total_has_ast_kcl0);
                    filteredKCL1 += parseInt(item.total_has_ast_kcl1);
                    filteredKCL2 += parseInt(item.total_has_ast_kcl2);
                    filteredKCL3 += parseInt(item.total_has_ast_kcl3);
                    filteredOthers += parseInt(item.total_has_ast_others);
                    filteredRecruits += parseInt(item.total_has_ast);
                    filteredDAO += parseInt(item.total_has_ast_staff);
                    filteredKJR += parseInt(item.total_has_ast_kjr);
                    filteredCellphone += parseInt(item.total_has_ast_cellphone);

                } else if (displayMode == "not_junior" || displayMode == "not_junior_percentage"){

                    filteredCH += parseInt(item.total_no_ast_ch);
                    filteredKCL += parseInt(item.total_no_ast_kcl);
                    filteredKCL0 += parseInt(item.total_no_ast_kcl0);
                    filteredKCL1 += parseInt(item.total_no_ast_kcl1);
                    filteredKCL2 += parseInt(item.total_no_ast_kcl2);
                    filteredKCL3 += parseInt(item.total_no_ast_kcl3);
                    filteredKJR += parseInt(item.total_no_ast_kjr);
                    filteredOthers += parseInt(item.total_no_ast_others);
                    filteredRecruits += parseInt(item.total_no_ast);
                    filteredDAO += parseInt(item.total_no_ast_staff);
                    filteredCellphone += parseInt(item.total_no_ast_cellphone);

                }
            }
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);
        filteredHasCellphonePercentage = ((filteredCellphone / filteredRecruits) * 100).toFixed(2);

        return (
            <div>
                {
                    self.state.showItemDetail && !self.isEmpty(self.state.targetMunicipality) &&
                    <OrganizationSummaryItemDetail
                        electId={self.props.electId}
                        proId={self.props.proId}
                        provinceCode={self.props.provinceCode}
                        municipalityNo={self.state.targetMunicipality}
                        voterGroup={self.state.targetVoterGroup}
                        hasId={self.state.targetHasId}
                        hasSubmitted={self.state.targetHasSubmitted}
                        hasAst={self.state.targetHasAst}
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                    >
                    </OrganizationSummaryItemDetail>
                }

                <div style={{ marginBottom: "5px" }}><strong>Province : {this.state.province.name}</strong></div>
                <div>
                    <div className="col-md-3" style={{ padding: 0 }}>
                        <div className="bold">Total Municipalty : <span className="font-red-sunglo">{this.numberWithCommas(filteredMunicipality)}</span></div>
                        <div className="bold">Total Cluster Precincts : <span className="font-red-sunglo">{this.numberWithCommas(filteredClusteredPrecincts)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(filteredPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(filteredRegistered)}</span></div>
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total LGC : <span className="font-red-sunglo">{this.numberWithCommas(filteredCH)}</span></div>
                        <div className="bold">Total LOPP : <span className="font-red-sunglo">{this.numberWithCommas(filteredKCL)}</span></div>
                        <div className="bold">Total LPPP : <span className="font-red-sunglo">{this.numberWithCommas(filteredKCL0)}</span></div>
                        <div className="bold">Total LPPP1 : <span className="font-red-sunglo">{this.numberWithCommas(filteredKCL1)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total LPPP2 : <span className="font-red-sunglo">{this.numberWithCommas(filteredKCL2)}</span></div>
                        <div className="bold">Total LPPP3 : <span className="font-red-sunglo">{this.numberWithCommas(filteredKCL3)}</span></div>
                        <div className="bold">Total JPM : <span className="font-red-sunglo">{this.numberWithCommas(filteredKJR)}</span></div>
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
                    </select>
                    <select value={this.state.displayMode} onChange={self.setDisplayMode} style={{ marginTop: "8px", marginLeft: "10px" }}>
                        <option value="default">All Recruits</option>
                        <option value="with_id">With ID's</option>
                        <option value="with_id_percentage">With ID Percentages</option>
                        <option value="no_id">No ID </option>
                        <option value="no_id_percentage">No ID Percentages</option>
                        {/* <option value="has_submitted">Has Submitted </option>
                        <option value="has_submitted_percentage">Submitted Percentages</option>
                        <option value="not_submitted">Not Submitted </option>
                        <option value="not_submitted_percentage">Not Submitted Percentages</option>
                        <option value="is_junior">Junior</option>
                        <option value="is_junior_percentage">Junior Percentages</option>
                        <option value="not_junior">Not Junior</option>
                        <option value="not_junior_percentage">Not Junior Percentages</option> */}
                    </select>
                </div>
                <div className="col-md-6 text-right" style={{ marginTop: "10px", marginBottom: "5px", padding: "0" }}>
                    <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000) - this.state.refreshCounter}s</span> </small>
                    <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                </div>
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%" >
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2">Mun</th>
                                <th className="text-center" rowSpan="2">Brgy</th>
                                <th className="text-center" rowSpan="2">CPrec</th>
                                <th className="text-center" rowSpan="2">Prec</th>
                                <th className="text-center" rowSpan="2">Reg</th>
                                <th className="text-center" colSpan="12">Organization</th>
                            </tr>
                            <tr>
                                <th className="text-center">LGC</th>
                                <th className="text-center">LOPP</th>
                                <th className="text-center">LPPP</th>
                                <th className="text-center">LPPP1</th>
                                <th className="text-center">LPPP2</th>
                                <th className="text-center">LPPP3</th>
                                <th className="text-center">JPM</th>
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
                                                {item.name}
                                                {
                                                    !self.isEmpty(item.updated_at) &&
                                                    (<span className="font-green-jungle bold" style={{ fontSize: "10px", marginLeft: "5px" }}>
                                                        {moment(new Date(item.updated_at)).fromNow()}
                                                    </span>)
                                                }
                                            </td>
                                            <td className="text-center">{item.total_barangays == 0 ? "- - -" : self.numberWithCommas(item.total_barangays)}</td>
                                            <td className="text-center">{item.total_clustered_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_clustered_precincts)}</td>
                                            <td className="text-center">{item.total_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_precincts)}</td>
                                            <td className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LGC")}>
                                                {self.displayCH(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LOPP")}>
                                                {self.displayKCL(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP")}>
                                                {self.displayKCL0(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP1")}>
                                                {self.displayKCL1(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP2")}>
                                                {self.displayKCL2(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP3")}>
                                                {self.displayKCL3(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "JPM")}>
                                                {self.displayKJR(item)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "ALL")}>
                                                {self.displayTotal(item)}
                                            </td>
                                            <td className="text-center">
                                                {self.displayWithCellphone(item)}
                                            </td>
                                            <td className="text-center">
                                                {self.displayWithCellphonePercentage(item)}
                                            </td>
                                        </tr>
                                    );
                                }
                            })}

                            {this.state.data.length == 0 && !this.state.loading &&
                                <tr>
                                    <td className="text-center" colSpan="18">No records was found...</td>
                                </tr>
                            }
                            {this.state.loading &&
                                <tr>
                                    <td className="text-center" colSpan="18">Data is being processed. Please wait for a while...</td>
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

    setDisplayMode: function (e) {
        this.setState({ displayMode: e.target.value });
    },

    showDetail: function (item, voterGroup) {
        var self = this;
        var hasId = null;
        var hasSubmitted = null;
        var displayMode = self.state.displayMode;
        var hasAst = null;

        if(displayMode == 'with_id' || displayMode == 'with_id_percentage'){
            hasId = 1;
        }else if(displayMode == 'has_submitted' || displayMode == 'has_submitted_percentage'){
            hasSubmitted = 1;
        }else if(displayMode == 'no_id' || displayMode == 'no_id_percentage'){
            hasId = 0;
        }else if(displayMode == "not_submitted" || displayMode == "not_submitted_percentage"){
            hasSubmitted = 0;
        }else if(displayMode == 'is_junior' || displayMode == 'is_junior_percentage'){
            hasAst = 1;
        }else if(displayMode == 'not_junior' || displayMode == 'not_junior_percentage'){
            hasAst = 0;
        }

        self.setState({
            targetVoterGroup: voterGroup,
            targetMunicipality: item.municipality_no,
            targetHasId: hasId,
            targetHasSubmitted: hasSubmitted,
            targetHasAst : hasAst,
            showItemDetail: true
        });
    },

    closeDetailModal: function () {
        this.setState({ showItemDetail: false, targetMunicipality: null, targetVoterGroup: null });
    },

    displayCH: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_ch == 0 ? "- - -" : this.numberWithCommas(item.total_ch);
                break;
            case "with_id":
                return item.total_with_id_ch == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_ch);
                break;
            case "with_id_percentage":
                return item.total_with_id_ch == 0 ? '- - -' : Math.floor(((item.total_with_id_ch / item.total_ch) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_ch == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_ch);
                break;
            case "no_id_percentage":
                return item.total_no_id_ch == 0 ? '- - -' : Math.floor(((item.total_no_id_ch / item.total_ch) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_ch == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_ch);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_ch == 0 ? '- - -' : Math.floor(((item.total_has_submitted_ch / item.total_ch) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_ch == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_ch);
                break;
            case "has_submitted_percentage":
                return item.total_not_submitted_ch == 0 ? '- - -' : Math.floor(((item.total_not_submitted_ch / item.total_ch) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_ch == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_ch);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_ch == 0 ? '- - -' : Math.floor(((item.total_has_ast_ch / item.total_ch) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_ch == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_ch);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_ch == 0 ? '- - -' : Math.floor(((item.total_no_ast_ch / item.total_ch) * 100)) + ' %';
                break;
        }
    },


    displayKCL: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kcl == 0 ? "- - -" : this.numberWithCommas(item.total_kcl);
                break;
            case "with_id":
                return item.total_with_id_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kcl);
                break;
            case "with_id_percentage":
                return item.total_with_id_kcl == 0 ? '- - -' : Math.floor(((item.total_with_id_kcl / item.total_kcl) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kcl);
                break;
            case "no_id_percentage":
                return item.total_no_id_kcl == 0 ? '- - -' : Math.floor(((item.total_no_id_kcl / item.total_kcl) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kcl);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kcl == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kcl / item.total_kcl) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kcl);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_kcl == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kcl / item.total_kcl) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kcl == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl / item.total_kcl) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kcl == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_kcl);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kcl == 0 ? '- - -' : Math.floor(((item.total_no_ast_kcl / item.total_kcl) * 100)) + ' %';
                break;
        }
    },

    displayKCL0: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kcl0 == 0 ? "- - -" : this.numberWithCommas(item.total_kcl0);
                break;
            case "with_id":
                return item.total_with_id_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kcl0);
                break;
            case "with_id_percentage":
                return item.total_with_id_kcl0 == 0 ? '- - -' : Math.floor(((item.total_with_id_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kcl0);
                break;
            case "no_id_percentage":
                return item.total_no_id_kcl0 == 0 ? '- - -' : Math.floor(((item.total_no_id_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kcl0);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kcl0 == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kcl0);
                break;
            case "not_submitted_percentage":
                return item.total_has_submitted_kcl0 == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl0);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kcl0 == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kcl0 == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_kcl0);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kcl0 == 0 ? '- - -' : Math.floor(((item.total_no_ast_kcl0 / item.total_kcl0) * 100)) + ' %';
                break;
        }
    },

    displayKCL1: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kcl1 == 0 ? "- - -" : this.numberWithCommas(item.total_kcl1);
                break;
            case "with_id":
                return item.total_with_id_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kcl1);
                break;
            case "with_id_percentage":
                return item.total_with_id_kcl1 == 0 ? '- - -' : Math.floor(((item.total_with_id_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kcl1);
                break;
            case "no_id_percentage":
                return item.total_no_id_kcl1 == 0 ? '- - -' : Math.floor(((item.total_no_id_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kcl1);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kcl1 == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kcl1);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_kcl1 == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl1);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kcl1 == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kcl1 == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_kcl1);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kcl1 == 0 ? '- - -' : Math.floor(((item.total_no_ast_kcl1 / item.total_kcl1) * 100)) + ' %';
                break;
        }
    },

    displayKCL2: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kcl2 == 0 ? "- - -" : this.numberWithCommas(item.total_kcl2);
                break;
            case "with_id":
                return item.total_with_id_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kcl2);
                break;
            case "with_id_percentage":
                return item.total_with_id_kcl2 == 0 ? '- - -' : Math.floor(((item.total_with_id_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kcl2);
                break;
            case "no_id_percentage":
                return item.total_no_id_kcl2 == 0 ? '- - -' : Math.floor(((item.total_no_id_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kcl2);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kcl2 == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kcl2);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_kcl2 == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl2);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kcl2 == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kcl2 == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl2);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kcl2 == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl2 / item.total_kcl2) * 100)) + ' %';
                break;
        }
    },

    displayKCL3: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kcl3 == 0 ? "- - -" : this.numberWithCommas(item.total_kcl3);
                break;
            case "with_id":
                return item.total_with_id_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kcl3);
                break;
            case "with_id_percentage":
                return item.total_with_id_kcl3 == 0 ? '- - -' : Math.floor(((item.total_with_id_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kcl3);
                break;
            case "no_id_percentage":
                return item.total_no_id_kcl3 == 0 ? '- - -' : Math.floor(((item.total_no_id_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kcl3);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kcl3 == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kcl3);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_kcl3 == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kcl3);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kcl3 == 0 ? '- - -' : Math.floor(((item.total_has_ast_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kcl3 == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_kcl3);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kcl3 == 0 ? '- - -' : Math.floor(((item.total_has_no_kcl3 / item.total_kcl3) * 100)) + ' %';
                break;
        }
    },

    displayKJR: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_kjr == 0 ? "- - -" : this.numberWithCommas(item.total_kjr);
                break;
            case "with_id":
                return item.total_with_id_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_kjr);
                break;
            case "with_id_percentage":
                return item.total_with_id_kjr == 0 ? '- - -' : Math.floor(((item.total_with_id_kjr / item.total_kjr) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_kjr);
                break;
            case "no_id_percentage":
                return item.total_no_id_kjr == 0 ? '- - -' : Math.floor(((item.total_no_id_kjr / item.total_kjr) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_kjr);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_kjr == 0 ? '- - -' : Math.floor(((item.total_has_submitted_kjr / item.total_kjr) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_kjr);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_kjr == 0 ? '- - -' : Math.floor(((item.total_not_submitted_kjr / item.total_kjr) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_kjr);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_kjr == 0 ? '- - -' : Math.floor(((item.total_has_ast_kjr / item.total_kjr) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_kjr == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_kjr);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_kjr == 0 ? '- - -' : Math.floor(((item.total_has_no_kjr / item.total_kjr) * 100)) + ' %';
                break;
        }
    },

    displayDAO: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_staff == 0 ? "- - -" : this.numberWithCommas(item.total_staff);
                break;
            case "with_id":
                return item.total_with_id_staff == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_staff);
                break;
            case "with_id_percentage":
                return item.total_with_id_staff == 0 ? '- - -' : Math.floor(((item.total_with_id_staff / item.total_staff) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_staff == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_staff);
                break;
            case "no_id_percentage":
                return item.total_no_id_staff == 0 ? '- - -' : Math.floor(((item.total_no_id_staff / item.total_staff) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_staff == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_staff);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_staff == 0 ? '- - -' : Math.floor(((item.total_has_ast_staff / item.total_staff) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_staff == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_staff);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_staff == 0 ? '- - -' : Math.floor(((item.total_no_ast_staff / item.total_staff) * 100)) + ' %';
                break;
        }
    },

    displayOthers: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_others == 0 ? "- - -" : this.numberWithCommas(item.total_others);
                break;
            case "with_id":
                return item.total_with_id_others == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_others);
                break;
            case "with_id_percentage":
                return item.total_with_id_others == 0 ? '- - -' : Math.floor(((item.total_with_id_others / item.total_others) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_others == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_others);
                break;
            case "no_id_percentage":
                return item.total_no_id_others == 0 ? '- - -' : Math.floor(((item.total_no_id_others / item.total_others) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_others == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_others);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_others == 0 ? '- - -' : Math.floor(((item.total_has_submitted_others / item.total_others) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_others == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_others);
                break;
            case "not_submitted_percentage":
                return item.total_not_submitted_others == 0 ? '- - -' : Math.floor(((item.total_not_submitted_others / item.total_others) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_others == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_others);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_others == 0 ? '- - -' : Math.floor(((item.total_has_ast_others / item.total_others) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_others == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_others);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_others == 0 ? '- - -' : Math.floor(((item.total_no_ast_others / item.total_others) * 100)) + ' %';
                break;
        }
    },

    displayWithCellphone: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_has_cellphone == 0 ? "- - -" : this.numberWithCommas(item.total_has_cellphone);
                break;
            case "with_id":
                return item.total_with_id_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_cellphone);
                break;
            case "no_id":
                return item.total_no_id_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_cellphone);
                break;
            case "has_submitted":
                return item.total_has_submitted_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_cellphone);
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_has_submitted_cellphone);
                break;
            case "is_junior":
                return item.total_has_ast_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_cellphone);
                break;
            case "is_junior_percentage":
                return item.total_has_ast_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast_cellphone);
                break;
            case "not_junior":
                return item.total_no_ast_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_cellphone);
                break;
            case "not_junior_percentage":
                return item.total_no_ast_cellphone == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast_cellphone);
                break;
            // case "not_submitted":
            //     return item.total_not_submitted_cellphone == 0 ? '- - -' : item.total_not_submitted_cellphone;
            //     break;
            // case "not_submitted_percentage":
            //     return item.total_not_submitted_cellphone == 0 ? '- - -' : item.total_not_submitted_cellphone;
            //     break;
        }
    },

    displayWithCellphonePercentage: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_has_cellphone == 0 ? "- - -" : Math.floor(((item.total_has_cellphone / item.total_recruits) * 100)) + ' %';
                break;
            case "with_id":
                return item.total_with_id_cellphone == 0 ? '- - -' : Math.floor(((item.total_with_id_cellphone / item.total_with_id_recruits) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_cellphone == 0 ? '- - -' : Math.floor(((item.total_no_id_cellphone / item.total_no_id_recruits) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_has_submitted_cellphone == 0 ? '- - -' : Math.floor(((item.total_has_submitted_cellphone / item.total_submitted) * 100)) + ' %';
                break;
            case "has_submitted_percentage":
                return item.total_has_submitted_cellphone == 0 ? '- - -' : Math.floor(((item.total_has_submitted_cellphone / item.total_submitted) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast_cellphone == 0 ? '- - -' : Math.floor(((item.total_has_ast_cellphone / item.total_has_ast) * 100)) + ' %';
                break;
            case "is_junior_percentage":
                return item.total_has_ast_cellphone == 0 ? '- - -' : Math.floor(((item.total_has_ast_cellphone / item.total_has_ast) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast_cellphone == 0 ? '- - -' : Math.floor(((item.total_no_ast_cellphone / item.total_no_ast) * 100)) + ' %';
                break;
            case "not_junior_percentage":
                return item.total_no_ast_cellphone == 0 ? '- - -' : Math.floor(((item.total_no_ast_cellphone / item.total_no_ast) * 100)) + ' %';
                break;
        }
    },

    displayTotal: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_recruits == 0 ? "- - -" : this.numberWithCommas(item.total_recruits);
                break;
            case "with_id":
                return item.total_with_id_recruits == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_recruits);
                break;
            case "with_id_percentage":
                return item.total_with_id_recruits == 0 ? '- - -' : Math.floor(((item.total_with_id_recruits / item.total_recruits) * 100)) + ' %';
                break;
            case "no_id":
                return item.total_no_id_recruits == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_recruits);
                break;
            case "no_id_percentage":
                return item.total_no_id_recruits == 0 ? '- - -' : Math.floor(((item.total_no_id_recruits / item.total_recruits) * 100)) + ' %';
                break;
            case "has_submitted":
                return item.total_submitted == 0 ? '- - -' : this.numberWithCommas(item.total_submitted);
                break;
            case "has_submitted_percentage":
                return item.total_submitted == 0 ? '- - -' : Math.floor(((item.total_submitted / item.total_recruits) * 100)) + ' %';
                break;
            case "not_submitted":
                return item.total_not_submitted_recruits == 0 ? '- - -' : this.numberWithCommas(item.total_not_submitted_recruits);
                break;
            case "has_submitted_percentage":
                return item.total_not_submitted_recruits == 0 ? '- - -' : Math.floor(((item.total_not_submitted_recruits / item.total_recruits) * 100)) + ' %';
                break;
            case "is_junior":
                return item.total_has_ast == 0 ? '- - -' : this.numberWithCommas(item.total_has_ast);
                break;
            case "is_junior_percentage":
                return item.total_has_ast == 0 ? '- - -' : Math.floor(((item.total_has_ast / item.total_recruits) * 100)) + ' %';
                break;
            case "not_junior":
                return item.total_no_ast == 0 ? '- - -' : this.numberWithCommas(item.total_no_ast);
                break;
            case "not_junior_percentage":
                return item.total_no_ast == 0 ? '- - -' : Math.floor(((item.total_no_ast / item.total_recruits) * 100)) + ' %';
                break;
        }
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    },

    display: function (item) {
        var mode = this.state.mode;

        if (mode == 'all') {
            return true;
        } else if (mode == 'active') {
            return this.isActive(item.updated_at);
        } else if (mode == 'no_entry') {
            return item.total_recruits == 0;
        } else if (mode == 'has_entry') {
            return item.total_recruits > 0;
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

window.OrganizationProvinceSummaryTable = OrganizationProvinceSummaryTable;