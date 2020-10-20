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
<div class="login-box">
    <div class="login-logo">
        <a href="index.php"><b>Interview</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <div class="alert alert-warning">
            Email: admin@interview.yalini.tk<br>
            Password: admin<br>
            <br>
            For candidate login <a href="candidate-login.php">click here</a><br>
            For candidate application <a href="candidate-application.php">click here</a>
        </div>

        <p class="login-box-msg">Sign in to start your session</p>

        <form class="login-form" method="post">
            <div class="form-group has-feedback">
                <input type="email" class="form-control" placeholder="Email" name="email" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Password" name="password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
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
<script>
    $('.login-form').on('submit', function (e) {
        e.preventDefault();

        var email = $(this).find('[name=email]').val();
        var password = $(this).find('[name=password').val();
        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        $.post('api/login.php?action=login&timezone=' + timezone, { email: email, password: password }, function (data) {
            if (data['success']) {
                location.href = "index.php";
            }
            else {
                $.alert(data['message']);
            }
        }, 'json')
    })

</script>
</body>
</html>
