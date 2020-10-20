<?php
include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

tryLogin();

if (!isset($_GET['id'])) {
    html_error_page_candidate('Interview', 'Application not found');
    exit;
}

$application = $mysql
    ->where('ja.id', $_GET['id'])
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
    ->getOne('interview_details id', 'id.*');


if (!$interview_details['scheduled_date'] || date('Ymd') != date('Ymd', strtotime($interview_details['scheduled_date']))) {
    html_error_page_candidate('Interview', 'No interview scheduled');
    exit;
}

if ($interview_details['status'] != 'Pending') {
    html_error_page_candidate('Interview', 'No interview scheduled');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<?php head('Interview') ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php html_header_simple(); ?>

    <?php html_loader(); ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-12 col-sm-12 text-center">
                    <button type="button" class="btn btn-success" id="start-session" disabled>Start Interview</button>
                    <button type="button" class="btn btn-danger"  id="stop-session"  disabled>Stop Interview</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="video-container">
                        <video autoplay class="remote-video" id="remote-video"></video>
                        <video autoplay muted class="local-video" id="local-video"></video>
                        <canvas id="remote-picture" class="display-none"></canvas>
                    </div>
                    <div class="capture-buttons display-none">
                        <button type="button" class="btn btn-success btn-capture" id="btn-take-picture">Take Picture</button>
                        <button type="button" class="btn btn-warning btn-capture-confirm display-none" id="btn-retake">Retake</button>
                        <button type="button" class="btn btn-success btn-capture-confirm display-none" id="btn-confirm-take">Confirm</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="video-container">
                        <video autoplay class="remote-screen" id="remote-screen"></video>
                    </div>
                </div>
            </div>
            <div class="row display-none" id="ask-question-pane">
                <div class="col-md-6">
                    <div class="box box-success">
                        <div class="box-header">
                            <h3 class="box-title">Ask Interview Questions</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="id-textarea-question">Question</label>
                                        <textarea class="form-control" id="id-textarea-question" placeholder="Enter question here..." name="question"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer clearfix">
                            <button type="button" class="pull-right btn btn-success" id="send-question">Send <i class="fa fa-arrow-circle-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-info" id="questions-pane" style="display: none;">
                        <div class="box-header">
                            <h3 class="box-title">Questions History</h3>
                        </div>
                        <div class="box-body">

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

<script>
    let app_id = '<?= $application['id'] ?>';
    let app_name = '<?= $application['name'] ?>';
    let app_post = '<?= $application['job_post_title'] ?>';

    let interview_id = '<?= $interview_details['interview_level_id'] ?>'

    let from = '<?= $_SESSION['user_id'] ?>';
    let to   = app_id;

    let cameraStream = null;
    let screenStream = null;
    /** @type WebSocket */
    let socket = null;
    /** @type RTCPeerConnection */
    let peerConnection = null;

    const remoteVideo = document.getElementById("remote-video");
    const remoteScreen = document.getElementById("remote-screen");
    const localVideo  = document.getElementById("local-video");
    const startSession  = document.getElementById("start-session");
    const stopSession  = document.getElementById("stop-session");
    const canvas = document.getElementById("remote-picture");
    const askQuestionPane = document.getElementById("ask-question-pane");

    const ctx = canvas.getContext('2d');
</script>

<script>
    const { RTCPeerConnection, RTCSessionDescription } = window;

    async function createPeerConnection() {
        peerConnection =  new RTCPeerConnection({
            iceServers: [
                { urls: ["stun:stun.l.google.com:19302"] },
                { urls: "turn:35.238.249.118:3478", username: "yalini", credential: "yalini"}
            ]
        });

        let gotVideo = false;
        peerConnection.ontrack = ({ streams: [stream] }) => {
            $('.capture-buttons').show();

            if (!gotVideo) {
                gotVideo = true;
                remoteVideo.srcObject = stream;
            }
            else {
                remoteScreen.srcObject = stream;
            }
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
    }
</script>

<script>
    $(startSession).on('click', startInterview);
    $(stopSession).on('click', stopInterview)

    startSession.disabled = false;

    createPeerConnection();
    connectCameraStream();

    async function startInterview() {
        try {

            await connectSocket();
            await connectCameraStream();
            await offerConnection();
        } catch (e) {
            $.alert("You must allow the camera and screen sharing" + e.toString());
            endSession();
        }
    }

    async function connectCameraStream() {
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = cameraStream;
        cameraStream.getTracks().forEach(track => peerConnection.addTrack(track, cameraStream));
    }

    function connectSocket() {
        return new Promise((resolve, reject) => {
            if (socket) {
                resolve();
                return;
            }

            socket = new WebSocket('<?= URL_WS_ROOT ?>?type=server&id=' + from);
            socket.onmessage = e => processMessage(JSON.parse(e.data));
            socket.onopen = () =>  {
                resolve()
            }
            socket.onclose = () => socket = null;
            socket.onerror = () => reject();
        })
    }

    function stopInterview() {
        showLoader();
        $.post('api/interviews.php?action=finish-interview', { id: app_id, interview_id: interview_id }, function (response) {
            hideLoader();

            if (!response['success']) {
                alertError(response['message']);
                return;
            }

            $.notify('Interview Finished', { position:"bottom center", className: "success" });

            send({ from: from, to: to, subject: 'end-session' });
            endSession();

            window.close();
        }, 'json');
    }

    function endSession() {
        if (peerConnection)
            peerConnection.close();
    }

    function sessionStarted() {
        startSession.disabled = true;
        stopSession.disabled = false;

        $(askQuestionPane).show();
    }

    async function processMessage(message) {
        switch (message['subject']) {
            case 'answer':
                await setRemoteDescription(message['answer']);
                sessionStarted();
                break;

            /*case 'offer':
                await setRemoteDescription(message['offer']);
                await answerConnection();
                break;*/

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
                break;

            case 'question-answer':
                addAnswer(message['question'])
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
    $('#btn-take-picture').on('click', function () {
        ctx.drawImage(remoteVideo, 0, 0, canvas.width, canvas.height);

        $('.btn-capture-confirm').show();
        $('.btn-capture').hide();
        $(remoteVideo).hide();
        $(canvas).show();
    });

    $('#btn-confirm-take').on('click', function () {
        $('.btn-capture-confirm').hide();
        $('.btn-capture').hide();
        $(remoteVideo).show();
        $(canvas).hide();

        canvas.toBlob(blob => {
            let form_data = new FormData();
            form_data.append('id', app_id);
            form_data.append('interview_id', interview_id);
            form_data.append('image', blob);

            showLoader();
            $.ajax({
                url: 'api/interviews.php?action=save-candidate-photo',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: (response) => {
                    hideLoader();
                    if (!response['success']) {
                        $('.btn-capture').show();
                        alertError(response['message']);
                        return;
                    }

                    $.notify('Image updated', { position: "bottom center", className: "success" });
                }
            });
        }, 'image/jpeg');
    });

    $('#btn-retake').on('click', function () {
        $('.btn-capture-confirm').hide();
        $('.btn-capture').show();

        $(remoteVideo).show();
        $(canvas).hide();
    });
</script>

<script type="text/html" id="question-item-template">
    <div class="question-item" data-question-id="{{ id }}">
        <p class="question text-bold">{{ question }}</p>
        <p class="answer">{{ answer }}</p>
    </div>
</script>

<script>
    const questionItemTemplate = Template7.compile($('#question-item-template').html());
    const questionPane = $('#questions-pane');
    const questionPaneBody = questionPane.find('.box-body');

    $('#send-question').on('click', function () {
        const question = $('#id-textarea-question').val();

        showLoader();
        $.post('api/interviews.php?action=add-question', { job_application_id: app_id, interview_level_id: interview_id, question: question }, function (response) {
            hideLoader();

            if (!response['success']) {
                alertError(response['message']);
                return;
            }

            send({ from: from, to: to, subject: 'question', question: { id: response['data']['id'], text: question }});

            $('#id-textarea-question').val('');

            questionPaneBody.prepend(questionItemTemplate({ question: question, answer: '', id: response['data']['id']}));
            questionPane.show();

            $.notify('Question Sent', { position:"bottom center", className: "success" });

        }, 'json');
    });

    function addAnswer(question) {
        questionPaneBody.find(`[data-question-id=${question['id']}]`).find('.answer').html(question['answer']);
    }
</script>

</body>
</html>