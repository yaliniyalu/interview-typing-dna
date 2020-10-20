<?php
include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

if (!isset($_GET['id'])) {
    html_error_page_candidate('Application Status', 'Application not found');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Interview | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="plugins/admin-lte/css/AdminLTE.min.css">

    <link rel="stylesheet" href="plugins/jquery-confirm/jquery-confirm.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="shortcut icon" type="image/png" href="assets/images/favicon.ico">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition login-page">
<div id="loader-overlay">
    <div class="cv-spinner">
        <span class="spinner"></span>
    </div>
</div>

<div class="login-box">
    <div class="login-logo">
        <a href="index.php"><b>Interview</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Enter the following text in text box</p>

        <form class="login-form" method="post">

            <div class="row">
                <div class="form-group">
                    <label for="id_input_t_dna"><code class="t_dna_text"><?= TYPING_DNA_TEXT_LOGIN ?></code></label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-text-width"></span></span>
                        <input type="text" class="form-control" id="id_input_t_dna" placeholder="Enter the above text here" name="t_dna">
                    </div>
                    <span class="help-block"></span>
                </div>
                <input type="hidden" name="code" value="<?= $_GET['id'] ?>">
                <!-- /.col -->
                <div class="col-xs-12 text-center">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 3 -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/jquery-confirm/jquery-confirm.min.js"></script>

<script src="https://api.typingdna.com/scripts/typingdna.js"></script>
<script>
    let typing_dna = null;
    $(function () {
        typing_dna = new TypingDNA();
    });
</script>

<script>
    function showLoader() {
        $("#loader-overlay").fadeIn(300);
    }

    function hideLoader() {
        $("#loader-overlay").fadeOut(300);
    }
</script>
<script>
    $('.login-form').on('submit', function (e) {
        e.preventDefault();

        if ($(this).find('[name=t_dna]').val() !== "<?= TYPING_DNA_TEXT_LOGIN ?>") {
            $.alert('Enter the text correctly');
            return;
        }

        var code = $(this).find('[name=code]').val();
        var t_dna_pattern = typing_dna.getTypingPattern({ type:0 });

        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        showLoader();
        $.post('api/login.php?action=candidate-login&timezone=' + timezone, { code: code, t_dna_pattern: t_dna_pattern }, function (data) {
            hideLoader();

            if (data['success']) {
                location.href = "candidate-status.php?id=<?= $_GET['id'] ?>";
            }
            else {
                $.alert(data['message']);
            }
        }, 'json')
    })

</script>
</body>
</html>