<?php
include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

if (!isset($_GET['id'])) {
    html_error_page_candidate('Application Status', 'Application not found');
    exit;
}

tryApplicationLogin();

$application = $mysql
    ->where('ja.code', $_GET['id'])
    ->join('job_posts jp', 'jp.id = ja.job_post_id')
    ->getOne('job_applications ja', 'ja.*, jp.title as job_post_title');

if (!$application) {
    html_error_page_candidate('Application Status', 'Application not found');
    exit;
}

$levels = $mysql
    ->where('is_active', true)
    ->orderBy('id', 'asc')
    ->get('interview_levels');

$interview_details = $mysql
    ->map('interview_level_id')
    ->where('id.job_application_id', $application['id'])
    ->join('users at', 'at.id = id.assigned_to', 'left')
    ->join('users ib', 'ib.id = id.interviewed_by', 'left')
    ->get('interview_details id', null, 'id.*, ib.name as interviewed_by, at.name as assigned_to, ib.id as interviewed_by_id, at.id as assigned_to_id');


$current_details = $interview_details[(int) $application['current_level']] ??($interview_details[$application['current_level']] ?? null);

?>
<!DOCTYPE html>
<html lang="en">
<?php head('Application Status') ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php html_header_simple(); ?>

    <?php html_loader(); ?>

    <style>
        .table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
            border: 1px solid #acacac !important;
        }

        .table-1 th, .table-1 td {
            vertical-align: middle !important;
        }
    </style>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <table class="table table-bordered table-1">
                        <tbody>
                        <tr>
                            <th style="width: 250px">Registration Id</th>
                            <td style="width: 250px"><?= $application['id'] ?></td>
                            <td rowspan="4">
                                <div class="account-image" style="display: contents">
                                    <img src="<?php e(get_application_profile_image($application['avatar'])) ?>" alt="avatar" id="profile_image" width="100%">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?= $application['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Post</th>
                            <td><?= $application['job_post_title'] ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><?php html_application_status($application['status']); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <?php if($current_details && $current_details['scheduled_date'] && date('Ymd') == date('Ymd', strtotime($current_details['scheduled_date']))): ?>
                    <div class="col-md-6 col-sm-12 text-center">
                        <a href="candidate-interview.php?id=<?= $_GET['id'] ?>" target="_blank" class="btn btn-info">Start Interview</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th style="width: 100px">Name</th>
                            <td><?= $application['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Dob</th>
                            <td><?= $application['dob'] ?></td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td><?= $application['gender'] ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= $application['email'] ?></td>
                        </tr>
                        <tr>
                            <th>Mobile</th>
                            <td><?= $application['mobile'] ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td class="pre-line"><?= $application['address'] ?></td>
                        </tr>
                        <tr>
                            <th>Skills</th>
                            <td class="pre-line"><?= $application['skills'] ?></td>
                        </tr>
                        <tr>
                            <th>Experience</th>
                            <td class="pre-line"><?= $application['experience'] ?></td>
                        </tr>
                        <tr>
                            <th>Other Details</th>
                            <td class="pre-line"><?= $application['details'] ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
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
                        <?php $current_level = $application['current_level']; $current_level = $current_level == null ? 1000: $current_level; ?>
                        <?php foreach($levels as $level): ?>
                            <?php $details = $interview_details[(int) $level['id']] ??($interview_details[$level['id']] ?? null); ?>

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
                            <tr>
                                <td class="text-success text-bold"><?= $level['name'] ?></td>
                                <td><?= $scheduled_on ?></td>
                                <td><?= $details['assigned_to'] ?></td>
                                <td><?= $attended_on ? $attended_on : "<span class='label label-warning'>Pending</span>" ?></td>
                                <td><?= $details['interviewed_by'] ?></td>
                                <td><?= $details['score'] ?></td>
                                <td><?php html_application_status($details['status']) ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach ?>
                        </tbody>

                        <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th>Total: </th>
                            <th><?= $application['total_scores']; ?></th><th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <?php html_footer() ?>
</div>
<!-- ./wrapper -->

<?php scripts() ?>

<script>

</script>

</body>
</html>