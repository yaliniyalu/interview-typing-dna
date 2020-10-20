<?php

include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

$action = 'new';

$posts = $mysql
    ->where('is_active', true)
    ->get('job_posts', null, 'id, title');

?>

<!DOCTYPE html>
<html lang="en">
<?php head('Job Application') ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php html_header_simple(); ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Job Application
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="active"><a href="#">Job Application</a></li>
            </ol>
        </section>

        <section class="content">
            <div class="alert alert-warning">
                <h4><i class="icon fa fa-warning"></i> Warning</h4>
                This application must be filled by the candidate.
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Job Application</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <form id="form_job_application">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php render_input_text('Name', 'name', '', 'user', true) ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_date('DOB', 'dob', '', 'calendar', true); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_select_simple("Job Post", "job_post_id", function () use ($posts) {
                                                    echo "<option></option>";
                                                    foreach ($posts as $post) {
                                                        echo "<option value='{$post['id']}'>{$post['title']}</option>";
                                                    }
                                                }, 'newspaper-o',  null, true); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php render_select_simple("Gender", "gender", ['', 'Male', 'Female'], 'genderless', '', true); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_email('Email', 'email', '', 'envelope', true) ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_text('Mobile', 'mobile', '', 'phone', true) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="account-image">
                                            <img src="<?php e(get_application_profile_image(null)) ?>" alt="avatar" id="profile_image">
                                            <p class="change-button">Change</p>
                                        </div>
                                        <input type="file" name="image" hidden style="display: none" onchange="profile_image.src=window.URL.createObjectURL(this.files[0])">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <?php render_textarea('Address', 'address', '', 'map-marker') ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php render_textarea('Skills', 'skills', '', 'plus') ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php render_textarea('Experience', 'experience', '', 'calendar-check-o') ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php render_textarea('Details', 'details', '', 'info') ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>Enter the following two text in the respective text box</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_input_t_dna_1"><code class="t_dna_text"><?= TYPING_DNA_TEXT_1 ?></code></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-text-width"></i></span>
                                                <input type="text" class="form-control t_dna_input" id="id_input_t_dna_1"
                                                       placeholder="Enter the above text here" name="t_dna_1"
                                                       ondrop="return false"
                                                >
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_input_t_dna_2"><code class="t_dna_text"><?= TYPING_DNA_TEXT_2 ?></code></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-text-width"></i></span>
                                                <input type="text" class="form-control t_dna_input" id="id_input_t_dna_2"
                                                       placeholder="Enter the above text here" name="t_dna_2"
                                                >
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="t_dna_pattern">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pull-right">
                                            <button class="btn btn-success" type="submit" id="btn-create-job-application"><i class="fa fa-save"></i> Apply</button>
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

<script src="https://api.typingdna.com/scripts/typingdna.js"></script>

<script>
    $('.account-image .change-button').on('click', function () {
        $('[name=image]').trigger('click');
    });
</script>

<script>
    let typing_dna = null;
    $(function () {
        typing_dna = new TypingDNA();
    });
</script>

<script>
    $('.t_dna_input').on('change', function() {
        let parent = $(this).closest('.form-group');

        if (parent.find('.t_dna_text').text() !== $(this).val()) {
            parent.addClass('has-error');
            parent.find('.help-block').text('Enter the above text in text box correctly');
        }
        else {
            parent.removeClass('has-error');
            parent.find('.help-block').text('');
        }
    });

    $('input, textarea').on("cut copy paste drop autocomplete", function(e) {
        e.preventDefault();
    });
</script>

<script>
    $('#form_job_application').on('submit', function (e) {
        e.preventDefault();

        if ($('[name=t_dna_1]').val() !== "<?= TYPING_DNA_TEXT_1 ?>") {
            alertError("Verification text 1 doesn't match");
            return;
        }

        if ($('[name=t_dna_2]').val() !== "<?= TYPING_DNA_TEXT_2 ?>") {
            alertError("Verification text 2 doesn't match");
            return;
        }

        $('[name=t_dna_pattern]').val(typing_dna.getTypingPattern({ type:0 }))

        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        showLoader();
        uploadFormWithFiles('api/job-applications.php?action=add&from=candidate&timezone=' + timezone, this, function (response) {
            hideLoader();

            if (!response['success']) {
                alertError(response['message']);
                return;
            }

            location.href = response['data']['redirect_url'];
        }, 'json');
    });
</script>


</body>
</html>