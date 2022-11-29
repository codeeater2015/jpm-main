var ReportComponent = React.createClass({

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
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <div className="row" style={{ marginBottom : "20px" }}>
                        <div className="col-md-6">
                            <h4 className="bold">Reports</h4>
                        </div>
                    </div>
                    
                </div>
            </div>
        )
    }
    
});

setTimeout(function () {
    ReactDOM.render(
        <ReportComponent />,
        document.getElementById('page-container')
    );
}, 500);
