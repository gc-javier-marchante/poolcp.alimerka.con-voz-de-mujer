document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    if (!calendarEl) {
        return;
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'timeGridWeek',
        nowIndicator: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        select: function (arg) {
            GCModal.openModal({
                url: $(calendarEl).attr('data-events-add-modal') + '?' + jQuery.param({
                    start: Math.floor(arg.start.getTime() / 1000),
                    end: Math.floor(arg.end.getTime() / 1000),
                    allDay: arg.allDay ? 1 : 0,
                    redirect_to: location.href.toString()
                })
            });
            calendar.unselect()
        },
        eventClick: function (arg) {
            GCModal.openModal({
                url: arg.event.url
            });
            arg.jsEvent.preventDefault();
        },
        editable: true,
        selectable: true,
        businessHours: true,
        dayMaxEvents: true,
        events: {
            url: $(calendarEl).attr('data-events-url'),
            failure: function () {
            }
        }
    });


    // render the calendar
    calendar.render();
});