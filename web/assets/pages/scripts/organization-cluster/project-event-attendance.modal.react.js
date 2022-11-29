var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var ProjectEventAttendanceModal = React.createClass({
    getInitialState: function () {
        return {
            proIdCode: null,
            member: null,
            showAttendeeModal: false,
            showAttendeeBatchModal: false,
            events : [],
            selectedEvent : null
        }
    },

    render: function () {
        var self = this;

        if (this.state.member != null) {
            var generatedIdNo = this.state.member.generated_id_no;
            var photoUrl = window.imgUrl + this.props.proId + '_' + generatedIdNo + "?" + new Date().getTime();
        }

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-full" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Event Attendance</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    {
                        this.state.showAttendeeModal &&
                        <ProjectEventAttendeeModal
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            eventId={this.props.eventId}
                            show={this.state.showAttendeeModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadFilteredDatatable}
                            onHide={this.closeAttendeeModal}
                        />
                    }

                    {
                        this.state.showAttendeeBatchModal &&
                        <ProjectEventAttendeeBatchModal
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            eventId={this.props.eventId}
                            show={this.state.showAttendeeBatchModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAttendeeBatchModal}
                        />
                    }

                    <form id="search-form" >
                        <div className="col-md-2">
                            <div className="col-md-12">
                                <FormGroup controlId="formProIdCode" >
                                    <FormControl bsClass="form-control " name="proIdCode" value={this.state.proIdCode} onChange={this.setFormProp} />
                                </FormGroup>
                            </div>
                            {this.state.member != null && (
                                <div className="row voter-view-header">
                                    <div className="col-md-12">
                                        <div style={{ marginBottom: "15px" }}>
                                            <div><em><small>Last Updated : {moment(this.state.member.updatedAt).format('MMM DD, YYYY hh:mm: A')}</small></em></div>
                                            <div><em><small>Updated By : {this.state.member.updatedBy}</small></em></div>
                                        </div>

                                        <div className="clearfix"></div>

                                        <div className="col-md-12">
                                            <div>
                                                <a href={photoUrl} data-lightbox="Profile Photo" data-title="Profile Photo" >
                                                    <img src={photoUrl} className="img-responsive" alt="" />
                                                </a>
                                            </div>
                                            <div className="profile-userbuttons" style={{ marginTop: "10px" }}>
                                                <span className="btn col-md-12 green btn-sm fileinput-button ">
                                                    <span> Change Photo</span>
                                                    <input id="voter-photo-upload" type="file" name="files[]" data-url={Routing.generate('ajax_upload_project_voter_photo', { proId: this.props.proId, voterId: this.state.member.voterId })} multiple={false} />
                                                </span>
                                            </div>

                                        </div>

                                        <div className="col-md-12">
                                            <div><strong>Name : </strong> {this.state.member.voter_name} </div>
                                            <div><strong>Birthdate : </strong> {this.state.member.birthdate == "" ? "- - - -" : this.state.member.birthdate} </div>
                                            <div><strong>Municipality :</strong> {this.state.member.municipality_name} </div>
                                            <div><strong>Barangay :</strong> {this.state.member.barangay_name} </div>
                                            <div><strong>Precinct No :</strong> {this.state.member.precinct_no} </div>
                                        </div>

                                        <div className="col-md-12">
                                            <div><strong>CP # :</strong> {this.state.member.cellphone} </div>
                                            <div><strong>Position : </strong> {this.state.member.voter_group == "" ? "- - - -" : this.state.member.voter_group} </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                            {this.state.member == null && (
                                <div className="row voter-view-header">
                                    <div className="col-md-12">
                                        <h4 className="text-center">Member not found...</h4>
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="col-md-10">
                            <div className="col-md-7" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                                <button onClick={this.openAttendeeModal} type="button" className="btn btn-sm btn-primary">Add Attendees</button>
                                {
                                    /*
                                    <button onClick={this.openAttendeeBatchModal} type="button" className="btn btn-sm btn-primary" style={{ marginLeft: "12px" }}>Add Batches</button>
                                    */
                                }
                                <div className="btn-group" style={{ marginLeft: "10px" }}>
                                    <button type="button" className="btn btn-sm blue">Export PDF</button>
                                    <button type="button" className="btn btn-sm blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                        <i className="fa fa-angle-down"></i>
                                    </button>
                                    <ul className="dropdown-menu" role="menu">
                                        <li><a href="javascript:;" onClick={this.showAllAttendees}>All Attendees</a></li>
                                        <li><a href="javascript:;" onClick={this.showAttendaceSummary}>Attendance Summary</a></li>
                                        <li><a href="javascript:;" onClick={this.showStabsByBarangay}>Attendee's Stab By Barangay</a></li>
                                        { /* 
                                             <li><a href="javascript:;" onClick={this.showAllAttendees}>All Attendees</a></li>
                                            <li><a href="javascript:;" onClick={this.showNewPrintout}>New Attendees Group By Precinct</a></li>
                                            <li><a href="javascript:;" onClick={this.showNewByAssignedPrecinctPrintout}>New Attendees Group by Assigned Precinct</a></li>
                                            <li><a href="javascript:;" onClick={this.showNewByBarangayPrintout}>New Attendees Group By Barangay</a></li>

                                            <li><a href="javascript:;" onClick={this.showNewAllPrintout}>New Expected Attendees Group By Precinct</a></li>
                                            <li><a href="javascript:;" onClick={this.showNewAllByAssignedPrecinctPrintout}>New Expected Attendees Group by Assigned Precinct</a></li>

                                            <li><a href="javascript:;" onClick={this.showOldPrintout}>Old Attendees</a></li>
                                            <li><a href="javascript:;" onClick={this.showStabs}>Attendee's Stabs</a></li>
                                        */ }

                                        
                                        { /*<li><a href="javascript:;" onClick={this.showStabsByPrecinct}>Attendee's Stab By Precinct</a></li>*/ }
                                    </ul>
                                </div>
                            </div>

                            <div className="col-md-5 text-right">
                                <div className="input-group">
                                    <select className="form-control input-sm" onChange={this.setSelectedEvent}>
                                        <option value=""> -- Select Event -- </option>
                                        {this.state.events.map(function(item){
                                            return (<option value={item.event_id} key={"event" + item.event_id}> {item.event_name} </option>);
                                        })}
                                    </select>
                                    <div className="input-group-btn">
                                        <button type="button" onClick={this.importAttendees} className="btn btn-sm btn-primary" style={{ marginLeft: "12px" }} onClick={self.appendEventMembers}>Import Attendees</button>
                                    </div>
                                </div>

                            </div>

                            <ProjectEventDetailDatatable ref="DetailDatatable" proId={this.props.proId} notify={this.props.notify} eventId={this.props.eventId}></ProjectEventDetailDatatable>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },


    componentDidMount : function(){
        this.loadEvents();
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    search: function () {
        var self = this;
        var proIdCode = this.state.proIdCode;

        if (proIdCode != null && proIdCode != "") {
            setTimeout(function () {
                self.requestMember = $.ajax({
                    url: Routing.generate("ajax_get_project_voter_alt", { proIdCode: proIdCode, proId: self.props.proId }),
                    type: "GET"
                }).done(function (res) {
                    
                    if(res.status == 'A'){
                        self.setState({ member: res }, self.add);
                    }else{
                        alert("Opps! Cant add to the list of attendees. Voter either blocked or deactivated");
                    }
                }).fail(function () {
                    console.log("member not found");
                    self.setState({ member: null });
                });
            }, 2000);
        }
    },

    loadEvents : function(){
        var self = this;

        self.requestEvents = $.ajax({
            url : Routing.generate("ajax_get_project_event_headers"),
            type : "GET"
        }).done(function(res){
            console.log("events has been received");
            console.log(res);
            self.setState({ events : res });
        });
    },

    appendEventMembers : function(){
        var self = this;

        console.log("event id");
        console.log(self.state.selectedEvent);

        self.appendMembers = $.ajax({
            url: Routing.generate("ajax_post_project_event_header_append"),
            data: {
                eventId : self.state.selectedEvent,
                currentEventId : self.props.eventId
            },
            type: "POST"
        }).done(function (res) {
            console.log("members has been added");
            self.refs.DetailDatatable.reload();
            self.setState({ selectedEvent : null });
        }).fail(function () {
            console.log('failed to append members');
        });
    },

    setSelectedEvent : function(e){
        this.setState({selectedEvent : e.target.value });
    },

    add: function () {
        var self = this;

        self.requestMember = $.ajax({
            url: Routing.generate("ajax_post_project_event_detail"),
            data: {
                proVoterId: this.state.member.pro_voter_id,
                proId: this.props.proId,
                proIdCode: this.state.member.pro_id_code,
                eventId: this.props.eventId
            },
            type: "POST"
        }).done(function (res) {
            self.refs.DetailDatatable.reload();
            self.setState({ proIdCode: "" });
        }).fail(function () {
            self.setState({ proIdCode: "" });
        });
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    reloadFilteredDatatable: function (precinctNo) {
        this.refs.DetailDatatable.reloadFiltered(precinctNo);
    },

    openAttendeeModal: function () {
        this.setState({ showAttendeeModal: true });
    },

    closeAttendeeModal: function () {
        this.setState({ showAttendeeModal: false });
    },

    openAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: true });
    },

    closeAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: false });
    },

    showAttendaceSummary : function () {
        console.log("showing attendance summary");
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/kfc/attendance-summary/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Attendance Summary', 900, 600);
    },

    showAllAttendees: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All Attendees', 900, 600);
    },

    showNewPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees', 900, 600);
    },

    showNewAllPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-all/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All New Expecteed Attendees', 900, 600);
    },

    showNewByBarangayPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-by-barangay/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees by Barangay', 900, 600);
    },

    showNewByAssignedPrecinctPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-by-assigned-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees by Assigned Precinct', 900, 600);
    },

    showNewAllByAssignedPrecinctPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-all-by-assigned-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All New Expecteed Attendees By Assigned Precinct', 900, 600);
    },

    showStabs: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs', 900, 600);
    },

    showStabsByBarangay: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab-by-barangay/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs', 900, 600);
    },


    showStabsByPrecinct: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab-by-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs By Precinct No', 900, 600);
    },

    showOldPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-old/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of Old Attendees', 900, 600);
    },

    popupCenter: function (url, title, w, h) {
        // Fixes dual-screen position                         Most browsers      Firefox  
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
        var width = 0;
        var height = 0;

        width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow  
        if (window.focus) {
            newWindow.focus();
        }
    }
});


window.ProjectEventAttendanceModal = ProjectEventAttendanceModal;