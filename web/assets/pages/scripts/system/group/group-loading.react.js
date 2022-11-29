
var AppLoading = React.createClass({

    render : function(){
        var loading = <span/>;
        if(this.props.loading){
            loading = <span className="fa fa-spinner fa-spin"/>;
        }

        return loading;
    }
});

window.AppLoading = AppLoading;
