var RescuePrintingSection = React.createClass({

  getInitialState: function () {
    return {
      showGroupingModal: false,
      filters: {
        electId: null,
        provinceCode: null,
        proId: null
      }
    };
  },

  componentDidMount: function () {
    $(document).ajaxError(function (event, request, settings) {
      switch (request.status) {
        case 400:
          window.services.growl.notify("Form Submission failed.", "danger");
          break;
        case 403:
          window.services.growl.notify("Action denied.You are not allowed to perform this action.", "danger");
          break;
        case 500:
          window.services.growl.notify("Opps. Something went wrong in the server. Please inform the system administrator.", "danger");
          break;
      }
    });

    this.loadUser(window.userId);
    this.initSelect2();
  },


  render: function () {
    return (
      <div className="portlet light bordered overflow-auto">
        <div className="portlet-body">
          <div className="col-md-6">
            <h3 className="h3 m-b-md">ID Maker</h3>
            <button type="button" className="btn green" onClick={this.openGroupingModal} >Create Template</button>
          </div>
          <div className="col-md-6">
            <form onSubmit={this.onApplyCode}>
              <div className="col-md-3 col-md-offset-1">
                <select id="election_select2" className="form-control form-filter input-sm" >
                </select>
              </div>
              <div className="col-md-4">
                <select id="province_select2" className="form-control form-filter input-sm" >
                </select>
              </div>
              <div className="col-md-4">
                <select id="project_select2" className="form-control form-filter input-sm" >
                </select>
              </div>
            </form>
          </div>

          {this.state.showGroupingModal && <PrintingGroupModal 
            proId={this.state.filters.proId} 
            electId={this.state.filters.electId}
            provinceCode={this.state.filters.provinceCode}
            show={this.state.showGroupingModal} 
            onHide={this.closeGroupingModal} 
          />}
          <div className="col-md-12">
            <PrintDatatable ref="datatable" proId={this.state.filters.proId} />
          </div>
        </div>
      </div>
    );
  },

  openGroupingModal: function () {
    this.setState({ showGroupingModal: true });
  },

  closeGroupingModal: function () {
    this.setState({ showGroupingModal: false });
    this.refs.datatable.reload();
  },

  loadUser: function (userId) {
    var self = this;

    self.requestUser = $.ajax({
      url: Routing.generate("ajax_get_user", { id: userId }),
      type: "GET"
    }).done(function (res) {
      self.setState({ user: res }, self.reinitSelect2);
    });
  },

  initSelect2: function () {
    var self = this;

    $("#election_select2").select2({
      casesentitive: false,
      placeholder: "Select Election...",
      allowClear: true,
      delay: 1500,
      width: '100%',
      containerCssClass: ':all:',
      ajax: {
        url: Routing.generate('ajax_select2_elections'),
        data: function (params) {
          return {
            searchText: params.term
          };
        },
        processResults: function (data, params) {
          return {
            results: data.map(function (item) {
              return { id: item.elect_id, text: item.elect_name };
            })
          };
        },
      }
    });

    $("#project_select2").select2({
      casesentitive: false,
      placeholder: "Select Project...",
      allowClear: true,
      delay: 1500,
      width: '100%',
      containerCssClass: ':all:',
      ajax: {
        url: Routing.generate('ajax_select2_projects'),
        data: function (params) {
          return {
            searchText: params.term
          };
        },
        processResults: function (data, params) {
          return {
            results: data.map(function (item) {
              return { id: item.pro_id, text: item.pro_name };
            })
          };
        },
      }
    });

    $("#province_select2").select2({
      casesentitive: false,
      placeholder: "Enter Province...",
      allowClear: true,
      delay: 1500,
      width: '100%',
      containerCssClass: ':all:',
      ajax: {
        url: Routing.generate('ajax_select2_province_strict'),
        data: function (params) {
          return {
            searchText: params.term
          };
        },
        processResults: function (data, params) {
          return {
            results: data.map(function (item) {
              return { id: item.province_code, text: item.name };
            })
          };
        },
      }
    });

    $("#election_select2").on("change", function () {
      var filters = self.state.filters;
      filters.electId = $(this).val();

      self.setState({ filters: filters }, self.reload);
    });

    $("#project_select2").on("change", function () {
      var filters = self.state.filters;
      filters.proId = $(this).val();
      self.setState({ filters: filters }, self.reload);
    });

    $("#province_select2").on("change", function () {
      var filters = self.state.filters;
      filters.provinceCode = $(this).val();
      self.setState({ filters: filters }, self.reload);
    });

  },

  reinitSelect2: function () {
    var self = this;

    if (!self.isEmpty(self.state.user.project)) {
      var provinceCode = self.state.user.project.provinceCode;

      self.requestProvince = $.ajax({
        url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
        type: "GET"
      }).done(function (res) {
        $("#province_select2").empty()
          .append($("<option/>")
            .val(res.province_code)
            .text(res.name))
          .trigger("change");
      });

      self.requestProject = $.ajax({
        url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
        type: "GET"
      }).done(function (res) {
        $("#project_select2").empty()
          .append($("<option/>")
            .val(res.proId)
            .text(res.proName))
          .trigger("change");
      });
    }

    self.requestActiveElection = $.ajax({
      url: Routing.generate("ajax_get_active_election"),
      type: "GET"
    }).done(function (res) {
      $("#election_select2").empty()
        .append($("<option/>")
          .val(res.electId)
          .text(res.electName))
        .trigger("change");
    });

    if (!self.state.user.isAdmin) {
      $("#election_select2").attr('disabled', 'disabled');
      $("#province_select2").attr('disabled', 'disabled');
      $("#project_select2").attr('disabled', 'disabled');
    }
  },

  isEmpty: function (value) {
    return value == null || value == "" || value == "undefined" || value <= 0;
  }

});

window.RescuePrintingSection = RescuePrintingSection;

setTimeout(function () {
  ReactDOM.render(
    <RescuePrintingSection />,
    document.getElementById('project-print-container')
  );
}, 500);