var UserAccessComponent = React.createClass({

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
                    <UserDatatable notify={this.notify}/>
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <UserAccessComponent />,
        document.getElementById('user-access-container')
    );
},500);
