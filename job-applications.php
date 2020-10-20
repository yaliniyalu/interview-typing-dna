<?php

include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

tryLogin();

global $mysql;

$posts = $mysql
    ->where('is_active', true)
    ->get('job_posts', null, 'id, title');

$levels = $mysql
    ->where('is_active', true)
    ->get('interview_levels', null, 'id, name');
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Job Applications') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar('job_applications') ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Job Applications
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="active"><a href="#">Job Applications</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Job Applications</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="box-group" id="filter">
                                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                <div class="panel box box-primary">
                                    <div class="box-header with-border">
                                        <h4 class="box-title">
                                            <a data-toggle="collapse" data-parent="#filter" href="#collapseOne" aria-expanded="true" class="">
                                                Filter
                                            </a>
                                        </h4>
                                        <div class="box-tools pull-right">
                                            <a data-toggle="collapse" data-parent="#filter" href="#collapseOne" aria-expanded="true" class="">
                                                <i class="fa fa-filter"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse" aria-expanded="true" style="">
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <?php render_select_simple("Job Post", "post", function () use ($posts) {
                                                        echo "<option></option>";
                                                        foreach ($posts as $post) {
                                                            echo "<option value='{$post['id']}'>{$post['title']}</option>";
                                                        }
                                                    }, 'newspaper-o', null, false, false, 'table-filter', 'data-allow-clear=true'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <?php render_select_simple("Current Level", "level", function () use ($levels) {
                                                        echo "<option></option>";
                                                        foreach ($levels as $level) {
                                                            echo "<option value='{$level['id']}'>{$level['name']}</option>";
                                                        }
                                                    }, 'level-up', null, false, false, 'table-filter', 'data-allow-clear=true'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <?php render_select_simple("Status", "status", ['', 'Pending', 'Accepted', 'Rejected', 'Selected'], 'check', null, false, false, 'table-filter', 'data-allow-clear=true'); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-offset-4 col-md-4 text-center">
                                                    <button type="button" class="btn btn-info table-filter-clear-all">Clear Filter</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="dt_job_applications" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th data-data="ja_id">ID</th>
                                    <th data-data="ja_name">Name</th>
                                    <th data-data="ja_gender">Gender</th>
                                    <th data-data="post_title">Post</th>
                                    <th data-data="ja_status" class="td-status">Status</th>
                                    <th data-data="ja_total_scores">Score</th>
                                    <th data-data="level_name">Current Level</th>
                                    <th data-data="_action" data-orderable="false" class="td-action_">Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php html_footer() ?>
</div>
<!-- ./wrapper -->

<?php scripts() ?>

<?php js_scripts(); ?>

<script type="text/html" id="action_column_template">
    <a href="#" class="view_job_application icon-view btn-action-lg" data-id="{{ ja_id }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
    <a href="#" class="edit_job_application icon-edit btn-action-lg" data-id="{{ ja_id }}">
        <i class="fa fa-pencil" aria-hidden="true"></i>
    </a>
    <a href="#" class="delete_job_application icon-delete" data-id="{{ ja_id }}">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </a>
</script>

<script>
    const colors = <?= json_encode(get_application_status_color_codes()) ?>
</script>

<script>
    var table = $('#dt_job_applications').DataTable({
        dom: 'Bfrtip',
        responsive: true,

        'paging'      : true,
        'lengthChange': false,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : true,

        order: [],

        columnDefs: [
            {
                render: function ( data, type, row ) {
                    return action_column_template(row);
                },
                targets: 'td-action_'
            },
            {
                render: function (data, type, row) {
                    return  `<span class='label label-${colors[data]}'>${data}</span>`
                },
                targets: 'td-status'
            }
        ],

        processing: true,
        serverSide: true,
        ajax: {
            url: 'api/job-applications.php?action=search',
            dataSrc: function (json) {
                return json.data;
            },
            data: function(data) {
                data['filter'] = {
                    post: $('.table-filter[name=post]').val(),
                    level: $('.table-filter[name=level]').val(),
                    status: $('.table-filter[name=status]').val()
                }
            }
        },

        buttons: [
            /*{
                text: '<i class="fa fa-plus"></i> Add Application',
                className: 'btn btn-success',
                action: function ( e, dt, node, config ) {
                    e.preventDefault();
                    location.href = "job-application.php"
                }
            }*/
        ]
    });

    table.on( 'draw', function () {
        initTooltips();
    });
</script>

<script>
    var action_column_template = Template7.compile($('#action_column_template').html());
</script>

<script>
    var dt_job_applications = $('#dt_job_applications');

    dt_job_applications.on('click', '.delete_job_application', function (e) {
        e.preventDefault();

        deleteJobApplication($(this).attr('data-id'), function () {
            table.ajax.reload();
        });
    });

    dt_job_applications.on('click', '.view_job_application', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id')
        location.href = `job-application.php?id=${id}&action=view`;
    });

    dt_job_applications.on('click', '.edit_job_application', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id')
        location.href = `job-application.php?id=${id}&action=edit`;
    });

    function deleteJobApplication(id, success) {
        $.confirm({
            title: 'Are You Sure?',
            content: 'Do you want to delete the job application?',
            backgroundDismiss: true,
            buttons: {
                yes: {
                    btnClass: 'btn-red',
                    action: function () {
                        $.post('api/job-applications.php?action=delete', { id: id }, function (data) {
                            if (data['success']) {
                                $.notify('Job Application Deleted', { position:"bottom center", className: "success" });

                                success();
                            }
                            else {
                                alertError(data['message']);
                            }
                        }, 'json')
                    }
                },
                no: {
                    btnClass: 'btn-blue',
                }
            }
        });
    }
</script>

<script>
    $('.table-filter').on('change', function (e) {
        table.ajax.reload();

        var cleared = true;
        $('.table-filter').each(function () {
            if ($(this).val()) {
                cleared = false;
                return false
            }
        });

        if (cleared) {
            $('#filter .box').removeClass('box-danger').addClass('box-primary');
        }
        else {
            $('#filter .box').removeClass('box-primary').addClass('box-danger');
        }
    });

    $('.table-filter-clear-all').on('click', function (e) {
        $('.table-filter').each(function () {
            $(this).val('').trigger("change.select2");
        });
        table.ajax.reload();

        $('#filter .box').removeClass('box-danger').addClass('box-primary');
    });
</script>

</body>
</html>

