var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var IndexingForm = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    fyCode : 2017,
                    idxDate : moment(new Date()).format('YYYY-MM-DDThh:mm'),
                    recvCode : '',
                    paxCount : 0,
                    dvNo : ''
                },
                errors : []
            },
            accIdxFiscalYears : []
        };
    },

    getDefaultProps : function(){
        return {
            create : true
        }
    },

    componentDidMount : function(){
        var self = this;

        self.loadAccIdxFiscalYears();
        self.initSelect2();

        if(!self.isEmpty(self.props.idxCode)){
            self.loadIndexHdr(self.props.idxCode);
        }
    },

    loadAccIdxFiscalYears : function(){
        var self = this;

        self.requestAccIdxFiscalYears = $.ajax({
            url : Routing.generate("indexing_ajax_get_acc_fiscal_years"),
            type : "GET"
        }).done(function(res){
            var form = self.state.form;

            res.map(function(item){
                if(item.status == 'A')
                    form.data.fyCode = item.fy_code;
            });

            self.setState({accIdxFiscalYears : res, form : form});
        });
    },

    loadIndexHdr : function(idxCode){
        var self = this;

        self.requestIndexHdr = $.ajax({
            url : Routing.generate("indexing_ajax_get_idx_hdr",{idxCode : idxCode}),
            type : "GET"
        }).done(function(res){
            var form = self.state.form;
            form.data = res;
            form.data.idxDate = moment(res.idxDate).format('YYYY-MM-DDThh:mm');
            self.setState({form : form});
            self.reinitSelect2(res);
        });
    },

    loadObrDetails : function(recvCode,dvNo){
        var self = this;
        var params = recvCode.split(" ");

        self.requestObr = $.ajax({
            url : Routing.generate('indexing_ajax_get_obr',{ recvCode : recvCode , dvNo : dvNo }),
            type : "GET"
        }).done(function(res){
            console.log(res);
            self.setState({details : res});
        });
    },

    initSelect2 : function(){
        var self = this;

        $("#idx-user-select2").select2({
            casesentitive : false,
            placeholder : "Enter Code or Name",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('indexing_ajax_select2_user'),
                data :  function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            return { id:item.user_code , text: item.user_name};
                        })
                    };
                },
            }
        });

        $("#idx-obr-select2").select2({
            casesentitive : false,
            placeholder : "Enter Code or Name",
            allowClear : true,
            delay : 1500,
            width : '100%',
            containerCssClass: ':all:',
            ajax : {
                url : Routing.generate('indexing_ajax_select2_obr'),
                data :  function (params) {
                    return {
                        searchText: params.term,
                        fyCode : self.state.form.data.fyCode,
                        recvCode : self.state.form.data.recvCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function(item){
                            var id =  item.recv_code + ' ' + item.dv_no;
                            var text = item.obr_desc + ' RECV NO : ' + item.recv_no + ' AMT : ' + self.numberWithCommas(item.total_amount);
                            return { id: id , text: text};
                        })
                    };
                },
            }
        });

        $("#idx-user-select2").on("change", function() {
            self.setUserCode($(this).val());
        });

        $("#idx-obr-select2").on("change", function() {
            self.setRecvCode($(this).val());
        });
    },

    reinitSelect2 : function(data){
        var self = this;

        self.requestIndexingUser = $.ajax({
            url : Routing.generate("indexing_user_ajax_get_user",{ userCode : data.userCode}),
            type : "GET"
        }).done(function(res){
            $("#idx-user-select2").empty()
                .append($("<option/>")
                    .val(res.userCode)
                    .text(res.userName))
                .trigger("change");
        });

        self.requestIndexingObr = $.ajax({
            url : Routing.generate("indexing_ajax_get_obr_by_recv_code",{ recvCode : data.recvCode}),
            type : "GET"
        }).done(function(res){
            var id =  res.recv_code + ' ' + res.dv_no;
            console.log(id);
            var text = res.obr_desc + ' RECV NO : ' + res.recv_no + ' AMT : ' + self.numberWithCommas(res.total_amount);

            $("#idx-obr-select2").empty()
                .append($("<option/>")
                    .val(id)
                    .text(text))
                .trigger("change");
        });
    },


    setUserCode : function(userCode){
        var form = this.state.form;
        form.data.userCode = userCode;
        this.setState({ form : form });
    },

    setRecvCode : function(code){
        var form = this.state.form;

        if(!this.isEmpty(code)){
            form.data.recvCode = code.split(" ")[0];
            form.data.dvNo = code.split(" ")[1];
            this.loadObrDetails(form.data.recvCode,form.data.dvNo);
        }else{
            form.data.recvCode = "";
            form.data.dvNo = "";
        }
        this.setState({form : form, details : null});
    },

    setFormProp : function(e){
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({form : form});
    },

    setErrors : function(errors){
        var form = this.state.form;
        form.errors = errors;

        this.setState({form : form});
    },

    getError : function(field){
        var errors = this.state.form.errors;
        for(var errorField in errors){
            if(errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState : function(field){
        return this.getError(field) != null ? 'error' : '';
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;


        self.requestPost = $.ajax({
            url : Routing.generate('indexing_ajax_patch_idx_hdr',{idxCode : self.props.idxCode}),
            type : "PATCH",
            data : (data)
        }).done(function(res){
            self.props.onHide();
            self.props.notify("Transaction has been completed.","teal");
        }).fail(function(err){
            self.props.notify("Form Validation Failed.","ruby");
            self.setErrors(err.responseJSON);
        });
    },

    getObrType : function(code){
        switch(code){
            case 'OTH' :
                return  'OTHERS';
            case 'PAY' :
                return 'PAYROLL';
            case 'TRA' :
                return 'TRAVEL';
            case 'PUR' :
                return 'PURCHASE';
            case 'REI' :
                return 'REIMBURSTMENT';
        }
    },


    numberWithCommas : function(x) {
        x = parseFloat(x).toFixed(2);
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    isEmpty : function(value){
        return value == null || value == '';
    },

    render : function(){
        var self = this;
        var details = self.state.details;
        return (
            <form id="carding-entry-form">

                <div className="col-md-12 bold font-red">
                    <span>IDX NO : </span> {this.state.form.data.idxNo}
                </div>
                <div className="clearfix"></div>
                <div className="col-md-6" style={{padding:0}} >
                    <div className="col-md-2" style={{paddingRight:0}}>
                        <FormGroup controlId="formFyCode" validationState={this.getValidationState('fyCode')}>
                            <ControlLabel >FY : </ControlLabel>
                            <select name="fyCode" className="form-control input-sm" value={this.state.form.data.fyCode} onChange={this.setFormProp}>
                                <option value=""> -- Select Year --</option>
                                {this.state.accIdxFiscalYears.map(function(item){
                                    return (<option key={"acc" + item.fy_code} value={item.fy_code}>{item.fy_code}</option>);
                                })}
                            </select>
                            <HelpBlock>{this.getError('fyCode')}</HelpBlock>
                        </FormGroup>
                    </div>

                    <div className="col-md-5">
                        <FormGroup controlId="formIdxDate" validationState={this.getValidationState('idxDate')}>
                            <ControlLabel> Date Indexed : </ControlLabel>
                            <FormControl type="datetime-local" bsClass="form-control input-sm" name="idxDate" value={this.state.form.data.idxDate} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('idxDate')}</HelpBlock>
                        </FormGroup>
                    </div>
                    <div className="col-md-5">
                        <FormGroup controlId="formUserCode" validationState={this.getValidationState('userCode')}>
                            <ControlLabel >Indexed By : </ControlLabel>
                            <select id="idx-user-select2" className="form-control input-sm">
                            </select>
                            <HelpBlock>{this.getError('userCode')}</HelpBlock>
                        </FormGroup>
                    </div>

                    <div className="clearfix"></div>

                    <div className="col-md-9">
                        <FormGroup controlId="formRecvCode" validationState={this.getValidationState('recvCode')}>
                            <ControlLabel >OBR : </ControlLabel>
                            <select id="idx-obr-select2" className="form-control input-sm">
                            </select>
                            <HelpBlock>{this.getError('recvCode')}</HelpBlock>
                        </FormGroup>
                    </div>
                    <div className="col-md-3">
                        <FormGroup controlId="formPaxCount" validationState={this.getValidationState('paxCount')}>
                            <ControlLabel> Pax Count : </ControlLabel>
                            <FormControl type="number" bsClass="form-control input-sm" name="paxCount" value={this.state.form.data.paxCount} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('paxCount')}</HelpBlock>
                        </FormGroup>
                    </div>
                    <div className="col-md-12">
                        <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                            <ControlLabel> Remarks : </ControlLabel>
                            <FormControl componentClass="textarea" rows="9" value={this.state.form.data.remarks} bsClass="form-control input-sm" name="remarks" onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('remarks')}</HelpBlock>
                        </FormGroup>
                    </div>
                </div>
                <div  className="col-md-6">
                    {this.state.details != null &&
                    (
                        <div className="bg-grey" style={{overflow:"auto",padding:"10px"}}>
                            <div className="col-md-12 font-red-sunglo bold" style={{marginBottom : "10px"}}> DV Information</div>
                            <div className="col-md-4">
                                <div className="bold">DV No :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{details.dv_no}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">DV Date :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{moment(details.dv_date).format('MMM DD, YYYY')}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">DV Name : </div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{details.dv_name}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">DV Particulars :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{details.remarks}</div>
                            </div>
                            <div className="clearfix"></div>

                            <div className="col-md-4" style={{marginTop:"8px"}}>
                                <div className="bold">Gross Amt :</div>
                            </div>
                            <div className="col-md-8 text-right" style={{marginTop:"8px"}}>
                                <div>{this.numberWithCommas(details.gross_amount)}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">Current Amt :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{this.numberWithCommas(details.current_amount)}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">NET Amt :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{this.numberWithCommas(details.net_amount)}</div>
                            </div>
                            <div className="clearfix"></div>

                            <div className="col-md-4"  style={{marginTop:"8px"}}>
                                <div className="bold">Audited By : </div>
                            </div>
                            <div className="col-md-8 text-right"  style={{marginTop:"8px"}}>
                                <div>{details.user_name}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4" >
                                <div className="bold">Audit Date :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{moment(details.audit_date).format('MMM DD, YYYY hh:mm A')}</div>
                            </div>

                            <div className="clearfix"></div>

                            <div className="col-md-12 bold font-red-sunglo" style={{marginTop : "20px", marginBottom : "10px" }}> ORB Information</div>

                            <div className="clearfix"></div>

                            <div className="col-md-4">
                                <div className="bold" >OBR Type :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{self.getObrType(details.obr_refnum_type)}</div>
                            </div>

                            <div className="clearfix"></div>

                            <div className="col-md-4">
                                <div className="bold" >OBR Desc :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{details.obr_desc}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">Remarks : </div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{details.obr_remarks}</div>
                            </div>
                            <div className="clearfix"></div>
                            <div className="col-md-4">
                                <div className="bold">Amount :</div>
                            </div>
                            <div className="col-md-8 text-right">
                                <div>{this.numberWithCommas(details.total_amount)}</div>
                            </div>
                        </div>
                    )
                    }
                    {
                        this.state.details == null &&
                        (
                            <div style={{height : "340px",padding:"10px"}} className="bg-grey">
                                <div className="bold font-red-sunglo">OBR Detail Preview : </div>
                            </div>
                        )
                    }
                </div>

                <div className="clearfix"></div>

                <div className="text-right col-md-12" style={{marginTop : "10px"}} >
                    <button type="button" className="btn  btn-default" style={{marginRight : "5px"}}  onClick={this.props.onHide}>Close</button>
                    <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                </div>
            </form>
        );
    }
});


window.IndexingForm = IndexingForm;