document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("classtime-calendar");

  if (!calendarEl) return;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: classtimeData.events,
  });

  calendar.render();
});
