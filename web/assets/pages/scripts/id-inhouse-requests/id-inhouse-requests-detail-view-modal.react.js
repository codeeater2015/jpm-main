var Modal = ReactBootstrap.Modal;

var IdInhouseRequestDetailViewModal = React.createClass({

    getInitialState : function(){
        return {
            showNewRequestItemModal : false,
            printedAt : null
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;
        $("#date_select2").select2({
            casesentitive: false,
            placeholder: "Select Date",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_printed_dates'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        proId : self.props.proId,
                        requestId : self.props.hdrId
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.print_date, text: item.print_date };
                        })
                    };
                },
            }
        });

        $("#date_select2").on("change", function () {
            self.setState({ printedAt : $(this).val() });
        });
    },

    
    openNewRequestItemModal : function(){
        this.setState({ showNewRequestItemModal : true});
    },

    closeNewRequestItemModal : function(){
        this.setState({ showNewRequestItemModal : false});
    },

    onSuccessCreate : function(){
        var self = this;

        self.reloadDatatable();
    },

    reloadDatatable : function(){
        this.refs.InhouseRequestDetailDatatable.reload();
    },

    showPrintout : function(){
        console.log("showing attendance summary");
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/id-request-printout/index.php?request_id=" + this.props.hdrId +  "&printed_at=" + this.state.printedAt;
        this.popupCenter(url, 'Request Printout', 900, 600);
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
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-95" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Request  Information </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="row">
                        <div className="col-md-8 text-left">
                            <button type="button" onClick={this.openNewRequestItemModal} className="btn btn-sm green">Add New Item</button>
                        </div>
                        <div classname="col-md-4 text-right">
                            <div className="form-group col-md-3">
                                <select id="date_select2" className="form-control form-filter input-sm" name="brgyNo">
                                </select>
                            </div> 
                            <button type="button" onClick={this.showPrintout} className="btn btn-sm green">Generate</button>
                        </div>
                    </div>
                    
                    {this.state.showNewRequestItemModal && 
                        <IdInhouseRequestItemModal 
                            show={this.state.showNewRequestItemModal}
                            proId={this.props.proId}
                            electId={this.props.electId}
                            provinceCode={this.props.provinceCode}
                            hdrId={this.props.hdrId}
                            onHide={this.closeNewRequestItemModal}
                            onSuccess={this.onSuccessCreate}
                            notify={this.props.notify}
                        />
                    }

                    <div className="row">
                        <div className="col-md-12">
                            <IdInhouseRequestDetailDatatable
                                hdrId={this.props.hdrId}
                                proId={this.props.proId}
                                electId={this.props.electId}
                                provinceCode={this.props.provinceCode}
                                ref="InhouseRequestDetailDatatable"
                                notify={this.props.notify}
                            />
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
});


window.IdInhouseRequestDetailViewModal = IdInhouseRequestDetailViewModal;