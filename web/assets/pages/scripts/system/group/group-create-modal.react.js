var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;
var Checkbox = ReactBootstrap.Checkbox;
var HelpBlock = ReactBootstrap.HelpBlock;

var GroupCreateModal = React.createClass({

	getInitialState: function () {
		return {
			loading: false,
			alertVisible: false,
			form: {
				errors: {},
				data: {
					name: "",
					description: "",
					allowRead : 0 ,
					allowWrite : 0,
					accessLevel : "",
					status: true
				}
			}
		};
	},

	render: function () {

		return (
			<Modal show={this.props.show} backdrop="static" keyboard={false} onHide={this.props.onHide} onExited={this.clearForm} onEnter={this.onEnter}>
				<form ref="group_form" onSubmit={this.onSubmit}>
					<Modal.Header closeButton>
						<Modal.Title><strong>Create Group</strong></Modal.Title>
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
									<FormControl componentClass="textarea" rows="5" name="description" value={this.state.form.data.description} onChange={this.onChange} />
									<HelpBlock>{this.getError('groupDesc')}</HelpBlock>
								</FormGroup>
							</div>
						</div>
						<div className="row">
							<div className="col-md-12">
								<FormGroup controlId="formIsActive">
									<ControlLabel>Status</ControlLabel><br />
									<FormControl type="checkbox" className="make-switch" name="status" data-on-text="Active" data-off-text="Inactive" data-off-color="danger" data-size="small" />
								</FormGroup>
							</div>
						</div>
					</Modal.Body>
					<Modal.Footer>
						<button type="submit" className="btn btn-primary btn-fill" disabled={this.state.loading}>Create <AppLoading loading={this.state.loading} /></button>
						<button type="button" onClick={this.props.onHide} className="btn btn-default">Close</button>
					</Modal.Footer>
				</form>
			</Modal>

		);
	},

	onSubmit: function (e) {
		e.preventDefault();
		var self = this;
		var data = this.state.form.data;
		var url = Routing.generate("ajax_create_group", {}, true);

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: JSON.stringify(data),
			beforeSend: function () {
				self.showLoading();
			}
		}).done(function (res) {
			self.setState({ alertType: 'info', alertMessage: res.message });
			self.props.onSuccess();
			self.clearForm();

		}).fail(function (res) {
			var message = "";
			switch (res.status) {
				case 400:
					message = res.responseJSON.message;
					self.setErrors(res.responseJSON.validation_error);
					break;
				case 403:
					message = res.responseJSON.message;
					break;
				default:
					message = "Unknown error.";
			}
			self.setState({ alertType: 'danger', alertMessage: message });

		}).always(function () {
			self.hideLoading();
			self.showAlert();
		});
	},

	onEnter: function () {
		var self = this;
		var status = $('input[name="status"]');
		var form = self.state.form;

		status.bootstrapSwitch('state', 1, 1);
		status.on('switchChange.bootstrapSwitch', function (event, state) {
			form.data.status = state;
			self.setState(form);
		});
	},

	onChange: function (e) {
		var form = this.state.form;
		form.data[e.target.name] = e.target.value;

		this.setState(form);
	},

	onCheckboxChange: function (e) {
		var form = this.state.form;
		form.data[e.target.name] = e.target.checked ? 1 : 0;

		this.setState(form);
	},

	clearForm: function () {
		this.setState(this.getInitialState());
	},

	showLoading: function () {
		this.setState({ loading: true });
	},

	hideLoading: function () {
		this.setState({ loading: false });
	},

	showAlert: function () {
		this.setState({ alertVisible: true });
	},

	hideAlert: function () {
		this.setState({ alertVisible: false });
	},

	setErrors: function (errors) {
		var form = this.state.form;
		form.errors = errors;

		this.setState(form);
	},

	getError: function (field) {
		for (var errorField in this.state.form.errors) {
			if (errorField == field)
				return this.state.form.errors[field];
		}
		return null;
	},

	getValidationState: function (field) {
		if (this.getError(field)) {
			return "error";
		}

		return null;
	}
});


window.GroupCreateModal = GroupCreateModal;