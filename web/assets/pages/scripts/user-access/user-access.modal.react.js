var Modal = ReactBootstrap.Modal;

var UserAccessModal = React.createClass({

    getInitialState : function(){
        return {
            showCreateModal : false,
            numDays : 1,
            generated : false,
            user : {
                validUntil : null
            }
        };
    },

    componentDidMount : function(){
       this.loadUser(this.props.userId);
    },

    loadUser : function(userId){   
        var self = this;
        self.requestUser = $.ajax({
            url : Routing.generate("ajax_get_user", {id : userId}),
            type : "GET"
        }).done(function(res){
            console.log("user has been received");
            console.log(res);
            self.setState({user : res })
        });
    },

    render : function(){
        var self = this;

        return (
            <Modal  keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>User Access Permissions</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="row">
                        <div className="col-md-4">
                            <div className="form-group">
                                <input type="text" value={self.state.user.accessCode} placeholder="No code was generated." name="accessCode" onChange="" className="form-control input-sm"/>
                            </div>
                            <div style={{margin:"0px", padding : "0px"}}>
                                <small></small>
                                <small>Valid Until : {self.state.user.validUntil ? moment(self.state.user.validUntil).format("MMM DD, YYYY hh:mm:ss A") : "- - - - -"} </small>
                            </div>
                        </div>
                        <div className="col-md-2">
                            <div className="form-group">
                                <input type="number" value={this.state.numDays} onChange={this.setNumDays} placeholder="days" min="1" max="100" className="form-control input-sm"/>
                            </div>
                        </div>
                        <div className="col-md-1">
                            <button className="btn btn-detault btn-sm" onClick={this.generateCode}>Generate</button>
                        </div>
                        <div className="col-md-1" style={{marginLeft : "5px"}}> 
                            <button className="btn btn-success btn-sm" disabled={!this.state.generated} onClick={this.activate}>Activate</button>
                        </div>
                        <div className="col-md-1" style={{marginLeft : "10px"}}>
                            <button className="btn btn-danger btn-sm" onClick={this.clear}>Remove All Access</button>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <UserAccessDatatable ref="datatable" notify={this.props.notify} userId={this.props.userId}/>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    },

    setNumDays : function(e){
      this.setState({numDays : e.target.value});  
    },

    generateCode : function(){
        var  self = this;
        self.requestCode = $.ajax({
            url : Routing.generate("ajax_generate_access_code",{id : self.props.userId, numDays : this.state.numDays}),
            type : "GET"
        }).done(function(res){
            self.loadUser(self.props.userId);
            self.setState({generated : true});
        });
    },

    reloadDatatable : function(){
        this.refs.datatable.reload();
    },

    activate : function(){
        var  self = this;
        self.requestCode = $.ajax({
            url : Routing.generate("ajax_activate_access_code",{id : self.props.userId}),
            type : "POST"
        }).done(function(res){
            alert("Data access has been activated..");
            self.reloadDatatable();
        });
    },

    clear : function(){
        var  self = this;
        self.requestCode = $.ajax({
            url : Routing.generate("ajax_clear_access",{id : self.props.userId}),
            type : "POST"
        }).done(function(res){
            self.reloadDatatable();
        });
    }
});


window.UserAccessModal = UserAccessModal;