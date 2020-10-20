<?php

include_once "db.php";
include_once "menu.php";

tryLogin();

global $mysql;
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Interviews') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar('interviews') ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Interviews
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="active"><a href="#">Interviews</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Interviews</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="dt_interviews" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th data-data="id">ID</th>
                                    <th data-data="name">Name</th>
                                    <th data-data="post">Post</th>
                                    <th data-data="assigned_to">Assigned To</th>
                                    <th data-data="_action" data-orderable="false" class="td-action_">Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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

<script type="text/html" id="action_column_template">
    <a href="job-application.php?id={{ id }}" class="view_application icon-view btn-action-lg" data-id="{{ id }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
</script>

<script>
    var table = $('#dt_interviews').DataTable({
        dom: 'frtip',
        responsive: true,

        'paging'      : true,
        'lengthChange': false,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : true,

        order: [],

        columnDefs: [
            {
                render: function ( data, type, row ) {
                    return action_column_template(row);
                },
                targets: 'td-action_'
            }
        ],

        createdRow: function( row, data, dataIndex ) {
            $(row).attr('data-tr-id', data['id']);
        },

        processing: true
    });

    table.on( 'draw', function () {
        initTooltips();
    });
</script>

<script>
    var action_column_template = Template7.compile($('#action_column_template').html());
</script>

<script>
    let socket = new WebSocket('<?= URL_WS_ROOT ?>?type=server');
    socket.onmessage = e => processMessage(JSON.parse(e.data));
    socket.onopen = _ => socket.send(JSON.stringify({ subject: 'get-active-clients', to: 'server' }));
    socket.onclose = _ => alertError("Socket connection closed. Please reload this page to connect again.")

    function processMessage(data) {
        switch (data['subject']) {
            case 'client-connected':
                table.row.add(data['client']);
                break;

            case 'client-disconnected':
            case 'client-session-started':
            case 'client-session-finished':
                table.row($('[data-tr-id=' + data['client']['id'] + ']')).remove();
                break;

            case 'active-clients':
                const row = table.row;
                data['clients'].forEach(v => row.add(v))
        }
        table.draw()
    }
</script>

</body>
</html>

