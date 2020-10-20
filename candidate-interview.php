<?php
include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

if (!isset($_GET['id'])) {
    html_error_page_candidate('Interview', 'Application not found');
    exit;
}

tryApplicationLogin();

$application = $mysql
    ->where('ja.code', $_GET['id'])
    ->join('job_posts jp', 'jp.id = ja.job_post_id')
    ->getOne('job_applications ja', 'ja.*, jp.title as job_post_title');

if (!$application) {
    html_error_page_candidate('Interview', 'Application not found');
    exit;
}

$interview_details = $mysql
    ->where('id.job_application_id', $application['id'])
    ->where('id.interview_level_id', $application['current_level'])
    ->join('interview_levels il', 'il.id = id.interview_level_id')
    ->join('users at', 'at.id = id.assigned_to')
    ->getOne('interview_details id', 'id.*, at.name as assigned_to');


if (!$interview_details['scheduled_date'] || date('Ymd') != date('Ymd', strtotime($interview_details['scheduled_date']))) {
    html_error_page_candidate('Interview', 'No interview scheduled');
    exit;
}

if ($interview_details['status'] != 'Pending') {
    html_error_page_candidate('Interview', 'No interview scheduled');
    exit;
}

if ($interview_details['attended_on']) {
    html_error_page_candidate('Interview', 'No interview scheduled');
    exit;
}

$_SESSION['interview_id'] = $interview_details['interview_level_id'];

?>
<!DOCTYPE html>
<html lang="en">
<?php head('Candidate Interview') ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php html_header_simple(); ?>

    <?php html_loader(); ?>

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
                <div class="col-md-6 col-sm-12 text-center">
                    <button type="button" class="btn btn-success" id="start-session">Start Interview</button>
                    <button type="button" class="btn btn-danger" id="stop-session" disabled>Stop Interview</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="video-container">
                        <video autoplay class="remote-video" id="remote-video"></video>
                        <video autoplay muted class="local-video" id="local-video"></video>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="question-pane" style="display: none">
                        <div class="box box-success">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="id-textarea-question">Question</label>
                                            <textarea class="form-control" id="id-textarea-question" name="question" readonly></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="id-textarea-answer">Answer</label>
                                            <textarea class="form-control" id="id-textarea-answer" placeholder="Enter answer here..." name="answer"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="question-id">
                            </div>
                            <div class="box-footer clearfix">
                                <button type="button" class="pull-right btn btn-success" id="send-answer">Send <i class="fa fa-arrow-circle-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="questions-history-pane" style="display: none">
                        <div class="box box-info">
                            <div class="box-header">
                                <h3 class="box-title">Previous Questions</h3>
                            </div>
                            <div class="box-body">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <?php html_footer() ?>
</div>
<!-- ./wrapper -->

<?php scripts() ?>
<?php js_scripts(); ?>

<script src="https://api.typingdna.com/scripts/typingdna.js"></script>
<script>
    let typing_dna = null;
    $(function () {
        typing_dna = new TypingDNA();
        typing_dna.stop();
        typing_dna.addTarget('id-textarea-answer');
    });
</script>

<script>
    let app_id = '<?= $application['id'] ?>';
    let app_name = '<?= $application['name'] ?>';
    let app_post = '<?= $application['job_post_title'] ?>';
    let assigned_to = '<?= $interview_details['assigned_to'] ?>';

    let from = app_id;
    let to   = null;

    let cameraStream = null;
    let screenStream = null;
    /** @type WebSocket */
    let socket = null;

    const remoteVideo = document.getElementById("remote-video");
    const localVideo  = document.getElementById("local-video");
    const startSession  = document.getElementById("start-session");
    const stopSession  = document.getElementById("stop-session");
</script>

