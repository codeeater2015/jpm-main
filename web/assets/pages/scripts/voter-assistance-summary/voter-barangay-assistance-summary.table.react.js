var VoterBarangaySummaryTable = React.createClass({

    getInitialState : function(){
      return {
          data : [],
          barangay : {
              name : ""
          },
          summary : {
            totalPrecincts : 0,
            totalRegistered : 0,
            totalRecruits : 0,
            totalLeaders : 0,
            totalMembers : 0,
            percentage : 0,
            total_has_cellphone : 0
          },
          targetProvince : null,
          targetMunicipality : null,
          targetBarangay : null,
          targetPrecinct : null,
          showItemDetail : false,
          loading : false,
          refreshInterval : 300000,
          refreshCounter : 0,
          refreshId : null,
          counterId : null,
          updating : false
      }
    },

    componentDidMount : function(){
       var self = this;

       self.loadData();
       self.initShortcuts();

       var refreshId = setInterval(function(){
            self.loadData();
       },self.state.refreshInterval);

       self.setState({refreshId : refreshId});
    },

    initShortcuts : function(){
        var self = this;
        $("body").keydown(function(e){
            var keyCode = e.keyCode || e.which;
            if(keyCode == 121){
                e.preventDefault();
                self.loadData(); 
            }
        });
    },
    
    componentWillUnmount : function(){
        clearInterval(this.state.refreshId);
    },

    loadData : function(){
        this.loadBarangayData(this.props.provinceCode,this.props.municipalityNo,this.props.brgyNo);
        this.loadBarangay(this.props.provinceCode,this.props.municipalityNo,this.props.brgyNo);
        this.initCounter();
    },

    recalculate : function(){
        var self = this;
        var data = {
            provinceCode : self.props.provinceCode,
            municipalityNo : self.props.municipalityNo,
            brgyNo : self.props.brgyNo
        };

        self.setState({updating : true});
        self.requestUpdateSummary = $.ajax({
            url : Routing.generate("ajax_update_summary"),
            type : 'GET',
            data : data
        }).done(function(){
            self.loadData();
        }).always(function(){
            self.setState({updating : false});
        });
    },
    
    initCounter : function(){
        var self = this;
        var counterId = self.state.counterId;

        if(counterId != null)
            clearInterval(counterId);

        var counterId = setInterval(function(){
            var refreshCounter = self.state.refreshCounter;
            refreshCounter++;
            self.setState({refreshCounter : refreshCounter});
        },1000);
        
        self.setState({ counterId : counterId, refreshCounter : 0});
    },

    loadBarangayData : function(provinceCode, municipalityNo, brgyNo){
        var self = this;

        self.requestProvinceData = $.ajax({
            url : Routing.generate("ajax_get_barangay_data_summary",{provinceCode : provinceCode, municipalityNo : municipalityNo, brgyNo : brgyNo}),
            type : "GET"
        }).done(function(res){
            self.setState({data : res});
            self.setSummary(res);
        });
    },

    loadBarangay : function(provinceCode, municipalityNo,brgyNo){
        var self = this;
        
        self.requestMunicipality = $.ajax({
            url : Routing.generate("ajax_get_barangay", { provinceCode : provinceCode, municipalityNo : municipalityNo, brgyNo : brgyNo}),
            type : "GET"
        }).done(function(res){
            self.setState({barangay : res});
        });
    },

    setSummary : function(data){
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalRecruits = 0;
        var totalLeaders = 0;
        var totalMembers = 0;
        var percentage = 0;

        data.map(function(item){
            totalPrecincts++;
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalLeaders += parseInt(item.total_leaders);
            totalMembers += parseInt(item.total_members);
        });

        if(totalRecruits > 0)
            percentage = (totalRecruits/totalRegistered * 100).toFixed(2);

        var summary = {
            totalPrecincts : totalPrecincts,
            totalRegistered  : totalRegistered,
            totalRecruits : totalRecruits,
            percentage : percentage
        };

        this.setState({summary : summary});
    },

    numberWithCommas : function(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },  

    render : function(){
        var self = this;
        var counter = 0;
        var filteredVoter = 0;
        var filteredVoted = 0;
        var filteredRecruits = 0;
        var filteredVotedRecruits = 0;
        var filteredRecruitsPercentage = 0;
        var filteredCellphone = 0;
        var filteredHasCellphonePercentage = 0;

        self.state.data.map(function(item){
                filteredVoter += parseInt(item.total_voters);
                filteredRecruits += parseInt(item.total_recruits);
                filteredCellphone += parseInt(item.total_has_cellphone);
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);
        filteredHasCellphonePercentage = ((filteredCellphone / filteredRecruits) * 100).toFixed(2); 

        return (
            <div>

                {
                    self.state.showItemDetail && !self.isEmpty(self.state.targetPrecinct) &&
                    <VoterSummaryItemDetail 
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                        provinceCode={self.state.targetProvince}
                        municipalityNo={self.state.targetMunicipality}
                        brgyNo={self.state.targetBarangay}
                        precinctNo={self.state.targetPrecinct}
                        >
                    </VoterSummaryItemDetail>        
                }

                <div style={{marginBottom:"5px"}}><strong>Barangay : {this.state.barangay.name}</strong></div>
                <div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRegistered)}</span></div>
                    </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Recruited Voter : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.totalRecruits)}</span></div>
                        <div className="bold">Percentage : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.percentage)} %</span> </div>
                    </div>
                    
                </div>
                <div className="clearfix"></div>
                <div style={{marginTop:"10px"}}>
                    <div className="bold" style={{ marginBottom : "5px" }}>Filtered Summary: </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(filteredVoter)}</span></div>
                    </div>                        
                    <div className="clearfix"/>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Recruited Voter : <span className="font-red-sunglo"> {this.numberWithCommas(filteredRecruits)}</span></div>
                    </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Percentage : <span className="font-red-sunglo">{this.numberWithCommas(filteredRecruitsPercentage)} %</span></div>
                    </div>
                    <div className="clearfix"></div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">With Cellphone : <span className="font-red-sunglo"> {this.numberWithCommas(filteredCellphone)}</span></div>
                    </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Percentage : <span className="font-red-sunglo">{this.numberWithCommas(filteredHasCellphonePercentage)} %</span></div>
                    </div>
                </div>
                <div className="clearfix"/>

                <div className="text-right" style={{marginTop:"10px" , marginBottom : "5px"}}>
                    <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000)  - this.state.refreshCounter}s</span> </small>
                    <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                    {!this.state.updating &&  <button className="btn btn-danger btn-xs" onClick={this.recalculate} style={{marginLeft : "8px"}}>Update</button>}
                    {this.state.updating &&  <button className="btn btn-danger btn-xs" onClick={this.recalculate} style={{marginLeft : "8px"}}><i className="fa fa-spinner fa-pulse fa-1x fa-fw"></i>Updating Please wait...</button>}
                </div>
                <div className="table-container">

                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2">Prec</th>
                                <th className="text-center" rowSpan="2">Reg</th>
                                <th className="text-center" rowSpan="2">Voted</th>
                                <th className="text-center" colSpan="8">Recruited Voter</th>
                                <th className="text-center" rowSpan="2"></th>
                            </tr>
                            <tr>
                                <th className="text-center">L</th>
                                <th className="text-center">M</th>
                                <th className="text-center">Total</th>
                                <th className="text-center">%</th>
                                <th className="text-center">Voted</th>
                                <th className="text-center">%</th>
                                <th className="text-center">CP</th>
                                <th className="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.data.map(function(item,index){
                                counter++;
                                return (
                                    <tr>
                                        <td  className="text-center">{++index}</td>
                                        <td  className="text-center"> {item.precinct_no} </td>
                                        <td  className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                        <td  className="text-center">{item.total_voted == 0 ? "- - -" : self.numberWithCommas(item.total_voted)}</td>
                                        <td  className="text-center">{item.total_leaders == 0 ? "- - -" : self.numberWithCommas(item.total_leaders)}</td>
                                        <td  className="text-center">{item.total_members == 0 ? "- - -" : self.numberWithCommas(item.total_members)}</td>
                                        <td  className="text-center">{item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}</td>
                                        <td  className="text-center">{item.total_recruits == 0 ? "- - -" : (item.total_recruits / item.total_voters * 100).toFixed(2) + " %"}</td>
                                        <td  className="text-center">{item.total_voted_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_voted_recruits)}</td>
                                        <td  className="text-center">{item.total_voted_recruits == 0 ? "- - -" : (item.total_voted_recruits / item.total_recruits * 100).toFixed(2) + " %"}</td>
                                        <td  className="text-center">{item.total_has_cellphone == 0 ? "- - -" : self.numberWithCommas(item.total_has_cellphone)}</td>
                                        <td  className="text-center">{item.total_has_cellphone == 0 ? "- - -" : (item.total_has_cellphone / item.total_recruits * 100).toFixed(2) + "%"}</td>
                                        <td  className="text-center">
                                            <button className="btn btn-primary btn-xs" type="button" onClick={self.showDetail.bind(self,item)}>
                                                <small>Details</small>
                                            </button>
                                        </td>
                                    </tr>
                                )
                            })}
                            { this.state.data.length == 0 && !this.state.loading && 
                                <tr>
                                    <td colSpan="13" className="text-center">No records was found...</td>
                                </tr>
                            }
                            { this.state.loading &&
                                <tr>
                                    <td colSpan="13" className="text-center">Data is being processed. Please wait for a while...</td>
                                </tr>
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    },

    showDetail : function(item){
        var self = this;
        self.setState(
            { 
                targetProvince : self.props.provinceCode,
                targetMunicipality : self.props.municipalityNo,
                targetBarangay : self.props.brgyNo,
                targetPrecinct : item.precinct_no,
                showItemDetail : true
            }
        );
    },

    closeDetailModal : function(){
        this.setState({
            showItemDetail : false, 
            targetProvince : null,
            targetMunicipality : null,
            targetBarangay : null,
            targetPrecinct : null
        });
    },

    isEmpty : function(value){
        return value == null || value == '' || value == 'undefined';
    }
});

window.VoterBarangaySummaryTable = VoterBarangaySummaryTable;