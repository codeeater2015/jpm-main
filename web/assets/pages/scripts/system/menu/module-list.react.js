var Checkbox = ReactBootstrap.Checkbox;
var FormGroup = ReactBootstrap.FormGroup;

var ModuleList = React.createClass({
	getInitialState : function(){
		return {
			items : [],
			selectedItems : [],
			selectAll : false,
			targetItem : null,
			loading: true
		}
	},
	
	componentDidMount : function(){
		this.getItems();
	},
	
	componentWillUnmount : function(){
		this.request.abort();
	},

	getItems : function(){
		var self = this;
		var url = Routing.generate("ajax_get_menu_module",{},true);
		this.request = $.ajax({
			url : url,
			type: "GET",
			beforeSend : function(){
				self.showLoading();
			}
		}).done(function(res){
			self.setState({items :res});
		}).fail(function(res){
			console.log(res);
		}).always(function(){
			self.hideLoading();
		});
	},
	showLoading : function(){
		this.setState({loading: true});
	},
	hideLoading : function(){
		this.setState({loading: false});
	},
	addToTree : function(item){
		this.setState({targetItem : item});
	},

	updateSelectedList : function(item,e){
		var selectedItems = this.state.selectedItems;
		var index = this.inlist(item);

		if(e.target.checked){
			if(index == -1)
				selectedItems.push(item)
		}else{
			if(index != -1){
				selectedItems.splice(index,1);
			}
		}

		this.setState({selectedItems : selectedItems});
	},
	
	inlist : function(item){
		var selectedItems = this.state.selectedItems;

		for(var index = 0;index < selectedItems.length;index++){
			if(selectedItems[index].moduleName == item.moduleName)
				return index;
		}
		return -1;
	},

	add : function(){
		var self = this;
		var menu = [];

		if(this.props.selectedGroup == null || this.props.selectedGroup == ""){
			bootbox.alert("Please select a group");
			return;
		}

		self.state.selectedItems.map(function(item){
			menu.push({
				menu_label: item.moduleLabel,
				menu_link: item.moduleRoute,
				menu_icon: item.moduleIcon,
				menu_target: "none",
				menu_type: "System",
				children:[]
			});
		});
		self.props.addHandler(menu);
		self.clear();
	},
	
	toggleSelect: function(){
		var selectAll = this.state.selectAll;
		var selectedItems = this.state.selectedItems;

		selectAll = !selectAll;	
	
		for(var ref in this.refs){
			this.refs[ref].checked = selectAll
		}

		if(selectAll)
			selectedItems = this.state.items;
		else
			selectedItems  = [];

		this.setState({selectAll : selectAll,selectedItems : selectedItems});
	},

	clear : function(){
		for(var ref in this.refs){
			this.refs[ref].checked = false
		}
		this.setState({selectedItems: [],selectAll : false});
	},

	render : function(){
		var self = this;
		return (
			<div>
				<div className="form-body">
					<div className="scroller" style={{height: "250px"}} data-always-visible="1" data-rail-visible="0" data-rail-color="red" data-handle-color="green">
						<div className="mt-checkbox-list">
						   {self.state.items.map(function(item){
								return(
									<label key={item.id} className="mt-checkbox mt-checkbox-outline"> <strong><span dangerouslySetInnerHTML={{__html: item.moduleIcon}}/> {item.moduleLabel}</strong> <small><i>//{item.moduleDesc}</i></small>
										<input type="checkbox" ref={item.moduleName} name={item.id} onChange={self.updateSelectedList.bind(self,item)} />
										<span/>
									</label>
								);
						   })}
						</div>
						{self.state.loading ? <div style={{textAlign : "center"}}><h3>Loading...</h3></div> : null}
					</div>

				</div>
				<div className="form-actions right">
					<button type='button' className="btn btn-sm btn-danger" onClick={self.toggleSelect} style={{marginRight: "10px"}}>{self.state.selectAll ? "Uncheck All" : "Check All"}</button>
					<button type='button' className="btn btn-sm btn-success" onClick={self.add}>Add to menu</button>
				</div>
			</div>
		);
	}

});


window.ModuleList = ModuleList;