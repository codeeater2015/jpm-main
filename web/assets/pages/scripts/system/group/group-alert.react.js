var AlertDismissable = React.createClass({

    render : function() {
        if (this.props.isVisible) {
            var alertType = "alert alert-"+this.props.alertType;
            return (
                <div className={alertType}>
                    <button type="button" className="close" onClick={this.props.hideAlert}/>
                    {this.props.alertMessage}
                </div>
            );
        }

        return null;
    }


});

window.AlertDismissable = AlertDismissable;