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
        ->getOne('job_applications');

    if (empty($context)) {
        html_error_page('Job Applications', 'job_applications', "Requested Job Application not available.", 'danger');
        exit;
    }

    if (empty($_GET['action']) || !in_array($_GET['action'], ['view', 'edit'])) {
        $action = 'view';
    }
    else {
        $action = $_GET['action'];
    }
}

$posts = $mysql
    ->where('is_active', true)
    ->get('job_posts', null, 'id, title');

$isTodayInterview = false;

$color = get_application_status_color_codes()[get_context_value('status')] ?? 'default';
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
                <li><a href="job-applications.php"><i class="fa fa-address-card"></i> Job Applications</a></li>
                <li class="active"><a href="#">Job Application</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content" data-action="view">
            <div class="row" id="interview-panel" style="display: none">
                <div class="col-md-12">
                    <div class="box box-<?= $color ?>">
                        <div class="box-header">
                            <h3 class="box-title">Waiting for Interview...</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <a href="#" class="btn btn-info" id="start-interview">Start Interview</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-<?= $color ?>">
                        <div class="box-header">
                            <?php if($action === 'new'): ?>
                                <h3 class="box-title">Job Application</h3>
                            <?php else: ?>
                                <h3 class="box-title">Job Application - <b>#<?php e($context['id']); ?></b></h3>
                                <span class="pull-right"><?php html_application_status(_e_ctx('status')) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <form id="form_job_application">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php render_input_text('Name', 'name', _e_ctx('name'), 'user', true) ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_date('DOB', 'dob', _e_ctx('dob'), 'calendar', true); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_select_simple("Job Post", "job_post_id", function () use ($posts) {
                                                    echo "<option></option>";
                                                    $post_id = get_context_value('job_post_id');
                                                    foreach ($posts as $post) {
                                                        $selected = $post['id'] == $post_id ? 'selected' : '';
                                                        echo "<option value='{$post['id']}' {$selected}>{$post['title']}</option>";
                                                    }
                                                }, 'newspaper-o', null, true); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php render_select_simple("Gender", "gender", ['', 'Male', 'Female'], 'genderless', get_context_value('gender'), true); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_email('Email', 'email', _e_ctx('email'), 'envelope', true) ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php render_input_text('Mobile', 'mobile', _e_ctx('mobile'), 'phone', true) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="account-image">
                                            <img src="<?php e(get_application_profile_image(get_context_value('avatar'))) ?>" alt="avatar" id="profile_image">
                                            <p class="change-button">Change</p>
                                        </div>
                                        <input type="file" name="image" hidden style="display: none" onchange="profile_image.src=window.URL.createObjectURL(this.files[0])">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <?php render_textarea('Address', 'address', _e_ctx('address'), 'map-marker') ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php render_textarea('Skills', 'skills', _e_ctx('skills'), 'plus') ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php render_textarea('Experience', 'experience', _e_ctx('experience'), 'calendar-check-o') ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php render_textarea('Details', 'details', get_context_value('details'), 'info') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php render_textarea('Comments', 'comments', get_context_value('comments'), 'comments') ?>
                                    </div>
                                </div>

                                <?php render_input_hidden('id', _e_ctx('id')); ?>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pull-right">
                                            <?php if($action === 'new'): ?>
                                                <a class="btn btn-default" href="job-applications.php">Cancel</a>
                                                <button class="btn btn-success" type="submit" id="btn-create-job-application"><i class="fa fa-save"></i> Create</button>
                                            <?php else: ?>
                                                <span class="on-action-view">
                                                    <button class="btn btn-danger" type="button" id="btn-delete-job-application" data-id="<?= _e_ctx('id') ?>"><i class="fa fa-trash"></i> Delete</button>
                                                    <button class="btn btn-info" type="button" id="btn-edit-job-application"><i class="fa fa-pencil"></i> Edit</button>
                                                </span>
                                                <span class="on-action-edit">
                                                    <a class="btn btn-default" href="job-application.php?id=<?= _e_ctx('id') ?>">Cancel</a>
                                                    <button class="btn btn-success" type="submit" id="btn-update-job-application"><i class="fa fa-save"></i> Update</button>
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

            <?php if($action != 'new'): ?>
                <?php $status = get_context_value('status') ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-<?= $color ?>">
                            <div class="box-header">
                                <h3 class="box-title">Status</h3> <?php html_application_status(_e_ctx('status')) ?>
                            </div>

                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <?php if($status == 'Pending' || $status == 'Rejected'): ?>
                                            <button type="button" class="btn btn-success btn-change-status" id="btn-accept-application" data-status="Accepted">Accept Application</button>
                                        <?php elseif($status != 'Selected'): ?>
                                            <button type="button" class="btn btn-success btn-change-status" id="btn-select-application" data-status="Selected">Select Candidate</button>
                                        <?php endif; ?>

                                        <?php if($status != 'Rejected'): ?>
                                            <button type="button" class="btn btn-danger btn-change-status" id="btn-reject-application" data-status="Rejected">Reject Application</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($status != 'Pending'): ?>
                    <?php
                    $levels = $mysql
                        ->where('is_active', true)
                        ->orderBy('id', 'asc')
                        ->get('interview_levels');

                    $interview_details = $mysql
                        ->map('interview_level_id')
                        ->where('id.job_application_id', _e_ctx('id'))
                        ->join('users at', 'at.id = id.assigned_to', 'left')
                        ->join('users ib', 'ib.id = id.interviewed_by', 'left')
                        ->get('interview_details id', null, 'id.*, ib.name as interviewed_by, at.name as assigned_to, ib.id as interviewed_by_id, at.id as assigned_to_id');

                    foreach ($levels as $key => $level) {
                        $levels[$key]['details'] = $interview_details[$level['id']] ?? null;
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-<?= $color ?>">
                                <div class="box-header">
                                    <h3 class="box-title">Interview Level & Score</h3>
                                </div>

                                <!-- /.box-header -->
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-hover">
                                                <thead>
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Scheduled On</th>
                                                    <th>Assigned To</th>
                                                    <th>Attended On</th>
                                                    <th>Interviewed By</th>
                                                    <th>Score</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                <?php $current_level = _e_ctx('current_level'); $current_level = $current_level == null ? 1000: $current_level; ?>
                                                <?php foreach($levels as $level): ?>
                                                    <?php $details = $level['details'] ?>

                                                    <?php if(!$details || ($level['id'] > $current_level)): ?>
                                                        <tr>
                                                            <td class="text-gray"><?= $level['name'] ?></td>
                                                            <td></td><td></td><td></td><td></td><td></td><td></td>
                                                        </tr>
                                                        <?php continue; endif; ?>

                                                    <?php
                                                    $scheduled_on = null;
                                                    if ($details['scheduled_date']) {
                                                        $scheduled_on = date('d-m-Y', strtotime($details['scheduled_date']));
                                                    }

                                                    $attended_on = null;
                                                    if ($details['attended_on']) {
                                                        $attended_on = date('d-m-Y h:i A', strtotime($details['attended_on']));
                                                    }
                                                    ?>

                                                    <?php if ($level['id'] < $current_level): ?>
                                                        <tr>
                                                            <td><?= $level['name'] ?></td>
                                                            <td><?= $scheduled_on ?></td>
                                                            <td><?= $details['assigned_to'] ?></td>
                                                            <td><?= $attended_on ? $attended_on : "<span class='label label-danger'>Not Attended</span>" ?></td>
                                                            <td><?= $details['interviewed_by'] ?></td>
                                                            <td><?= $details['score'] ?></td>
                                                            <td><?php html_application_status($details['status']) ?></td>
                                                        </tr>
                                                    <?php elseif ($level['id'] == $current_level): ?>
                                                        <?php
                                                        $users = $mysql
                                                            ->where('is_active', true)
                                                            ->get('users', null, 'id, name');

                                                        if ($details['scheduled_date'] && date('Ymd') == date('Ymd', strtotime($details['scheduled_date']))) {
                                                            $isTodayInterview = true;
                                                        }
                                                        ?>
                                                        <tr class="job_assignment_current_details">
                                                            <td class="text-success"><b><?= $level['name'] ?></b></td>
                                                            <td>
                                                                <label hidden for="id-input-scheduled-on"></label>
                                                                <input type="text" class="form-control pull-right datepicker form-details" placeholder="Scheduled On" id="id-input-scheduled-on"
                                                                       name="scheduled_date" value="<?= date('Y-m-d', strtotime($details['scheduled_date'])); ?>">
                                                            </td>
                                                            <td>
                                                                <label hidden for="id-input-assigned-to"></label>
                                                                <select class="form-control select2 form-details" name="assigned_to" data-placeholder="Assigned To" id="id-input-assigned-to">
                                                                    <option></option>
                                                                    <?php foreach ($users as $user) {
                                                                        $selected = $user['id'] == $details['assigned_to_id'] ? 'selected': '';
                                                                        echo "<option value='{$user['id']}' $selected>{$user['name']}</option>";
                                                                    } ?>
                                                                </select>
                                                            </td>

                                                            <?php if($details['scheduled_date'] && date('Ymd') <= date('Ymd', strtotime($details['scheduled_date']))): ?>
                                                                <td>
                                                                    <label hidden for="id-input-attended-on"></label>
                                                                    <input type="text" class="form-control pull-right datepicker form-details" placeholder="Attended On" id="id-input-attended-on"
                                                                           name="attended_on" value="<?= $details['attended_on'] ? date('Y-m-d', strtotime($details['attended_on'])) : $attended_on; ?>">
                                                                </td>
                                                                <td>
                                                                    <label hidden for="id-input-interviewed-by"></label>
                                                                    <select class="form-control select2 form-details" name="interviewed_by" data-placeholder="Interviewed By" id="id-input-interviewed-by">
                                                                        <option></option>
                                                                        <?php foreach ($users as $user) {
                                                                            $selected = $user['id'] == $details['interviewed_by_id'] ? 'selected': '';
                                                                            echo "<option value='{$user['id']}' $selected>{$user['name']}</option>";
                                                                        } ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <label hidden for="id-input-score"></label>
                                                                    <input type="text" class="form-control form-details" placeholder="Score" id="id-input-score" name="score" value="<?= $details['score']; ?>">
                                                                </td>
                                                                <td>
                                                                    <label hidden for="id-input-status"></label>
                                                                    <select class="form-control select2 form-details" name="status" data-placeholder="Status" id="id-input-status">
                                                                        <option <?= $details['status'] == 'Pending' ? 'selected' : '' ?> disabled>Pending</option>
                                                                        <option <?= $details['status'] == 'Promoted' ? 'selected' : '' ?>>Promoted</option>
                                                                        <option <?= $details['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                    </select>
                                                                </td>
                                                            <?php else: ?>
                                                                <td><?= $attended_on ?></td>
                                                                <td><?= $details['interviewed_by'] ?></td>
                                                                <td><?= $details['score'] ?></td>
                                                                <td><?php html_application_status($details['status']) ?></td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach ?>
                                                </tbody>

                                                <tfoot>
                                                <tr>
                                                    <th></th><th></th><th></th><th></th>
                                                    <th>Total: </th>
                                                    <th><?= _e_ctx('total_scores'); ?></th><th></th>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <?php
                                    $active_level = _e_ctx('current_level');
                                    $active_level = $active_level == null ? 1 : $active_level;

                                    $q_raw = $mysql
                                        ->where('job_application_id', get_context_value('id'))
                                        ->get('interview_questions');

                                    $questions = [];
                                    foreach ($q_raw as $question) {
                                        $questions[$question['interview_level_id']][] = $question;
                                    }

                                    $tab_levels = [];
                                    foreach($levels as $level) {
                                        $lv = $level;
                                        $lv['__active'] = $level['id'] == $active_level ? 'active' : '';
                                        $lv['__disabled'] = $level['id'] > $current_level ? 'disabled' : '';
                                        $lv['__href'] = $level['id'] > $current_level ? '#' : '#tab_' . $level['id'];

                                        $lv['questions'] = $questions[$level['id']] ?? [];
                                        $tab_levels[] = $lv;
                                    }
                                    ?>
                                    <?php foreach($tab_levels as $level) {?>
                                        <li class="<?= $level['__active'] ?> <?= $level['__disabled'] ?>">
                                            <a class="<?= $level['__disabled'] ?>" href="#tab_<?= $level['__href'] ?>" data-toggle="tab"><?= $level['name'] ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                                <div class="tab-content">
                                    <?php foreach($tab_levels as $level): ?>
                                    <?php $details = $level['details'] ?>
                                        <div class="tab-pane <?= $level['__active'] ?>" id="tab_<?= $level['id'] ?>">
                                            <div class="form-group">
                                                <label for="id-textarea-<?= $level['id'] ?>">Remarks</label>
                                                <textarea class="form-control interview-remarks"
                                                          id="id-textarea-<?= $level['id'] ?>"
                                                          data-level-id="<?= $level['id'] ?>"
                                                ><?= _e($details ? $details['remarks'] : '') ?></textarea>
                                            </div>

                                            <?php if(count($level['questions'])): ?>
                                                <h4 class="text-bold">Questions</h4>
                                            <?php endif; ?>

                                            <?php foreach($level['questions'] as $key => $question): ?>
                                                <div class="question-item">
                                                    <span class="label label-primary">Question #<?= $key + 1 ?></span>
                                                    <?php if($question['t_dna_match']): ?>
                                                        <span class="badge label-success" data-toggle="tooltip" title="Typing DNA Verified"><i class="fa fa-check"></i> <?= $question['t_dna_confidence'] ?>%</span>
                                                    <?php else: ?>
                                                        <span class="badge label-danger" data-toggle="tooltip" title="Typing DNA Not Verified"><i class="fa fa-times"></i> <?= $question['t_dna_confidence'] ?>%</span>
                                                    <?php endif; ?>

                                                    <p class="question text-bold"><?= $question['question'] ?></p>
                                                    <p class="answer"><?= $question['answer'] ? $question['answer'] : '-' ?></p>
                                                </div>
                                            <?php endforeach ?>
                                        </div>
                                        <!-- /.tab-pane -->
                                    <?php if ($level['id'] == $current_level) break; ?>
                                    <?php endforeach ?>
                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- nav-tabs-custom -->
                        </div>
                        <div class="col-md-4">
                            <?php $verification_types = ['Experience', 'Skills', 'Email', 'Mobile', 'Address', 'Person', 'Typing', 'All Other Details'] ?>
                            <?php
                            $verifications = $mysql
                                ->map('type')
                                ->where('job_application_id', get_context_value('id'))
                                ->get('candidate_verifications');

                            $vtc = count($verification_types);
                            $v = array_reduce($verifications, fn($c, $i) => $i['status'] == 'Verified' ? $c + 1 : $c, 0);

                            $percent = ($v * 100)/$vtc;

                            if ($percent < 80) {
                                $progress_color = "danger";
                            } elseif ($percent == 100) {
                                $progress_color = "success";
                            } else {
                                $progress_color = "warning";
                            }
                            ?>

                            <div class="box box-<?= $color ?>">
                                <div class="box-header">
                                    <h3 class="box-title">Manual Verification</h3>
                                </div>

                                <!-- /.box-header -->
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="progress progress-xs">
                                                <div class="progress-bar progress-bar-<?= $progress_color ?> progress-bar-striped" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent ?>%">
                                                    <span class="sr-only"><?= $percent ?>% Complete (<?= $progress_color ?>)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-bordered">
                                                <tbody>
                                                <?php foreach($verification_types as $type): ?>
                                                <?php $verification = $verifications[$type] ?? ['status' => 'Pending']; ?>
                                                    <tr>
                                                        <th><?= $type ?></th>
                                                        <td class="verification-type" data-toggle="modal" data-target="#vm-modal" data-type="<?= $type ?>" style="cursor: pointer"><?php html_application_status($verification['status']); ?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php html_footer() ?>
</div>
<!-- ./wrapper -->

<div id="modal-container">
    <div class="modal fade" tabindex="-1" role="dialog" id="vm-modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title">Verification - <b class="type"></b></h3>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <div class="controls">
                        <button type="button" class="btn btn-default btn_vm_cancel" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger  btn-vm-action" data-action="Invalid"><i class="fa fa-times"></i> Invalid</button>
                        <button type="button" class="btn btn-success btn-vm-action" data-action="Verified"><i class="fa fa-check"></i> Verify</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php scripts() ?>

<?php js_scripts(); ?>
<?php js_upload_form_with_files(); ?>

<?php if($action === 'new'): ?>
    <script>
        $('#form_job_application').on('submit', function (e) {
            e.preventDefault();

            showLoader();
            uploadFormWithFiles('api/job-applications.php?action=add', this, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                location.href = "job-application.php?id=" + response['data']['id'];
            }, 'json');
        });

        $('.content[data-action]').attr('data-action', 'new');
    </script>
<?php else: ?>
    <script>
        var context_id = '<?= _e_ctx('id') ?>'
        var context_current_level = '<?= _e_ctx('current_level') ?>'
    </script>
    <script>
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
        $('#form_job_application').on('submit', function (e) {
            e.preventDefault();

            showLoader();
            uploadFormWithFiles('api/job-applications.php?action=update', this, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                $.notify('Job Application Updated', { position:"bottom center", className: "success" });
                convertFormToView();
            }, 'json');
        });

        $('#btn-edit-job-application').on('click', function (e) {
            e.preventDefault();

            convertFormToEdit();
        })

        $('#btn-delete-job-application').on('click', function (e) {
            e.preventDefault();

            deleteJobApplication($(this).attr('data-id'), function () {
                location.href = "job-applications.php";
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
        $('.account-image .change-button').on('click', function (e) {
            $('[name=image]').trigger('click');
        });
    </script>

    <script>
        function convertFormToView() {
            $('#form_job_application').find('[name]').attr('readonly', 'readonly');
            $('.content[data-action]').attr('data-action', 'view')
        }

        function convertFormToEdit() {
            $('#form_job_application').find('[name]').removeAttr('readonly');
            $('.content[data-action]').attr('data-action', 'edit')
        }

        <?php if($action === 'edit'): ?>
        convertFormToEdit();
        <?php else: ?>
        convertFormToView();
        <?php endif; ?>
    </script>

    <script>
        $('.btn-change-status').on('click', function (e) {
            e.preventDefault();

            showLoader();
            $.post('api/job-applications.php?action=update-status', { id: context_id, status: $(this).attr('data-status') }, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                $.notify('Application Status Updated', { position:"bottom center", className: "success" });

                location.reload();
            }, 'json');
        })
    </script>

    <script>
        $('.form-details').on('change', function () {
            showLoader();
            var field = $(this).attr('name');
            var data = { id: context_id, level_id: context_current_level };

            data[field] = $(this).val();

            $.post('api/job-applications.php?action=update-interview-details', data, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                $.notify('Interview Details Updated', { position:"bottom center", className: "success" });

                if (field === 'scheduled_date' || field === 'status' || field === 'score') {
                    location.reload();
                }
            }, 'json');
        })
    </script>

    <script>
        $('.interview-remarks').on('change', function () {
            showLoader();
            var data = { id: context_id, level_id: $(this).attr('data-level-id'), remarks: $(this).val() };

            $.post('api/job-applications.php?action=update-interview-details', data, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                $.notify('Interview Remarks Updated', { position:"bottom center", className: "success" });
            }, 'json');
        })
    </script>

<?php if ($status != 'Pending'): ?>
    <template id="template-vm-typing">
        <div>
            <h4>Ask the candidate to type the following text.</h4>
            <div class="form-group">
                <label for="id_input_t_dna"><code class="t_dna_text"><?= TYPING_DNA_TEXT_VERIFICATION ?></code></label>
                <div class="input-group">
                    <span class="input-group-addon"><span class="glyphicon glyphicon-text-width"></span></span>
                    <input type="text" class="form-control" id="id_input_t_dna" placeholder="Enter the above text here" name="t_dna">
                </div>
                <span class="help-block"></span>
            </div>
        </div>
    </template>

    <template id="template-vm-person">
        <div style="display: flex">
            <?php if(get_context_value('avatar')): ?>
            <div class="vm-modal-image" style="margin: 3px">
                <img src="<?= get_application_profile_image(_e_ctx('avatar')) ?>" alt="avatar">
                <b class="text-center" style="display: block;">Profile Image</b>
            </div>

            <?php endif; ?>
            <?php foreach($levels as $level): ?>
                <?php $details = $level['details'] ?>

                <?php if($details && ($level['id'] <= $current_level) && $details['attended_by_photo']): ?>
                    <div class="vm-modal-image" style="margin: 3px">
                        <img src="uploads/interviews/<?= $details['attended_by_photo'] ?>" alt="avatar">
                        <b class="text-center" style="display: block;"><?= $level['name'] ?></b>
                    </div>
                <?php endif; ?>

            <?php endforeach ?>
        </div>
    </template>

    <script src="https://api.typingdna.com/scripts/typingdna.js"></script>

    <script>
        var modal = $('#vm-modal');
        let modal_callback = null;
        let typing_dna = null;

        let templateVMTyping = Template7.compile($('#template-vm-typing').html());
        let templateVMPerson = Template7.compile($('#template-vm-person').html());

        modal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget)
            const type = button.attr('data-type');

            modal.attr('data-type', type)
            modal.find('.modal-title .type').text(type);

            modal.find('.modal-body').html('');

            let text = null;
            if (type === 'Experience') {
                text = $('[name=experience]').val();
            }
            else if (type === 'Skills') {
                text = $('[name=skills]').val();
            }
            else if (type === 'Mobile') {
                text = $('[name=mobile]').val();
            }
            else if (type === 'Address') {
                text = $('[name=address]').val();
            }
            else if (type === 'All Other Details') {
                text = 'All Provided Details';
            }

            if (text) {
                modal.find('.modal-body').html(`<blockquote><p>${text}</p></blockquote>`);
                modal_callback = (type, action) => updateVerificationStatus(type, action, null);
                return;
            }

            if (type === 'Person') {
                modal.find('.modal-body').html(templateVMPerson({}));
                modal_callback = (type, action) => updateVerificationStatus(type, action, null);
                return;
            }

            if (type === 'Email') {
                sendEmailVerificationMail((email) => {
                    modal.find('.modal-body').html(`<blockquote><p>The verification code has been sent to <b>${email}</b>.<br><input type=text class=form-control placeholder="Verification Code" name=code></p></blockquote>`);
                    modal_callback = (type, action) => updateVerificationStatus(type, action, modal.find('[name=code]').val());
                }, _ => {
                    modal.modal('hide');
                });
                return;
            }

            if (type === 'Typing') {
                modal.find('.modal-body').html(templateVMTyping({}));
                typing_dna = new TypingDNA();
                typing_dna.addTarget('id_input_t_dna');
                modal_callback = (type, action) => {
                    let pattern = typing_dna.getTypingPattern({ type:0 });
                    typing_dna.stop();
                    updateVerificationStatus(type, action, pattern);
                }
                return;
            }
        });

        $('.btn-vm-action').on('click', function (e) {
            e.preventDefault();
            modal_callback(modal.attr('data-type'), $(this).attr('data-action'), modal)
        });

        function updateVerificationStatus(type, status, data = null) {
            let req = {job_application_id: context_id, type: type, status: status, data: data}

            showLoader();
            $.post('api/job-applications.php?action=verify-details', req, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    return;
                }

                modal.modal('hide');
                $.notify('Verification Details Updated', { position:"bottom center", className: "success" });
                location.reload();
            }, 'json');
        }

        function sendEmailVerificationMail(success, error) {
            showLoader();
            $.post('api/job-applications.php?action=email-send-code', { id: context_id }, function (response) {
                hideLoader();

                if (!response['success']) {
                    alertError(response['message']);
                    error();
                    return;
                }

                $.notify('Verification Code sent', { position:"bottom center", className: "success" });

                success(response['data']['email'])
            }, 'json');
        }
    </script>
<?php endif; ?>

<?php if($isTodayInterview): ?>
    <script>
        let socket = new WebSocket('<?= URL_WS_ROOT ?>?type=server');
        socket.onmessage = e => processMessage(JSON.parse(e.data));
        socket.onopen = _ => socket.send(JSON.stringify({ subject: 'get-client-details', id: context_id, to: 'server' }));

        function processMessage(data) {
            if (data['subject'] === 'client-details') {
                if(data['client']) {
                    $('#interview-panel').show();
                }
                else {
                    socket.close();
                }
                return;
            }

            if (data['subject'] === 'client-disconnected') {
                if (data['client']['id'] === context_id) {
                    $('#interview-panel').hide();
                }
                return;
            }

            if (data['subject'] === 'client-connected') {
                if (data['client']['id'] === context_id) {
                    $('#interview-panel').show();
                }
            }
        }

        $('#start-interview').on('click', function (e) {
            e.preventDefault();
            window.open('interview.php?id=<?= _e_ctx('id') ?>', 'Interview', 'height=800,width=1200');
        })
    </script>
<?php endif; ?>
<?php endif; ?>

</body>
</html>

