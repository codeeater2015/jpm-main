var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FinancialAssistanceReleasedListModal = React.createClass({
    getInitialState: function () {
        return {
            member: null,
            showAddMemberModal: false,
            header: {
                closingDate: ""
            }
        }
    },

    render: function () {
        var self = this;
        var data = self.state.header;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-95" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Closing Date Summary  : {this.state.header.closingDate}  </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <FinancialDailySummaryDetailDatatable id = {this.props.id}/>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        //this.loadHeader(this.props.id);
    },

    // loadHeader : function(id){
    //     var self = this;

    //     self.requestRecruiter = $.ajax({
    //         url : Routing.generate("ajax_get_household_header",{ id : id }),
    //         type : "GET"
    //     }).done(function(res){
    //         self.setState({ header : res });
    //     });
    // },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    // reloadDatatable: function () {
    //     this.refs.DetailDatatable.reload();
    // },

});


window.FinancialAssistanceReleasedListModal = FinancialAssistanceReleasedListModal;