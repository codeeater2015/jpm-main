var ProjectRecruitmentEncodingSummaryComponent = React.createClass({

    getInitialState : function(){
        return {
            totalRecruit : 0,
            totalRecruiter : 0 
        }
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

    componentDidMount : function(){
        var self = this;

        self.requestEncodingStatus = $.ajax({
            url : Routing.generate("ajax_get_project_recruitment_status"),
            type : "GET"
        }).done(function(res){
            self.setState({
                totalRecruit : parseFloat(res.totalRecruit)
            });
        });
    },

    render: function () {
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <div className="row" style={{ marginBottom : "20px" }}>
                        <div className="col-md-6">Recruitment Encoding Summary</div>
                        <div className="col-md-6 text-right">
                            <span style={{ marginLeft : "10px" }}><span className="bold font-red-sunglo">Today's Total Encoded :  </span> <strong>{ this.numberWithCommas(this.state.totalRecruit) }</strong></span>
                        </div>
                    </div>
                    <ProjectRecruitmentEncodingSummaryByEncoderDatatable/>
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
        <ProjectRecruitmentEncodingSummaryComponent />,
        document.getElementById('page-container')
    );
}, 500);
