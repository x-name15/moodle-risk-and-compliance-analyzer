/* global Chart */
/**
 * MRCA Dashboard AMD module.
 *
 * Handles: Chart rendering, pagination, whitelist AJAX, report sending.
 *
 * @module     local_mrca/dashboard
 * @package
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(
    ['jquery'],
    function ($) {

        return {
            /**
             * Initializes the dashboard.
             *
             * @param {Object} chartData Risk distribution chart data.
             * @param {Object} trendData Risk trend chart data (may be null).
             * @param {string} scanUrl Base URL for actions.
             * @param {string} sesskey Session key.
             */
            init: function (chartData, trendData, scanUrl, sesskey) {

                // ===== CHARTS =====
                try {
                    if (typeof Chart !== 'undefined') {
                        // Risk Distribution doughnut.
                        var riskCanvas = document.getElementById('mrca-risk-chart');
                        if (riskCanvas && chartData && chartData.labels) {
                            new Chart(riskCanvas, {
                                type: 'doughnut',
                                data: {
                                    labels: chartData.labels,
                                    datasets: [{
                                        data: chartData.values,
                                        backgroundColor: chartData.colors,
                                        borderWidth: 2,
                                        borderColor: '#fff'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: 'bottom' }
                                    }
                                }
                            });
                        }

                        // Risk Trend line chart.
                        var trendCanvas = document.getElementById('mrca-trend-chart');
                        if (trendCanvas && trendData && trendData.labels) {
                            new Chart(trendCanvas, {
                                type: 'line',
                                data: {
                                    labels: trendData.labels,
                                    datasets: [{
                                        label: 'Site Risk Index',
                                        data: trendData.values,
                                        borderColor: '#007bff',
                                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                        fill: true,
                                        tension: 0.3,
                                        pointBackgroundColor: '#007bff',
                                        pointRadius: 5,
                                        pointHoverRadius: 7
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            min: 0,
                                            max: 100,
                                            title: { display: true, text: 'Risk Index' }
                                        }
                                    },
                                    plugins: {
                                        legend: { display: false }
                                    }
                                }
                            });
                        }
                    }
                } catch (e) {
                    // eslint-disable-next-line no-console
                    console.warn('MRCA: Chart rendering failed', e);
                }

                // ===== PAGINATION (reusable) =====
                // Excludes rows with .mrca-pii-row class from page count.
                var paginateTable = function (tableId, pagerId, perPage) {
                    var $tbl = $(tableId);
                    var $dataRows = $tbl.find('tbody tr').not('.mrca-pii-row');
                    var $piiRows = $tbl.find('tbody tr.mrca-pii-row');
                    var pages = Math.ceil($dataRows.length / perPage);
                    var cur = 1;

                    if ($dataRows.length <= perPage) {
                        return;
                    }

                    var show = function (page) {
                        // Hide all data rows and PII sub-rows.
                        $dataRows.hide();
                        $piiRows.hide();
                        // Show only the current page of data rows.
                        $dataRows.slice((page - 1) * perPage, page * perPage).show();
                        cur = page;
                        render();
                    };

                    var render = function () {
                        var $pg = $(pagerId);
                        $pg.empty();

                        if (pages <= 1) {
                            return;
                        }

                        var prevDis = cur === 1 ? ' disabled' : '';
                        $pg.append('<li class="page-item' + prevDis + '">' +
                            '<a class="page-link" href="#" data-page="' + (cur - 1) + '">«</a></li>');

                        for (var i = 1; i <= pages; i++) {
                            var act = i === cur ? ' active' : '';
                            $pg.append('<li class="page-item' + act + '">' +
                                '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
                        }

                        var nextDis = cur === pages ? ' disabled' : '';
                        $pg.append('<li class="page-item' + nextDis + '">' +
                            '<a class="page-link" href="#" data-page="' + (cur + 1) + '">»</a></li>');
                    };

                    show(1);

                    $(document).on('click', pagerId + ' a.page-link', function (e) {
                        e.preventDefault();
                        var page = parseInt($(this).data('page'), 10);
                        if (page >= 1 && page <= pages) {
                            show(page);
                        }
                    });
                };

                // Plugin risk table — 20 per page.
                paginateTable('#mrca-plugin-table', '#mrca-pagination', 20);

                // Dependency audit table — 10 per page.
                paginateTable('#mrca-dep-table', '#mrca-dep-pagination', 10);

                // ===== PII FIELD TOGGLE =====
                $(document).on('click', '.mrca-toggle-pii', function (e) {
                    e.preventDefault();
                    var target = $($(this).data('target'));
                    target.toggle();
                    // Toggle icon.
                    var icon = $(this).find('i');
                    icon.toggleClass('fa-database fa-chevron-up');
                });

                // ===== WHITELIST ADD =====
                $(document).on('click', '.mrca-whitelist-add', function () {
                    var btn = $(this);
                    var comp = btn.data('component');
                    var tbl = btn.data('table');
                    var fld = btn.data('field');
                    btn.prop('disabled', true).find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
                    $.post(scanUrl, {
                        action: 'whitelist_add',
                        component: comp,
                        table: tbl,
                        field: fld,
                        sesskey: sesskey
                    }).done(function (response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            btn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check text-success');
                            btn.closest('.mrca-pii-field').fadeOut(500, function () {
                                $(this).remove();
                            });
                        } else {
                            btn.prop('disabled', false).find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
                        }
                    }).fail(function (xhr) {
                        // eslint-disable-next-line no-console
                        console.warn('MRCA: Whitelist add failed', xhr.status, xhr.responseText);
                        btn.prop('disabled', false).find('i').removeClass('fa-spinner fa-spin').addClass('fa-times text-danger');
                    });
                });

                // ===== WHITELIST REMOVE =====
                $(document).on('click', '.mrca-whitelist-remove', function () {
                    var btn = $(this);
                    var id = btn.data('id');
                    btn.prop('disabled', true);
                    $.post(scanUrl, {
                        action: 'whitelist_remove',
                        id: id,
                        sesskey: sesskey
                    }).done(function (response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            btn.closest('.d-flex').fadeOut(300, function () {
                                $(this).remove();
                            });
                        }
                    }).fail(function (xhr) {
                        // eslint-disable-next-line no-console
                        console.warn('MRCA: Whitelist remove failed', xhr.status, xhr.responseText);
                        btn.prop('disabled', false);
                    });
                });

                // ===== SEND SINGLE REPORT =====
                $(document).on('click', '.mrca-send-report', function () {
                    var btn = $(this);
                    var resultid = btn.data('resultid');
                    btn.prop('disabled', true).find('i').removeClass('fa-paper-plane').addClass('fa-spinner fa-spin');
                    $.post(scanUrl, {
                        action: 'send_single_report',
                        resultid: resultid,
                        sesskey: sesskey
                    }).done(function (response) {
                        var data = JSON.parse(response);
                        btn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-paper-plane');
                        if (data.success) {
                            btn.find('i').addClass('text-success');
                            btn.prop('disabled', false);
                        } else {
                            btn.find('i').addClass('text-danger');
                            btn.prop('disabled', false);
                        }
                    }).fail(function (xhr) {
                        // eslint-disable-next-line no-console
                        console.warn('MRCA: Send report failed', xhr.status, xhr.responseText);
                        btn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-paper-plane text-danger');
                        btn.prop('disabled', false);
                    });
                });
            }
        };
    }
);
