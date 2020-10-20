<?php

include_once "db.php";
include_once "menu.php";

tryLogin();

if (empty($_SESSION['last_error'])) {
    redirect('index.php');
}

$error = $_SESSION['last_error'];
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Ticket') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar($error['menu']) ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content">
            <div class="alert alert-<?php echo $error['type']; ?>">
                <h4><i class="icon fa fa-warning"></i> Error!</h4>
                <?php echo $error['message']; ?>
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <?php html_footer() ?>
</div>
<!-- ./wrapper -->

<?php scripts() ?>

<?php js_scripts(); ?>

</body>
</html>