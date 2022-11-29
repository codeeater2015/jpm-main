var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HouseholdMemberModal = React.createClass({
    getInitialState: function () {
        return {
            member: null,
            showAddMemberModal: false,
            header : {
                householdCode : "",
                voterName : "",
                barangayName : "",
                municipalityName : "",
                cellphone : "",
                lgc : {
                    voter_name : ""
                }
            }
        }
    },

    render: function () {
        var self = this;
        var data = self.state.header;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Household Information : {data.voterName} | LGC : {data.lgc.voter_name} | { data.lgc.cellphone == "" ? "NO CP" : data.lgc.cellphone} </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showAddMemberModal &&
                        <HouseholdMemberCreateModal
                            proId={self.props.proId}
                            provinceCode={53}
                            municipalityNo={this.state.header.municipalityNo}
                            municipalityName={this.state.header.municipalityName}
                            barangayNo={this.state.header.barangayNo} 
                            barangayName={this.state.header.barangayName}

                            electId={self.props.electId}
                            householdId={this.props.id}
                            show={this.state.showAddMemberModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAddMemberModal}
                        />
                    }   
                    <div style={{ marginBottom : "25px" }} >
                        <strong>Household # : </strong> { this.state.header.householdCode } <br/>    
                        <strong>Household Leader : </strong> { this.state.header.voterName } <br/>    
                        <strong>Municipality : </strong> {this.state.header.municipalityName} <br/>
                        <strong>Barangay : </strong>  {this.state.header.barangayName} <br/>
                        <strong>Cellphone : </strong>  {this.state.header.cellphone} 
                    </div>

                    <div className="col-md-7" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                        <button onClick={this.openAddMemberModal} type="button" className="btn btn-sm btn-primary">Add Member</button>
                    </div>

                    <HouseholdDetailDatatable ref="DetailDatatable" 
                        municipalityNo={this.state.header.municipalityNo} 
                        municipalityName={this.state.header.municipalityName} 
                        barangayNo={this.state.header.barangayNo} 
                        barangayName={this.state.header.barangayName} 
                        notify={this.props.notify} 
                        householdId={this.props.id}
                        electId={self.props.electId}
                        proId={self.props.proId}
                    >
                    </HouseholdDetailDatatable>
                
                    </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadHeader(this.props.id);
    },

    loadHeader : function(id){
        var self = this;

        self.requestRecruiter = $.ajax({
            url : Routing.generate("ajax_get_household_header",{ id : id }),
            type : "GET"
        }).done(function(res){
            self.setState({ header : res });
        });
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    openAddMemberModal : function(){
        console.log("showing add member modal");
        this.setState({ showAddMemberModal : true })
    },

    closeAddMemberModal : function(){
        this.setState({ showAddMemberModal : false});
    }

});


window.HouseholdMemberModal = HouseholdMemberModal;