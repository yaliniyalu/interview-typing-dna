<?php

include_once "db.php";
include_once "menu.php";

tryLogin();

global $mysql;
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Job Posts') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar('job_posts') ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Job Posts
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="active"><a href="#">Job Posts</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Job Posts</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="dt_job_posts" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th data-data="id">ID</th>
                                    <th data-data="title">Title</th>
                                    <th data-data="created_on">Posted On</th>
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
    <a href="#" class="view_job_post icon-view btn-action-lg" data-id="{{ id }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
    <a href="#" class="edit_job_post icon-edit btn-action-lg" data-id="{{ id }}">
        <i class="fa fa-pencil" aria-hidden="true"></i>
    </a>
    <a href="#" class="delete_job_post icon-delete" data-id="{{ id }}">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </a>
</script>

<script>
    var table = $('#dt_job_posts').DataTable({
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
            }
        ],

        processing: true,
        ajax: {
            url: 'api/job-posts.php?action=get&deleted=0',
            dataSrc: function (json) {
                return json.data;
            }
        },

        buttons: [
            {
                text: '<i class="fa fa-plus"></i> Add Post',
                className: 'btn btn-success',
                action: function ( e, dt, node, config ) {
                    e.preventDefault();
                    location.href = "job-post.php"
                }
            }
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
    var dt_job_posts = $('#dt_job_posts');

    dt_job_posts.on('click', '.delete_job_post', function (e) {
        e.preventDefault();

        deleteJobPost($(this).attr('data-id'), function () {
            table.ajax.reload();
        });
    });

    dt_job_posts.on('click', '.view_job_post', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id')
        location.href = `job-post.php?id=${id}&action=view`;
    });

    dt_job_posts.on('click', '.edit_job_post', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id')
        location.href = `job-post.php?id=${id}&action=edit`;
    });

    function deleteJobPost(id, success) {
        $.confirm({
            title: 'Are You Sure?',
            content: 'Do you want to delete the job post?',
            backgroundDismiss: true,
            buttons: {
                yes: {
                    btnClass: 'btn-red',
                    action: function () {
                        $.post('api/job-posts.php?action=delete', { id: id }, function (data) {
                            if (data['success']) {
                                $.notify('Job Post Deleted', { position:"bottom center", className: "success" });

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

</body>
</html>

