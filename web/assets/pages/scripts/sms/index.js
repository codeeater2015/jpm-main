var SmsSection = React.createClass({

  getInitialState: function () {
    return {
      proId: 3,
      isLoading: false
    }
  },

  componentDidMount: function () {
    // $(document).ajaxError(function (event, request, settings) {
    //   switch (request.status) {
    //     case 400:
    //       window.services.growl.notify("Form Submission failed.", "danger");
    //       break;
    //     case 403:
    //       window.services.growl.notify("Action denied.You are not allowed to perform this action.", "danger");
    //       break;
    //     case 500:
    //       window.services.growl.notify("Opps. Something went wrong in the server. Please inform the system administrator.", "danger");
    //       break;
    //   }
    // });
  },

  syncSender() {
    var self = this;

    self.requestVoter = $.ajax({
      url: Routing.generate("ajax_sms_update_sender_name", { proId: self.state.proId }),
      type: "GET"
    }).done(function (res) {
      self.setState({ isLoading: false });
    });

    self.setState({ isLoading: true })
  },

  render: function () {
    var isLoading = this.state.isLoading;

    return (
      <div className="portlet light bordered overflow-auto">
        <div className="portlet-body">
          <div className="col-md-12">
            <h3 className="h3 m-b-md">SMS Section</h3>

            {
              isLoading ? 
              <button type="button" disabled className="demo-loading-btn btn btn-primary"> Please wait... Syncing sender... </button> : 
              <button type="button" onClick={this.syncSender} className="demo-loading-btn btn btn-primary"> Sync Sender</button>
            }
            
          </div>
          <div className="col-md-12">
            <ReceivedSmsDatatable proId={this.state.proId} />
          </div>
        </div>
      </div>
    );
  }

});

window.SmsSection = SmsSection;

setTimeout(function () {
  ReactDOM.render(
    <SmsSection />,
    document.getElementById('container')
  );
}, 500);