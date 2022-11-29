var OrganizationMunicipalityPhotoSummaryTable = React.createClass({

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
                totalRecruits : 0,
                totalRecruitsMorning : 0,
                totalRecruitsAfternoon : 0
            },
            targetBarangay: null,
            targetMunicipality: null,
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
    },

    loadData: function () {
        this.loadMunicipality(this.props.provinceCode, this.props.municipalityNo);
        this.loadMunicipalityData(this.props.electId, this.props.proId, this.props.provinceCode, this.props.municipalityNo,this.props.photoDate);
    },

    loadMunicipalityData: function (electId, proId, provinceCode, municipalityNo,photoDate) {
        var self = this;

        self.requestMunicipalityData = $.ajax({
            url: Routing.generate("ajax_get_municipality_organization_photo_summary", {
                electId: electId,
                proId: proId,
                provinceCode: provinceCode,
                municipalityNo: municipalityNo,
                photoDate : photoDate
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
        var totalRecruitsMorning = 0;
        var totalRecruitsAfternoon = 0;

        data.map(function (item) {
            totalBarangay++;
            totalPrecincts += parseInt(item.total_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalRecruitsMorning += parseInt(item.total_recruits_morning);
            totalRecruitsAfternoon += parseInt(item.total_recruits_afternoon);
        });

        var summary = {
            totalBarangay : totalBarangay,
            totalPrecincts : totalPrecincts,
            totalRegistered : totalRegistered,
            totalRecruits : totalRecruits,
            totalRecruitsMorning : totalRecruitsMorning,
            totalRecruitsAfternoon : totalRecruitsAfternoon
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
    
        return (
            <div>

                <div style={{ marginBottom: "5px" }}><strong>City/Municipality : {this.state.municipality.name}</strong></div>
                <div className="row">
                    <div className="col-md-3" style={{ marginBottom : "15px" }}>
                        <div className="bold">Total Barangay : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalBarangay)}</span></div>
                        <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalPrecincts)}</span></div>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRegistered)}</span></div>
                    </div>
                    <div className="col-md-3">
                        <div className="bold">Total Morning : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRecruitsMorning)}</span></div>
                        <div className="bold">Total Afternoon : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRecruitsAfternoon)}</span></div>
                        <div className="bold">Total Attendees : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRecruits)}</span></div>
                    </div>
                </div>
                <div className="clearfix" />
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%">
                        <thead className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2">Brgy</th>
                                <th className="text-center" rowSpan="2">Total Precincts</th>
                                <th className="text-center" rowSpan="2">Total Voters</th>
                                <th className="text-center" colSpan="3">Attendees Photo Taken</th>
                            </tr>
                            <tr>
                                <th className="text-center">Morning</th>
                                <th className="text-center">Afternoon</th>
                                <th className="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.data.map(function (item, index) {
                                counter += 1;
                                return (
                                    <tr>
                                        <td className="text-center">{counter}</td>
                                        <td className="text-center"> {item.name}</td>
                                        <td className="text-center">{item.total_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_precincts)}</td>
                                        <td className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                        <td className="text-center">{item.total_recruits_morning == 0 ? "- - -" : self.numberWithCommas(item.total_recruits_morning)}</td>
                                        <td className="text-center">{item.total_recruits_afternoon == 0 ? "- - -" : self.numberWithCommas(item.total_recruits_afternoon)}</td>
                                        <td className="text-center">{item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}</td>
                                    </tr>
                                )
                            })}

                            {this.state.data.length == 0 && !this.state.loading &&
                                <tr>
                                    <td colSpan="11" className="text-center">No records was found...</td>
                                </tr>
                            }

                            {this.state.loading &&
                                <tr>
                                    <td colSpan="11" className="text-center">Data is being processed. Please wait for a while...</td>
                                </tr>
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    },

    isEmpty: function (value) {
        return value == null || value == '' || value == 'undefined';
    }

});

window.OrganizationMunicipalityPhotoSummaryTable = OrganizationMunicipalityPhotoSummaryTable;