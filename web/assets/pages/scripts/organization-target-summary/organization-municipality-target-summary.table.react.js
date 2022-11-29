var OrganizationMunicipalityTargetSummaryTable = React.createClass({

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
            targetHasId: null,
            targetHasSubmitted: null,
            targetHasAst: null,
            showItemDetail: false,
            loading: false,
            refreshInterval: 300000,
            refreshCounter: 0,
            refreshId: null,
            counterId: null,
            mode: 'all',
            updating: false,
            updateCount: 0,
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
        this.loadMunicipalityData(this.props.electId, this.props.proId, this.props.provinceCode, this.props.municipalityNo, this.props.createdAt);
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
                url: Routing.generate("ajax_update_project_voter_summary_by_municipality", data),
                type: 'GET'
            }).done(function () {
                self.loadData();
            }).always(function () {
                self.setState({ updating: false });
                alert("Re-computation has been completed...");
            });

        } else if (self.state.mode == 'my_favorites') {

            self.setState({ updateCount: 0 });

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

                        console.log("update count");
                        console.log(updateCount);

                        self.setState({ updateCount: updateCount });
                    });

                    counter++;
                }

            });
            
            self.setState({ updateCount: counter })
        }
    },

    loadMunicipalityData: function (electId, proId, provinceCode, municipalityNo, createdAt) {
        var self = this;

        self.requestMunicipalityData = $.ajax({
            url: Routing.generate("ajax_get_municipality_organization_summary", {
                electId: electId,
                proId: proId,
                provinceCode: provinceCode,
                municipalityNo: municipalityNo,
                createdAt : createdAt
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
        var totalCH = 0;
        var totalKCL = 0;
        var totalKCL0 = 0;
        var totalKCL1 = 0;
        var totalKCL2 = 0;
        var totalDAO = 0;
        var totalOthers = 0;
        var totalVoted = 0;
        var totalVotedRecruits = 0;

        var percentage = 0;

        data.map(function (item) {
            totalBarangay++;
            totalPrecincts += parseInt(item.total_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalCH += parseInt(item.total_ch);
            totalKCL += parseInt(item.total_kcl);
            totalKCL0 += parseInt(item.total_kcl0);
            totalKCL1 += parseInt(item.total_kcl1);
            totalKCL2 += parseInt(item.total_kcl2);
            totalDAO += parseInt(item.total_staff);
            totalOthers += parseInt(item.total_others)
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

    setMode: function (e) {
        this.setState({ mode: e.target.value });
    },

    setDisplayMode: function (e) {
        this.setState({ displayMode: e.target.value });
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    },

    caclTurnOut : function(item){
        return parseInt(item.total_voters * 0.8);
    },

    calcTargetVotes : function(item){
        return parseInt(item.total_voters * 0.8 * 0.6);
    },

    calcLess : function(item){
        return  parseInt(item.total_voters * 0.8 * 0.6 * 0.05);
    },

    calcTotalTarget : function(item){
        return this.calcTargetVotes(item) - this.calcLess(item)
    },

    calcTargetPercentage : function(item){
        return ((this.getTotalRecruits(item) / this.calcTotalTarget(item)) * 100).toFixed(2);
    },

    calcTargetDiff : function(item){
        return this.calcTotalTarget(item)- this.getTotalRecruits(item);
    },

    getTotalRecruits : function(item){
        switch (this.state.displayMode) {
            case "default":
                return item.total_recruits;
                break;
            case "with_id":
                return item.total_with_id_recruits;
                break;
            case "no_id":
                return item.total_no_id_recruits;
                break;
        }
    },

    getTotalTL: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_tl == 0 ? "- - -" : this.numberWithCommas(item.total_tl);
                break;
            case "with_id":
                return item.total_with_id_tl == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_tl);
                break;
            case "no_id":
                return item.total_no_id_tl == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_tl);
                break;
        }
    },

    getTotalSL: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_sl == 0 ? "- - -" : this.numberWithCommas(item.total_sl);
                break;
            case "with_id":
                return item.total_with_id_sl == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_sl);
                break;
            case "no_id":
                return item.total_no_id_sl == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_sl);
                break;
        }
    },

    getTotalMembers: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_members == 0 ? "- - -" : this.numberWithCommas(item.total_members);
                break;
            case "with_id":
                return item.total_with_id_members == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_members);
                break;
            case "no_id":
                return item.total_no_id_members == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_members);
                break;
        }
    },

    getTotalOthers: function (item) {
        switch (this.state.displayMode) {
            case "default":
                return item.total_others == 0 ? "- - -" : this.numberWithCommas(item.total_others);
                break;
            case "with_id":
                return item.total_with_id_others == 0 ? '- - -' : this.numberWithCommas(item.total_with_id_others);
                break;
            case "no_id":
                return item.total_no_id_others == 0 ? '- - -' : this.numberWithCommas(item.total_no_id_others);
                break;
        }
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
    },

    showOrganizationSummary : function(){
        var url = window.reportUrl + "kfc/organization-target-summary/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&municipality_code=" + this.props.provinceCode + this.props.municipalityNo + "&created_at=" + this.props.createdAt;
        this.popupCenter(url, 'Organization Target Summary', 900, 600);
    },

    showOrganizationSummaryByNORV : function(){
        var url = window.reportUrl + "kfc/organization-target-summary-by-norv/index.php?province_code=" + this.props.provinceCode +  "&pro_id=" + this.props.proId + "&elect_id=" + this.props.electId  + "&municipality_code=" + this.props.provinceCode + this.props.municipalityNo + "&created_at=" + this.props.createdAt;
        this.popupCenter(url, 'Organization Target Summary', 900, 600);
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
  
    showDetail: function (item, voterGroup) {
        var self = this;
        var hasId = null;
        var hasSubmitted = null;
        var displayMode = self.state.displayMode;
        var hasAst = null;

        if (displayMode == 'with_id' || displayMode == 'with_id_percentage') {
            hasId = 1;
        } else if (displayMode == 'has_submitted' || displayMode == 'has_submitted_percentage') {
            hasSubmitted = 1;
        } else if (displayMode == 'no_id' || displayMode == 'no_id_percentage') {
            hasId = 0;
        } else if (displayMode == "not_submitted" || displayMode == "not_submitted_percentage") {
            hasSubmitted = 0;
        } else if (displayMode == 'is_junior' || displayMode == 'is_junior_percentage') {
            hasAst = 1;
        } else if (displayMode == 'not_junior' || displayMode == 'not_junior_percentage') {
            hasAst = 0;
        }

        self.setState({
            targetVoterGroup: voterGroup,
            targetBarangay: item.brgy_no,
            targetHasId: hasId,
            targetHasSubmitted: hasSubmitted,
            targetHasAst: hasAst,
            showItemDetail: true
        });
    },

    closeDetailModal: function () {
        this.setState({ showItemDetail: false, targetMunicipality: null, targetVoterGroup: null });
    },

    render: function () {
        var self = this;

        var counter = 0;
        var filteredVoter = 0;
        var filteredRecruits = 0;
        var filteredRecruitsPercentage = 0;
        var filteredBarangay = 0;
        var filteredClusteredPrecincts = 0;
        var filteredPrecincts = 0;
        var filteredRegistered = 0;
        var filteredTL = 0;
        var filteredSL = 0;
        var filteredMembers = 0;
        var filteredOthers = 0;
        var filteredDAO = 0;


        self.state.data.map(function (item) {
            if (self.display(item)) {

                var displayMode = self.state.displayMode;

                filteredBarangay++;
                filteredClusteredPrecincts += parseInt(item.total_clustered_precincts);
                filteredPrecincts += parseInt(item.total_precincts);
                filteredRegistered += parseInt(item.total_voters);
                filteredVoter += parseInt(item.total_voters);

                if (displayMode == "default") {

                    filteredTL += parseInt(item.total_tl);
                    filteredSL += parseInt(item.total_sl);
                    filteredMembers += parseInt(item.total_members);
                    filteredDAO += parseInt(item.total_staff);
                    filteredOthers += parseInt(item.total_others);
                    filteredRecruits += parseInt(item.total_recruits);

                } else if (displayMode == "with_id") {
                    
                    filteredTL += parseInt(item.total_with_id_tl);
                    filteredSL += parseInt(item.total_with_id_sl);
                    filteredMembers += parseInt(item.total_with_id_members);
                    filteredDAO += parseInt(item.total_with_id_staff);
                    filteredOthers += parseInt(item.total_with_id_others);
                    filteredRecruits += parseInt(item.total_with_id_recruits);

                } else if (displayMode == "no_id") {

                    filteredTL += parseInt(item.total_no_id_tl);
                    filteredSL += parseInt(item.total_no_id_sl);
                    filteredMembers += parseInt(item.totalno_id_members);
                    filteredDAO += parseInt(item.total_no_id_staff);
                    filteredOthers += parseInt(item.total_no_id_others);
                    filteredRecruits += parseInt(item.total_no_id_recruits);

                }  
            }
        });

        return (
            <div>
                <div style={{ marginBottom: "5px" }}><strong>City/Municipality : {this.state.municipality.name}</strong></div>

                    {
                        self.state.showItemDetail && !self.isEmpty(self.state.targetBarangay) &&
                        <OrganizationSummaryItemDetail
                            electId={self.props.electId}
                            proId={self.props.proId}
                            provinceCode={self.props.provinceCode}
                            municipalityNo={self.props.municipalityNo}
                            brgyNo={self.state.targetBarangay}
                            voterGroup={self.state.targetVoterGroup}
                            hasId={self.state.targetHasId}
                            hasSubmitted={self.state.targetHasSubmitted}
                            hasAst={self.state.targetHasAst}
                            show={self.state.showItemDetail}
                            onHide={self.closeDetailModal}
                        >
                        </OrganizationSummaryItemDetail>
                    }

                <div>
                    <div className="col-md-3" style={{ padding: 0 }}>
                        <div className="bold">Total Barangay : <span className="font-red-sunglo">{this.numberWithCommas(filteredBarangay)}</span></div>
                        <div className="bold">Total Clustered Precincts : <span className="font-red-sunglo">{this.numberWithCommas(filteredClusteredPrecincts)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(filteredPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(filteredRegistered)}</span></div>
                    </div>
                    
                    <div className="col-md-2" style={{ padding: 0 }}>
                        <div className="bold">Total TL : <span className="font-red-sunglo">{this.numberWithCommas(filteredTL)}</span></div>
                        <div className="bold">Total SL : <span className="font-red-sunglo">{this.numberWithCommas(filteredSL)}</span></div>
                        <div className="bold">Total Members : <span className="font-red-sunglo">{this.numberWithCommas(filteredMembers)}</span></div>
                    </div>
                
                    <div className="col-md-4">
                        <div className="bold">Total DAO : <span className="font-red-sunglo">{this.numberWithCommas(filteredDAO)}</span></div>
                        <div className="bold">Total Others : <span className="font-red-sunglo">{this.numberWithCommas(filteredOthers)}</span></div>
                        <div className="bold">Total Recruits : <span className="font-red-sunglo"> {this.numberWithCommas(filteredRecruits)} </span></div>
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
                    <select value={this.state.displayMode} onChange={self.setDisplayMode} style={{ marginTop: "8px", marginLeft: "10px" }}>
                        <option value="default">All Recruits</option>
                        <option value="with_id">With ID's</option>
                        <option value="no_id">No ID </option>
                    </select>
                </div>
                <div className="col-md-6 text-right" style={{ marginTop: "10px", marginBottom: "5px", padding: "0" }}>
                    <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000) - this.state.refreshCounter}s</span> </small>
                   
                    <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                   
                    <div style={{ marginLeft: "10px" }} className="btn-group">
                        <button type="button" className="btn btn-xs blue">Download PDF</button>
                        <button type="button" className="btn btn-xs blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"><i className="fa fa-angle-down"></i></button>
                        <ul className="dropdown-menu" role="menu">
                            <li><a href="javascript:;" onClick={this.showOrganizationSummary} ><i className="fa fa-file-pdf-o"></i>By Name</a></li>';
                            <li><a href="javascript:;" onClick={this.showOrganizationSummaryByNORV} ><i className="fa fa-file-pdf-o"></i>By NORV</a></li>';
                        </ul>
                    </div>
                   
                    <button style={{ marginLeft: "10px" }} className="btn btn-danger btn-xs" disabled={this.state.mode == 'all' ? this.state.updating : this.state.updateCount > 0} onClick={this.recalculate}>
                        {!this.state.updating || this.state.updateCount <= 0 ? "Recalculate" : "Please wait this may take a while.."}
                    </button>
                </div>
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2"><small>BRGY</small></th>
                                <th className="text-center" rowSpan="2"><small>CPREC</small></th>
                                <th className="text-center" rowSpan="2"><small>PREC</small></th>
                                <th className="text-center" rowSpan="2"><small>NORV</small></th>
                                <th className="text-center" rowSpan="2"><small>TO (80%)</small></th>
                                <th className="text-center" rowSpan="2"><small>TV (60%)</small></th>
                                <th className="text-center" rowSpan="2"><small>Less (5%)</small></th>
                                <th className="text-center" rowSpan="2"><small>Target</small></th>
                                <th className="text-center" colSpan="5"><small>ORGANIZATION</small></th>
                                <th className="text-center" rowSpan="2"><small>% of Target</small></th>
                                <th className="text-center" rowSpan="2"><small>Diff</small></th>
                            </tr>
                            <tr>
                                <th className="text-center">TL</th>
                                <th className="text-center">SL</th>
                                <th className="text-center">MEM</th>
                                <th className="text-center">KFC</th>
                                <th className="text-center">Total</th>
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
                                                {item.name}
                                            </td>
                                            <td className="text-center">
                                                {item.total_clustered_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_clustered_precincts)}
                                            </td>
                                            <td className="text-center">
                                                {item.total_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_precincts)}
                                            </td>
                                            <td className="text-center">
                                                {item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}
                                            </td>
                                            <td className="text-center" >
                                                { item.total_voters == 0 ? "- - -" : self.numberWithCommas(self.caclTurnOut(item)) }
                                            </td>
                                            <td className="text-center" >
                                                {item.total_voters == 0 ? "- - -" : self.numberWithCommas(self.calcTargetVotes(item)) }
                                            </td>
                                            <td className="text-center" >
                                                {item.total_voters == 0 ? "- - -" : self.numberWithCommas(self.calcLess(item)) }
                                            </td>
                                            
                                            <td className="text-center">
                                                {item.total_voters == 0 ? "- - -" : (self.calcTotalTarget(item))}
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "CH,KCL")}>
                                                { self.getTotalTL(item) }
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "KCL0,KCL1,KCL2")}>
                                                {self.getTotalSL(item)}
                                            </td>
                                            <td  className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "KCL3")}>
                                                { self.getTotalMembers(item) }
                                            </td>
                                            <td  className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "KFC")}>
                                                { self.getTotalOthers(item) }
                                            </td>
                                            <td className="text-center font-grey-gallery bold pointer" onClick={self.showDetail.bind(self, item, "ALL")}>
                                                { self.getTotalRecruits(item) == 0 ? "- - - " : self.numberWithCommas(self.getTotalRecruits(item)) }
                                            </td>
                                            <td className="text-center">
                                                { self.calcTargetPercentage(item) <= 0 ? "- - -" :  self.calcTargetPercentage(item)  }
                                            </td>
                                            <td className="text-center">
                                                { self.numberWithCommas(self.calcTargetDiff(item)) }
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
    }
});

window.OrganizationMunicipalityTargetSummaryTable = OrganizationMunicipalityTargetSummaryTable;