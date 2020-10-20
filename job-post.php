<?php

include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

tryLogin();
allowAdminPanelUsers();

global $mysql;

$action = 'new';

if (isset($_GET['id'])) {
    $context = $mysql
        ->where('id', $_GET['id'])
        ->getOne('job_posts');

    if (empty($context)) {
        html_error_page('Job Posts', 'job_posts', "Requested Job Post not available.", 'danger');
        exit;
    }

    if (empty($_GET['action']) || !in_array($_GET['action'], ['view', 'edit'])) {
        $action = 'view';
    }
    else {
        $action = $_GET['action'];
    }
}


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
                <li><a href="job-posts.php"><i class="fa fa-newspaper-o"></i> Job Posts</a></li>
                <li class="active"><a href="#">Job Post</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content" data-action="view">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box">
                        <div class="box-header">
                            <?php if($action === 'new'): ?>
                                <h3 class="box-title">Job Post</h3>
                            <?php else: ?>
                                <h3 class="box-title">Job Post - <b>#<?php e($context['id']); ?></b></h3>
                            <?php endif; ?>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <form id="form_job_post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php render_input_text('Title', 'title', _e_ctx('title'), 'newspaper-o') ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php render_textarea('Skills', 'skills', _e_ctx('skills'), 'plus') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php render_textarea('Experience', 'experience', _e_ctx('experience'), 'calendar-check-o') ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php render_textarea('Salary', 'salary', _e_ctx('salary'), 'money') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php render_textarea('Details', 'details', _e_ctx('details'), 'info') ?>
                                    </div>
                                </div>

                                <?php render_input_hidden('id', _e_ctx('id')); ?>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pull-right">
                                            <?php if($action === 'new'): ?>
                                                <a class="btn btn-default" href="job-posts.php">Cancel</a>
                                                <button class="btn btn-success" type="button" id="btn-create-job-post"><i class="fa fa-save"></i> Create</button>
                                            <?php else: ?>
                                                <span class="on-action-view">
                                                    <button class="btn btn-danger" type="button" id="btn-delete-job-post" data-id="<?= _e_ctx('id') ?>"><i class="fa fa-trash"></i> Delete</button>
                                                    <button class="btn btn-info" type="button" id="btn-edit-job-post"><i class="fa fa-pencil"></i> Edit</button>
                                                </span>
                                                <span class="on-action-edit">
                                                    <a class="btn btn-default" href="job-post.php?id=<?= _e_ctx('id') ?>">Cancel</a>
                                                    <button class="btn btn-success" type="button" id="btn-update-job-post"><i class="fa fa-save"></i> Update</button>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </form>
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

<?php if($action === 'new'): ?>
    <script>
        $('#btn-create-job-post').on('click', function (e) {
            e.preventDefault();

            var job_post = $('#form_job_post').serialize();

            showLoader();
            $.post('api/job-posts.php?action=add', job_post, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                location.href = "job-post.php?id=" + response['data']['id'];
            }, 'json');
        });
    </script>
<?php else: ?>
    <script>
        $('#btn-update-job-post').on('click', function (e) {
            e.preventDefault();

            var job_post = $('#form_job_post').serialize();

            showLoader();
            $.post('api/job-posts.php?action=update', job_post, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                $.notify('Job Post Updated', { position:"bottom center", className: "success" });
                convertFormToView();
            }, 'json');
        });

        $('#btn-edit-job-post').on('click', function (e) {
            e.preventDefault();

            convertFormToEdit();
        })

        $('#btn-delete-job-post').on('click', function (e) {
            e.preventDefault();

            deleteJobPost($(this).attr('data-id'), function () {
                location.href = "job-posts.php";
            })
        })
    </script>

    <script>
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

    <script>
        function convertFormToView() {
            $('#form_job_post').find('[name]').attr('readonly', 'readonly');
            $('.content[data-action]').attr('data-action', 'view')
        }

        function convertFormToEdit() {
            $('#form_job_post').find('[name]').removeAttr('readonly');
            $('.content[data-action]').attr('data-action', 'edit')
        }

        <?php if($action === 'edit'): ?>
        convertFormToEdit();
        <?php else: ?>
        convertFormToView();
        <?php endif; ?>
    </script>
<?php endif; ?>

</body>
</html>

