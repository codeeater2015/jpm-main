var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var VoterNetworkReportHierarchyList = React.createClass({

    getInitialState : function(){
        return {
            data : [],
            barangay : {
                total_voter : 0 ,
                total_recruits : 0,
                percentage : 0,
                max_deep : 0
            },
            loading : false
        }
    },
   
    render : function(){
        var self = this;
        var excelUrl =  Routing.generate("ajax_export_network_report_nodes",{
            provinceCode : self.props.provinceCode,
            municipalityNo : self.props.municipalityNo,
            brgyNo : self.props.brgyNo,
            maxDeep : self.state.barangay.max_deep
        });

        
        return (
            <div>
                <div className="col-md-9" style={{padding:0,marginBottom:"10px"}}>
                    <div style={{marginBottom : "5px"}}>
                        <span className="bold">City/Municipality : <span className="font-red-sunglo">{this.state.barangay.municipality}</span></span>
                        <span className="bold" style={{marginLeft : "10px"}}>Barangay : <span className="font-red-sunglo">{this.state.barangay.name}</span> </span>
                    </div>
                    <div className="bold">
                        Registered : <span className="font-red-sunglo" style={{marginRight:"5px"}}>{this.state.barangay.total_voter}</span>
                        Recruited : <span className="font-red-sunglo" style={{marginRight:"5px"}}>{this.state.barangay.total_recruits}</span> 
                        Percentage : <span className="font-red-sunglo" style={{marginRight:"5px"}}>{parseFloat(this.state.barangay.percentage).toFixed(2)} %</span>
                        Max Deep : <span className="font-red-sunglo">{ this.isEmpty(this.state.barangay.max_deep) ? "0" : this.state.barangay.max_deep } Levels</span>
                    </div>
                </div>
                <div className="col-md-3 text-right" style={{padding: 0}}>
                    <button className="btn btn-sm red-sunglo" onClick={this.downloadPdf}><i className="fa fa-file-pdf"></i> PDF</button>
                    <a className="btn btn-sm green-jungle" href={excelUrl} style={{marginLeft:"10px"}}><i className="fa fa-file-excel"></i> EXCEL</a>
                </div>
                <div className="clearfix"></div>
                <table id="voter_network_table" className="bg-default table table-striped table-bordered table-condensed" style={{fontSize:"12px"}}>
                    <thead className="bg-blue-dark font-white">
                        <tr>
                            <th className="text-center" style={{width : "23%", padding:"10px"}}>PARENT</th>
                            <th className="text-center" style={{width : "23%", padding:"10px"}}>MEMBER 1 </th>
                            <th className="text-center" style={{width : "23%", padding:"10px"}}>MEMBER 2 </th>
                            <th className="text-center" style={{width : "23%", padding:"10px"}}>MEMBER 3 </th>
                            <th className="text-center" style={{fontSize:"10px", width : "50px"}}>NO. OF MEMBERS</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.data.map(function(item,index){
                            return (
                                <tr>
                                    <td>{item[0]}</td>
                                    <td>{item[1]}</td>
                                    <td>{item[2]}</td>
                                    <td>{item[3]}</td>
                                    <td className="text-center">{(Number.isInteger(item[4]) && item[4] != 0) ?  item[4] : '- - -'}</td>
                                </tr>
                            );
                        })}

                        {this.state.data.length <=  0 && !this.state.loading &&
                            (
                            <tr>
                                <td colSpan="5" className="text-center">No data has found...</td>
                            </tr>
                            )
                        }

                        
                        {this.state.loading && 
                            (
                            <tr>
                                <td colSpan="5" className="text-center">Requesting data from the server. This may take a while...</td>
                            </tr>
                            )
                        }
                    </tbody>
                </table>
            </div>
        );
    },

    componentDidMount : function(){
        this.loadBarangay(this.props.provinceCode,this.props.municipalityNo, this.props.brgyNo);
    },

    getView : function(node){
        var self = this;
      
        return (
        <li>
            { node.node_label}
            <ul>
                {node.children.map(function(item){
                    return self.getView(item);
                })}
            </ul>
        </li>
        );
    },

    apply : function(){
        this.loadData();
        this.loadBarangay();
    },

    loadData : function(provinceCode, municipalityNo, brgyNo, maxDeep){
        var self = this;

        self.requestData = $.ajax({
            url : Routing.generate("ajax_get_network_report_nodes",{
                provinceCode : provinceCode,
                municipalityNo : municipalityNo,
                brgyNo : brgyNo,
                maxDeep : maxDeep
            }),
            type : "GET"
        }).done(function(res){
            self.setState({ data : res});
        }).always(function(){
            self.setState({ loading : false });
        });

        self.setState({loading : true})
    },

    downloadPdf : function(){
        var columns = [];
        var doc = new jsPDF('l','mm',[203.2,330.2]);
        var barangay = this.state.barangay;
        var data = this.state.data;

        var totalPagesExp = "{total_pages_count_string}";
        var pageContent = function (data) {
            // FOOTER
            var str = "Page " + data.pageCount;
            // Total page number plugin only available in jspdf v1.0+
            if (typeof doc.putTotalPages === 'function') {
                str = str + " of " + totalPagesExp;
            }
            doc.setFontSize(10);
            doc.text(str, data.settings.margin.left, doc.internal.pageSize.height - 5);
            doc.text("P4Change Generated At " + moment(new Date()).format("dddd, MMMM Do YYYY, h:mm:ss a"), 225, doc.internal.pageSize.height - 5);
        };
        
        var lastColumn = parseInt(this.state.barangay.max_deep);
        var fontSize = 0;

        if(lastColumn > 3){
            fontSize = 6.8;
            columns.push("Parent");

            for(var i = 1;i < parseInt(this.state.barangay.max_deep);i++){
                columns.push('Member ' + i);
            }

            columns.push("Members");
                
            data.map(function(item){
                item[lastColumn] = (parseInt(item[lastColumn]) != 0 && Number.isInteger(item[lastColumn])) ? item[lastColumn] : ""; 
            });

        }else{
            fontSize = 10;
            columns.push("Parent");
            columns.push("Member 1");
            columns.push("Member 2");
            columns.push("Member 3");
            columns.push("Members");

            data.map(function(item){
                item[4] = (parseInt(item[4]) != 0 && Number.isInteger(item[4])) ? item[4] : ""; 
            });

            lastColumn = 4;
        }     

        doc.setFontSize(12);

        doc.text("City/Municipality : " + barangay.municipality, 8,20);
        doc.text("Registered Voter : " + barangay.total_voter, 120,20);
        doc.text("Percentage : " + parseFloat(barangay.percentage).toFixed(2) + " %", 190,20);
        doc.text("Barangay : " + barangay.name, 8,26);
        doc.text("Recruited Voter : " + barangay.total_recruits , 120,26);

        var columnStyles  = {};
        columnStyles[lastColumn] = {valign : "middle", fontStyle : "bold", halign : "center"};
       
        doc.autoTable(columns, data,{
            startY: 30,
            showHeader : "firstPage",
            theme: 'grid',
            margin : {top:10 , bottom : 10, left : 7, right : 7},
            styles : {
                columnWidth: 'auto',
                overflow: "visible",
                fontSize : fontSize,
                font: "Arial",
                cellPadding : 1
            },   
            columnStyles : columnStyles,
            addPageContent : pageContent
        });

        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }

        doc.save(barangay.name + ' ' + moment(new Date()).format() + '.pdf');
    },

    loadBarangay : function(provinceCode,municipalityNo,brgyNo){
        var self = this;
        self.requestItems = $.ajax({
            url : Routing.generate("ajax_get_baranagy_full",{
                provinceCode : provinceCode,
                municipalityNo : municipalityNo,
                brgyNo : brgyNo
            }),
            type : "GET"
        }).done(function(res){
            self.loadData(provinceCode, municipalityNo, brgyNo,res.max_deep);
            self.setState({barangay : res});
        });
    },

    isEmpty : function(value){
        return value == null || value == "" || value == "undefined";
    }
});

window.VoterNetworkReportHierarchyList = VoterNetworkReportHierarchyList;