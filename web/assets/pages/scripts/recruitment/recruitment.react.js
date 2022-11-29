var RecruitmentComponent = React.createClass({

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
        return (
            <div>
                <div className="portlet light portlet-fit bordered" style={{ marginTop : "10px" }}>
                    <div className="portlet-body">
                        <div className="row" >
                            <div className="col-md-6">
                                <h4 className="bold">Recruitement Page</h4>
                            </div>
                        </div>

                        <div className="row" id="handler_component">
                            <form onSubmit={this.onApplyCode}>
                                <div className="col-md-2">
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
                            </form>
                        </div>

                    </div>
                </div>

                <RecruitmentDatatable />

            </div>
        )
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

});

setTimeout(function () {
    ReactDOM.render(
        <RecruitmentComponent />,
        document.getElementById('page-container')
    );
}, 500);
