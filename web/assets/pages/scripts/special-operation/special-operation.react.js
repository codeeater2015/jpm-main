var SpecialOperationPage = React.createClass({

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
                                <h4 className="bold">Special Operation Page</h4>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12">
                                <SpecialOperationDatatable/> 
                            </div>
                        </div>
                    </div>
                </div>
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
        <SpecialOperationPage />,
        document.getElementById('page-container')
    );
}, 500);
