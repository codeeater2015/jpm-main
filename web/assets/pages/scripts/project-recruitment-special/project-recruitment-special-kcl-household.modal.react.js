var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectRecruitmentSpecialKclHouseholdModal = React.createClass({
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
            <Modal style={{ marginTop: "90px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Household Recruitment Modal</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showAddMemberModal &&
                        <ProjectRecruitmentSpecialMemberModal
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
                        <button onClick={this.openAddMemberModal} type="button" className="btn btn-sm btn-primary">New Household Member</button>
                    </div>

                    <ProjectRecruitmentHouseholdMemberDatatable ref="MemberDatatable" proId={this.props.proId} notify={this.props.notify} recId={this.props.recId}></ProjectRecruitmentHouseholdMemberDatatable>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadKCL(this.props.recId);
    },

    loadKCL : function(recId){
        var self = this;

        self.requestRecruiter = $.ajax({
            url : Routing.generate("ajax_get_project_recruitment_special_kcl",{ recId : recId }),
            type : "GET"
        }).done(function(res){
            self.setState({ header : res });
        });
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    reloadDatatable: function () {
        this.refs.MemberDatatable.reload();
    },

    openAddMemberModal: function () {
        this.setState({ showAddMemberModal: true });
    },

    closeAddMemberModal: function () {
        this.setState({ showAddMemberModal: false });
    }

});


window.ProjectRecruitmentSpecialKclHouseholdModal = ProjectRecruitmentSpecialKclHouseholdModal;