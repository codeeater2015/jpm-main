var ProjectEventComponent = React.createClass({

    notify : function(message,color){
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    render : function(){
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <p>Organization Cluster</p>
                    <OrganizationClusterDatatable notify={ this.notify }/>
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <ProjectEventComponent />,
        document.getElementById('page-container')
    );
},500);
