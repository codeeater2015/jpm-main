var gulp = require('gulp');
var pump = require('pump');
var path = require('path');
var react = require('gulp-react');
var concat = require('gulp-concat-util');
var rename = require('gulp-rename');
var uglifyjs = require('gulp-uglify');
var cleanCSS = require('gulp-clean-css');
var bust = require('gulp-buster');
var uglifyjs_latest = require('uglify-js');
var minifier = require('gulp-uglify/minifier');

var webroot = path.join(__dirname,'/../../web/');
var jsPath = path.join(webroot, "compiled/js");
var cssPath = path.join(webroot, "compiled/css");

var pdsPath = webroot + 'assets/pages/scripts/pds/';
var joPath = webroot + 'assets/pages/scripts/job_order/';
var obrtrackingPath = webroot + 'assets/pages/scripts/obr_tracking/';
var myPath = webroot + 'assets/pages/scripts/';

var systemPath = webroot + 'assets/pages/scripts/system/';
var userPath = systemPath + 'user/';
var groupPath = systemPath + 'group/';
var menuPath = systemPath + 'menu/';
var userrcenterPath = systemPath + 'user_rcenter/';


gulp.task('compile-main-css',function(cb){
    pump([
        gulp.src([
            webroot + 'assets/fonts/fonts.css',
            webroot + 'assets/global/plugins/font-awesome/css/font-awesome.min.css',
            webroot + 'assets/global/plugins/simple-line-icons/simple-line-icons.min.css',
            webroot + 'assets/global/plugins/bootstrap/css/bootstrap.min.css',
            webroot + 'assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css',

            webroot + 'assets/global/plugins/icheck/skins/square/_all.css',
            webroot + 'assets/global/plugins/animate/animate.css',
            webroot + 'assets/global/plugins/jquery-multi-select/css/multi-select.css',
            webroot + 'assets/global/plugins/datatables/datatables.min.css',
            webroot + 'assets/global/plugins/datatables/dataTables.bootstrap.css',
            webroot + 'assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css',
            webroot + 'assets/global/plugins/bootstrap-daterangepicker/daterangepicker.css',
            webroot + 'assets/global/plugins/jquery-nestable/jquery.nestable.css',
            webroot + 'assets/global/plugins/select2/css/select2.min.css',
            webroot + 'assets/global/plugins/select2/css/select2-bootstrap.min.css',
            webroot + 'assets/global/plugins/jquery-notific8/jquery.notific8.min.css',
            webroot + 'assets/global/plugins/sweetalert2/sweetalert2.css',
            webroot + 'assets/global/plugins/fancybox/source/jquery.fancybox.css',
            webroot + 'assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css',
            webroot + 'assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css',
            webroot + 'assets/global/plugins/jstree/dist/themes/default/style.min.css',
            webroot + 'assets/global/plugins/dropzone/dropzone.css',
            webroot + 'assets/global/plugins/unitegallery/css/unite-gallery.css',
            webroot + 'assets/global/plugins/unitegallery/themes/default/ug-theme-default.css',
            
            webroot + 'assets/global/css/components-rounded.min.css',
            webroot + 'assets/global/css/plugins.css',

            webroot + 'assets/pages/css/inbox.min.css',

            webroot + 'assets/layout/css/layout.min.css',
            webroot + 'assets/layout/css/themes/blue.min.css',
            webroot + 'assets/layout/css/custom.css'
        ]),
        cleanCSS({
            compatibility: 'ie8',
            specialComments : 0,
            rebaseTo: '../web/compiled/css'
        }),
        concat('app.css'),
        rename({suffix: '.min'}),
        gulp.dest(cssPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-main-js',function(cb){
    var options = {
        preserveComments: 'license'
    };

    pump([
        gulp.src([
            webroot + 'assets/global/plugins/jquery.min.js',
            webroot + 'assets/global/plugins/bootstrap/js/bootstrap.min.js',
            webroot + 'assets/global/plugins/js.cookie.min.js',
            webroot + 'assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js',
            webroot + 'assets/global/plugins/jquery.blockui.min.js',
            webroot + 'assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js',
            webroot + 'assets/global/plugins/moment.min.js',
            webroot + 'assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js',
            webroot + 'assets/global/plugins/jquery.number.min.js',
            webroot + 'assets/global/plugins/icheck/icheck.min.js',
            webroot + 'assets/global/plugins/jquery.quicksearch.js',
            webroot + 'assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js',
            webroot + 'assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js',

            webroot + 'assets/global/plugins/bootbox/bootbox.min.js',
            webroot + 'assets/global/scripts/datatable.js',
            webroot + 'assets/global/plugins/datatables/datatables.min.js',
            webroot + 'assets/global/plugins/datatables/dataTables.bootstrap.js',
            webroot + 'assets/global/plugins/datatables/ellipsis.js',
            webroot + 'assets/global/plugins/CellEdit/js/dataTables.cellEdit.js',
            webroot + 'assets/global/plugins/bootstrap-contextmenu/bootstrap-contextmenu.js',
            webroot + 'assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js',
            webroot + 'assets/global/plugins/bootstrap-growl/jquery.bootstrap-growl.min.js',
            webroot + 'assets/global/plugins/noty/packaged/jquery.noty.packaged.min.js',
            webroot + 'assets/global/plugins/jquery-notific8/jquery.notific8.min.js',
            webroot + 'assets/global/plugins/jquery-nestable/jquery.nestable.js',
            webroot + 'assets/global/plugins/select2/js/select2.full.min.js',
            webroot + 'assets/global/plugins/sweetalert2/sweetalert2.js',
            webroot + 'assets/global/plugins/fancybox/source/jquery.fancybox.pack.js',
            webroot + 'assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js',
            webroot + 'assets/global/plugins/webcamjs-master/webcam.min.js',
            webroot + 'assets/global/plugins/jstree/dist/jstree.min.js',
            webroot + 'assets/global/plugins/js-pdf/examples/libs/jspdf.debug.js',
            webroot + 'assets/global/plugins/js-pdf/dist/jspdf.plugin.autotable.js',
            webroot + 'assets/global/plugins/unitegallery/js/unitegallery.min.js',
            webroot + 'assets/global/plugins/unitegallery/themes/default/ug-theme-default.js',
            webroot + 'assets/global/plugins/dropzone/dropzone.js',
            webroot + 'assets/global/scripts/app.js',
            webroot + 'assets/layout/scripts/layout.js',
            webroot + 'assets/pages/scripts/pgpis.js'
        ]),
        concat('app.js'),
        rename({suffix: '.min'}),
        uglifyjs(options),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-public-main-css',function(cb){
    pump([
        gulp.src([
            webroot + 'assets/fonts/fonts.css',
            webroot + 'assets/global/plugins/font-awesome/css/font-awesome.min.css',
            webroot + 'assets/global/plugins/simple-line-icons/simple-line-icons.min.css',
            webroot + 'assets/global/plugins/bootstrap/css/bootstrap.min.css',
            webroot + 'assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css',

            webroot + 'assets/global/plugins/icheck/skins/square/_all.css',
            webroot + 'assets/global/plugins/animate/animate.css',
            webroot + 'assets/global/plugins/jquery-multi-select/css/multi-select.css',
            webroot + 'assets/global/plugins/datatables/datatables.min.css',
            webroot + 'assets/global/plugins/datatables/dataTables.bootstrap.css',
            webroot + 'assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css',
            webroot + 'assets/global/plugins/bootstrap-daterangepicker/daterangepicker.css',
            webroot + 'assets/global/plugins/jquery-nestable/jquery.nestable.css',
            webroot + 'assets/global/plugins/select2/css/select2.min.css',
            webroot + 'assets/global/plugins/select2/css/select2-bootstrap.min.css',
            webroot + 'assets/global/plugins/jquery-notific8/jquery.notific8.min.css',
            webroot + 'assets/global/plugins/sweetalert2/sweetalert2.css',
            webroot + 'assets/global/plugins/fancybox/source/jquery.fancybox.css',
            webroot + 'assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css',
            webroot + 'assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css',

            webroot + 'assets/global/css/components-rounded.min.css',
            webroot + 'assets/global/css/plugins.css',

            webroot + 'assets/layouts/layout_1/css/layout.min.css',
            webroot + 'assets/layouts/layout_1/css/custom.css'
        ]),
        cleanCSS({
            compatibility: 'ie8',
            specialComments : 0,
            rebaseTo: '../web/compiled/css'
        }),
        concat('public_app.css'),
        rename({suffix: '.min'}),
        gulp.dest(cssPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-public-main-js',function(cb){
    var options = {
        preserveComments: 'license'
    };

    pump([
        gulp.src([
            webroot + 'assets/global/plugins/jquery.min.js',
            webroot + 'assets/global/plugins/bootstrap/js/bootstrap.min.js',
            webroot + 'assets/global/plugins/js.cookie.min.js',
            webroot + 'assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js',
            webroot + 'assets/global/plugins/jquery.blockui.min.js',
            webroot + 'assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js',
            webroot + 'assets/global/plugins/moment.min.js',
            webroot + 'assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js',
            webroot + 'assets/global/plugins/jquery.number.min.js',
            webroot + 'assets/global/plugins/icheck/icheck.min.js',
            webroot + 'assets/global/plugins/jquery.quicksearch.js',
            webroot + 'assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js',
            webroot + 'assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js',

            webroot + 'assets/global/plugins/bootbox/bootbox.min.js',
            webroot + 'assets/global/scripts/datatable.js',
            webroot + 'assets/global/plugins/datatables/datatables.min.js',
            webroot + 'assets/global/plugins/datatables/dataTables.bootstrap.js',
            webroot + 'assets/global/plugins/datatables/ellipsis.js',
            webroot + 'assets/global/plugins/CellEdit/js/dataTables.cellEdit.js',
            webroot + 'assets/global/plugins/bootstrap-contextmenu/bootstrap-contextmenu.js',
            webroot + 'assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js',
            webroot + 'assets/global/plugins/bootstrap-growl/jquery.bootstrap-growl.min.js',
            webroot + 'assets/global/plugins/noty/packaged/jquery.noty.packaged.min.js',
            webroot + 'assets/global/plugins/jquery-notific8/jquery.notific8.min.js',
            webroot + 'assets/global/plugins/jquery-nestable/jquery.nestable.js',
            webroot + 'assets/global/plugins/select2/js/select2.full.min.js',
            webroot + 'assets/global/plugins/sweetalert2/sweetalert2.js',
            webroot + 'assets/global/plugins/fancybox/source/jquery.fancybox.pack.js',
            webroot + 'assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js',
            webroot + 'assets/global/plugins/jquery-idle-timer/idle-timer.min.js',
            
            webroot + 'assets/global/scripts/app.js',
            webroot + 'assets/layouts/layout_1/scripts/layout.js',
            webroot + 'assets/pages/scripts/pgpis.js'
        ]),
        concat('public_app.js'),
        rename({suffix: '.min'}),
        uglifyjs(options),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-amcharts',function(cb){
    pump([
        gulp.src([
            webroot + 'assets/global/plugins/amcharts/amcharts/amcharts.js',
            webroot + 'assets/global/plugins/amcharts/amcharts/pie.js',
            webroot + 'assets/global/plugins/amcharts/amcharts/serial.js',
            webroot + 'assets/global/plugins/amcharts/amcharts/themes/light.js'
        ]),
        concat('amcharts.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-login-css',function(cb){
    pump([
        gulp.src([
            webroot + 'assets/fonts/fonts.css',
            webroot + 'assets/global/plugins/font-awesome/css/font-awesome.min.css',
            webroot + 'assets/global/plugins/simple-line-icons/simple-line-icons.min.css',
            webroot + 'assets/global/plugins/bootstrap/css/bootstrap.min.css',
            webroot + 'assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css',

            webroot + 'assets/global/css/components.min.css',
            webroot + 'assets/global/css/plugins.min.css',
            webroot + 'assets/pages/css/login.css'
        ]),
        cleanCSS({
            compatibility: 'ie8',
            specialComments : 0,
            rebaseTo: '../web/compiled/css/'
        }),
        concat('login.css'),
        rename({suffix: '.min'}),
        gulp.dest(cssPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-login-js',function(cb){
    pump([
        gulp.src([
            webroot + 'assets/global/plugins/jquery.min.js',
            webroot + 'assets/global/plugins/bootstrap/js/bootstrap.min.js',
            webroot + 'assets/global/plugins/backstretch/jquery.backstretch.min.js',
            webroot + 'assets/global/scripts/app.js'
        ]),
        concat('login.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});


gulp.task("compile-system-user",function(cb){
    pump([
        gulp.src([
            userPath + '/user-create-new-user.js',
            userPath + '/user-edit-user.js',
            userPath + '/user-change-user-password.js',
            userPath + '/user.js'
        ]),
        concat('user.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task("compile-system-group",function(cb){
    pump([
        gulp.src([
            groupPath + 'group-loading.react.js',
            groupPath + 'group-alert.react.js',
            groupPath + 'group-create-modal.react.js',
            groupPath + 'group-edit-modal.react.js',
            groupPath + 'group-permission-modal.react.js',
            groupPath + 'group-datatable.react.js',
            groupPath + 'group-section.react.js',
            groupPath + 'group-index.react.js'
        ]),
        concat('group.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task("compile-system-menu",function(cb){
    pump([
        gulp.src([
            menuPath + 'item-form.react.js',
            menuPath + 'module-list.react.js',
            menuPath + 'nestable.react.js',
            menuPath + 'menu-section.react.js',
            menuPath + 'menu-index.react.js'
        ]),
        concat('menu.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task("compile-system-user-rcenter",function(cb){
    pump([
        gulp.src([
            userrcenterPath + 'user_rcenter.js'
        ]),
        concat('user_rcenter.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task("compile-loading-react",function(cb){
    pump([
        gulp.src([
            webroot + 'assets/pages/scripts/loading.react.js'
        ]),
        concat('loading.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-joborder',function(cb){
    pump([
        gulp.src([
            joPath + 'jquery-datatables-react.js',
            joPath + 'yeardate-filter-react.js',
            joPath + 'request-list-react.js',
            joPath + 'updatable-request-list-react.js',
            joPath + 'job-order-create-react.js',
            joPath + 'job-order-view-react.js',
            joPath + 'job-order-process-react.js',
            joPath + 'job-order-report-option-react.js',
            joPath + 'maintenance-jobtype-react.js',
            joPath + 'job-orders-react.js',
            joPath + 'index-react.js'
        ]),
        concat('joborder.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-pds',function(cb){
    pump([
        gulp.src([
            pdsPath + 'personal/personal-form.react.js',
            pdsPath + 'personal/personal-section.react.js',
            pdsPath + 'family/family-section.react.js',
            pdsPath + 'family/family-background-form.react.js',
            pdsPath + 'family/children/children-form.react.js',
            pdsPath + 'family/children/children-create-modal.react.js',
            pdsPath + 'family/children/children-edit-modal.react.js',
            pdsPath + 'family/children/children-section.react.js',
            pdsPath + 'family/children/children-datatable.react.js',
            pdsPath + 'education/education-form.react.js',
            pdsPath + 'education/education-create-modal.react.js',
            pdsPath + 'education/education-edit-modal.react.js',
            pdsPath + 'education/education-datatable.react.js',
            pdsPath + 'education/education-section.react.js',
            pdsPath + 'civic/civic-form.react.js',
            pdsPath + 'civic/civic-create-modal.react.js',
            pdsPath + 'civic/civic-edit-modal.react.js',
            pdsPath + 'civic/civic-datatable.react.js',
            pdsPath + 'civic/civic-section.react.js',
            pdsPath + 'training/training-form.react.js',
            pdsPath + 'training/training-create-modal.react.js',
            pdsPath + 'training/training-edit-modal.react.js',
            pdsPath + 'training/training-datatable.react.js',
            pdsPath + 'training/training-section.react.js',
            pdsPath + 'civil-service/civil-form.react.js',
            pdsPath + 'civil-service/civil-create-modal.react.js',
            pdsPath + 'civil-service/civil-edit-modal.react.js',
            pdsPath + 'civil-service/civil-datatable.react.js',
            pdsPath + 'civil-service/civil-section.react.js',
            pdsPath + 'work-experience/work-form.react.js',
            pdsPath + 'work-experience/work-create-modal.react.js',
            pdsPath + 'work-experience/work-edit-modal.react.js',
            pdsPath + 'work-experience/work-datatable.react.js',
            pdsPath + 'work-experience/work-experience-section.react.js',
            pdsPath + 'other-info/other-info-form.react.js',
            pdsPath + 'other-info/other-info-edit-modal.react.js',
            pdsPath + 'other-info/other-info-create-modal.react.js',
            pdsPath + 'other-info/other-info-datatable.react.js',
            pdsPath + 'other-info/other-info-questions.react.js',
            pdsPath + 'other-info/other-info-section.react.js',
            pdsPath + 'reference/reference-form.react.js',
            pdsPath + 'reference/reference-edit-modal.react.js',
            pdsPath + 'reference/reference-create-modal.react.js',
            pdsPath + 'reference/reference-datatable.react.js',
            pdsPath + 'reference/reference-section.react.js',
            pdsPath + 'cedula/cedula-form.react.js',
            pdsPath + 'cedula/cedula-section.react.js',
            pdsPath + 'attachment/attachment-form.react.js',
            pdsPath + 'attachment/attachment-section.react.js',
            pdsPath + 'upload-modal.react.js',
            pdsPath + 'registered-profile.react.js',
            pdsPath + 'anonymous-profile.react.js',
            pdsPath + 'pds-section.react.js',
            pdsPath + 'index.react.js',
        ]),
        concat('pds.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-obr-tracking',function(cb){
    pump([
        gulp.src([
            obrtrackingPath + 'obr_tracking.datatable.react.js',
            obrtrackingPath + 'obr_tracking.react.js'
        ]),
        concat('obr_tracking.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-rcd-inquiry',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/rcd_inquiry/rcd_inquiry.bank_deposits.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.aoc_account_summary_details.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.aoc_receipt_details.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.ri_fund_source_details.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.rcd_details.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.modal.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.datatable.react.js',
            myPath + 'tms/rcd_inquiry/rcd_inquiry.react.js'
        ]),
        concat('rcd_inquiry.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-tms-monitoring',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/tms_monitoring/tms_monitoring.js'
        ]),
        concat('tms_monitoring.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-ri-fund-source',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/ri_fund_source/ri_fund_source.filterbox.react.js',
            myPath + 'tms/ri_fund_source/ri_fund_source.table.react.js',
            myPath + 'tms/ri_fund_source/ri_fund_source.react.js'
        ]),
        concat('ri_fund_source.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-aoc-account-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/aoc_account_summary/aoc_account_summary.table.react.js',
            myPath + 'tms/aoc_account_summary/aoc_account_summary.react.js'
        ]),
        concat('aoc_account_summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-aoc-receipt',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/aoc_receipt/aoc_receipt.datatable.react.js',
            myPath + 'tms/aoc_receipt/aoc_receipt.react.js'
        ]),
        concat('aoc_receipt.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-tax-clearance',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/tax_clearance/tax_clearance.filterbox.react.js',
            myPath + 'tms/tax_clearance/tax_clearance.datatable.react.js',
            myPath + 'tms/tax_clearance/tax_clearance.react.js'
        ]),
        concat('tax_clearance.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-rci-inquiry',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/rci_inquiry/rci_inquiry.modal_details.react.js',
            myPath + 'tms/rci_inquiry/rci_inquiry.datatable.react.js',
            myPath + 'tms/rci_inquiry/rci_inquiry.react.js'
        ]),
        concat('rci_inquiry.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-lbp-transfer',function(cb){
    pump([
        gulp.src([
            myPath + 'treasury/lbp_transfer/lbp_transfer.modal_details.react.js',
            myPath + 'treasury/lbp_transfer/lbp_transfer.datatable.react.js',
            myPath + 'treasury/lbp_transfer/lbp_transfer.react.js'
        ]),
        concat('lbp_transfer.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-check-issuance',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/check_issuance/check_issuance.modal_details.react.js',
            myPath + 'tms/check_issuance/check_issuance.datatable.react.js',
            myPath + 'tms/check_issuance/check_issuance.react.js'
        ]),
        concat('check_issuance.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-dv-monitoring',function(cb){
    pump([
        gulp.src([
            myPath + 'accounting/dv_monitoring/dv_monitoring.datatable.react.js',
            myPath + 'accounting/dv_monitoring/dv_monitoring.react.js'
        ]),
        concat('dv_monitoring.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-dv-suspension',function(cb){
    pump([
        gulp.src([
            myPath + 'accounting/dv_suspension/dv_suspension.modal_bulk_sending.react.js',
            myPath + 'accounting/dv_suspension/dv_suspension.datatable.react.js',
            myPath + 'accounting/dv_suspension/dv_suspension.react.js'
        ]),
        concat('dv_suspension.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-rpt-billing',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/rpt_billing/rpt_billing.js',
        ]),
        concat('rpt_billing.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-assessment-roll',function(cb){
    pump([
        gulp.src([
            myPath + 'spidc/assessment_roll/assessment_roll.js',
        ]),
        concat('assessment_roll.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-rptar',function(cb){
    pump([
        gulp.src([
            myPath + 'spidc/rptar/rptar.js',
        ]),
        concat('rptar.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-property-inquiry',function(cb){
    pump([
        gulp.src([
            myPath + 'spidc/property_inquiry/property_inquiry.js',
        ]),
        concat('property_inquiry.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-billing',function(cb){
    pump([
        gulp.src([
            myPath + 'spidc/billing/billing.js',
        ]),
        concat('billing.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-indexing-user-performance',function(cb){
    pump([
        gulp.src([
            myPath + 'accounting/indexing_user_performance/indexing_user_performance.js',
        ]),
        concat('indexing_user_performance.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});


gulp.task('compile-indexing',function(cb){
    pump([
        gulp.src([
            myPath + 'accounting/indexing/indexing.obr_detail_view_modal.react.js',
            myPath + 'accounting/indexing/indexing.form.react.js',
            myPath + 'accounting/indexing/indexing.edit_modal.react.js',
            myPath + 'accounting/indexing/indexing.create_modal.react.js',
            myPath + 'accounting/indexing/indexing.datatable.react.js',
            myPath + 'accounting/indexing/indexing.react.js',
        ]),
        concat('indexing.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-indexing-user',function(cb){
    pump([
        gulp.src([
            myPath + 'accounting/indexing_user/indexing_user.form.react.js',
            myPath + 'accounting/indexing_user/indexing_user.edit_modal.react.js',
            myPath + 'accounting/indexing_user/indexing_user.create_modal.react.js',
            myPath + 'accounting/indexing_user/indexing_user.datatable.react.js',
            myPath + 'accounting/indexing_user/indexing_user.react.js',
        ]),
        concat('indexing_user.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-online-check-posting',function(cb){
    pump([
        gulp.src([
            myPath + 'tms/online_check_posting/online_check_posting.js',
        ]),
        concat('online_check_posting.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-dcms_docs',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/dcms_docs.js',
        ]),
        concat('dcms_docs.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-dcms_services',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/dcms_services.js',
        ]),
        concat('dcms_services.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-category',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/management/category.js',
        ]),
        concat('category.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-office',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/management/office.js',
        ]),
        concat('office.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-office_user',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/management/office_user.js',
        ]),
        concat('office_user.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-document_type',function(cb){
    pump([
        gulp.src([
            myPath + 'dcmis/management/document_type.js',
        ]),
        concat('document_type.js'),
        rename({suffix: '.min'}),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ],cb);
});

gulp.task('compile-public-obr-tracking',function(cb){
    pump([
        gulp.src([
            myPath + 'public/obr_tracking/obr_tracking.js'
        ]),
        concat('public_obr_tracking.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);

});

gulp.task('compile-voter-record-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'voter-record-summary/voter-summary-item-detail.react.js',
            myPath + 'voter-record-summary/voter-barangay-summary.table.react.js',
            myPath + 'voter-record-summary/voter-municipality-summary.table.react.js',
            myPath + 'voter-record-summary/voter-province-summary.table.react.js',
            myPath + 'voter-record-summary/voter-record-summary.react.js'
        ]),
        concat('voter-record-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-voter-network',function(cb){
    pump([
        gulp.src([
            myPath + 'voter-network/voter-network-root-create-modal.react.js',
            myPath + 'voter-network/voter-network-create-modal.react.js',
            myPath + 'voter-network/voter-network-edit-modal.react.js',
            myPath + 'voter-network/voter-network.react.js'
        ]),
        concat('voter-network.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-voter-approval',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-history.datatable.react.js',
            myPath + 'voter/voter.view_modal.react.js',
            myPath + 'voter-record-approval/voter-approval.modal.react.js',
            myPath + 'voter-record-approval/voter.datatable.react.js',
            myPath + 'voter-record-approval/voter.react.js'
        ]),
        concat('voter-approval.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});


gulp.task('compile-voter-record-update',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-history.datatable.react.js',
            myPath + 'voter/voter.view_modal.react.js',
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'voter/voter.upload_modal.react.js',
            myPath + 'voter-record-update/voter.datatable.react.js',
            myPath + 'voter-record-update/voter.react.js'
        ]),
        concat('voter-record-update.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-voter',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/location-assignment-datatable.react.js',
            myPath + 'voter/location-assignment-create-modal.react.js',
            myPath + 'voter/location-assignment-modal.react.js',
            myPath + 'voter/voter-crop-modal.react.js',
            myPath + 'voter/voter-temporary-edit-modal.react.js',
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'voter/voter-crop-modal.react.js',
            myPath + 'voter/voter-jpm-modal.react.js',
            myPath + 'voter/sms-template-modal.react.js',
            myPath + 'voter/sms-modal.react.js',
            myPath + 'voter/dswd-sms-modal.react.js',
            myPath + 'voter/voter.create_assistance_modal.react.js',
            myPath + 'voter/voter-assistance.datatable.react.js',
            myPath + 'voter/voter-history.datatable.react.js',
            myPath + 'voter/voter.view_modal.react.js',
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'voter/voter.upload_birthday_modal.react.js',
            myPath + 'voter/voter.upload_2016_voting_status_modal.react.js',
            myPath + 'voter/voter.upload_modal.react.js',
            myPath + 'voter/voter.create_modal.react.js',
            myPath + 'voter/voter.datatable.react.js',
            myPath + 'voter/voter.react.js'
        ]),
        concat('voter.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-project-event',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-crop-modal.react.js',
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'project-event/project-event-attendee-batch.modal.react.js',
            myPath + 'project-event/project-event-attendee.modal.react.js',
            myPath + 'project-event/project-event-detail.datatable.react.js',
            myPath + 'project-event/project-event-attendance.modal.react.js',
            myPath + 'project-event/project-event-edit.modal.react.js',
            myPath + 'project-event/project-event-create.modal.react.js',
            myPath + 'project-event/project-event.datatable.react.js',
            myPath + 'project-event/project-event.react.js'
        ]),
        concat('project-event.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-household',function(cb){
    pump([
        gulp.src([
            myPath + 'household/household-member-create-modal.react.js',
            myPath + 'household/household-detail-datatable.react.js',
            myPath + 'household/household-member-modal.react.js',
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'household/household-edit-modal.react.js',
            myPath + 'household/household-create-modal.react.js',
            myPath + 'household/household-datatable.react.js',
            myPath + 'household/household.react.js'
        ]),
        concat('household.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-project-recruitment',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'recruitment/recruitment-detail-datatable.react.js',
            myPath + 'recruitment/recruitment-member-create-modal.react.js',
            myPath + 'recruitment/recruitment-member-modal.react.js',
            myPath + 'recruitment/recruitment-create-modal.react.js',
            myPath + 'recruitment/recruitment.datatable.react.js',
            myPath + 'recruitment/recruitment.react.js'
        ]),
        concat('recruitment.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-project-recruitment2',function(cb){
    pump([
        gulp.src([
            myPath + 'household/household-detail-datatable.react.js',
            myPath + 'household/household-member-create-modal.react.js',
            myPath + 'household/household-member-modal.react.js',
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'recruitment/recruitment2.datatable.react.js',
            myPath + 'recruitment/recruitment2.react.js'
        ]),
        concat('recruitment2.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-form-status',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'form-status/form-status-edit-modal.react.js',
            myPath + 'form-status/form-status-create-modal.react.js',
            myPath + 'form-status/form-status-datatable.react.js',
            myPath + 'form-status/form-status.react.js',
        ]),
        concat('form-status.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});


gulp.task('compile-form-status-checklist',function(cb){
    pump([
        gulp.src([
            myPath + 'form-status-checklist/form-status-checklist-datatable.react.js',
            myPath + 'form-status-checklist/form-status-checklist.react.js',
        ]),
        concat('form-status-checklist.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-organization-cluster',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/location-assignment-datatable.react.js',
            myPath + 'organization-cluster/organization-cluster-edit.modal.react.js',
            myPath + 'organization-cluster/organization-cluster-create.modal.react.js',
            myPath + 'organization-cluster/organization-cluster.datatable.react.js',
            myPath + 'organization-cluster/organization-cluster.react.js',
        ]),
        concat('organization-cluster.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-project-recruitment-special',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-member.modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-household-member.datatable.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-kcl-household.modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-speical-household.datatable.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-household.modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-kcl.modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special-create.modal.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special.datatable.react.js',
            myPath + 'project-recruitment-special/project-recruitment-special.react.js'
        ]),
        concat('project-recruitment-special.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-project-print',function(cb){
    pump([
        gulp.src([
            myPath + 'project-print/printing-group-modal.js',
            myPath + 'project-print/printout-datatable.js',
            myPath + 'project-print/index.js'
        ]),
        concat('project-print.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-organization-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'organization-summary/organization-summary-photo-datatable.react.js',
            myPath + 'organization-summary/organization-summary-detail-datatable.react.js',
            myPath + 'organization-summary/organization-summary-item-detail.react.js',
            myPath + 'organization-summary/organization-barangay-summary.table.react.js',
            myPath + 'organization-summary/organization-municipality-summary.table.react.js',
            myPath + 'organization-summary/organization-province-summary.table.react.js',
            myPath + 'organization-summary/organization-summary.react.js'
        ]),
        concat('organization-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-organization-target-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'organization-summary/organization-summary-photo-datatable.react.js',
            myPath + 'organization-summary/organization-summary-detail-datatable.react.js',
            myPath + 'organization-summary/organization-summary-item-detail.react.js',
            myPath + 'organization-target-summary/organization-barangay-target-summary.table.react.js',
            myPath + 'organization-target-summary/organization-municipality-target-summary.table.react.js',
            myPath + 'organization-target-summary/organization-province-target-summary.table.react.js',
            myPath + 'organization-target-summary/organization-target-summary.react.js'
        ]),
        concat('organization-target-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});


gulp.task('compile-financial-assistance',function(cb){
    pump([
        gulp.src([
            myPath + 'financial-assistance/fa.monthly-summary-report.datatable.react.js',
            myPath + 'financial-assistance/fa.municipality-summary-report.datatable.react.js',
            myPath + 'financial-assistance/fa.daily-summary-report.datatable.react.js',
            myPath + 'financial-assistance/fa.closing-modal.react.js',
            myPath + 'financial-assistance/fa.release-modal.react.js',
            myPath + 'financial-assistance/fa.create-new-profile-modal.react.js',
            myPath + 'financial-assistance/fa.edit-modal.react.js',
            myPath + 'financial-assistance/fa.create-modal.react.js',
            myPath + 'financial-assistance/fa.released-list-modal.react.js',
            myPath + 'financial-assistance/fa.daily-summary-detail.datatable.react.js',
            myPath + 'financial-assistance/fa.daily-summary.datatable.react.js',
            myPath + 'financial-assistance/fa.datatable.react.js',
            myPath + 'financial-assistance/fa.react.js'
        ]),
        concat('financial-assistance.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-recruitment-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'project-recruitment-summary/project-recruitment-barangay-summary.table.react.js',
            myPath + 'project-recruitment-summary/project-recruitment-municipality-summary.table.react.js',
            myPath + 'project-recruitment-summary/project-recruitment-province-summary.table.react.js',
            myPath + 'project-recruitment-summary/project-recruitment-summary.react.js'
        ]),
        concat('project-recruitment-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-recruitment-encoding-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'project-recruitment-encoding-summary/project-recruitment-encoding-summary-by-encoder.datatable.react.js',
            myPath + 'project-recruitment-encoding-summary/project-recruitment-encoding-summary.datatable.react.js',
            myPath + 'project-recruitment-encoding-summary/project-recruitment-encoding-summary.react.js'
        ]),
        concat('project-recruitment-encoding-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-pulahan',function(cb){
    pump([
        gulp.src([
            myPath + 'pulahan/pulahan-create-modal.react.js',
            myPath + 'pulahan/pulahan.datatable.react.js',
            myPath + 'pulahan/pulahan.react.js'
        ]),
        concat('pulahan.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});


gulp.task('compile-update-manager',function(cb){
    pump([
        gulp.src([
            myPath + 'update-manager/data-import-view-modal.react.js',
            myPath + 'update-manager/data-import-datatable.react.js',
            myPath + 'update-manager/data-updater-modal.react.js',
            myPath + 'update-manager/update-manager.react.js'
        ]),
        concat('update-manager.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-photo-upload',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-temporary-edit-modal.react.js',
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'voter/voter-crop-modalv2.react.js',
            myPath + 'voter/voter-jpm-modal.react.js',
            myPath + 'photo-upload/photo-upload-item-edit.modal.react.js',
            myPath + 'photo-upload/photo-upload-items.modal.react.js',
            myPath + 'photo-upload/photo-upload-modal.react.js',
            myPath + 'photo-upload/photo-upload.datatable.react.js',
            myPath + 'photo-upload/photo-upload.react.js'
        ]),
        concat('photo-upload.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-special-operation',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-crop-modal.react.js',
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'voter/voter-temporary-create-modal.react.js',
            myPath + 'special-operation/special-operation-crop-modal.react.js',
            myPath + 'special-operation/special-operation-photo-edit-modal.react.js',
            myPath + 'special-operation/special-operation-photo-modal.react.js',
            myPath + 'special-operation/special-operation-upload-photo-modal.react.js',
            myPath + 'special-operation/special-operation-member-create-modal.react.js',
            myPath + 'special-operation/special-operation-detail-datatable.react.js',
            myPath + 'special-operation/special-operation-member-modal.react.js',
            myPath + 'special-operation/special-operation-create-modal.react.js',
            myPath + 'special-operation/special-operation.datatable.react.js',
            myPath + 'special-operation/special-operation.react.js'
        ]),
        concat('special-operation.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-id-inhouse-requests',function(cb){
    pump([
        gulp.src([
            myPath + 'voter/voter-crop-modal.react.js',
            myPath + 'voter/voter.edit_modal.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-request-voter-profile.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-request-detail-datatable.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-request-print-modal.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-request-item-modal.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-requests-detail-view-modal.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-requests-datatable.react.js',
            myPath + 'id-inhouse-requests/new-id-request-modal.react.js',
            myPath + 'id-inhouse-requests/id-inhouse-requests.react.js'
        ]),
        concat('id-inhouse-requests.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-special-opt',function(cb){
    pump([
        gulp.src([
            myPath + 'special-opt-new-member-modal.react.js',
            myPath + 'special-opt-detail-datatable.react.js',
            myPath + 'special-opt-detail-view-modal.react.js',
            myPath + 'special-opt/special-opt-create-leader-modal.react.js',
            myPath + 'special-opt/special-opt-datatable.react.js',
            myPath + 'special-opt/special-opt.react.js'
        ]),
        concat('special-opt.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-organization-photo-summary',function(cb){
    pump([
        gulp.src([
            myPath + 'organization-photo-summary/organization-municipality-photo-summary.table.react.js',
            myPath + 'organization-photo-summary/organization-photo-summary.react.js'
        ]),
        concat('organization-photo-summary.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-voter-network-report',function(cb){
    pump([
        gulp.src([
            myPath + 'voter-network-report/voter-network-report-datatable.react.js',
            myPath + 'voter-network-report/voter-network-report-hierarchy-list.react.js',
            myPath + 'voter-network-report/voter-network-report.react.js'
        ]),
        concat('voter-network-report.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-sms-component',function(cb){
    pump([
        gulp.src([
            myPath + 'sms/response-modal.js',
            myPath + 'sms/sms-datatable.js',
            myPath + 'sms/index.js'
        ]),
        concat('sms.react.js'),
        rename({suffix: '.min'}),
        react(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});


gulp.task('compile-user-access',function(cb){
    pump([
        gulp.src([
            myPath + 'user-access/user-access-create.modal.react.js',
            myPath + 'user-access/user-access.datatable.react.js',
            myPath + 'user-access/user-access.modal.react.js',
            myPath + 'user-access/user.datatable.react.js',
            myPath + 'user-access/user-access.react.js'
        ]),
        concat('user-access.react.js'),
        rename({suffix: '.min'}),
        react(),
        uglifyjs(),
        gulp.dest(jsPath),
        bust({
            relativePath : "web"
        }),
        gulp.dest('.')
    ], cb);
});

gulp.task('compile-css',[
    'compile-main-css',
    'compile-login-css'
]);

gulp.task('compile-public-css',[
    'compile-public-main-css'
]);

gulp.task('compile-js',[
    'compile-main-js',
    'compile-system-user-rcenter',
    'compile-system-user',
    'compile-amcharts',
    'compile-login-js',
    'compile-rpt-billing',
    'compile-assessment-roll',
    'compile-rptar',
    'compile-property-inquiry',
    'compile-billing',
    'compile-indexing-user-performance',
    'compile-online-check-posting',
    'compile-tms-monitoring',
    'compile-dcms_docs',
    'compile-dcms_services',
    'compile-category',
    'compile-office',
    'compile-office_user',
    'compile-document_type'
]);

gulp.task('compile-public-js',[
    'compile-public-main-js',
    'compile-public-obr-tracking'
]);

gulp.task('compile-react-js',[
    'compile-loading-react',
    'compile-system-group',
    'compile-system-menu',
    'compile-system-user-rcenter',
    'compile-pds',
    'compile-joborder',
    'compile-obr-tracking',
    'compile-rcd-inquiry',
    'compile-ri-fund-source',
    'compile-aoc-account-summary',
    'compile-aoc-receipt',
    'compile-tax-clearance',
    'compile-lbp-transfer',
    'compile-dv-monitoring',
    'compile-rci-inquiry',
    'compile-lbp-transfer',
    'compile-check-issuance',
    'compile-dv-monitoring',
    'compile-dv-suspension',
    'compile-indexing',
    'compile-indexing-user'
]);

gulp.task('default',['compile-react-js','compile-js', 'compile-css', 'compile-public-js', 'compile-public-css']);