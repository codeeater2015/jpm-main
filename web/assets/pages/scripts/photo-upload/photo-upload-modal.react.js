var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var PhotoUploadModal = React.createClass({

    getInitialState: function () {
        return {
            images: []
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Uploaded Images</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div id="gallery">
                        {this.state.images.map(function (item) {
                            let imgUrl = Routing.generate("ajax_get_field_upload_photo", { id: item.id });
                            console.log(imgUrl);

                            return (
                                <img
                                    src={imgUrl}
                                    dataImage={imgUrl}
                                />
                            );
                        })}
                    </div>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadImages(this.props.id);
    },

    loadImages: function (id) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_field_upload_images", { hdrId: id }),
            type: "GET"
        }).done(function (res) {
            self.setState({ images: res }, self.initGallery);
        });
    },

    initGallery() {
        var gallery = $("#gallery").unitegallery();
        gallery.enterFullscreen();
    }
});


window.PhotoUploadModal = PhotoUploadModal;