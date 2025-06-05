document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("classtime-calendar");
  if (!calendarEl || !window.classtimeCalendarData) return;

  const allEvents = window.classtimeCalendarData.events;

  const defaultView = window.innerWidth <= 600 ? "timeGridDay" : "dayGridMonth";

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: defaultView,
    contentHeight: "auto",
    events: allEvents,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },

    eventContent: function (info) {
      const props = info.event.extendedProps;
      const lines = [];

      const time = props.time || "";
      const classType = props.class_type || "";
      const classLevel = props.class_level || "";

      if (time) lines.push(time);
      if (classType) lines.push(classType);
      if (classLevel) lines.push(classLevel);
      if (props.instructors && Array.isArray(props.instructors)) {
        const instructorNames = props.instructors
          .map((inst) => inst.name)
          .join(", ");
        lines.push(instructorNames);
      }

      const inner = document.createElement("div");

      if (classType) {
        const badgeType = document.createElement("div");
        badgeType.textContent = classType;
        badgeType.style.display = "inline-block";
        badgeType.style.padding = "2px 8px";
        badgeType.style.marginBottom = "4px";
        badgeType.style.marginRight = "4px";
        badgeType.style.fontSize = "0.75em";
        badgeType.style.borderRadius = "8px";
        badgeType.style.backgroundColor = props.badge_color || "#3478f6";
        badgeType.style.color = "white";
        badgeType.style.fontWeight = "bold";
        inner.appendChild(badgeType);
      }

      if (classLevel) {
        const badgeLevel = document.createElement("div");
        badgeLevel.textContent = classLevel;
        badgeLevel.style.display = "inline-block";
        badgeLevel.style.padding = "2px 8px";
        badgeLevel.style.marginBottom = "4px";
        badgeLevel.style.fontSize = "0.75em";
        badgeLevel.style.borderRadius = "8px";
        badgeLevel.style.backgroundColor = "#4b5563";
        badgeLevel.style.color = "white";
        badgeLevel.style.fontWeight = "bold";
        inner.appendChild(badgeLevel);
      }

      lines.forEach((line) => {
        const div = document.createElement("div");
        div.textContent = line;
        inner.appendChild(div);
      });

      return { domNodes: [inner] };
    },

    eventClick: function (info) {
      info.jsEvent.preventDefault();

      const props = info.event.extendedProps;
      const modal = document.getElementById("classtime-modal");

      modal.querySelector(".classtime-title").textContent =
        props.class_type || "";
      modal.querySelector(".classtime-level").textContent =
        props.class_level || "";
      modal.querySelector(".classtime-time").textContent = props.time || "";

      const instructorsEl = modal.querySelector(".classtime-instructors");
      if (props.instructors && Array.isArray(props.instructors)) {
        const instructorsArray = props.instructors;
        instructorsEl.innerHTML = instructorsArray
          .map((inst) => {
            if (inst.link) {
              if (inst.certification && inst.certification.trim() !== "") {
                const certs = inst.certification
                  .split(",")
                  .map((c) => c.trim());
                const certHtml = certs
                  .map((cert) => `<div>${cert}</div>`)
                  .join("");

                return `
                  <a href="${inst.link}" class="instructor-with-tooltip" style="color:#4da6ff; text-decoration: underline; cursor: pointer; position: relative;">
                    ${inst.name}
                    <span class="instructor-tooltip">${certHtml}</span>
                  </a>
                `;
              } else {
                return `<a href="${inst.link}" style="color:#4da6ff; text-decoration: underline; cursor: pointer;">${inst.name}</a>`;
              }
            } else {
              if (inst.certification && inst.certification.trim() !== "") {
                const certs = inst.certification
                  .split(",")
                  .map((c) => c.trim());
                const certHtml = certs
                  .map((cert) => `<div>${cert}</div>`)
                  .join("");

                return `
                  <span class="instructor-with-tooltip" style="color:#4da6ff; text-decoration: underline; cursor: pointer; position: relative;">
                    ${inst.name}
                    <span class="instructor-tooltip">${certHtml}</span>
                  </span>
                `;
              } else {
                return `<span style="color:#4da6ff; text-decoration: underline; cursor: pointer;">${inst.name}</span>`;
              }
            }
          })
          .join(", ");
      } else {
        instructorsEl.textContent = "";
      }

      modal.querySelector(".classtime-notes").textContent = props.notes || "";

      modal.classList.add("open");
    },

    windowResize: function () {
      const width = window.innerWidth;
      if (width <= 600 && calendar.view.type !== "timeGridDay") {
        calendar.changeView("timeGridDay");
      } else if (width > 600 && calendar.view.type !== "dayGridMonth") {
        calendar.changeView("dayGridMonth");
      }
    },
  });

  calendar.render();

  document
    .getElementById("classtime-modal-close")
    ?.addEventListener("click", function () {
      document.getElementById("classtime-modal").classList.remove("open");
    });

  const instructorFilter = document.getElementById("instructor-filter");
  const typeFilter = document.getElementById("type-filter");
  const levelFilter = document.getElementById("level-filter");

  function applyFilters() {
    const selectedInstructor = instructorFilter ? instructorFilter.value : "";
    const selectedType = typeFilter ? typeFilter.value : "";
    const selectedLevel = levelFilter ? levelFilter.value : "";

    const filtered = allEvents.filter((event) => {
      const props = event.extendedProps || {};
      let matches = true;

      if (
        selectedInstructor &&
        (!props.instructors ||
          !props.instructors.some((inst) => inst.name === selectedInstructor))
      ) {
        matches = false;
      }
      if (
        selectedType &&
        (!props.class_type || props.class_type !== selectedType)
      ) {
        matches = false;
      }
      if (
        selectedLevel &&
        (!props.class_level || props.class_level !== selectedLevel)
      ) {
        matches = false;
      }

      return matches;
    });

    calendar.removeAllEvents();
    calendar.addEventSource(filtered);
  }

  [instructorFilter, typeFilter, levelFilter].forEach(function (filter) {
    if (filter) {
      filter.addEventListener("change", applyFilters);
    }
  });
});
