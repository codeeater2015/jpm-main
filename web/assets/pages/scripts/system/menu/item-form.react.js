var FormGroup = ReactBootstrap.FormGroup;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;
var HelpBlock = ReactBootstrap.HelpBlock;

var ItemForm = React.createClass({
	getInitialState : function(){
        return {
            form : {
                errors : {},
                data :{
                    menu_link : "",
                    menu_label : "",
                    menu_target: "",
                    menu_type: "Custom",
                    menu_icon: '<i class="fa fa-question"></i>'
                }
            },
        };  
    },
    
    componentDidMount: function(){
        var self = this;
        var iconpicker = $("#form-icon-picker");
        iconpicker.iconpicker({
            arrowClass: 'btn-danger',
            arrowPrevIconClass: 'glyphicon glyphicon-chevron-left',
            arrowNextIconClass: 'glyphicon glyphicon-chevron-right',
            cols: 6,
            footer: true,
            header: true,
            icon: '',
            iconset: 'fontawesome',
            labelHeader: '{0} of {1} pages',
            labelFooter: '{0} - {1} of {2} icons',
            placement: 'bottom',
            rows: 5,
            search: true,
            searchText: 'Search',
            selectedClass: 'btn-success'
        });
        iconpicker.on('change', function(e) {
            self.setMenuIcon(e);
        });

    },

    componentWillUnmount : function(){
        this.clear();
    },

    render : function(){
        return (
            <div>
                <div className="form-body">
                    <form>
                        <div className="clearfix"></div>

                        <FormGroup controlId="formLabel" validationState={this.getValidationState('menu_label')}>
                            <ControlLabel>Menu Title</ControlLabel>
                            <FormControl type="text" value={this.state.form.data.menu_label} bsClass="form-control input-xs"  name="menu_label" onChange={this.setMenuLabel}/>
                            <HelpBlock>{this.getError('menu_label')}</HelpBlock>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <FormGroup controlId="formLink" validationState={this.getValidationState('menu_link')}>
                            <ControlLabel>Route</ControlLabel>
                            <FormControl type="text" value={this.state.form.data.menu_link} bsClass="form-control input-xs"  name="menu_link" onChange={this.setMenuLink}/>
                            <HelpBlock>{this.getError('menu_link')}</HelpBlock>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <FormGroup controlId="formLabel" validationState={this.getValidationState('menu_target')}>
                            <ControlLabel>Target</ControlLabel>
                            <FormControl componentClass="select" value={this.state.form.data.menu_target}  bsClass="form-control input-xs" name="menu_target" onChange={this.setMenuTarget}>
                                <option value="">Select Target</option>
                                <option value="none">None</option>
                                <option value="_blank">Blank</option>
                                <option value="_parent">Parent</option>
                                <option value="_self">Self</option>
                                <option value="_top">Top</option>
                            </FormControl>
                            <HelpBlock>{this.getError('menu_target')}</HelpBlock>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <FormGroup controlId="formIcon" validationState={this.getValidationState('menu_icon')}>
                            <ControlLabel>Icon</ControlLabel>
                            <div id="form-icon-picker" data-search="true" data-search-text="Search..." role="iconpicker"></div>
                            <HelpBlock>{this.getError('menu_icon')}</HelpBlock>
                        </FormGroup>
                    </form>
                </div>
                <div className="form-actions right">
                    <button type="button" className="btn btn-sm btn-danger" style={{ marginRight:"7px" }} onClick={this.clear}>Clear</button>
                    <button type="button" className="btn btn-sm btn-success" onClick={this.submit}>Add custom menu</button>
                </div>
            </div>
        );
    },

    submit : function(){
        var data = this.state.form.data;
        var errors = [];
        data.children = [];

        if(this.props.selectedGroup == null){
            bootbox.alert("Please select a group");
            return;
        }

        if(data.menu_label == "" || data.menu_label == null)
            errors.push({field : "menu_label" , message : "This value cannot be blank"});

        if(data.menu_target == "" || data.menu_target == null)
            errors.push({field : "menu_target", message : "This value cannot be blank"});

       
        if(errors.length > 0){
            this.setErrors(errors);
        }else {
            console.log(data)
            this.props.addHandler([data]);
            this.clear();
        }
    },

    clear : function(){

        this.setState(this.getInitialState,function(){
            $("#form-icon-picker").iconpicker('setIcon', 'empty');
        });
    },


    setMenuLabel : function(e){
        var form = this.state.form;
        form.data.menu_label = e.target.value;
        this.setState({form : form});
    },

    setMenuIcon : function(e){
        var form = this.state.form;
        form.data.menu_icon = '<i class="fa ' + e.icon + '" ></i>';
        this.setState({form : form});
    },

    setMenuTarget : function(e){
        var form = this.state.form;
        form.data.menu_target = e.target.value;
        this.setState({form : form});
    },

    setMenuLink : function(e){
        var form = this.state.form;
        form.data.menu_link = e.target.value;
        this.setState({form : form});
    },

    setErrors : function(errors){
        var form = this.state.form;
        form.errors = errors;

        this.setState({form : form});
    },

    getError : function(field){
        var errors = this.state.form.errors;
        for(var index=0;index < errors.length;index++){
           
            if(errors[index].field == field)
                    return errors[index].message;
        }
        return null;
    },

    getValidationState : function(field){

        if(this.getError(field))
            return "error";

        return null;
    } 
});


window.ItemForm = ItemForm;