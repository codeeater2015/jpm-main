var Modal = ReactBootstrap.Modal;
var Tab = ReactBootstrap.Tab;
var Tabs = ReactBootstrap.Tabs;
var Nav = ReactBootstrap.Nav;
var NavItem = ReactBootstrap.NavItem;

var VoterCropModal = React.createClass({

    getInitialState: function () {
        return {
            imageCropper : null
        };
    },

    render: function () {
        let imgUrl = Routing.generate("ajax_get_field_upload_photo", { id: this.props.itemId });

        var photoUrl = imgUrl + "?" + new Date().getTime();
        
        return (
            <Modal keyboard={false} enforceFocus={false}  backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Crop Photo</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                
                <div>
                    <img id="original_image" style={{maxWidth: "100%"}} src={photoUrl}/>
                </div>
                <div className="col-md-12 text-right" style={{ marginTop: "15px" }}>
                    <button className="btn btn-sm btn-primary" onClick={this.saveImage} style={{ marginRight : "10px" }}>Crop Selected Area</button>
                <button className="btn btn-sm btn-default" onClick={this.props.onHide}>Close </button>

                </div>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initCropper();
    },

    initCropper : function(){
        var self = this;
        var image = document.getElementById('original_image');
        var croppable = false;

        var cropper = new Cropper(image, {
            aspectRatio: 4 / 5,
            autoCropArea: 0.65,
            viewMode: 1,
            ready: function () {
              croppable = true;
            },
        });

        this.imageCropper = cropper;
    },

    saveImage : function(){
        
        console.log("saving image");
        var self = this;

        var croppedCanvas = this.imageCropper.getCroppedCanvas();

        croppedCanvas.toBlob(function (blob) {
            console.log("blob data");
            console.log(blob);

            var formData = new FormData();
            formData.append('files[]', blob, 'profile_photo.jpg');

            $.ajax(Routing.generate('ajax_upload_project_voter_photo', { proId: self.props.proId, proVoterId: self.props.proVoterId }), {
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false }).done(function(res){
                self.props.onSuccess();
                self.props.onHide();
            }).fail(function(err){
                console.log("something went wrong");
            });
        });
    }

});

window.VoterCropModal = VoterCropModal;