var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOperationUploadPhotoModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    electId: 4,
                    proVoterId: null
                },
                errors: []
            },
            provinceCode: 53,
            showNewVoterCreateModal: false
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Special Ops Photo Upload</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="row">
                        <div className="col-md-12">
                            <h4>Members Photo Upload</h4>
                            <form action="/file-upload"
                                className="dropzone"
                                id="photo-uploader">
                            </form>
                        </div>
                    </div>
                    <div className="clearfix" />
                    <br />
                    <div className="row">
                        <div className="col-md-12 text-right">
                            <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        //initialize uploader
        this.initUploader()
    },

    initUploader() {
        var myDropzone = new Dropzone("#photo-uploader", { url: Routing.generate("ajax_special_ops_photo_upload", { recId: this.props.id }) });
        this.dropzone = myDropzone;
    }

});


window.SpecialOperationUploadPhotoModal = SpecialOperationUploadPhotoModal;