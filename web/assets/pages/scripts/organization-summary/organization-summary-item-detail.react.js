var Modal = ReactBootstrap.Modal;

var OrganizationSummaryItemDetail = React.createClass({
    getInitialState: function () {
        return {
            withPhoto: false
        }
    },

    getInitialProp: function () {
        return {
            electId: null,
            proId: null,
            provinceCode: null,
            municipalityNo: null,
            brgyNo: null,
            precinctNo: null,
            voterGroup: null,
            hasId: null,
            hasSubmitted: null
        }
    },

    setListView: function (e) {
        if (e.target.checked)
            this.setState({ withPhoto: false });
    },

    setPhotoView: function (e) {
        if (e.target.checked)
            this.setState({ withPhoto: true });
    },

    render: function () {
        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-custom-85" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Organization Member List</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body">

                    <div className="mt-radio-inline">
                        <label className="mt-radio">
                            <input type="radio" name="no_photo" checked={!this.state.withPhoto} onChange={this.setListView} /> List View
                        <span></span>
                        </label>
                        {/* <label className="mt-radio">
                            <input type="radio" name="with_photo" checked={this.state.withPhoto} onChange={this.setPhotoView} /> List View <small><em>( with photo )</em></small>
                            <span></span>
                        </label> */}
                    </div>
                    {this.state.withPhoto ?
                        (
                            <OrganizationSummaryPhotoDatatable
                                electId={this.props.electId}
                                proId={this.props.proId}
                                provinceCode={this.props.provinceCode}
                                municipalityNo={this.props.municipalityNo}
                                brgyNo={this.props.brgyNo}
                                precinctNo={this.props.precinctNo}
                                voterGroup={this.props.voterGroup}
                                hasId={this.props.hasId}
                                assignedPrecinct={this.props.assignedPrecinct}
                            />
                        )
                        :
                        (
                            <OrganizationSummaryDetailDatatable
                                electId={this.props.electId}
                                proId={this.props.proId}
                                provinceCode={this.props.provinceCode}
                                municipalityNo={this.props.municipalityNo}
                                brgyNo={this.props.brgyNo}
                                precinctNo={this.props.precinctNo}
                                voterGroup={this.props.voterGroup}
                                hasId={this.props.hasId}
                                assignedPrecinct={this.props.assignedPrecinct}
                            />
                        )
                    }
                </Modal.Body>
            </Modal>
        )
    }

});

window.OrganizationSummaryItemDetail = OrganizationSummaryItemDetail;