

var GroupSection = React.createClass({
	getInitialState: function(){
		return {
			showCreateModal : false
		};
	},

	render : function(){
		return (
			<div className="portlet light portlet-fit portlet-datatable bordered">
				<div className="portlet-title">
					<div className="caption">
						<i className="icon-grid font-grey-gallery"/>
						<span className="caption-subject font-grey-gallery">Group List</span>
					</div>
					<div className="actions">
						<a href="javascript:;" className="btn btn-primary btn-sm"  onClick={this.openCreateModal} style={{ marginRight : "5px"}}>
							<i className="fa fa-plus"/> Create new
						</a>
						<a href="javascript:;" className="btn btn-danger btn-sm" onClick={this.batchDelete}>
							<i className="fa fa-trash"/> Delete selected item
						</a>

					</div>
				</div>
				<GroupCreateModal show={this.state.showCreateModal} onHide={this.closeCreateModal} onSuccess={this.reloadTable}/>
				<div className="portlet-body">
					<GroupDataTable ref="GroupDataTable" notify={this.notify}/>
				</div>
			</div>
		);
	},

	openCreateModal : function(){
		this.setState({showCreateModal : true});
	},

	closeCreateModal : function(){
		this.setState({showCreateModal : false});
	},

	batchDelete : function(){
		this.refs.GroupDataTable.batchDelete();
	},

	reloadTable : function(){
		this.refs.GroupDataTable.reload();
	},

	notify : function(message,color){
		$.notific8('zindex', 11500);
		$.notific8(message, {
			heading: 'System Message',
			color: color,
			life: 5000,
			verticalEdge: 'right',
			horizontalEdge: 'bottom',
		});
    }

});

window.GroupSection = GroupSection;