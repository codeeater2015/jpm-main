var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectRecruitsModal = React.createClass({
    getInitialState: function () {
        return {
            member: null,
            showAddMemberModal: false,
            header : {
                voter_name : "",
                barangay_name : "",
                municipality_name : "",
                voter_group : ""
            }
        }
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Recruits</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showAddMemberModal &&
                        <ProjectRecruitmentAddMemberModal
                            proId={this.props.proId}
                            recId={this.props.recId}
                            show={this.state.showAddMemberModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAddMemberModal}
                        />
                    }   

                    <div style={{ marginBottom : "25px" }} >
                        <strong>Recruiter Name : </strong> { this.state.header.voter_name } <br/>    
                        <strong>Position  : </strong> { this.state.header.voter_group } <br/>
                        <strong>Municipality : </strong> {this.state.header.municipality_name} <br/>
                        <strong>Barangay : </strong>  {this.state.header.barangay_name} <br/>
                    </div>

                    <div className="col-md-7" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                        <button onClick={this.openAddMemberModal} type="button" className="btn btn-sm btn-primary">Add Recruits</button>
                    </div>

                    <ProjectRecruitmentDetailDatatable ref="DetailDatatable" proId={this.props.proId} notify={this.props.notify} recId={this.props.recId}></ProjectRecruitmentDetailDatatable>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadHeader(this.props.recId);
    },

    loadHeader : function(recId){
        var self = this;

        self.requestRecruiter = $.ajax({
            url : Routing.generate("ajax_get_project_recruitment_header",{ recId : recId }),
            type : "GET"
        }).done(function(res){
            console.log("project header has been received");
            console.log(res);

            self.setState({ header : res });
        });
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    openAddMemberModal: function () {
        console.log("showing add member modal");
        this.setState({ showAddMemberModal: true });
    },

    closeAddMemberModal: function () {
        this.setState({ showAddMemberModal: false });
    }

});


window.ProjectRecruitsModal = ProjectRecruitsModal;