/**
 * Created by Hideaki Oguchi on 2016/06/23.
 */
(function ($) {
    $(function () {
        // Calendar
        if ($('table.calendar_table')[0]) {
            $("#up").on('click', function () {
                moveWeek(-7);
            });

            $("#down").on('click', function () {
                moveWeek(7);
            });

            var currentYear = $('#year').val();
            var currentMonth = $('#month').val();
            var currentDay = $('#day').val();

            ajax_request_calendar_view(currentYear, currentMonth, currentDay, notify_calendar_view);
        }
    });

    function appendDate(year, month, day, addDays) {

        var returnDate = new Date(year, month - 1, day);

        var addSec = addDays * 24 * 60 * 60 * 1000;
        var newSec = returnDate.getTime() + addSec;

        returnDate.setTime(newSec);

        return returnDate;
    }

    function moveWeek(offset) {

        var yearObject = $("#year");
        var monthObject = $("#month");
        var dayObject = $("#day");

        var year = yearObject.val();
        var month = monthObject.val();
        var day = dayObject.val();

        var addDate = appendDate(year, month, day, offset);

        yearObject.val(addDate.getFullYear());
        monthObject.val(addDate.getMonth() + 1);
        dayObject.val(addDate.getDate());

        ajax_request_calendar_view(addDate.getFullYear(), addDate.getMonth() + 1, addDate.getDate(), notify_calendar_view);
    }

    function notify_calendar_view(response) {

        response = JSON.parse(response);
        var response_data = response['data'];

        if (response_data instanceof Object) {

            var current_month_string = response_data['current_month_string'];
            var prev_month_url = response_data['prev_month_url'].replace( /&#038;/g, '&' ) ;
            var next_month_url = response_data['next_month_url'].replace( /&#038;/g, '&' ) ;
            var calendarObj = response_data['calendar_data'];

            $('#current_month').text(current_month_string);
            $('#prev_month_url').prop('href', prev_month_url);
            $('#next_month_url').prop('href', next_month_url);

            if (calendarObj instanceof Array) {

                for (var $i = 0; $i < calendarObj.length; $i++) {
                    var $cell = $('#calendar_cell' + $i);
                    var $cell_date = $('#calendar_cell' + $i + ' .calendar_cell_date');
                    var $cell_content = $('#calendar_cell' + $i + ' .calendar_cell_content');
                    var $cell_ul = $('#calendar_cell' + $i + ' ul');

                    var dateData = calendarObj[$i];
                    var date = dateData['date'];
                    var backgroundColor = dateData['background_color'];
                    var sendData = dateData['sendData'];
                    var is_future = dateData['is_future'];
                    var register_url = dateData['register_url'];

                    $cell.removeClass('calendar_today_cell');
                    $cell.removeClass('calendar_current_month_cell');
                    $cell.removeClass('calendar_old_cell');
                    $cell.addClass(backgroundColor);

                    $cell_date.empty();
                    $cell_date.text(date);

                    $cell_content.empty();

                    if ('true' === is_future) {
                        var register_link = '<a href="' + register_url + '">';
                        var $anchor = $(register_link).appendTo($cell_content);
                        $anchor.append('<i class="bi bi-plus"></i>');
                    }

                    $cell_ul.empty();

                    if (sendData.length > 0) {

                        for (var $j = 0; $j < sendData.length; $j++) {

                            var aData = sendData[$j];
                            var url = aData['url'];
                            var subject = aData['subject'];
                            var reserved_time = aData['reserved_time'];
                            var target_count = aData['target_count'];

                            var $li = $('<li>').appendTo($cell_ul);

                            var link = '';
                            if (reserved_time === '') {
                                $li.append('<span class="badge badge-info mr-1"> ' + target_count + '</span>');
                            } else {
                                link = '<span class="badge badge-light mr-1"> ' + reserved_time + '</span>';
                            }

                            link = link + '<a href="' + url + '">' + subject + ' </a>';
                            $li.append(link);
                        }
                    }
                }
            }
        }
    }
})(jQuery);

