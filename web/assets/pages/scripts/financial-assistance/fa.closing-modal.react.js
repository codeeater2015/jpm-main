var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FinancialAssistanceClosingModal = React.createClass({

    getInitialState: function () {

        var date = new Date();

        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
    
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
    

        return {
            unselected: [],
            options: [],
            form: {
                data: {
                    profiles: [],
                    closingDate : year + '-' + month + '-' + day,
                },
                errors: []
            }
        };
    },

    componentDidMount: function () {
        this.initComponents();
        this.loadData();
    },

    componentWillUnmount: function () {
        this.isEmpty(this.requestProfiles) || this.requestProfiles.abort();
    },

    loadData: function () {
        var self = this;

        var endpoint = Routing.generate("ajax_get_unclosed_transactions");

        self.requestProfiles = $.ajax({
            url: endpoint,
            type: "GET"
        }).done(function (res) {
            self.setState({ options: res, unselected: res });
            setTimeout(self.refreshSelectBox, 2000);
        });
    },

    initComponents: function () {
        this.initMultiSelect();
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
                return selected.indexOf(item.profile_no) == -1;
            });
        } else {
            form.data.profiles = [];
            unselected = this.state.options;
        }

        this.setState({ form: form, unselected: unselected });
    },

    setPrintMode: function (e) {
        if (e.target.checked) {
            this.setState({ 'printMode': e.target.value }, this.initProfileEndpoint(e.target.value));
        }
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

        self.requestTransmit = $.ajax({
            url: Routing.generate('ajax_post_close_transactions', { proId: this.props.proId }),
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
                    <Modal.Title>Close Transactions</Modal.Title>
                </Modal.Header>

                <Modal.Body bsClass="modal-body overflow-auto">

                    <form onSubmit={this.submit}>
                        <div className="col-md-2 no-padding">
                            <FormGroup controlId="formEventName" validationState={this.getValidationState('closingDate')}>
                                <ControlLabel> Closing Date : </ControlLabel>
                                <input type="date" value={this.state.form.data.closingDate} className="input-sm form-control" onChange={this.setFormProp} name="closingDate" />
                                <HelpBlock>{this.getError('closingDate')}</HelpBlock>
                            </FormGroup>
                        </div>
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
                                    return (<option key={item.trn_id} value={item.trn_id}>Trn : {item.trn_no} | Date :  {item.trn_date} | Released Date : {item.release_date}  | Applicant : {item.applicant_name} ( {item.barangay_name},{item.municipality_name} )</option>)
                                })}
                            </select>
                            <div className="text-right">
                                <HelpBlock>{this.getError('profiles')}</HelpBlock>
                            </div>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <div className="text-right m-t-md">
                            <button type="button" className="btn btn-default" onClick={this.props.onHide}>Cancel</button>
                            <button type="submit" className="btn btn-primary" style={{ marginLeft : "10px" }}>Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.FinancialAssistanceClosingModal = FinancialAssistanceClosingModal;