var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;
var HelpBlock = ReactBootstrap.HelpBlock;

var GroupPermissionModal = React.createClass({

    getInitialState : function(){
        return {
            loading : false,
            selectAllPermission : false,
            selectedModule : "",
            selectedGroup : {},
            selectedPermission : [],
            module_list : [],
            permission_list : [],
            module_disable : true,
            checkState : []
        };
    },
    reset : function(){
      this.setState(this.getInitialState);
    },
    onEntering : function(){
        var self = this;
        self.setState({selectedGroup : self.props.model});

        $(self.refs.module).select2({
            theme: "bootstrap",
            placeholder: "Select a module",
            width: 'auto',
            selectOnClose: true
        }).on("change",function(){
            self.loadPermission();
        });
    },
    onEntered : function(){
        this.loadModule();
    },
    onExited : function(){
        this.reset();
    },
    loadModule : function(){
        var self = this;
        var url = Routing.generate("ajax_select2_group_module",{},true);

        $.ajax({
            url : url,
            type: "GET",
            dataType: "json",
            beforeSend : function(){
                self.setState({module_disable: true});
            }
        }).done(function(res){
            console.log(res);
            self.setState({module_list : res});
        }).fail(function(res){
            console.log(res);
        }).always(function(){
            self.setState({module_disable: false});
        });
    },
    loadPermission : function(){
        var self = this;
        var moduleId =$(self.refs.module).val();
        var group = self.state.selectedGroup;

        self.setSelectedModule(moduleId);

        if(moduleId == "" || moduleId == null){
            self.setState({
               permission_list : []
            });
            return;
        }

        var url = Routing.generate("ajax_get_permission_by_module",{groupId : group.id,moduleId : moduleId},true);
        $.ajax({
            url : url,
            type: "GET",
            dataType: "json",
            beforeSend : function(){
            }
        }).done(function(res){
            console.log(res);
            var checkState = []
            var group_permission = [];
            res.group_permission.map(function(row){
                checkState[row.permission.id] = true;
                group_permission.push(row.permission.id);
            });

            self.setState({
                permission_list : res.permission,
                selectedPermission : group_permission,
                checkState : checkState,
                selectAllPermission : (res.permission.length == res.group_permission.length && res.permission.length != 0)
            });
            console.log(res.permission.length)

        }).fail(function(res){
            console.log(res);
        }).always(function(){

        });
    },
    setSelectedModule : function(module){
        this.setState({selectedModule : module});
    },
    setSelectedPermission : function(e){
        var selectedPermission = this.state.selectedPermission;
        var permissionId = parseInt(e.target.value);
        var index = selectedPermission.indexOf(permissionId);

        if (this.state.selectAllPermission) {
            this.state.selectAllPermission = !this.state.selectAllPermission;
        }

        if(!this.state.checkState[permissionId]){
            if (index == -1) {
                this.state.checkState[permissionId] = true;
                selectedPermission.push(permissionId);
            }
        }else{
            if (index > -1) {
                this.state.checkState[permissionId] = false;
                selectedPermission.splice(index, 1);
            }
        }

        this.setState({selectAllPermission :  (this.state.permission_list.length == selectedPermission.length) ? true : this.state.selectAllPermission,selectedPermission : selectedPermission, checkState : this.state.checkState});
    },
    toggleSelect: function(){
        var self = this;
        var selectAllPermission = this.state.selectAllPermission;
        var permission = [];

        if(this.state.selectedModule == ""){
            return;
        }

        var state = !selectAllPermission;
        var checkState = [];
        self.state.permission_list.map(function(row){
            checkState[row.id] = state;
        });

        if(state)
            this.state.permission_list.map(function(item){
                permission.push(item.id);
            });
        else
            permission  = [];

        this.setState({selectAllPermission : state,selectedPermission : permission, checkState : checkState});
    },
    onSubmit : function(){
        var self = this;
        var data = {
            group : self.state.selectedGroup.id,
            permission : self.state.selectedPermission,
            module : self.state.selectedModule
        };
        console.log(data);
        var url = Routing.generate("ajax_save_group_permission",{},true);
        $.ajax({
            url : url,
            type: "POST",
            dataType: "json",
            data: (data),
            beforeSend : function(){
                self.setState({loading : true});
            }
        }).done(function(res){
            console.log(res);
            self.props.notify(res.message,'teal');
        }).fail(function(res){
            console.log(res);
            var message;
            switch(res.status){
                case 403:
                    message = res.responseJSON.message;
                    break;
                default:
                    message = "Unknown error.";
            }
            self.props.notify(message,'ruby');
        }).always(function(){
            self.setState({loading : false});
        });

    },
    render : function(){
        var self = this;
        return (
            <Modal enforceFocus={false} show={this.props.show} backdrop="static" keyboard={false} onHide={this.props.onHide} onEntering={this.onEntering} onEntered={this.onEntered} onExited={this.onExited}>
                <form ref="group_form" onSubmit={this.onSubmit}>
                    <Modal.Header closeButton>
                        <Modal.Title><strong>Group Permission</strong></Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                        <div className="row">
                            <div className="col-md-12">
                                <div className="portlet light bordered">
                                    <div className="portlet-title">
                                        <div className="caption">
                                            {this.state.selectedGroup.groupName} <small>({this.state.selectedGroup.groupDesc})</small>
                                        </div>
                                    </div>
                                    <div className="portlet-body">
                                        <div className="row">
                                            <div className="col-md-6">
                                                <select name="module" ref="module" className="form-control" disabled={this.state.module_disable}>
                                                    <option value="">Select module</option>
                                                    {this.state.module_list.map(function(module){
                                                        return (<option key={module.id} value={module.id}>{module.text}</option>);
                                                    })}
                                                </select>
                                            </div>
                                            <div className="col-md-2">
                                                <button type='button' className="btn btn-sm btn-danger" onClick={self.toggleSelect} >{self.state.selectAllPermission ? "Uncheck All" : "Check All"}</button>
                                            </div>
                                        </div>
                                        <br/>
                                        <div className="row">
                                            <div className="col-md-5">
                                                {(this.state.selectedModule == "") ? <h4>Please select module</h4> : null }
                                                {(this.state.permission_list.length == 0 && this.state.selectedModule != "") ?
                                                <h4>No permission found</h4>
                                                    :
                                                <div className="mt-checkbox-list">
                                                    {this.state.permission_list.map(function(permission,index){
                                                        return (<label key={permission.id} className="mt-checkbox mt-checkbox-outline"> {permission.permissionName}
                                                            <input type="checkbox" ref={permission.id} checked={self.state.checkState[permission.id] || ''} value={permission.id || ''} name={permission.id} onChange={self.setSelectedPermission}/>
                                                            <span/>
                                                        </label>)
                                                    })}
                                                </div>}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" onClick={this.onSubmit} className="btn btn-primary btn-fill" disabled={this.state.loading || this.state.selectedModule == ""}>Save changes <AppLoading loading={this.state.loading}/></button>
                        <button type="button" onClick={this.props.onHide} className="btn btn-default">Close</button>
                    </Modal.Footer>
                </form>
            </Modal>

        );
    },

});


window.GroupPermissionModal = GroupPermissionModal;