<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 04:25 PM
 */

function head($title, $callback = null) { ?>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Interview | <?php echo $title ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.7 -->
        <link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css">
        <!-- DataTables -->
        <link rel="stylesheet" href="plugins/datatables.net-bs/css/dataTables.bootstrap.min.css">

        <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="plugins/jquery-confirm/jquery-confirm.min.css">

        <link rel="stylesheet" href="plugins/select2/css/select2.min.css">

        <link rel="stylesheet" href="plugins/bootstrap-daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css">

        <!-- PACE -->
        <link rel="stylesheet" href="plugins/pace/pace.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="plugins/admin-lte/css/AdminLTE.min.css">
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="plugins/admin-lte/css/skins/_all-skins.min.css">

        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="shortcut icon" type="image/png" href="assets/images/favicon.ico">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <?php if ($callback) $callback(); ?>

        <!-- Google Font -->
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    </head>
<?php }

function html_header() { ?>
    <header class="main-header">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>I</b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>Inter</b>view</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="glyphicon glyphicon-menu-hamburger"></span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php e(get_profile_image($_SESSION['user_avatar'])) ?>" class="user-image" alt="User Image">
                            <span class="hidden-xs"><?php e($_SESSION['user_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php e(get_profile_image($_SESSION['user_avatar'])) ?>" class="img-circle" alt="User Image">

                                <p>
                                    <?php e($_SESSION['user_name']) ?>
                                    <small><?php e($_SESSION['user_type']) ?></small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="my-account.php" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <!-- Control Sidebar Toggle Button -->
                </ul>
            </div>
        </nav>
    </header>
<?php }

function html_header_simple() { ?>
    <header class="main-header">
        <nav class="navbar navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <a href="#" class="navbar-brand"><b>Inter</b>view</a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>
<?php }

function html_side_bar($active) {
    $parents = [ 'admin' => ['locations', 'device_types', 'device_makes', 'districts', 'accounts', 'export']];
    $set_active = function ($active, $current) { echo $active == $current ? 'active' : ''; };
    ?>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php e(get_profile_image($_SESSION['user_avatar'])) ?>" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p><?php e($_SESSION['user_name']) ?></p>
                    <a href="#"><?php e($_SESSION['user_type']) ?></a>
                </div>
            </div>
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">MAIN NAVIGATION</li>

                <li class="<?php $set_active($active,'job_applications') ?>">
                    <a href="job-applications.php">
                        <i class="fa fa-address-card"></i> <span>Job Applications</span>
                    </a>
                </li>

                <li class="<?php $set_active($active,'job_posts') ?>">
                    <a href="job-posts.php">
                        <i class="fa fa-newspaper-o"></i> <span>Job Posts</span>
                    </a>
                </li>

                <li class="<?php $set_active($active,'interviews') ?>">
                    <a href="interviews.php">
                        <i class="fa fa-thumbs-o-up"></i> <span>Interviews</span>
                    </a>
                </li>

                <?php if ($_SESSION['user_type'] === 'Admin'): ?>
                    <li class="<?php $set_active($active,'accounts') ?>">
                        <a href="accounts.php">
                            <i class="fa fa-users"></i> <span>Accounts</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="<?php $set_active($active, 'my-account') ?>">
                    <a href="my-account.php">
                        <i class="fa fa-user"></i> <span>My Account</span>
                    </a>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
<?php }

function html_footer() { ?>
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 1.0
        </div>
        <strong>Copyright &copy; 2020-2021 <a href="#">Interview</a>.</strong> All rights reserved.
    </footer>
<?php }

function html_loader() { ?>
    <div id="loader-overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>

    <script>
        function showLoader() {
            $("#loader-overlay").fadeIn(300);
        }

        function hideLoader() {
            $("#loader-overlay").fadeOut(300);
        }
    </script>
<?php }

function scripts() { ?>
    <!-- jQuery 3 -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script src="plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/dataTables.buttons.min.js"></script>
    <!-- SlimScroll -->
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="plugins/fastclick/lib/fastclick.js"></script>
    <script src="plugins/jquery-confirm/jquery-confirm.min.js"></script>
    <script src="plugins/select2/js/select2.min.js"></script>

    <script src="plugins/moment/min/moment.min.js"></script>
    <script src="plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- AdminLTE App -->
    <script src="plugins/admin-lte/js/adminlte.min.js"></script>

    <script src="plugins/template7/template7.min.js"></script>
    <script src="plugins/notifyjs/notify.min.js"></script>
    <script src="plugins/autosize/autosize.min.js"></script>
    <!--    <script src="plugins/pace/pace.min.js"></script>-->
<?php }

function js_datatable_export_buttons() { ?>
    <script type="text/javascript" src="plugins/datatable-buttons/buttons.flash.min.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/jszip.min.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/pdfmake.min.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/vfs_fonts.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/buttons.html5.min.js"></script>
    <script type="text/javascript" src="plugins/datatable-buttons/buttons.print.min.js"></script>
<?php }

function js_scripts() { ?>
    <script>
        Template7.registerHelper('human_date', function (date) {
            if (typeof date === 'function') date = date.call(this);

            return getHumanDate(date);
        });

        Template7.registerHelper('render_select_options', function (type, value, text = 'text', id = 'value') {
            var html = "";

            window[type].forEach(function (v) {
                var val, txt;
                if (typeof v === "string") {
                    val = txt = v;
                }
                else {
                    val = v[id];
                    txt = v[text];
                }

                var option = document.createElement('option');
                option.setAttribute('value', val);
                option.innerText = txt;

                if (val === value) {
                    option.setAttribute('selected', 'selected');
                }

                html += option.outerHTML;
            })

            return html;
        });

        function alertError(text) {
            $.alert({ title: '', content: text, type: 'red',});
        }

        function getHumanDate(date) {
            date = new Date(date);

            var hours = date.getHours() % 12;
            if (hours === 0) {
                hours = 12;
            }
            date = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear() + " " + hours + ":" + date.getMinutes() + " " + (date.getHours() > 12 ? 'PM' : 'AM')

            return date;
        }

    </script>
    <script>
        function initTooltips() {
            $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
            $('.icon-delete').tooltip({ title: "Delete", trigger: 'hover'  });
            $('.icon-edit').tooltip({ title: "Edit", trigger: 'hover'  });
            $('.icon-view').tooltip({ title: "View", trigger: 'hover'  });
        }

        function initSelect2() {
            $('.select2').not('.select2-no-search').select2({
                minimumResultsForSearch: 6
            });

            $('.select2.select2-no-search').select2({
                minimumResultsForSearch: Infinity
            });
        }

        $(function () {
            initSelect2();
            autosize($('textarea'));

            $('.datepicker').datepicker({
                autoclose: true,
                format: "yyyy-mm-dd"
            });

            $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
        })
    </script>
<?php }

function js_upload_form_with_files() { ?>
    <script>
        function uploadFormWithFiles(url, form, success, error)
        {
            var form_data = new FormData(form);
            $.ajax({
                url: url,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: success,
                error: error
            });
        }
    </script>
<?php }

function html_error_page($title, $menu, $message, $type = 'danger') { ?>
    <!DOCTYPE html>
    <html lang="en">
    <?php head($title) ?>

    <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php html_header() ?>

        <?php html_side_bar($menu) ?>

        <?php html_loader() ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content">
                <div class="alert alert-<?php echo $type; ?>">
                    <h4><i class="icon fa fa-warning"></i> Error!</h4>
                    <?php echo $message; ?>
                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->

        <?php html_footer() ?>
    </div>
    <!-- ./wrapper -->

    <?php scripts() ?>

    </body>
    </html>
<?php }

function html_error_page_candidate($title, $message, $type = 'danger', $errorTitle = 'Error!', $errorIcon = 'warning') { ?>
    <!DOCTYPE html>
    <html lang="en">
    <?php head($title) ?>

    <body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">

        <?php html_header_simple(); ?>

        <?php html_loader() ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content">
                <div class="alert alert-<?php echo $type; ?>">
                    <h4><i class="icon fa fa-<?= $errorIcon ?>"></i> <?= $errorTitle ?></h4>
                    <?php echo $message; ?>
                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->

        <?php html_footer() ?>
    </div>
    <!-- ./wrapper -->

    <?php scripts() ?>

    </body>
    </html>
<?php }

function get_application_status_color_codes() {
    return ['Rejected' => 'danger', 'Selected' => 'success', 'Accepted' => 'info', 'Pending' => 'warning', 'Promoted' => 'success', 'Verified' => 'success', 'Invalid' => 'danger'];
}

function html_application_status($status) {
    $colors_map = get_application_status_color_codes();
    echo "<span class='label label-{$colors_map[$status]}'>$status</span>";
}