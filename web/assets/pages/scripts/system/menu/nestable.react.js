(function(window){
    window.nestable = {};
    window.nestable.generateDropdownItem = generateDropdownItem;
    window.nestable.generateItem = generateItem;

    function generateDropdownItem (menu){

        var menuStr = "";
            menuStr += "<li class='dd-item dd3-item' data-menu_target='" + menu.menu_target + "' data-menu_label='" + menu.menu_label + "' data-menu_icon='" + menu.menu_icon + "' data-menu_link='" + menu.menu_link + "' data-menu_type='" + menu.menu_type + "'>";
            menuStr += "<div class='dd-handle dd3-handle'>Drag</div><div class='dd3-content'>" + menu.menu_icon + " " + menu.menu_label + " <button type='button' data-menu='" + menu + "' class='menu-remove-btn btn btn-link btn-xs pull-right'><i class='glyphicon glyphicon-remove font-grey-gallery'></i></button></div>";
            menuStr += "<ol class='dd-list'>";

            menu.children.map(function(menu_item){
                if ((menu_item.children) && menu_item.children.length > 0) {
                    menuStr += window.nestable.generateDropdownItem(menu_item);
                } else {
                    menuStr += window.nestable.generateItem(menu_item);
                }
            });

            menuStr += "</ol>";
            menuStr += "</li>";

        return menuStr;
    }

    function generateItem (menu){
        var menuStr = "";
        menuStr += "<li class='dd-item dd3-item' data-menu_target='" + menu.menu_target + "' data-menu_label='" + menu.menu_label + "' data-menu_icon='" + menu.menu_icon + "' data-menu_link='" + menu.menu_link + "' data-menu_type='" + menu.menu_type + "'>";
        menuStr += "<div class='dd-handle dd3-handle'>Drag</div><div class='dd3-content'>" + menu.menu_icon + " "+ menu.menu_label + "<button type='button' data-menu='" + menu + "' class='menu-remove-btn btn btn-link btn-xs pull-right'><i class='glyphicon glyphicon-remove font-grey-gallery' ></i></button></div>";
        menuStr += "</li>";
        return menuStr;
    }

})(window)

var MenuBody = React.createClass({

    componentWillReceiveProps : function(nextProps){
       var rootList = $(this.refs.root_list);
       var html = this.renderItems(nextProps.data);
       rootList.html(html);
    },

    renderItems : function(menus){
        var menuStr = "";
        for(var index=0; index < menus.length;index++){
            if ((menus[index].children) && menus[index].children.length > 0) {
                menuStr += window.nestable.generateDropdownItem(menus[index]);
            } else {
                menuStr += window.nestable.generateItem(menus[index]);
            }
        }

        return menuStr;
    },

    render: function() {
        return (
            <div className="panel-body" style={{padding:"20px"}}>
                {this.props.data.length  == 0 ? <h4>Selected group menu is empty.</h4> : null}
                <div className="dd" id={this.props.menu_id}>
                    <ol className="dd-list" ref="root_list">
                    </ol>
                </div>
            </div>
        );
    }

});

var MenuTitle = React.createClass({
    render: function() {
        return (
            <div className="panel-heading clearfix">
                <h4 className="panel-title pull-left"><strong>{this.props.title}</strong></h4>
            </div>
        );
    }
});

var FormControl  = ReactBootstrap.FormControl;
var FormGroup = ReactBootstrap.FormGroup;
var InputGroup = ReactBootstrap.InputGroup;
var ControlLabel  = ReactBootstrap.ControlLabel;
var Panel = ReactBootstrap.Panel;

