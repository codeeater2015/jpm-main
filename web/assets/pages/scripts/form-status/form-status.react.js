var FormStatusComponent = React.createClass({

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
                    <FormStatusDatatable /> 
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <FormStatusComponent />,
        document.getElementById('page-container')
    );
},500);
