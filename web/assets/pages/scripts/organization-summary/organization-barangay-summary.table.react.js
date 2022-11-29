var OrganizationBarangaySummaryTable = React.createClass({

    getInitialState: function () {
        return {
            data: [],
            summary: {
                totalClusteredPrecincts : 0,
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
            targetHasId : null,
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
            this.props.brgyNo,
            this.props.createdAt
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
            url: Routing.generate("ajax_update_project_voter_summary",data),
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

    loadBarangayData: function (electId, proId, provinceCode, municipalityNo, brgyNo, createdAt) {
        var self = this;
        var endpoint = self.props.assignedPrecinct ? "ajax_get_barangay_organization_summary_assigned" : "ajax_get_barangay_organization_summary";

        self.requestProvinceData = $.ajax({
            url: Routing.generate(endpoint, {
                electId : electId,
                proId : proId,
                provinceCode : provinceCode,
                municipalityNo : municipalityNo,
                brgyNo : brgyNo,
                createdAt : createdAt
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
        var totalClusteredPrecincts = 0;
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalRecruits = 0;
        var percentage = 0;
        var totalCH = 0;
        var totalKCL = 0;
        var totalKCL0 = 0;
        var totalKCL1 = 0;
        var totalKCL2 = 0;
        var totalKCL3 = 0;
        var totalDAO = 0;
        var totalOthers = 0;
        var totalHasCellphone = 0;
        var totalWithIdRecruits = 0;
        var totalKJR = 0;

        data.map(function (item) {
            totalPrecincts++;
            totalClusteredPrecincts = parseInt(item.total_clustered_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalCH += parseInt(item.total_ch);
            totalKCL += parseInt(item.total_kcl);
            totalKCL0 += parseInt(item.total_kcl_0);
            totalKCL1 += parseInt(item.total_kcl_1);
            totalKCL2 += parseInt(item.total_kcl_2);
            totalKCL3 += parseInt(item.total_kcl_3);
            totalKJR += parseInt(item.total_kjr);
            totalDAO += parseInt(item.total_dao);
            totalOthers += parseInt(item.total_others);
            totalHasCellphone += parseInt(item.total_has_cellphone);
            totalWithIdRecruits += parseInt(item.total_with_id_recruits);
        });

        if (totalRecruits > 0)
            percentage = (totalRecruits / totalRegistered * 100).toFixed(2);

        var summary = {
            totalClusteredPrecincts : totalClusteredPrecincts,
            totalPrecincts: totalPrecincts,
            totalRegistered: totalRegistered,
            totalRecruits: totalRecruits,
            percentage: percentage,
            totalCH: totalCH,
            totalKCL: totalKCL,
            totalKCL0: totalKCL0,
            totalKCL1: totalKCL1,
            totalKCL2: totalKCL2,
            totalKCL3: totalKCL3,
            totalKJR : totalKJR,
            totalDAO: totalDAO,
            totalOthers: totalOthers,
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
        var filteredWithIdRecruits = 0;
        var filteredWithIdRecruitsPercentage = 0;
        var filteredCellphone = 0;
        var filteredHasCellphonePercentage = 0;

        self.state.data.map(function (item) {
            filteredVoter += parseInt(item.total_voters);
            filteredRecruits += parseInt(item.total_recruits);
            filteredCellphone += parseInt(item.total_has_cellphone);
            filteredWithIdRecruits += parseInt(item.total_with_id_recruits);
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);
        filteredWithIdRecruitsPercentage = ((filteredWithIdRecruits / filteredRecruits) * 100).toFixed(2);
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
                        assignedPrecinct={self.props.assignedPrecinct}
                        hasId={self.state.targetHasId}
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                    >
                    </OrganizationSummaryItemDetail>
                }


                <div style={{ marginBottom: "5px" }}>Barangay :  <strong>{this.state.barangay.name}</strong></div>
                <div>
                    <div className="col-md-3" style={{ padding: 0 }}>
                        <div className="bold">Clustered Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalClusteredPrecincts)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRegistered)}</span></div>
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total LGC : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalCH)}</span></div>
                        <div className="bold">Total LOPP : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKCL)}</span></div>
                        <div className="bold">Total LPPP : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKCL0)}</span></div>
                        <div className="bold">Total LPPP1 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKCL1)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total LPPP2 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKCL2)}</span></div>
                        <div className="bold">Total LPPP3 : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKCL3)}</span></div>
                        <div className="bold">Total JPM : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalKJR)}</span></div>
                        <br />
                    </div>
                    <div className="col-md-5">
                        <div className="bold">Total : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.totalRecruits)} </span> <small><em>( {this.numberWithCommas(filteredRecruitsPercentage)} %  of total voters )</em></small> </div>
                        <div className="bold">With ID : <span className="font-red-sunglo"> {this.numberWithCommas(filteredWithIdRecruits)} </span> <small><em>( {this.numberWithCommas(filteredWithIdRecruitsPercentage)} %  of total voters )</em></small> </div>
                        <div className="bold">With Cellphone : <span className="font-red-sunglo"> {this.numberWithCommas(filteredCellphone)} </span> <small><em>( {this.numberWithCommas(filteredHasCellphonePercentage)} %  of total members )</em></small></div>
                    </div>
                </div>

                <div>
                    <div className="col-md-6 col-md-offset-6 text-right" style={{ marginTop: "10px", marginBottom: "5px", padding: "0" }}>
                        <small  style={{ marginLeft: "10px" }} >Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000) - this.state.refreshCounter}s</span> </small>
                        <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                        
                        {/* <div style={{ marginLeft: "10px" }} className="btn-group">
                            <button type="button" className="btn btn-xs blue">Download PDF</button>
                            <button type="button" className="btn btn-xs blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"><i className="fa fa-angle-down"></i></button>
                            <ul className="dropdown-menu" role="menu">
                                <li><a href="javascript:;" onClick={this.showAll} ><i className="fa fa-file-pdf-o"></i>All</a></li>
                                <li><a href="javascript:;" onClick={this.showWithId} ><i className="fa fa-file-pdf-o"></i>With ID</a></li>
                                <li><a href="javascript:;" onClick={this.showNoId} ><i className="fa fa-file-pdf-o"></i>No ID</a></li>
                                <li><a href="javascript:;" onClick={this.showDistributionListByPrecinct} ><i className="fa fa-file-pdf-o"></i>Dist by Precinct</a></li>
                                <li><a href="javascript:;" onClick={this.showDistributionListByVotingCenter} ><i className="fa fa-file-pdf-o"></i>Dist by Voting Center</a></li>
                                <li><a href="javascript:;" onClick={this.showDistributionListByFirstLetter} ><i className="fa fa-file-pdf-o"></i>Dist by Letter Group</a></li>
                                <li><a href="javascript:;" onClick={this.showClaimStubByVotingCenter} ><i className="fa fa-file-pdf-o"></i>Claim Stub</a></li>
                            </ul>
                        </div> */}
                        
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
                                <th className="text-center" rowSpan="2">CPrec</th>
                                <th className="text-center" rowSpan="2">Prec</th>
                                <th className="text-center" rowSpan="2">Reg</th>
                                <th className="text-center" colSpan="14">Organization</th>
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
                                <th className="text-center">ID</th>
                                <th className="text-center">No ID</th>
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
                                                {item.clustered_precinct}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "KCL0,KCL1,KCL2,KCL3,KFC","")}>
                                                {item.precinct_no}
                                            </td>
                                            <td className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LGC","")} >
                                                {item.total_ch == 0 ? "- - -" : self.numberWithCommas(item.total_ch)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LOPP","")}>
                                                {item.total_kcl == 0 ? "- - -" :  self.numberWithCommas(item.total_kcl)} 
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP","")}>
                                                {item.total_kcl_0 == 0 ? "- - -" : self.numberWithCommas(item.total_kcl_0)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP1","")}>
                                                {item.total_kcl_1 == 0 ? "- - -" : self.numberWithCommas(item.total_kcl_1)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP2","")}>
                                                {item.total_kcl_2 == 0 ? "- - -" : self.numberWithCommas(item.total_kcl_2)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "LPPP3", "")}>
                                                {item.total_kcl_3 == 0 ? "- - -" : self.numberWithCommas(item.total_kcl_3)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "JPM", "")}>
                                                {item.total_kjr == 0 ? "- - -" : self.numberWithCommas(item.total_kjr)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "ALL","")}>
                                                {item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "ALL", 1)}  >
                                                {item.total_with_id_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_with_id_recruits)}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "ALL", 0)}>
                                                {item.total_with_id_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits - item.total_with_id_recruits)}
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
                                    <td colSpan="17" className="text-center">No records was found...</td>
                                </tr>
                            }

                            {this.state.loading &&
                                <tr>
                                    <td colSpan="17" className="text-center">Data is being processed. Please wait for a while...</td>
                                </tr>
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    },

    showDistributionListByPrecinct : function(){
        var url = window.reportUrl + "kfc/distribution-list-by-precinct/index.php?province_code=" + this.props.provinceCode;
        url += "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId;
        url += "&municipality_no=" + this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'Distrubution List by Precinct', 900, 600);
    },

    showDistributionListByVotingCenter : function(){
        var url = window.reportUrl + "kfc/distribution-list-by-voting-center/index.php?province_code=" + this.props.provinceCode;
        url += "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId;
        url += "&municipality_no=" + this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'Distrubution List by Voting Center', 900, 600);
    },

    showDistributionListByFirstLetter : function(){
        var url = window.reportUrl + "kfc/distribution-list-by-first-letter/index.php?province_code=" + this.props.provinceCode;
        url += "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId;
        url += "&municipality_no=" + this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'Distrubution List by Voting Center', 900, 600);
    },

    showClaimStubByVotingCenter : function(){
        var url = window.reportUrl + "kfc/claim-stub-by-voting-center/index.php?province_code=" + this.props.provinceCode;
        url += "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId;
        url += "&municipality_no=" + this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'Claim Stub by Voting Center', 900, 600);
    },

    showAll : function(){
        var url = window.reportUrl + "kfc-member-list-by-barangay-all/index.php?elect_id=";
        url += this.props.electId + "&pro_id=" + this.props.proId +"&province_code=" + this.props.provinceCode + "&municipality_no="; 
        url +=  this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'All KFC Members', 900, 600);
    },

    showWithId : function(){
        var url =  window.reportUrl + "kfc-member-list-by-barangay-with-id/index.php?elect_id=";
        url += this.props.electId + "&pro_id=" + this.props.proId +"&province_code=" + this.props.provinceCode + "&municipality_no="; 
        url +=  this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'KFC Members With ID', 900, 600);
    },

    showNoId : function(){
        var url =  window.reportUrl + "kfc-member-list-by-barangay-no-id/index.php?elect_id=";
        url += this.props.electId + "&pro_id=" + this.props.proId +"&province_code=" + this.props.provinceCode + "&municipality_no="; 
        url +=  this.props.municipalityNo + "&brgy_no=" + this.props.brgyNo;

        this.popupCenter(url, 'KFC Members No ID', 900, 600);
    },

    popupCenter : function(url, title, w, h) {  
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

    showDetail: function (item, voterGroup, hasId) {
        var self = this;

        self.setState(
            {
                targetProvince: self.props.provinceCode,
                targetMunicipality: self.props.municipalityNo,
                targetBarangay: self.props.brgyNo,
                targetPrecinct: item.precinct_no,
                targetVoterGroup: voterGroup,
                targetHasId : hasId,
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
            targetVoterGroup: null,
            targetHasId : null
        });
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    }
});

window.OrganizationBarangaySummaryTable = OrganizationBarangaySummaryTable;