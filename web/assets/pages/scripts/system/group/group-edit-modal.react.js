var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;
var HelpBlock = ReactBootstrap.HelpBlock;
var Checkbox = ReactBootstrap.Checkbox;

var GroupEditModal = React.createClass({

	getInitialState : function(){
		return {
			loading: false,
			alertVisible: false,
			form : {
				errors : {},
				data : {
				    id : "",
				    name : "",
                    description : "",
					status : true
                }
			}
		};
	},
	
	componentDidMount : function(){
		
	},

	componentWillReceiveProps : function(nextProps){
		if(nextProps.hasOwnProperty('model')){
			var form = this.state.form;
			form.data.name = (nextProps.model.groupName == null) ? "" : nextProps.model.groupName;
			form.data.description = (nextProps.model.groupDesc == null) ? "" : nextProps.model.groupDesc;
			form.data.accessLevel = (nextProps.model.accessLevel == null) ? "" : nextProps.model.accessLevel;
			form.data.allowRead = (nextProps.model.allowRead == null) ? 0 : nextProps.model.allowRead;
			form.data.allowWrite = (nextProps.model.allowWrite == null) ? 0 : nextProps.model.allowWrite;
			form.data.status = (nextProps.model.status == "Active") ? 1 : 0;
			form.data.id = nextProps.model.id;
			this.setState({form : form});
		}
	},

	render : function(){
		return (
			<Modal backdrop="static" show={this.props.show} onHide={this.props.onHide} onExited={this.clear} onEnter={this.onEnter}>
				<form onSubmit={this.onSubmit}>
					<Modal.Header closeButton>
					<Modal.Title><strong>Edit Group</strong></Modal.Title>
					</Modal.Header>
					<Modal.Body>

						<div className="row">
							<div className="col-md-12">
								<AlertDismissable isVisible={this.state.alertVisible} alertType={this.state.alertType} alertMessage={this.state.alertMessage} hideAlert={this.hideAlert} />
							</div>
						</div>
						<div className="row">
							<div className="col-md-6">
								<FormGroup bsClass="form-group" controlId="formName" validationState={this.getValidationState('groupName')}>
									<ControlLabel>Group Name</ControlLabel>
									<FormControl type="text" name="name" value={this.state.form.data.name} onChange={this.onChange} autoFocus />
									<HelpBlock>{this.getError('groupName')}</HelpBlock>
								</FormGroup>
							</div>
							<div className="col-md-6">
								<FormGroup bsClass="form-group" controlId="formAccessLevel" validationState={this.getValidationState('accessLevel')}>
									<ControlLabel>Access Level</ControlLabel>
									<FormControl componentClass="select" name="accessLevel" value={this.state.form.data.accessLevel} onChange={this.onChange}>
										<option value=""> -- Select -- </option>
										<option value="ALL"> All Access </option>
										<option value="PROVINCE_LEVEL">Province Access </option>
										<option value="MUNICIPALITY_LEVEL">Municipality Access </option>
										<option value="BARANGAY_LEVEL">Barangay Level </option>
									</FormControl>
									<HelpBlock>{this.getError('accessLevel')}</HelpBlock>
								</FormGroup>
							</div>
						</div>
						<div className="row">
							<div className="col-md-6" style={{ marginLeft : "20px" }}>
								<FormGroup bsClass="form-group" controlId="formAccessLevel" validationState={this.getValidationState('allowWrite')}>
									<Checkbox name="allowRead" checked={this.state.form.data.allowRead == 1 ? true : false } onChange={this.onCheckboxChange}> Allow Read </Checkbox>
									<Checkbox name="allowWrite" checked={this.state.form.data.allowWrite == 1 ? true : false } onChange={this.onCheckboxChange}> Allow Write </Checkbox>
									<HelpBlock>{this.getError('allowWrite')}</HelpBlock>
								</FormGroup>
							</div>
						</div>
						<div className="row">
							<div className="col-md-12">
								<FormGroup bsClass="form-group" controlId="formDescription" validationState={this.getValidationState('groupDesc')}>
									<ControlLabel>Description</ControlLabel>
									<FormControl componentClass="textarea"  value={this.state.form.data.description} rows="5" name="description" onChange={this.onChange} />
									<HelpBlock>{this.getError('groupDesc')}</HelpBlock>
								</FormGroup>
							</div>
						</div>
						<div className="row">
							<div className="col-md-12">
								<FormGroup controlId="formIsActive">
									<ControlLabel>Status</ControlLabel><br/>
									<FormControl type="checkbox" className="make-switch" name="status" data-on-text="Active" data-off-text="Inactive" data-off-color="danger" data-size="small"/>

								</FormGroup>
							</div>
						</div>
					</Modal.Body>
					<Modal.Footer>
						<button type="submit" className="btn btn-primary btn-fill" disabled={this.state.loading}>Update <AppLoading loading={this.state.loading}/></button>
						<button type="button" onClick={this.props.onHide} className="btn btn-default">Close</button>
					</Modal.Footer>
				</form>
			</Modal>
		);
	},

	onSubmit : function(e){
		e.preventDefault();
		var self = this;
		var data = this.state.form.data;
		var url = Routing.generate("ajax_update_group",{},true);

		console.log('Editing User Group');
		$.ajax({
			url : url,
			type: 'PATCH',
			dataType : 'json',
			data : JSON.stringify(data),
			beforeSend : function(){
				self.showLoading();
			}
		}).done(function(response){
			console.log(response);
			self.setState({alertType : 'info', alertMessage : response.message});
			self.props.onSuccess();
			self.clearErrors();
		}).fail(function(res){
			var message = "";
			switch(res.status){
				case 400:
					message = res.responseJSON.message;
					self.setErrors(res.responseJSON.validation_error);
					break;
				case 403:
					message = res.responseJSON.message;
					break;
				case 404:
					message = res.responseJSON.message;
					break;
				default:
					message = "Unknown error.";
			}
			self.setState({alertType : 'danger', alertMessage : message});
		}).always(function(){
			self.hideLoading();
			self.showAlert();
		});
	},
	onEnter: function(){
		var self = this;
		var status = $('input[name="status"]');
		var form = self.state.form;

		console.log(form.data.status)
		status.bootstrapSwitch('state', form.data.status, form.data.status);
		status.on('switchChange.bootstrapSwitch', function(event, state) {
			form.data.status = state;
			self.setState(form);
		});
	},
	clear : function(){
		this.setState(this.getInitialState());
	},
	clearErrors : function(){
		var form = this.state.form;
		form.errors = {};
		this.setState(form);
	},
	
	onChange : function(e){
		var form = this.state.form;
		form.data[e.target.name] = e.target.value;

		this.setState(form);
	},

	showLoading : function(){
		this.setState({loading : true});
	},

	hideLoading : function(){
		this.setState({loading : false});
	},

	showAlert : function(){
		this.setState({alertVisible: true});
	},

	hideAlert : function() {
		this.setState({alertVisible: false});
	},
	
	setErrors : function(errors){
		var form = this.state.form;
		form.errors = errors;

		this.setState({form : form});
	},

	getError : function(field){
		for(var errorField in this.state.form.errors){
			if(errorField == field)
				return this.state.form.errors[field];
		}
		return null;
	},

	getValidationState : function(field){
		if(this.getError(field)) {
			return "error";
		}

		return null;
	},	

	onCheckboxChange: function (e) {
		var form = this.state.form;
		form.data[e.target.name] = e.target.checked ? 1 : 0;

		this.setState(form);
	}
});

window.GroupEditModal = GroupEditModal;