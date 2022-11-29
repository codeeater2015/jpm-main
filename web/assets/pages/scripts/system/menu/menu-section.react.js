var MenuSection = React.createClass({
    componentDidMount: function(){
        App.initSlimScroll('.scroller');
    },
    render : function(){
        return (
            <div className="portlet light bordered" >
                <div className="portlet-title">
                    <div className="caption">
                        <i className="icon-grid font-grey-gallery"/>
                        <span className="caption-subject font-grey-gallery">Menu Management</span>
                    </div>
                </div>
                <div className="portlet-body">
                    <MenuContainer/>
                </div>
            </div>
        );
    }
});

window.MenuSection = MenuSection;