var MenuContainer = React.createClass({
    
    getInitialState : function(){
        return {
            menuItems : [],
            groups:[],
            selectedGroup : "",
            loading : false
        };
    },

    componentDidMount : function(){
      this.getGroups();
    },

    componentWillUnmount : function(){
        this.requestItems.abort();
        this.requestGroups.abort();
    },

    getGroups : function(){
        var self = this;
        var url = Routing.generate("ajax_get_menu_group",{},true);
        self.requestGroups = $.ajax({
            url : url,
            method : "GET"
        }).done(function(res){
            self.setState({groups : res});
        }).fail(function(res){
            console.log(res)
        });
    },

    getMenuItems : function(){
        var self  = this;
        var url = Routing.generate("ajax_get_menu_by_group",{},true);
        self.requestItems = $.ajax({
            url : url + "?group=" + self.state.selectedGroup,
            method : "GET",
            beforeSend: function(){
                self.setState({loading : true});
            }
        }).done(function(res){
            self.setState({menuItems : res},function(){
                this.reinitNestable();
            });
        }).fail(function(res){
            console.log(res);
        }).always(function(){
            self.setState({loading : false},function(){
                this.reinitNestable();
            });
        });
    },

    setGroup : function(e){
        if(e.target.value == null || e.target.value == ""){
            this.setState({selectedGroup : e.target.value});
            return;
        }
        this.setState({selectedGroup : e.target.value},function(){
            this.getMenuItems();
        });
    },

    reinitNestable : function(){
        var self = this;
        var list = $('.dd').nestable().data('nestable');
        var menu_remove_btn = $('.menu-remove-btn');
        var menu_edit_btn = $('.menu-edit-btn');
        $.each(list.el.find(list.options.itemNodeName), function(k, el) {
            if ($(el).children(list.options.listNodeName).length) {
                $(el).children('button').remove();
            }
        });

        // remove delegated event handlers
        list.el.off('click', 'button');
        menu_remove_btn.off('click');
        menu_edit_btn.off('click');

        //handle remove menu item
        menu_remove_btn.on("click",function(e){
            $(e.target).closest('.dd-item').remove();
            self.setState({menuItems : $('.dd').nestable('serialize')});
            self.reinitNestable();
        });

        menu_edit_btn.on("click",function(e){
            console.log(e);
        });

        var hasTouch = 'ontouchstart' in document;

        if (hasTouch) {
            list.el.off('touchstart');
            list.w.off('touchmove');
            list.w.off('touchend');
            list.w.off('touchcancel');
        }

        list.el.off('mousedown');
        list.w.off('mousemove');
        list.w.off('mouseup');

        // call init again
        list.init();
    },

    add : function(menu){
        var self = this;
        var menuItems = self.state.menuItems;

        menu.map(function(item){
            menuItems.push(item);
        });
        self.setState({menuItems : menuItems});

    },

    save : function(){
        var self = this;
        var data = {
            menu : $('.dd').nestable('serialize'),
            group_id : self.state.selectedGroup
        };
        var url = Routing.generate("ajax_menu_save",{},true);
        console.log(data);

        $.ajax({
            url : url,
            type:"POST",
            dataType : "json",
            data: JSON.stringify(data),
            beforeSend: function(){
                $.enable_loading('processing');
            }
        }).done(function(res){
            self.notify(res.message,"teal");
        }).fail(function(res){
            console.log(res);
            var message = "";
            switch(res.status){
                case 403:
                    message = res.responseJSON.message;
                    break;
                default:
                    message = "Menu update failed. Please try again.";

            }
            self.notify(message,"ruby");
        }).always(function(){
            $.disable_loading('processing');
        });
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
    },
   
    render: function() {
        return (              
            <div className="row">
                <div className="col-md-4">
                    <div className="portlet light bordered">
                        <div className="portlet-title">
                            <div className="caption">
                                <span className="caption-subject font-dark bold uppercase">System Modules</span>
                            </div>
                            <div className="tools">
                                <a href="" className="collapse" data-original-title="" title=""> </a>
                            </div>
                        </div>
                        <div className="portlet-body form">
                            <ModuleList addHandler={this.add} selectedGroup={this.state.selectedGroup}/>
                        </div>
                    </div>
                    <div className="portlet light bordered">
                        <div className="portlet-title">
                            <div className="caption">
                                <span className="caption-subject font-dark bold uppercase">Custom Menu</span>
                            </div>
                            <div className="tools">
                                <a href="" className="collapse" data-original-title="" title=""> </a>
                            </div>
                        </div>
                        <div className="portlet-body form">
                            <ItemForm addHandler={this.add} selectedGroup={this.state.selectedGroup}/>
                        </div>
                    </div>
                </div>
                <div className="col-md-8">
                    <div className="row">
                        <div className="col-md-6">
                            <FormGroup>
                                <ControlLabel>Select Group</ControlLabel>
                                <InputGroup>
                                    <FormControl componentClass="select" name="menu-item" onChange={this.setGroup}>
                                        <option value="">Select...</option>
                                        {this.state.groups.map(function(group){
                                            return (<option key={group.id} value={group.id}>{group.groupName}</option>);
                                        })}
                                    </FormControl>
                                    <InputGroup.Button>
                                        <button type="button" className="btn btn-primary" onClick={this.save} disabled={this.state.selectedGroup == null || this.state.selectedGroup == ""}>
                                            Save Menu
                                        </button>
                                    </InputGroup.Button>
                                </InputGroup>
                            </FormGroup>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <div className="panel panel-default">
                                <MenuTitle title="Main Structure"/>
                                {this.state.loading ? <div style={{textAlign : "center"}}><h3>Loading...</h3></div> : null}
                                {(this.state.selectedGroup != null && this.state.selectedGroup != "") ?
                                    <MenuBody menu_id="main_menu" data-title="Main Menu" data={this.state.menuItems}/>
                                    : <div className="panel-body" style={{padding:"20px"}}><h4>Please select a group</h4></div>
                                }
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        );
    }
});

window.MenuContainer = MenuContainer;