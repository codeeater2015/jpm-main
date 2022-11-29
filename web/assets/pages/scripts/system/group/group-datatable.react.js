
var GroupDataTable = React.createClass({

	getInitialState : function(){
		return {
			showEditModal : false,
			showPermissionModal : false,
			targetGroup : {}
		};
	},

	componentDidMount : function(){
		var self = this;
		var grid = new Datatable();
		var group_table = $("#group_table");

		var url = Routing.generate("ajax_get_group",{},true);
		grid.init({
			src: group_table,
            dataTable : {
				"bState" : true,
				"autoWidth": true,
				"deferRender": true,
	            "ajax" : {
	                "url" : url,
	                "type" : "GET"
	            },
                "columnDefs": [{  // set default column settings
                    'orderable': false,
                    'targets': [0,2,3,4,5,6]
                },{
                	'searchable' : false,
					'targets':[0,2,3,4,6]
				},{
                	'className': 'align-center',
					'targets' : [0,2,3,4,5,6]
				}],
				"order": [],
	            "columns" : [
					{
						"data" : "id",
						"render" : function(data){
							return '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'+data+'"/><span></span></label>';
						}
					},
					{"data" : "groupName"},
					{"data" : "accessLevel"},
					{
						"data" : "allowRead",
						"render" : function(data){
							return data == 1 ? "Yes" : "No";
						}
					},
					{
						"data" : "allowWrite",
						"render" : function(data){
							return data == 1 ? "Yes" : "No";
						}
					},
					{"data" : "status"},
	        	  	{"render" : function(){
		                    var editBtn = "<a href='javascript:;' class='btn btn-xs btn-success group-edit-btn' data-toggle='tooltip' data-title='Edit'><i class='glyphicon glyphicon-edit'></i></a>";
							var permissionBtn = "<a href='javascript:;' class='btn btn-xs btn-info group-permission-btn' data-toggle='tooltip' data-title='Permission'><i class='fa fa-shield'></i></a>";
							var deleteBtn = "<a href='javascript:;' class='btn btn-xs btn-danger group-delete-btn' data-toggle='tooltip' data-title='Delete'><i class='glyphicon glyphicon-trash'></i></a>";

		                    return editBtn + permissionBtn + deleteBtn ;
	                	}
	            	}
	            ]
            }
            
        });

        group_table.find("tbody").on('click','.group-edit-btn',function(e){
			e.preventDefault();
			$(this).blur();
        	var group =  grid.getDataTable().row($(this).parents('tr') ).data();
        	self.edit(group);
        });

		group_table.find("tbody").on('click','.group-permission-btn',function(e){
			e.preventDefault();
			$(this).blur();
			var group =  grid.getDataTable().row($(this).parents('tr') ).data();
			self.permission(group);
		});

        group_table.find('tbody').on('click','.group-delete-btn',function(e){
			e.preventDefault();
			$(this).blur();
        	var group=  grid.getDataTable().row($(this).parents('tr') ).data();
        	self.delete(group);
        });

        self.grid = grid;
	},

	render : function(){
		return (
		    <div className="table-container">
				<div className="table-actions-wrapper">
					<span> </span>
				</div>
		    	<table id="group_table" className="table table-striped table-bordered table-checkable" width="100%">
			        <thead>
			            <tr>
							<th width="2%">
								<label className="mt-checkbox mt-checkbox-single mt-checkbox-outline">
									<input type="checkbox" className="group-checkable"  />
									<span/>
								</label>
							</th>
			                <th>Group Name</th>
							<th className="text-center">Access Level </th>
							<th className="text-center">Allow Read </th>
							<th className="text-center">Allow Write </th>
							<th width="12%">Status</th>
			                <th className="center" width="10%">Action</th>
			            </tr>
						<tr role="row" className="filter">
							<td> </td>
							<td><input type="text" className="form-control form-filter input-sm" name="group_name"/> </td>
							<td></td>
							<td></td>
							<td></td>
							<td>
								<select name="status" className="form-control form-filter input-sm">
									<option value="">Select...</option>
									<option value="1">Active</option>
									<option value="0">Inactive</option>
								</select>
							</td>
							<td>
								<button className="btn btn-sm green btn-outline filter-submit margin-bottom">
								<i className="fa fa-search"/> Search</button>
							</td>
						</tr>
			        </thead>
			        <tbody>
			        </tbody>
		    	</table>
				<GroupPermissionModal model={this.state.targetGroup} show={this.state.showPermissionModal} onHide={this.closePermissionModal} notify={this.props.notify}/>
		    	<GroupEditModal onSuccess={this.reload} model={this.state.targetGroup} show={this.state.showEditModal} onHide={this.closeEditModal}/>
		   </div>
		);
	},

	openEditModal : function(){
		this.setState({showEditModal : true});
	},

	closeEditModal : function(){
		this.setState({showEditModal : false});
	},

	openPermissionModal : function(){
		this.setState({showPermissionModal : true});
	},

	closePermissionModal : function(){
		this.setState({showPermissionModal : false});
	},

	setTargetGroup : function(group){
		this.setState({targetGroup : group})
	},

	unsetTargetGroup : function(){
		this.setState({targetGroup : {}});
	},
	permission : function(group){
		this.setTargetGroup(group);
		this.openPermissionModal();
	},
	edit : function(group){
		this.setTargetGroup(group);
		this.openEditModal();
	},
	delete : function(group){
		var self = this;
		var url = Routing.generate("ajax_delete_group",{},true);
		bootbox.dialog({
			title: "<strong><i class='fa fa-warning'></i> Warning</strong>",
			message: "<strong>Are you sure you want to remove this?</strong>",
			buttons: {
				'cancel': {
					label: 'Cancel',
					className: 'btn-default '
				},
				'confirm': {
					label: 'Yes, Delete',
					className: 'btn-danger',
					callback: function(){
						$.ajax({
							url : url + '?id=' + group.id,
							type: 'DELETE',
							beforeSend: function(){
								$.enable_loading('processing');
							}
						}).done(function(response){
							self.reload();
							self.props.notify(response.message,'teal');
						}).fail(function(res){
							console.log(res)
							var message = "";
							switch(res.status){
								case 403:
									message = res.responseJSON.message;
									break;
								case 404:
									message = res.responseJSON.message;
									break;
								default:
									message = "Something went wrong while processing your request in the server. Please try again.";
							}
							self.props.notify(message,'ruby');
						}).always(function(){
							$.disable_loading('processing');
						});
					}
				}
			}
		});

	},
	batchDelete : function(){
		var self = this;
		var selectedCount = self.grid.getSelectedRowsCount();
		var rowsSelected = self.grid.getSelectedRows();

		if(selectedCount == 0){
			bootbox.alert("<strong>Please select an item(s) you want to remove!</strong>");
			return false;
		}

		var count = (selectedCount > 1) ? selectedCount+" items" : selectedCount+" item";

		bootbox.dialog({
			title: "<strong><i class='fa fa-warning'></i> Warning</strong>",
			message: "<strong>You have selected "+count+". Are you sure you want to remove?</strong>",
			buttons: {
				'cancel': {
					label: 'Cancel',
					className: 'btn-default '
				},
				'confirm': {
					label: 'Yes, Delete',
					className: 'btn-danger',
					callback: function(){
						var url = Routing.generate("ajax_batch_delete_group",{},true);
						$.ajax({
							url : url,
							type: 'DELETE',
							data:{
								id : rowsSelected
							},
							beforeSend: function(){
								$.enable_loading('processing');
							}
						}).done(function(response){
							self.reload();
							self.props.notify(response.message,'teal');
						}).fail(function(res){
							var message = "";
							switch(res.status){
								case 403:
									message = res.responseJSON.message;
									break;
								case 404:
									message = res.responseJSON.message;
									break;
								default:
									message = "Something went wrong while processing your request in the server. Please try again.";
							}
							self.props.notify(message,'ruby');
						}).always(function(){
							$.disable_loading('processing');
						});
					}
				}
			}
		});
		console.log(this.grid.getSelectedRowsCount());
	},
	reload: function(){
		this.grid.getDataTable().ajax.reload();
	},

	getSelectedRowsCount: function(){
		return this.grid.getSelectedRowsCount();
	},


});

window.GroupDataTable = GroupDataTable;