var FinancialAssistanceComponent = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showClosingModal: false,
            showPostingModal : false,
            activeTable: "TRANSACTIONS",
            startDate: null,
            endDate: null,
        }
    },

    componentDidMount: function () {

        var start = moment().subtract(29, 'days');
        var end = moment();
        var self = this;

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')]
            }
        }, self.datetimeCallback);

        self.datetimeCallback(start, end);
    },

    datetimeCallback: function (start, end) {
        var self = this;
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        self.setState({ startDate: start.format('YYYY-MM-DD'), endDate: end.format('YYYY-MM-DD') });
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

    setActiveTable: function (e) {
        this.setState({ activeTable: e.target.value });
    },

    reload: function () {
        if (this.state.activeTable == 'TRANSACTIONS') {
            this.refs.transactionTable.reload();
        } else if (this.state.activeTable == 'DAILY_SUMMARY') {
            this.refs.summaryTable.reload();
        }
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    openClosingModal: function () {
        this.setState({ showClosingModal: true });
    },

    openPostingModal: function () {
        console.log("open posting modal");
        this.setState({ showPostingModal: true });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    closeClosingModal: function () {
        this.setState({ showClosingModal: false });
    },

    closePostingModal: function () {
        this.setState({ showPostingModal: false });
    },

    render: function () {
        var self = this;
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">

                    {
                        this.state.showCreateModal &&
                        <FinancialAssistanceCreateModal
                            proId={3}
                            show={this.state.showCreateModal}
                            notify={this.props.notify}
                            reload={this.reload}
                            onHide={this.closeCreateModal}
                        />
                    }

                    {
                        this.state.showClosingModal &&
                        <FinancialAssistanceClosingModal
                            proId={3}
                            show={this.state.showClosingModal}
                            notify={this.props.notify}
                            reload={this.reload}
                            onHide={this.closeClosingModal}
                        />
                    }

{
                        this.state.showPostingModal &&
                        <FinancialAssistancePostingModal
                            proId={3}
                            show={this.state.showPostingModal}
                            notify={this.props.notify}
                            reload={this.reload}
                            onHide={this.closePostingModal}
                        />
                    }


                    <div className="row">
                        <div className="col-md-7">
                            <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Assistance</button>
                            <button type="button" className="btn btn-primary" style={{ marginLeft: "10px" }} onClick={this.openClosingModal}>Close Transactions</button>
                            <button type="button" className="btn btn-primary" style={{ marginLeft: "10px" }} onClick={this.openPostingModal}>Post Transactions</button>
                        </div>
                        <div className="col-md-3">

                            <div id="reportrange" className="form-control" style={{
                                background: "#fff",
                                cursor: "pointer",
                                padding: "5px 10px",
                                border: "1px solid #ccc",
                                width: "100%"
                            }}>
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div className="col-md-2">
                            <select className="form-control" onChange={this.setActiveTable} value={this.state.activeTable} name="activeTable">
                                <option value="TRANSACTIONS">Transactions</option>
                                <option value="DAILY_SUMMARY">Daily Closing</option>
                                <option value="DAILY_SUMMARY_REPORT">Daily Summary Report</option>
                                <option value="MUNICIPALITY_SUMMARY_REPORT">Municipality Summary Report</option>
                                <option value="MONTHLY_SUMMARY_REPORT">Monthly Summary Report</option>
                            </select>
                        </div>
                    </div>

                    {
                        this.state.activeTable == "TRANSACTIONS" ?
                            <FinancialAssistanceDatatable ref="transactionTable" notify={this.notify} /> : null
                    }

                    {
                        this.state.activeTable == "DAILY_SUMMARY" ?
                            <FinancialAssistanceDailySummaryDatatable ref="summaryTable" notify={this.notify} /> : null
                    }

                    {
                        this.state.activeTable == "DAILY_SUMMARY_REPORT" ?
                            <FinancialAssistanceDailySummaryReportDatatable
                                startDate={self.state.startDate}
                                endDate={self.state.endDate}
                                ref="reportSummaryTable"
                                notify={this.notify}
                            /> : null
                    }


                    {
                        this.state.activeTable == "MUNICIPALITY_SUMMARY_REPORT" ?
                            <FinancialAssistanceMunicipalitySummaryReportDatatable
                                startDate={self.state.startDate}
                                endDate={self.state.endDate}
                                ref="municipalitySummaryTable"
                                notify={this.notify}
                            /> : null
                    }

                    {
                        this.state.activeTable == "MONTHLY_SUMMARY_REPORT" ?
                            <FinancialAssistanceMonthlySummaryReportDatatable
                                startDate={self.state.startDate}
                                endDate={self.state.endDate}
                                ref="monthlySummaryTable"
                                notify={this.notify}
                            /> : null
                    }


                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <FinancialAssistanceComponent />,
        document.getElementById('page-container')
    );
}, 500);
