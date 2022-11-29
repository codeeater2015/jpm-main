
var LoadingMd = React.createClass({
	render : function(){
		return (<div className="text-center" style={{margin: "50px"}}><img src={appUrlConfig.baseUrl + '/assets/global/img/loading/loading-md.gif' }/></div>);
	}
});

var LoadingSm = React.createClass({
	render : function(){
		return (<div className="text-center" style={{margin: "50px"}}><img src={appUrlConfig.baseUrl + '/assets/global/img/loading/loading-sm.gif' }/></div>);
	}
});


var LoadingLg = React.createClass({
	render : function(){
		return (<div className="text-center" style={{margin: "50px"}}><img src={appUrlConfig.baseUrl + '/assets/global/img/loading/loading-lg.gif' }/></div>);
	}
});


var LoadingXl = React.createClass({
	render : function(){
		return (<div className="text-center" style={{margin: "50px"}}><img src={appUrlConfig.baseUrl + '/assets/global/img/loading/loading-xl.gif' }/></div>);
	}
});

window.LoadingSm = LoadingSm;
window.LoadingMd = LoadingMd;
window.LoadingLg = LoadingLg;
window.LoadingXl = LoadingXl;