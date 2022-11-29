var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var LocationAssignmentModal = React.createClass({

    getInitialState: function () {
        return {
            showAddLocationModal: false
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Assigned Locations</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showAddLocationModal &&
                        <LocationAssignmentCreateModal
                            proIdCode={self.props.proIdCode}
                            show={this.state.showAddLocationModal}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAddLocationModal}
                        />
                    }

                    <div className="col-md-7" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                        <button onClick={this.openAddLocationModal} type="button" className="btn btn-sm btn-primary">Add location</button>
                    </div>

                    <div>
                        <LocationAssignmentDatatable
                            proIdCode={self.props.proIdCode}
                            ref="locationDatatable"
                        />
                    </div>

                </Modal.Body>
            </Modal>
        );
    },


    reloadDatatable: function () {
        if(this.refs.locationDatatable != null){
            this.refs.locationDatatable.reload();
        }
    },

    openAddLocationModal: function () {
        console.log("showing add member modal");
        this.setState({ showAddLocationModal: true })
    },

    closeAddLocationModal: function () {
        this.setState({ showAddLocationModal: false });
    }

});


window.LocationAssignmentModal = LocationAssignmentModal;