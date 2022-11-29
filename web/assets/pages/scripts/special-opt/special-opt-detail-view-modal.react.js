var Modal = ReactBootstrap.Modal;

var SpecialOptDetailViewModal = React.createClass({

    getInitialState : function(){
        return {
            showNewMemberModal : false,
            header : {
                voter_name : "",
                municipality_name : "",
                barangay_name : "",
                opt_type : "",
                voter_group : "",
                cellphone : ""
            }
        }
    },

    componentDidMount: function () {
        this.loadHeader(this.props.hdrId);
    },

    loadHeader : function(hdrId){
        var self = this;
        console.log("loading header");

        self.requestHeader = $.ajax({
            url : Routing.generate("ajax_get_special_opt_header",{ hdrId : hdrId }),
            type : "GET"
        }).done(function(res){
            console.log("header has been loaded");
            self.setState({header : res });
        }).fail(function(err){
            console.log("failed to load header");
        });
    },

    openNewMemberModal : function(){
        this.setState({ showNewMemberModal : true});
    },

    closeNewMemberModal : function(){
        this.setState({ showNewMemberModal : false});
    },

    reloadDatatable : function(){
        this.refs.SpecialOptDetailDatatable.reload();
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-95" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Special Operation Member List</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <button type="button" onClick={this.openNewMemberModal} className="btn btn-sm green">Add New Member</button>
                    {
                        this.state.showNewMemberModal && 
                        <SpecialOptNewMemberModal 
                            show={this.state.showNewMemberModal}
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            hdrId={this.props.hdrId}
                            optType={this.state.header.opt_type}
                            onHide={this.closeNewMemberModal}
                            onSuccess={this.reloadDatatable}
                            notify={this.props.notify}
                        />
                    }
                    
                    <br/>

                    <div className="row">
                        <div className="col-md-12">
                            <SpecialOptDetailDatatable
                                hdrId={this.props.hdrId}
                                proId={this.props.proId}
                                electId={this.props.electId}
                                provinceCode={this.props.provinceCode}
                                ref="SpecialOptDetailDatatable"
                                notify={this.props.notify}
                            />
                        </div>
                    </div>

                </Modal.Body>
            </Modal>
        );
    }
});


window.SpecialOptDetailViewModal = SpecialOptDetailViewModal;