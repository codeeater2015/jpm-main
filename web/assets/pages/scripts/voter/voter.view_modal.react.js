var Modal = ReactBootstrap.Modal;
var Tab = ReactBootstrap.Tab;
var Tabs = ReactBootstrap.Tabs;
var Nav = ReactBootstrap.Nav;
var NavItem = ReactBootstrap.NavItem;

var VoterViewModal = React.createClass({

    getInitialState: function () {
        return {
            voterName: "",
            birthdate: "",
            active: "assistance"
        };
    },

    render: function () {
        var photoUrl = window.imgUrl + this.props.proId + '_' + this.state.proIdCode + "?" + new Date().getTime();
        var self = this;

        return (
            <Modal keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Details</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    <div className="row voter-view-header">
                        <div className="col-md-12">

                            <div className="col-md-12" style={{ marginBottom: "15px" }}>
                                <div><em><small>Last Updated : {moment(this.state.updatedAt).format('MMM DD, YYYY hh:mm: A')}</small></em></div>
                                <div><em><small>Updated By : {this.state.updatedBy}</small></em></div>
                            </div>

                            <div className="clearfix"></div>

                            <div className="col-md-3">
                              {
                            //     <div>
                            //     <img onClick={this.openCropModal}  src={photoUrl} className="img-responsive" alt="" />
                            // </div>

                              }
                                {
                                    /* <div>
                                     <img id="sample_image" style={{maxWidth: "100%"}} src={photoUrl}/>
                                 </div>*/
                                }

                                <div>
                                    <img src={photoUrl} className="img-responsive" alt="" />
                                </div>

                                <div className="profile-userbuttons" style={{ marginTop: "10px" }}>
                                    <span className="btn col-md-12 green btn-sm fileinput-button ">
                                        <span> Change Photo</span>
                                        <input id="voter-photo-upload" type="file" name="files[]" data-url={Routing.generate('ajax_upload_project_voter_photo', { proId: this.props.proId, voterId: this.props.voterId })} multiple={false} />
                                    </span>
                                </div>

                                {
                                    // <div>
                                    //     <button className="btn btn-primary col-md-12" onClick={this.openCropModal} style={{ "marginTop": "10px" }}>Crop Image</button>
                                    // </div>
                                }

                                {this.isEmpty(this.state.proIdCode) && (
                                    <div>
                                        <button className="btn btn-primary col-md-12" onClick={this.generateIdNo} style={{ "marginTop": "10px" }}>Generate ID No</button>
                                    </div>
                                )}
                                {this.state.hasId && (
                                    <div>
                                        <button className="btn btn-info col-md-12" onClick={this.resetId} style={{ "marginTop": "10px" }}>Reprint ID</button>
                                    </div>
                                )}
                            </div>

                            <div className="col-md-5">
                                <div><strong>Name : </strong> {this.state.voterName} </div>
                                <div><strong>Birthdate : </strong> {this.state.birthdate == "" ? "- - - -" : this.state.birthdate} </div>
                                <div><strong>Municipality :</strong> {this.state.municipalityName} </div>
                                <div><strong>Barangay :</strong> {this.state.barangayName} </div>
                                <div><strong>Voter No :</strong> {this.state.voterNo} </div>
                                <div><strong>Precinct No :</strong> {this.state.precinctNo} </div>
                                <div><strong>Clustered Precinct :</strong> {this.state.clusteredPrecinct} </div>
                                <div><strong>Voting Center :</strong> {this.state.votingCenter} </div>
                                <div><strong>Assigned Precinct No :</strong> {this.state.precinctNo} </div>
                                <br />
                                <div><strong>Cellphone No :</strong> {this.state.cellphoneNo} </div>
                                <div><strong>Address :</strong> {this.state.address} </div>
                                <br/>

                                <div><strong>Activated Reason : </strong> { self.isEmpty(this.state.activatedReason) ? "..." : this.state.activatedReason } </div>
                                <div><strong>Activated At : </strong> { self.isEmpty(this.state.activatedAt) ? "..." : this.state.activatedAt } </div>
                                <div><strong>Activated By : </strong> { self.isEmpty(this.state.activatedBy) ? "..." : this.state.activatedBy } </div>
                                <br/>

                                <div><strong>Block Reason : </strong> { self.isEmpty(this.state.blockedReason) ? "..." : this.state.blockedReason } </div>
                                <div><strong>Blocked At : </strong> { self.isEmpty(this.state.blockedAt) ? "..." : this.state.blockedAt } </div>
                                <div><strong>Blocked By : </strong> { self.isEmpty(this.state.blockedBy) ? "..." : this.state.blockedBy } </div>
                                <br/>
                                
                                <div><strong>Deactivation Reason : </strong> { self.isEmpty(this.state.deactivatedReason) ? "..." : this.state.deactivatedReason } </div>
                                <div><strong>Deactivated At : </strong> { self.isEmpty(this.state.deactivatedAt) ? "..." : this.state.deactivatedAt } </div>
                                <div><strong>Deactivated By : </strong> { self.isEmpty(this.state.deactivatedBy) ? "..." : this.state.deactivatedBy } </div>
                                <br/>

                                <div><strong>Status : </strong> { this.state.status == 'I' ? "Inactive" : (this.state.status == 'B' ? "BLOCKED" : 'ACTIVE') } </div>
                            </div>

                            <div className="col-md-4">
                                <div><strong>ID No : </strong> {this.state.proIdCode == "" ? "- - - -" : this.state.proIdCode} </div>
                                <div><strong>Position : </strong> {this.state.voterGroup == "" ? "- - - -" : this.state.voterGroup} </div>
                                <div><strong>Remarks : </strong> {this.state.remarks == "" ? "- - - -" : this.state.remarks} </div>
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        {/* <Tab.Container  id="voter-tabs"  activeKey={this.state.active} onSelect={this.setSelectedTab}>
                        <div className="portlet light">
                            <div className="portlet-title  text-right tabbable-line">
                                <Nav bsStyle="tabs">
                                    <NavItem eventKey="assistance">
                                        Assistance
                                    </NavItem>
                                    <NavItem eventKey="history">
                                        History
                                    </NavItem>
                                </Nav>
                            </div>
                            <div className="portlet-body overflow-auto">
                                <Tab.Content animation>
                                    <Tab.Pane eventKey="assistance">
                                        { this.state.active == "assistance" &&
                                            <VoterAssistanceDatatable 
                                                notify = {this.props.notify}
                                                voterId = {this.props.voterId}>
                                            </VoterAssistanceDatatable>
                                        }
                                    </Tab.Pane>
                                    <Tab.Pane eventKey="history">
                                        { this.state.active == "history" &&
                                            <VoterHistoryDatatable voterId={this.props.voterId}></VoterHistoryDatatable>
                                        }
                                    </Tab.Pane>
                                </Tab.Content>
                            </div>
                        </div>
                    </Tab.Container> */}

                    </div>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadVoter(this.props.proId, this.props.voterId);
        this.initUploader();
        //this.initCropper();
    },

    // initCropper : function(){
    //     var self = this;
    //     var image = document.getElementById('sample_image');
    //     var croppable = false;

    //     var cropper = new Cropper(image, {
    //         aspectRatio: 1,
    //         viewMode: 1,
    //         ready: function () {
    //           croppable = true;
    //         },
    //       });

    //       setTimeout(function(){
    //         console.log("cropped canvas");
    //         var croppedCanvas = cropper.getCroppedCanvas();
    //         croppedCanvas.toBlob(function (blob) {
    //             console.log("blob data");
    //             console.log(blob);

    //             var formData = new FormData();
    //             formData.append('files[]', blob, 'avatar.jpg');

    //             $.ajax(Routing.generate('ajax_upload_project_voter_photo', { proId: self.props.proId, voterId: self.props.voterId }), {
    //             method: 'POST',
    //             data: formData,
    //             processData: false,
    //             contentType: false }).done(function(res){
    //                 console.log("submitted");
    //             }).fail(function(err){
    //                 console.log("something went wrong");
    //             });
    //         });
    //       },5000);
    // },

    initUploader: function () {
        var self = this;

        $('#voter-photo-upload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo(document.body);
                });

                // $('.progress-bar').css(
                //     'width', '0%'
                // );
                // $('.fileupload-progress').css(
                //     'display', 'none'
                // );

                // services.growl.notify('Profile photo has been updated.','success');
                // self.loadStudent(self.props.stdCode);
                self.loadVoter(self.props.proId, self.props.voterId);
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                // $('.fileupload-progress').css(
                //     'display', 'block'
                // );
                // $('.progress-bar').css(
                //     'width',
                //     progress + '%'
                // );
            }
        });
    },

    openCropModal : function(){
        console.log("opening crop modal");
    },

    generateIdNo: function () {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_generate_id_no", {
                voterId: self.props.voterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("ID NO has been created : " + res.proIdCode);
            self.loadVoter(self.props.proId, self.props.voterId);
        });
    },

    resetId: function () {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter_reset_id", {
                voterId: self.props.voterId,
                proId: self.props.proId
            }),
            type: "GET"
        }).done(function (res) {
            alert("You can now re-print this member ID");
            self.loadVoter(self.props.proId, self.props.voterId);
        });
    },

    isEmpty: function (value) {
        return value == null || value == "";
    },

    loadVoter: function (proId, voterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", {
                voterId: voterId,
                proId: proId
            }),
            type: "GET"
        }).done(function (res) {
            self.setState(res);
        });
    },

    setSelectedTab: function (key) {
        this.setState({ active: key });
    }
});

window.VoterViewModal = VoterViewModal;