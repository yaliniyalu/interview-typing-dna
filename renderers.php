<?php


function render_textarea($title, $name, $value, $icon, $required = false, $readonly = false) { ?>
    <div class="form-group">
        <label for="id_textarea_<?= $name; ?>"><?= $title; ?></label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-<?= $icon ?>"></i></span>
            <textarea
                    class="form-control"
                    id="id_textarea_<?= $name; ?>"
                    placeholder="<?= $title; ?>"
                    name="<?= $name; ?>"
                <?= $readonly ? 'readonly': ''; ?>
                <?= $required ? 'required': ''; ?>><?php echo $value ?></textarea>
        </div>
    </div>
<?php }


function render_input_text($title, $name, $value, $icon, $required = false, $readonly = false) { ?>
    <div class="form-group">
        <label for="id_input_<?= $name; ?>"><?= $title; ?></label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-<?= $icon ?>"></i></span>
            <input type="text"
                   class="form-control"
                   id="id_input_<?= $name; ?>"
                   placeholder="<?= $title; ?>"
                   name="<?= $name; ?>"
                   value="<?= $value ?>"
                <?= $readonly ? 'readonly': ''; ?>
                <?= $required ? 'required': ''; ?>
            >
        </div>
    </div>
<?php }

function render_input_email($title, $name, $value, $icon, $required = false, $readonly = false) { ?>
    <div class="form-group">
        <label for="id_input_email_<?= $name; ?>"><?= $title; ?></label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-<?= $icon ?>"></i></span>
            <input type="email"
                   class="form-control"
                   id="id_input_email_<?= $name; ?>"
                   placeholder="<?= $title; ?>"
                   name="<?= $name; ?>"
                   value="<?= $value ?>"
                <?= $readonly ? 'readonly': ''; ?>
                <?= $required ? 'required': ''; ?>
            >
        </div>
    </div>
<?php }

function render_input_hidden($name, $value) { ?>
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
<?php }

function render_select_simple($title, $name, $select_options, $icon, $value = null, $required = false, $readonly = false, $class = '', $attr = '') { ?>
    <div class="form-group">
        <label for="id_select_<?= $name; ?>"><?= $title; ?></label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-<?= $icon ?>"></i></span>
            <select
                    class="form-control select2 <?= $class ?>"
                    id="id_select_<?= $name; ?>"
                    name="<?= $name; ?>"
                    data-placeholder="<?= $title; ?>"

                <?= $readonly ? 'readonly': ''; ?>
                <?= $required ? 'required': ''; ?>

                <?= $attr ?>
            >
                <?php
                if (is_array($select_options)) {
                    foreach ($select_options as $select_option) {
                        $selected = $value == $select_option ? 'selected': '';
                        echo "<option value='$select_option' $selected>$select_option</option>";
                    }
                }
                else {
                    $select_options();
                }
                ?>
            </select>
        </div>
    </div>
<?php }

function render_is_active($status) {
    $color = 'danger';
    $text = 'Inactive';

    if ($status) {
        $color = 'success';
        $text = 'Active';
    }

    echo "<span class='label label-{$color}'>$text</span>";
}

function render_input_date($title, $name, $value, $icon = 'calendar', $required = false, $readonly = false) { ?>
    <!-- Date -->
    <div class="form-group">
        <label for="id_input_date_<?= $name; ?>"><?= $title ?></label>

        <div class="input-group date">
            <div class="input-group-addon">
                <i class="fa fa-<?= $icon ?>"></i>
            </div>
            <input type="text" class="form-control pull-right datepicker" id="id_input_date_<?= $name; ?>" placeholder="<?= $title ?>"
                   name="<?= $name ?>"
                   value="<?= $value ?>"
                <?= $readonly ? 'readonly': ''; ?>
                <?= $required ? 'required': ''; ?>
            >
        </div>
        <!-- /.input group -->
    </div>
    <!-- /.form group -->
<?php }