<script>
    $(startSession).on('click', startInterview);
    $(stopSession).on('click', stopInterview)

    async function startInterview() {
        try {
            await connectSocket();

            cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            localVideo.srcObject = cameraStream;
            cameraStream.getTracks().forEach(track => peerConnection.addTrack(track, cameraStream));

            screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            screenStream.getTracks().forEach(track => peerConnection.addTrack(track, screenStream));
            requestInterview();
        } catch (e) {
            alertError("Error Occurred. Please reload the page and try again.");
            endSession();
        }
    }

    function connectSocket() {
        return new Promise((resolve, reject) => {
            if (socket) {
                resolve();
                return;
            }

            socket = new WebSocket('<?= URL_WS_ROOT ?>?type=client&id=' + from);
            socket.onmessage = e => processMessage(JSON.parse(e.data));
            socket.onopen = () => resolve();
            socket.onclose = () => socket = null;
            socket.onerror = () => reject();
        })
    }

    function requestInterview() {
        send({ from: from, to: null, subject: 'request-session', info: { id: app_id, name: app_name, post: app_post, assigned_to: assigned_to } });
        sessionStarted();
    }

    function stopInterview() {
        send({ from: from, to: to, subject: 'end-session' });
        endSession();
        location.reload();
    }

    function endSession() {
        if (socket)
            socket.close();

        sessionEnded();
    }

    function sessionStarted() {
        startSession.disabled = true;
        stopSession.disabled = false;
    }

    function sessionEnded() {
        startSession.disabled = false;
        stopSession.disabled = true;
    }

    async function processMessage(message) {
        switch (message['subject']) {
            /* case 'answer':
                 await setRemoteDescription(message['answer']);
                 break;*/

            case 'offer':
                to = message['from'];
                await setRemoteDescription(message['offer']);
                await answerConnection();
                break;

            case 'ice-candidate':
                await setIceCandidate(message['candidate'])
                break;

            case 'offer-rejected':
            case 'answer-rejected':
            case 'ice-candidate-rejected':
                alertError(message['reason']);
                endSession();
                break;

            case 'end-session':
                alertError('Connection closed by remote end');
                endSession();
                location.reload();
                break;

            case 'question':
                askQuestion(message['question']);
                break;
        }
    }

    async function setRemoteDescription(desc) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(desc));
    }

    async function setIceCandidate(candidate) {
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
    }

    async function offerConnection() {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(new RTCSessionDescription(offer));

        send({ from: from, to: to, subject: 'offer', offer: offer });
    }

    async function answerConnection() {
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(new RTCSessionDescription(answer));

        send({ from: from, to: to, subject: 'answer', answer: answer });
    }

    function send(data) {
        socket.send(JSON.stringify(data))
    }
</script>

<script>
    const { RTCPeerConnection, RTCSessionDescription } = window;

    let peerConnection =  new RTCPeerConnection({
        iceServers: [
            { urls: ["stun:stun.l.google.com:19302"] },
            { urls: "turn:35.238.249.118:3478", username: "yalini", credential: "yalini"}
        ]
    });

    peerConnection.ontrack = ({ streams: [stream] }) => {
        sessionStarted();
        remoteVideo.srcObject = stream;
    }

    peerConnection.onicecandidate = e => {
        if (e.candidate) {
            send({ from: from, to: to, subject: 'ice-candidate', candidate: e.candidate });
        }
    }

    peerConnection.onconnectionstatechange = _ => {
        switch(peerConnection.iceConnectionState) {
            case "closed":
            case "failed":
                endSession();
                break;
        }
    }

    peerConnection.onsignalingstatechange = _ => {
        switch(peerConnection.signalingState) {
            case "closed":
                endSession();
                break;
        }
    }
</script>

<script type="text/html" id="question-item-template">
    <div class="question-item" data-question-id="{{ id }}">
        <p class="question text-bold">{{ question }}</p>
        <p class="answer">{{ answer }}</p>
    </div>
</script>

<script>
    const questionPane = $('#question-pane')
    const questionsHistoryPane = $('#questions-history-pane')
    const questionsHistoryPaneBody = questionsHistoryPane.find('.box-body')
    const questionItemTemplate = Template7.compile($('#question-item-template').html());

    function askQuestion(question) {
        typing_dna.reset();
        typing_dna.start();

        questionPane.find('[name=question]').val(question.text)
        questionPane.find('[name=question-id]').val(question.id);
        questionPane.show();
    }

    $('#send-answer').on('click', e => {
        e.preventDefault();

        const question_id =  questionPane.find('[name=question-id]').val();
        const question =  questionPane.find('[name=question]').val();
        const answer = questionPane.find('[name=answer]').val();
        const typing_pattern = typing_dna.getTypingPattern({ type:0 });

        showLoader();
        $.post('api/candidate-interview.php?action=add-answer&id=<?= $_GET['id'] ?>', { question_id: question_id, answer: answer, t_dna_pattern: typing_pattern }, function (response) {
            hideLoader();

            if (!response['success']) {
                alertError(response['message']);
                return;
            }

            send({from: from, to: to, subject: 'question-answer', question: { id: question_id, answer: answer }});

            questionPane.find('[name=question]').val('')
            questionPane.find('[name=question-id]').val('');
            questionPane.find('[name=answer]').val('');
            questionPane.hide();

            typing_dna.stop();

            questionsHistoryPaneBody.prepend(questionItemTemplate({ question: question, answer: answer, id: question_id }));
            questionsHistoryPane.show();

            $.notify('Answer Sent', {position: "bottom center", className: "success"});
        }, 'json')
    })
</script>

</body>
</html>