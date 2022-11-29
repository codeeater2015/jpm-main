var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOperationMemberModal = React.createClass({
    getInitialState: function () {
        return {
            member: null,
            showAddMemberModal: false,
            header: {
                voterName: "",
                voterGroup: "",
                barangayName: "",
                position : "",
                municipalityName: "",
                specialOpGroup : "",
                cellphone : "",
                lgc : {
                    voter_name : "",
                    cellphone : ""
                }
            },
            defaults : {
                dialect : "TAGALOG",
                religion : "ROMAN CATHOLIC"
            }
        }
    },

    render: function () {
        var self = this;
        var defaults = self.state.defaults;
        var data = self.state.header;
        
        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Organization Information : {data.voterName} | LGC : {data.lgc.voter_name} | { data.lgc.cellphone == "" ? "NO CP" : data.lgc.cellphone} </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showAddMemberModal &&
                        <SpecialOperationMemberCreateModal
                            proId={this.props.proId}
                            provinceCode={53}
                            municipalityNo={this.state.header.municipalityNo}
                            municipalityName={this.state.header.municipalityName}
                            barangayNo={this.state.header.barangayNo}
                            barangayName={this.state.header.barangayName}
                            leader={this.state.header}

                            electId={this.props.electId}
                            recId={self.props.id}
                            show={this.state.showAddMemberModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAddMemberModal}

                            defaultReligion={this.state.defaults.religion}
                            defaultDialect={this.state.defaults.dialect}
                        />
                    }

                    <div className="row">
                        <div className="col-md-6">
                            <strong>Organization Leader : </strong> {this.state.header.voterName} <br />
                            <strong>Organization : </strong> {this.state.header.specialOpGroup} <br />
                            <strong>Municipality : </strong> {this.state.header.municipalityName} <br />
                            <strong>Barangay : </strong>  {this.state.header.barangayName} <br />
                            <strong>Cellphone : </strong>  {this.state.header.cellphone} 
                        </div>
                    </div>
                    
                    <br/>

                    <div className="col-md-7" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                        <button onClick={this.openAddMemberModal} type="button" className="btn btn-sm btn-primary">Add Member</button>
                    </div>

                    {
                        <SpecialOperationDetailDatatable ref="DetailDatatable"
                            municipalityNo={this.state.header.municipalityNo}
                            municipalityName={this.state.header.municipalityName}
                            barangayNo={this.state.header.barangayNo}
                            barangayName={this.state.header.barangayName}
                            notify={this.props.notify}
                            recId={self.props.id}
                            proId={self.props.proId}
                            electId={self.props.electId}
                        >
                        </SpecialOperationDetailDatatable>
                    }

                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadHeader(this.props.id);
    },

    loadHeader: function (id) {
        var self = this;

        self.requestRecruiter = $.ajax({
            url: Routing.generate("ajax_get_special_operation_header", { recId: id }),
            type: "GET"
        }).done(function (res) {
            self.setState({ header: res });
        });
    },





    
    setFormProp: function (e) {
        var defaults = this.state.defaults;
        defaults[e.target.name] = e.target.value;

        this.setState({ defaults : defaults });
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    openAddMemberModal: function () {
        console.log("showing add member modal");
        this.setState({ showAddMemberModal: true })
    },

    closeAddMemberModal: function () {
        this.setState({ showAddMemberModal: false });
    }

});


window.SpecialOperationMemberModal = SpecialOperationMemberModal;