var UserRCenterComponent = React.createClass({
    getInitialState : function(){
        return {
            disabled : true,
            loading : false,
            userSelected : "",
            user_list : [],
            rcenter_list : []
        }
    },
    componentDidMount : function(){
        var self = this;

        $('#user').select2();
        $('#user').on('change',function(){
            self.setState({userSelected : $(this).val()},function(){
                self.loadUserRCenter();
            });
        });

        $('#rcenter').multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
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
            afterSelect: function(){
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function(){
                this.qs1.cache();
                this.qs2.cache();
            },
            cssClass: "fluid-size"
        });

        this.loadUser();
        this.loadRCenter();
    },
    loadUserRCenter : function(){
        var self = this;
        var rcenter = $('#rcenter');

        var user_rcenter = [];
        var url = Routing.generate("ajax_get_user_rcenter",{ userid : self.state.userSelected},true);

        $.ajax({
            type : "GET",
            url : url,
            beforeSend : function(){
                self.setState({disabled : true, loading : true});
            }
        })
        .done(function(res){
            res.map(function(row){
                user_rcenter.push(row.rc_code);
            });
            rcenter.multiSelect('deselect_all');
            rcenter.multiSelect('select', user_rcenter);

        })
        .fail(function(res){
            console.log(res);
        })
        .always(function(){
            self.setState({disabled : false, loading : false});
        });
    },
    loadRCenter : function(){
        var self = this;
        var rcenter = $('#rcenter');
        var url = Routing.generate("ajax_get_rcenter",{},true);

        $.ajax({
            type : "GET",
            url : url
        })
        .done(function(res){
            self.setState({rcenter_list : res},function(){
                rcenter.multiSelect('refresh');
            });
        })
        .fail(function(res){
            console.log(res);
        });
    },
    loadUser : function(){
        var self = this;
        var url = Routing.generate("ajax_get_user_list",{},true);

        $.ajax({
            type : "GET",
            url : url
        })
        .done(function(res){
            self.setState({disabled : false, user_list : res});
        })
        .fail(function(res){
            console.log(res);
        });
    },
    saveChanges : function(){
        var self = this;
        var user = self.state.userSelected;
        var rcenter = $('#rcenter').val();

        var url = Routing.generate("ajax_save_user_rcenter",{},true);

        $.ajax({
            type : "POST",
            url : url,
            data : ({
                userid : user,
                rcenter : rcenter
            }),
            beforeSend : function(){
                self.setState({disabled : true, loading : true});
            }
        })
        .done(function(res){
            $.notific8('zindex', 11500);
            $.notific8(res.message, {
                heading: 'System Message',
                color: "teal",
                life: 5000,
                verticalEdge: 'right',
                horizontalEdge: 'bottom',
            });
        })
        .fail(function(res){
            var message = "";
            switch(res.status){
                case 403:
                    message = res.responseJSON.message;
                    break;
                default:
                    message = "Menu update failed. Please try again.";

            }

            $.notific8('zindex', 11500);
            $.notific8(message, {
                heading: 'System Message',
                color: "ruby",
                life: 5000,
                verticalEdge: 'right',
                horizontalEdge: 'bottom',
            });
        })
        .always(function(){
            self.setState({disabled : false, loading : false});
        });

    },
    selectAll : function(){
        var rcenter = $('#rcenter');
        rcenter.multiSelect('select_all');
    },
    deselectAll : function(){
        var rcenter = $('#rcenter');
        rcenter.multiSelect('deselect_all');
    },
    render : function(){
        return (
            <div className="portlet light portlet-fit portlet-datatable bordered">
                <div className="portlet-title">
                    <div className="caption">
                        <i className="icon-grid font-grey-gallery"/>
                        <span className="caption-subject font-grey-gallery">User Responsibility Center</span>
                    </div>
                </div>
                <div className="portlet-body">
                    <div className="row">
                        <div className="col-md-4 margin-bottom">
                            <select name="user" id="user" className="form-control" disabled={this.state.disabled}>
                                <option value="">Select User...</option>
                                {this.state.user_list.map(function(user){
                                    return (<option key={user.id} value={user.id}>({user.username}) {user.name}</option>);
                                })}
                            </select>
                        </div>
                        {(this.state.loading) ?
                        <div className="col-md-1" style={{marginTop: "10px"}}>
                            <i className="fa fa-spinner fa-pulse fa-3x fa-fw" />
                        </div>
                            :
                            ""
                        }
                        <div className="col-md-2">
                            <button type="button" onClick={this.saveChanges} className="btn btn-primary" disabled={(this.state.userSelected == "" || this.state.disabled)}>Save Changes</button>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-md-12">
                            <div className="row">
                                <div className="col-md-10">
                                    <select multiple="multiple" id="rcenter" name="rcenter" >
                                        {this.state.rcenter_list.map(function(row,index){
                                            return (<option key={index} value={row.rc_code}>({row.rc_code}) {row.rc_project}</option>);
                                        })};
                                    </select>
                                </div>
                                <div className="col-md-2">
                                    <button type="button" onClick={this.selectAll} className="btn btn-primary btn-block margin-bottom">Select All</button>
                                    <button type="button" onClick={this.deselectAll} className="btn btn-danger btn-block">Deselect All</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        )
    }
});


setTimeout(function(){
    ReactDOM.render(
        <UserRCenterComponent />,
        document.getElementById('user_rcenter_content')
    );
},500);