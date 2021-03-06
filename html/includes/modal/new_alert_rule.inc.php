<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Copyright (c) 2018 Tony Murray <murraytony@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Alerting\QueryBuilderFilter;

if (is_admin()) {
    $filters = json_encode(new QueryBuilderFilter('alert'));

    ?>

    <div class="modal fade" id="create-alert" tabindex="-1" role="dialog"
         aria-labelledby="Create" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Create">Alert Rules :: <a href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">

                    <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                        <input type="hidden" name="device_id" id="device_id" value="<?php echo isset($device['device_id']) ? $device['device_id'] : -1; ?>">
                        <input type="hidden" name="device_name" id="device_name" value="<?php echo format_hostname($device); ?>">
                        <input type="hidden" name="rule_id" id="rule_id" value="">
                        <input type="hidden" name="type" id="type" value="alert-rules">
                        <input type="hidden" name="template_id" id="template_id" value="">
                        <input type="hidden" name="builder_json" id="builder_json" value="">
                        <div class='form-group'>
                            <label for='name' class='col-sm-3 control-label'>Rule name: </label>
                            <div class='col-sm-9'>
                                <input type='text' id='name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <div class="pull-right">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button"
                                                id="import-from" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="true">
                                            Import from
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="import-from"
                                            id="import-dropdown">
                                            <li><a href="#" name="import-query" id="import-query">SQL Query</a>
                                            </li>
                                            <li><a href="#" name="import-old-format" id="import-old-format">Old
                                                    Format</a></li>
                                            <li><a href="#" name="import-collection" id="import-collection">Collection</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div id="builder"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for='severity' class='col-sm-3 control-label'>Severity: </label>
                            <div class="col-sm-2">
                                <select name='severity' id='severity' class='form-control'>
                                    <option value='ok'>OK</option>
                                    <option value='warning'>Warning</option>
                                    <option value='critical' selected>Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-inline">
                            <label for='count' class='col-sm-3 control-label'>Max alerts: </label>
                            <div class="col-sm-2">
                                <input type='text' id='count' name='count' class='form-control' size="4" value="123">
                            </div>
                            <div class="col-sm-3">
                                <label for='delay' class='control-label' style="vertical-align: top;">Delay: </label>
                                <input type='text' id='delay' name='delay' class='form-control' size="4">
                            </div>
                            <div class="col-sm-4">
                                <label for='interval' class='control-label align-top' style="vertical-align: top;">Interval: </label>
                                <input type='text' id='interval' name='interval' class='form-control' size="4">
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='mute' class='col-sm-3 control-label'>Mute alerts: </label>
                            <div class='col-sm-2'>
                                <input type="checkbox" name="mute" id="mute">
                            </div>
                            <label for='invert' class='col-sm-3 control-label'>Invert match: </label>
                            <div class='col-sm-2'>
                                <input type='checkbox' name='invert' id='invert'>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for='maps' class='col-sm-3 control-label'data-toggle="tooltip" title="Restricts this alert rule to the selected devices or groups.">Map To: </label>
                            <div class="col-sm-9">
                                <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='proc' class='col-sm-3 control-label'>Procedure URL: </label>
                            <div class='col-sm-5'>
                                <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80'>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-3">
                                <button type="button" class="btn btn-success" id="btn-save" name="save-alert">
                                    Save Rule
                                </button>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>


    <script src="js/sql-parser.min.js"></script>
    <script src="js/query-builder.standalone.min.js"></script>
    <script>
        $('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
            $("[name$='_filter']").each(function () {
                $(this).select2({
                    dropdownParent: $("#create-alert"),
                    dropdownAutoWidth : true,
                    width: 'auto'
                });
            });
        }).on('ruleToSQL.queryBuilder.filter', function (e, rule) {
            if (rule.operator === 'regexp') {
                e.value += ' \'' + rule.value + '\'';
            }
        }).queryBuilder({
            plugins: [
                'bt-tooltip-errors'
                // 'not-group'
            ],

            filters: <?php echo $filters; ?>,
            operators: [
                'equal', 'not_equal', 'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null',
                {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'regex', nb_inputs: 1, multiple: false, apply_to: ['string']},
                {type: 'not_regex', nb_inputs: 1, multiple: false, apply_to: ['string']}
            ],
            lang: {
                operators: {
                    regexp: 'regex',
                    not_regex: 'not regex'
                }
            },
            sqlOperators: {
                regexp: {op: 'REGEXP'},
                not_regexp: {op: 'NOT REGEXP'}
            },
            sqlRuleOperator: {
                'REGEXP': function (v) {
                    return {val: v, op: 'regexp'};
                },
                'NOT REGEXP': function (v) {
                    return {val: v, op: 'not_regexp'};
                }
            }
        });

        $('#btn-save').on('click', function (e) {
            e.preventDefault();
            var result_json = $('#builder').queryBuilder('getRules');
            if (result_json !== null && result_json.valid) {
                $('#builder_json').val(JSON.stringify(result_json));
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: $('form.alerts-form').serializeArray(),
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            toastr.success(data.message);
                            $('#create-alert').modal('hide');
                            window.location.reload(); // FIXME: reload table
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error('Failed to process rule');
                    }
                });
            }
        });
        $('#import-query').on('click', function (e) {
            e.preventDefault();
            var sql_import = window.prompt("Enter your SQL query:");
            if (sql_import) {
                try {
                    $("#builder").queryBuilder("setRulesFromSQL", sql_import);
                } catch (e) {
                    alert('Your query could not be parsed');
                }
            }
        });

        $('#import-old-format').on('click', function (e) {
            e.preventDefault();
            var old_import = window.prompt("Enter your old alert rule:");
            if (old_import) {
                try {
                    old_import = old_import.replace(/&&/g, 'AND');
                    old_import = old_import.replace(/\|\|/g, 'OR');
                    old_import = old_import.replace(/%/g, '');
                    old_import = old_import.replace(/"/g, "'");
                    old_import = old_import.replace(/~/g, "REGEXP");
                    old_import = old_import.replace(/@/g, ".*");
                    $("#builder").queryBuilder("setRulesFromSQL", old_import);
                } catch (e) {
                    alert('Your query could not be parsed');
                }
            }
        });

        $('#import-collection').on('click', function (e) {
            e.preventDefault();
            $("#search_rule_modal").modal('show');
        });

        $('#create-alert').on('show.bs.modal', function(e) {
            //get data-id attribute of the clicked element
            var rule_id = $(e.relatedTarget).data('rule_id');
            $('#rule_id').val(rule_id);

            if (rule_id >= 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: { type: "parse-alert-rule", alert_id: rule_id },
                    dataType: "json",
                    success: function (data) {
                        loadRule(data);
                    }
                });
            } else {
                // new, reset form
                $("#builder").queryBuilder("reset");
                var $severity = $('#severity');
                $severity.val($severity.find("option[selected]").val());
                $("#mute").bootstrapSwitch('state', false);
                $("#invert").bootstrapSwitch('state', false);
                $(this).find("input[type=text]").val("");
                $('#count').val('-1');
                $('#delay').val('5 m');
                $('#interval').val('5 m');

                var $maps = $('#maps');
                $maps.empty();
                $maps.val(null).trigger('change');
                setRuleDevice() // pre-populate device in the maps if this is a per-device rule
            }
        });

        function loadRule(rule) {
            $('#name').val(rule.name);
            $('#proc').val(rule.proc);
            $('#builder').queryBuilder("setRules", rule.builder);
            $('#severity').val(rule.severity).trigger('change');

            var $maps = $('#maps');
            $maps.empty();
            $maps.val(null).trigger('change'); // clear
            if (rule.maps == null) {
                // collection rule
                setRuleDevice()
            } else {
                $.each(rule.maps, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $maps.append(option).trigger('change')
                });
            }

            if (rule.extra != null) {
                var extra = rule.extra;
                $('#count').val(extra.count);
                if ((extra.delay / 86400) >= 1) {
                    $('#delay').val(extra.delay / 86400 + ' d');
                } else if ((extra.delay / 3600) >= 1) {
                    $('#delay').val( extra.delay / 3600 + ' h');
                } else if ((extra.delay / 60) >= 1) {
                    $('#delay').val( extra.delay / 60 + ' m');
                } else {
                    $('#delay').val(extra.delay);
                }

                if ((extra.interval / 86400) >= 1) {
                    $('#interval').val(extra.interval / 86400 + ' d');
                } else if ((extra.interval / 3600) >= 1) {
                    $('#interval').val(extra.interval / 3600 + ' h');
                } else if ((extra.interval / 60) >= 1) {
                    $('#interval').val(extra.interval / 60 + ' m');
                } else {
                    $('#interval').val(extra.interval);
                }

                $("[name='mute']").bootstrapSwitch('state', extra.mute);
                $("[name='invert']").bootstrapSwitch('state', extra.invert);
            } else {
                $('#count').val('-1');
            }
        }

        function setRuleDevice() {
            // pre-populate device in the maps if this is a per-device rule
            var device_id = $('#device_id').val();
            if (device_id > 0) {
                var device_name = $('#device_name').val();
                var option = new Option(device_name, device_id, true, true);
                $('#maps').append(option).trigger('change')
            }
        }

        $("#maps").select2({
            width: '100%',
            placeholder: "Devices or Groups",
            ajax: {
                url: 'ajax_list.php',
                delay: 250,
                data: function (params) {
                    return {
                        type: 'devices_groups',
                        search: params.term
                    };
                }
            }
        });
    </script>
    <?php
}
