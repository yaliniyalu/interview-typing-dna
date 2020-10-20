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
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Accounts') ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php html_header() ?>

    <?php html_side_bar('accounts') ?>

    <?php html_loader() ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Accounts
            </h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="active"><a href="#">Accounts</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-xs-12">

                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Accounts</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="dt_accounts" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th data-data="id">ID</th>
<!--                                    <th data-data="profile_image" data-orderable="false" class="td-profile-image">Avatar</th>-->
<!--                                    <th data-data="name">Name</th>-->
                                    <th data-data="name">First Name</th>
                                    <th data-data="type" class="td-type">Type</th>
                                    <th data-data="email">Email</th>
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
<?php js_upload_form_with_files(); ?>


<div id="modal-container"></div>

<script type="text/html" id="modal-account-details">
    <div class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title">Account Details - <b>{{#if id}}#{{ id }}{{else}}New{{/if}} {{#js_if "this.is_active === false || this.is_active === 0" }}<i class="text-danger">(Deleted)</i>{{/js_if}}</b></h3>
                </div>
                <div class="modal-body">
                    <form role="form" id="accounts-form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="id_form_1_name">Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                                    <input type="text" class="form-control" id="id_form_1_name" placeholder="Name" name="name" value="{{ name }}" required readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="id_form_1_type">Type</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-list-alt"></i></span>
                                                    <select class="form-control select2" id="id_form_1_type" name="type" readonly data-placeholder="Type">
                                                        {{ render_select_options 'types' type }}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="id_form_1_email">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-at"></i></span>
                                                    <input type="email" class="form-control" id="id_form_1_email" placeholder="Email" name="email" value="{{ email }}" required readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="id_form_1_password">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                    <input type="text" class="form-control" id="id_form_1_password" placeholder="Password" name="password" required readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="account-image">
                                        <img src="{{#if avatar }} uploads/images/{{avatar}} {{else}}assets/images/avatar.jpg{{/if}}" alt="avatar" id="profile_image">
                                        <p class="change-button">Change</p>
                                    </div>

                                    <input type="file" name="image" hidden style="display: none" onchange="profile_image.src=window.URL.createObjectURL(this.files[0])">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="{{ id }}">
                        <!-- /.box-body -->
                    </form>
                </div>
                {{#js_if "!(this.is_active === false || this.is_active === 0)" }}
                <div class="modal-footer">
                    <div class="controls">
                        <div class="controls-view">
                            <button type="button" class="btn btn-primary btn_modal_edit"><i class="fa fa-edit"></i> Edit</button>
                            <button type="button" class="btn btn-danger btn_modal_delete" data-id="{{ id }}"><i class="fa fa-trash"></i> Delete</button>
                        </div>
                        <div class="controls-edit">
                            <!--                            <button type="button" class="btn btn-primary btn_modal_view" data-id="{{ id }}"><i class="fa fa-eye"></i> Cancel</button>-->
                            <button type="button" class="btn btn-success btn_modal_update" data-id="{{ id }}"><i class="fa fa-save"></i> Save</button>
                        </div>
                        <div class="controls-add">
                            <button type="button" class="btn btn-success btn_modal_save" data-id="{{ id }}"><i class="fa fa-save"></i> Add</button>
                        </div>
                    </div>
                </div>
                {{/js_if}}
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="action_column_template">
    <a href="#" class="view_account icon-view btn-action-lg" data-id="{{ id }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
    <a href="#" class="edit_account icon-edit btn-action-lg" data-id="{{ id }}">
        <i class="fa fa-pencil" aria-hidden="true"></i>
    </a>
    <a href="#" class="delete_account icon-delete" data-id="{{ id }}">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </a>
</script>

<script type="text/html" id="type_column_template">
    {{#js_if "this.type === 'Employee'"}}
    <span class="label label-success">{{ type }}</span>
    {{else}}
    <span class="label label-danger">{{ type }}</span>
    {{/js_if}}
</script>

<script type="text/html" id="profile_image_column_template">
    {{#if profile_image }}
    <img class="img-circle" src="uploads/images/{{profile_image}}" alt="image">
    {{else}}
    <img class="img-circle" src="assets/images/avatar.png" alt="image">
    {{/if}}
</script>

<script>
    var types = [
        'Employee',
        'Admin'
    ];
</script>

<script>
    var table = $('#dt_accounts').DataTable({
        dom: 'Bfrtip',
        responsive: true,

        'paging'      : true,
        'lengthChange': false,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : true,

        "order": [],

        columnDefs: [
            {
                className: "img-circle-td",
                render: function ( data, type, row ) {
                    return profile_image_column_template(row);
                },
                targets: 'td-profile-image'
            },
            {
                render: function ( data, type, row ) {
                    return type_column_template(row);
                },
                targets: 'td-type'
            },
            {
                render: function ( data, type, row ) {
                    return action_column_template(row);
                },
                targets: 'td-action_'
            }
        ],

        processing: true,
        ajax: {
            url: 'api/accounts.php?action=get',
            dataSrc: function (json) {
                return json.data;
            }
        },

        buttons: [
            {
                text: '<i class="fa fa-plus"></i> Add Account',
                className: 'btn btn-success',
                action: function ( e, dt, node, config ) {
                    e.preventDefault();
                    setAccountModal({}, 'add');
                }
            }
        ]
    });

    table.on( 'draw', function () {
        initTooltips();
    });
</script>

<script>
    var account_template = Template7.compile($('#modal-account-details').html());
    var action_column_template = Template7.compile($('#action_column_template').html());
    var type_column_template = Template7.compile($('#type_column_template').html());
    var profile_image_column_template = Template7.compile($('#profile_image_column_template').html());

    var container = $('#modal-container');

    function setAccountModal(data, mode) {
        var html = account_template(data);
        container.html(html);
        initSelect2();
        container.find('.modal').attr('data-id', data.id ? data.id : '').modal('show');

        setAccountModalMode(mode);
    }

    function setAccountModalMode(mode) {
        container.attr('data-action', mode);

        if (mode === 'add') {
            container.find('input, select, textarea').not('.readonly').removeAttr('readonly');
            container.find('.controls').addClass('add').removeClass('edit').removeClass('view');
        }
        else if (mode === 'edit') {
            container.find('input, select, textarea').not('.readonly').removeAttr('readonly');
            container.find('.controls').addClass('edit').removeClass('add').removeClass('view');
        }
        else if (mode === 'view') {
            container.find('input, select, textarea').not('.readonly').attr('readonly', 'readonly');
            container.find('.controls').addClass('view').removeClass('edit').removeClass('add');
        }
    }

    function viewAccount(id, mode) {
        showLoader();
        $.get('api/accounts.php?action=get', { id: id }, function (data) {
            hideLoader();

            if (!(data['success'] && data['data'].length)) {
                alertError('Unable to load data');
                return;
            }

            setAccountModal(data['data'][0], mode)
        }, 'json');
    }

    function deleteAccount(id, success) {
        $.confirm({
            title: 'Are You Sure?',
            content: 'Do you want to delete the account?',
            backgroundDismiss: true,
            buttons: {
                yes: {
                    btnClass: 'btn-red',
                    action: function () {
                        $.post('api/accounts.php?action=delete', { id: id }, function (data) {
                            if (data['success']) {
                                $.notify('Account Deleted', { position:"bottom center", className: "success" });

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
    var dt_accounts = $('#dt_accounts');

    dt_accounts.on('click', '.delete_account', function (e) {
        e.preventDefault();

        var row = this;
        deleteAccount($(this).attr('data-id'), function () {
            table.ajax.reload();
        });
    });

    dt_accounts.on('click', '.view_account', function (e) {
        e.preventDefault();
        viewAccount($(this).attr('data-id'), 'view')
    });

    dt_accounts.on('click', '.edit_account', function (e) {
        e.preventDefault();
        viewAccount($(this).attr('data-id'), 'edit')
    });
</script>

<script>
    container.on('click', '.btn_modal_edit', function (e) {
        e.preventDefault();
        setAccountModalMode('edit');
    });

    container.on('click', '.btn_modal_update', function (e) {
        e.preventDefault();

        uploadFormWithFiles('api/accounts.php?action=update', $('#accounts-form')[0], function (data) {
            if (!data['success']) {
                alertError(data['message']);
                return;
            }

            $.notify('Account Updated', { position:"bottom center", className: "success" });

            table.ajax.reload();
            setAccountModalMode('view');
        });
    });

    container.on('click', '.btn_modal_save', function (e) {
        e.preventDefault();

        uploadFormWithFiles('api/accounts.php?action=add', $('#accounts-form')[0], function (data) {
            if (!data['success']) {
                alertError(data['message']);
                return;
            }

            table.ajax.reload();
            container.find('.modal').modal('hide');
        }, 'json');
    });

    container.on('click', '.btn_modal_delete', function (e) {
        e.preventDefault();

        deleteAccount($(this).attr('data-id'), function () {
            table.ajax.reload();
            container.find('.modal').modal('hide');
        });
    });

    container.on('click', '.btn_modal_view', function (e) {
        e.preventDefault();
        setAccountModalMode('view');
    })
</script>

<script>
    container.on('click', '.account-image .change-button', function (e) {
        $('[name=image]').trigger('click');
    });
</script>

</body>
</html>

