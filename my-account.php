<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 04:21 PM
 */

include_once "db.php";
include_once "menu.php";

tryLogin();

global $mysql;

$user = $mysql
    ->where('is_active', true)
    ->where('id', $_SESSION['user_id'])
    ->getOne('users');
?>

<!DOCTYPE html>
<html lang="en">
<?php head('My Account') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar('my-account') ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                My Account
                <small><?php echo _e($user['name']) ?></small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="accounts.php"><i class="fa fa-users"></i> Accounts</a></li>
                <li class="active"><a href="#">My Account</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box box-success">
                        <div class="box-header">
                            <h3 class="box-title">My Account</h3>
                        </div>
                        <!-- /.box-header -->

                        <form role="form" id="my-account-form">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_form_1_f_name">Name</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                                        <input type="text" class="form-control" id="id_form_1_f_name" placeholder="Name" name="name" value="<?php echo _e($user['name']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_form_1_email">Email</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fa fa-at"></i></span>
                                                        <input type="email" class="form-control" id="id_form_1_email" placeholder="Email" name="email" value="<?php echo _e($user['email']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="account-image">
                                            <img src="<?php e(get_profile_image($user['avatar'])) ?>" alt="avatar" id="profile_image">
                                            <p class="change-button">Change</p>
                                        </div>

                                        <p class="image-upload-info">Click Update Account Details button after changing image</p>

                                        <input type="file" name="image" hidden style="display: none" onchange="profile_image.src=window.URL.createObjectURL(this.files[0])">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-success btn_update_account"><i class="fa fa-save"></i> Update Account Details</button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </form>

                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->

                <div class="col-xs-12">
                    <div class="box box-danger">
                        <div class="box-header">
                            <h3 class="box-title">Change Password</h3>
                        </div>
                        <!-- /.box-header -->

                        <form role="form" id="change-password-form">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_form_1_password">Current Password</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                <input type="password" class="form-control" id="id_form_1_password" placeholder="Current Password" name="password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_form_1_password_n">New Password</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                                <input type="password" class="form-control" id="id_form_1_password_n" placeholder="New Password" name="new_password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_form_1_password_rn">Re-enter New Password</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                                <input type="password" class="form-control" id="id_form_1_password_rn" placeholder="Re-enter New Password" name="r_new_password" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-success"><i class="fa fa-lock"></i> Update Password</button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </form>

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

<?php js_upload_form_with_files(); ?>

<script>
    var privilege_column_template = Template7.compile($('#privilege_column_template').html());
</script>

<script>
    $('#my-account-form').on('submit', function (e) {
        e.preventDefault();
        showLoader();

        uploadFormWithFiles('api/my-account.php?action=update', this, function (data) {
            hideLoader();

            if (!data['success']) {
                alertError(data['message']);
                return;
            }

            $.notify('Account Updated', { position:"bottom center", className: "success" });
        });
    });

    $('#change-password-form').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        let data = $(this).serialize();

        showLoader();
        $.post('api/my-account.php?action=update-password', data, function (data) {
            hideLoader();

            if (!data['success']) {
                alertError(data['message']);
                return;
            }

            $(form).find('input').val('')

            $.notify('Password Updated', { position:"bottom center", className: "success" });
        }, 'json');
    })
</script>

<script>
    $('.account-image .change-button').on('click', '', function (e) {
        $('[name=image]').trigger('click');
    });
</script>

</body>
</html>

