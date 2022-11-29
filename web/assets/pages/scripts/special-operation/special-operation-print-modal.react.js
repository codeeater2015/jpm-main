var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SpecialOperationPrintModal = React.createClass({

    getInitialState: function () {
        return {
            printMode: 'SO',
            unselected: [],
            options: [],
            form: {
                data: {
                    profiles: [],

                },
                errors: []
            }
        };
    },

    componentDidMount: function () {
        this.initMultiSelect();
        this.loadProfiles();
    },

    componentWillUnmount: function () {
        this.isEmpty(this.requestProfiles) || this.requestProfiles.abort();
    },

    loadProfiles: function () {
        var self = this;

        self.requestProfiles = $.ajax({
            url: Routing.generate("ajax_get_so_no_id", { id: this.props.id }),
            type: "GET"
        }).done(function (res) {
            self.setState({ options: res, unselected: res });
            setTimeout(self.refreshSelectBox, 2000);
        });
    },

    initMultiSelect: function () {
        var self = this;

        var selectBox = this.refs.selectBox;

        $(selectBox).multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },

            afterSelect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setProfiles($(self.refs.selectBox).val());
            },

            afterDeselect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setProfiles($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });


    },

    refreshSelectBox: function () {
        $(this.refs.selectBox).multiSelect('refresh');
    },

    deselectAll: function () {
        $(this.refs.selectBox).multiSelect('deselect_all');
    },

    selectAll: function () {
        $(this.refs.selectBox).multiSelect('select_all');
    },

    setProfiles: function (selected) {
        var form = this.state.form;
        var unselected = [];

        if (selected != null) {
            form.data.profiles = selected;
            unselected = this.state.options.filter(function (item) {
                return selected.indexOf(item.pro_voter_id) == -1;
            });
        } else {
            form.data.profiles = [];
            unselected = this.state.options;
        }

        this.setState({ form: form, unselected: unselected });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setErrors: function (errors) {
        var form = this.state.form;
        form.errors = errors;
        this.setState({ form: form });
    },

    getError: function (field) {
        var errors = this.state.form.errors;
        for (var errorField in errors) {
            if (errorField == field)
                return errors[field];
        }

        return null;
    },

    getValidationState: function (field) {
        if (this.getError(field) != null)
            return "error";

        return null;
    },

    submit: function (e) {
        e.preventDefault();
        var self = this;
        var data = self.state.form.data;
        data.printOrigin = "SPECIAL OPERATIONS";

        self.requestTransmit = $.ajax({
            url: Routing.generate('ajax_post_project_print', { proId: this.props.proId }),
            type: 'POST',
            data: (data)
        }).done(function (res) {
            self.props.onHide();
        }).fail(function (res) {
            self.setErrors(res.responseJSON);
        });
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

    isEmpty: function (value) {
        return value == null || value == '';
    },

    render: function () {

        return (
            <Modal enforceFocus={false} backdrop="static" dialogClassName="modal-custom-85" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header closeButton>
                    <Modal.Title>Create Template</Modal.Title>
                </Modal.Header>

                <Modal.Body bsClass="modal-body overflow-auto">

                    <form onSubmit={this.submit}>
                        <div className="col-md-12 no-padding">
                            <div className="text-right">
                                <button type="button" onClick={this.deselectAll} className="btn btn-xs btn-default" style={{ marginRight: "5px" }}>Deselect All</button>
                                <button type="button" onClick={this.selectAll} className="btn btn-xs btn-success">Select All</button>
                            </div>
                        </div>
                        <div className="clearfix"></div>

                        <div className="col-md-6 no-padding">
                            <div><strong>Available :</strong> {this.state.unselected.length}</div>
                        </div>
                        <div className="col-md-6 ">
                            <div style={{ marginLeft: "32px" }}><strong>Selected : </strong> {this.state.form.data.profiles.length}</div>
                        </div>
                        <FormGroup controlId="formProfiles" validationState={this.getValidationState('profiles')} >
                            <select multiple ref="selectBox" className="searchable" id="contracts" name="profiles[]">
                                {this.state.options.map(function (item) {
                                    return (<option key={item.pro_voter_id} value={item.pro_voter_id}>{item.voter_name} ({item.voter_group}) - {item.barangay_name} - {item.generated_id_no}</option>)
                                })}
                            </select>
                            <div className="text-right">
                                <HelpBlock>{this.getError('profiles')}</HelpBlock>
                            </div>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <div className="text-right m-t-md">
                            <button type="button" className="btn btn-default" onClick={this.props.onHide}>Cancel</button>
                            <button type="submit" className="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.SpecialOperationPrintModal = SpecialOperationPrintModal;