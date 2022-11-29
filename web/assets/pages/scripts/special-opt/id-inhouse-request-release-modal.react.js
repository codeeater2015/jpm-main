var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var IdInhouseRequestReleaseModal = React.createClass({

	getInitialState : function(){
		return {
			unselected : [],
			options : [],
			form : {
				data : {
					projectVoters : [],
					
				},
				errors : []
			},

			isLoading : false,
			progressValue : 0
		};
	},

	componentDidMount : function(){
		this.initComponents();
        this.loadProjectVoters();
	},

	loadProjectVoters : function(){
		var self = this;

		self.requestProjectVoters = $.ajax({
			url : Routing.generate("ajax_get_id_request_for_release",{ 
                hdrId : this.props.hdrId
            }),
			type : "GET"
		}).done(function(res){
			self.setState({options : res,unselected : res});
            setTimeout(self.refreshSelectBox,2000);
        });
	},

    componentWillUnmount : function(){
        this.isEmpty(this.requestProjectVoters) || this.requestProjectVoters.abort();
    },


	initComponents : function(){
		this.initMultiSelect();
	},

	initMultiSelect : function(){
		var self = this;

		var selectBox = this.refs.selectBox;

		$(selectBox).multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function(ms){
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function(e){
                        if (e.which == 40){
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },

            afterSelect: function(values){
                this.qs1.cache();
                this.qs2.cache();
                self.setProjectVoters($(self.refs.selectBox).val());
            },

            afterDeselect: function(values){
                this.qs1.cache();
                this.qs2.cache();
                self.setProjectVoters($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });

       
	},

	refreshSelectBox : function(){
		$(this.refs.selectBox).multiSelect('refresh');
	},

    deselectAll : function(){
        $(this.refs.selectBox).multiSelect('deselect_all');
    },

    selectAll : function(){
        $(this.refs.selectBox).multiSelect('select_all');
    },

	setProjectVoters : function(selected){
		var form = this.state.form;
        var unselected = [];

        if(selected != null){
            form.data.projectVoters = selected;
            unselected = this.state.options.filter(function(item){
                return selected.indexOf(item.pro_voter_id) == -1;
            });
        }else{
            form.data.projectVoters = [];
            unselected = this.state.options;
        }
        
        this.setState({form : form, unselected : unselected});
	}, 

    setFormProp : function(e){
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({form : form});
    },

    setErrors : function(errors){
        var form = this.state.form;
        form.errors = errors;
        this.setState({form : form});
    },

    getError : function(field){
        var errors = this.state.form.errors;
        for(var errorField in errors){
            if(errorField == field)
                return errors[field];
        }

        return null;
    },

    getValidationState : function(field){
        if(this.getError(field) != null)
            return "error";

        return null;
    },

	submit : function(e){
		e.preventDefault();

		var self = this;
        var data = self.state.form.data;

        self.requestPostUpdate = $.ajax({
            url: Routing.generate("ajax_post_id_request_for_release"),
            type: "POST",
			data: data
        }).done(function () {
			self.props.notify("Items has been released...", "ruby");
			self.props.onHide();
			self.props.onSuccess();
        }).fail(function (err) {
			self.props.notify("Opps!", "ruby");
            self.setErrors(err.responseJSON)
        }).always(function () {
            self.setState({ isLoading : false, progressValue : 0 });
		});
		
		self.setState({isLoading : true, progressValue : 0 });
	},

    popupCenter : function(url, title, w, h) {  
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

    isEmpty : function(value){
        return value == null || value == '';
    },

	render: function () {
		return (
			<Modal enforceFocus={false} backdrop="static" dialogClassName="modal-custom-85" show={this.props.show} onHide={this.props.onHide}>
				<Modal.Header closeButton>
					<Modal.Title>ID Release Form</Modal.Title>
				</Modal.Header>

				<Modal.Body bsClass="modal-body overflow-auto">
					<form onSubmit={this.submit}>
						<div className="text-right">
							<button type="button" onClick={this.deselectAll} className="btn btn-xs btn-default" style={{ marginRight: "5px" }}>Deselect All</button>
							<button type="button" onClick={this.selectAll} className="btn btn-xs btn-success">Select All</button>
						</div>
						<div className="clearfix"></div>

						<div className="col-md-6 remove-padding">
							<div style={{ marginLeft: "-15px" }}><strong>Available :</strong> {this.state.unselected.length}</div>
						</div>
						<div className="col-md-6 ">
							<div style={{ marginLeft: "30px" }}><strong>Selected : </strong> {this.state.form.data.projectVoters.length}</div>
						</div>
						<FormGroup controlId="formProjectVoters" validationState={this.getValidationState('projectVoters')} >
							<select multiple ref="selectBox" className="searchable" id="voters_multiselect" name="projectVoters[]">
								{this.state.options.map(function (item) {
									return (<option key={item.dtl_id} value={item.dtl_id}>{item.voter_name} / {item.barangay_name} ({moment(item.updated_at).format('MMM DD, YYYY hh:mm A')})</option>)
								})}
							</select>
							<div className="text-right">
								<HelpBlock>{this.getError('projectVoters')}</HelpBlock>
							</div>
                        </FormGroup>
                        
						<div className="clearfix"></div>
						
						<div className="text-right m-t-md">
							<button type="button" className="btn btn-default" style={{ marginRight : "10px" }} onClick={this.props.onHide}>Cancel</button>
							<button type="submit" className="btn btn-primary">Submit</button>
						</div>
					</form>
				</Modal.Body>
			</Modal>
		);
	}
});


window.IdInhouseRequestReleaseModal = IdInhouseRequestReleaseModal;