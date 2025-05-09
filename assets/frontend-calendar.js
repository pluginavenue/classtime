// Full updated frontend-calendar.js with override modal rendering

document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("classtime-calendar");
  if (!calendarEl || !window.classtimeCalendarData) return;

  const allEvents = window.classtimeCalendarData?.events || [];

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    contentHeight: "auto",
    events: allEvents,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },

    eventContent: function (info) {
      const props = info.event.extendedProps;
      const isClinic = !!props.clinic_id;
      const override = props.override || {};

      const inner = document.createElement("div");
      inner.style.padding = "4px";
      inner.style.fontSize = "0.85rem";
      inner.style.whiteSpace = "pre-line";

      if (isClinic) {
        inner.style.backgroundColor = props.clinic_color || "#2563eb";
        inner.style.color = "white";
        inner.style.borderRadius = "6px";
        inner.style.fontWeight = "bold";

        const title = document.createElement("div");
        title.innerHTML = info.event.title || "Clinic";
        title.style.fontSize = "0.9rem";
        title.style.padding = "2px 4px";
        inner.appendChild(title);
      } else {
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

        // Override: Cancelled styling
        if (override.cancelled) {
          inner.style.backgroundColor = "#991b1b";
          inner.style.color = "white";
          const cancelledBanner = document.createElement("div");
          cancelledBanner.textContent = "❌ Cancelled";
          cancelledBanner.style.fontWeight = "bold";
          inner.appendChild(cancelledBanner);
        }

        // Class badges
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
          badgeLevel.style.backgroundColor = props.level_color || "#4b5563";
          badgeLevel.style.color = "white";
          inner.appendChild(badgeLevel);
        }

        // Override badges
        if (override.guest_instructor) {
          const guest = document.createElement("div");
          guest.textContent = "👤 Guest Instructor";
          guest.style.fontSize = "1em";
          guest.style.marginBottom = "4px";
          inner.appendChild(guest);
        }

        if (override.technique_focus) {
          const focus = document.createElement("div");
          focus.textContent = "📘 Technique Focus";
          focus.style.fontSize = "1em";
          focus.style.marginBottom = "4px";
          inner.appendChild(focus);
        }

        lines.forEach((line) => {
          const div = document.createElement("div");
          div.textContent = line;
          inner.appendChild(div);
        });

        if (override.note) {
          const note = document.createElement("div");
          note.textContent = override.note;
          note.style.fontSize = "0.75em";
          note.style.marginTop = "4px";
          inner.appendChild(note);
        }
      }

      return { domNodes: [inner] };
    },

    eventClick: function (info) {
      info.jsEvent.preventDefault();

      const props = info.event.extendedProps;
      const override = props.override || {};
      const isClinic = info.event.classNames.includes(
        "classtime-clinic-session"
      );

      if (isClinic) {
        const modal = document.getElementById("classtime-clinic-modal");
        modal.querySelector(".clinic-title").textContent =
          props.clinic_title || "Clinic";

        let notesHTML = "";
        if (props.clinic_info) {
          notesHTML += `<div style="margin-bottom:1rem;"><strong>Clinic Info:</strong><br>${props.clinic_info}</div>`;
        }

        if (Array.isArray(props.sessions)) {
          notesHTML += `<div><strong>Sessions:</strong><ul style="padding-left:1rem;">`;
          props.sessions.forEach((session) => {
            if (session.date && session.start && session.end) {
              const time = `${session.date}: ${session.start}–${session.end}`;
              const note = session.note ? ` <em>(${session.note})</em>` : "";
              notesHTML += `<li>${time}${note}</li>`;
            }
          });
          notesHTML += `</ul></div>`;
        }

        modal.querySelector(".clinic-notes").innerHTML = notesHTML;
        modal.classList.add("open");
      } else {
        const modal = document.getElementById("classtime-modal");
        const overrideEl = modal.querySelector(".classtime-override-banner");
        overrideEl.innerHTML = "";

        if (override.cancelled) {
          overrideEl.innerHTML = `<div style="background:#991b1b;color:#fff;padding:6px 12px;border-radius:6px;font-weight:bold;margin-bottom:8px;">❌ This class has been cancelled</div>`;
          if (override.note) {
            overrideEl.innerHTML += `<div style="font-size:0.85rem;color:#fcdcdc;margin-top:4px;">${override.note}</div>`;
          }
        } else {
          if (override.guest_instructor) {
            overrideEl.innerHTML += `<div style=\"color:#2563eb;font-size:1.1rem;font-weight:bold;margin-bottom:4px;\">👤 Guest Instructor</div>`;
          }
          if (override.technique_focus) {
            overrideEl.innerHTML += `<div style=\"color:#0f766e;font-size:1.1rem;font-weight:bold;margin-bottom:4px;\">📘 Technique Focus</div>`;
          }
          if (override.note) {
            overrideEl.innerHTML += `<div style="font-size:0.85rem;margin-top:4px;">${override.note}</div>`;
          }
        }

        modal.querySelector(".classtime-title").textContent =
          props.class_type || "";
        modal.querySelector(".classtime-level").textContent =
          props.class_level || "";
        modal.querySelector(".classtime-time").textContent = props.time || "";

        const instructorsEl = modal.querySelector(".classtime-instructors");
        if (props.instructors && Array.isArray(props.instructors)) {
          instructorsEl.innerHTML = props.instructors
            .map((inst) => {
              const certs =
                inst.certification
                  ?.split(",")
                  .map((c) => c.trim())
                  .filter(Boolean) || [];
              const certHtml = certs
                .map((cert) => `<div>${cert}</div>`)
                .join("");
              const nameEl = inst.link
                ? `<a href="${inst.link}" class="instructor-with-tooltip" style="color:#4da6ff; text-decoration: underline; position: relative;">${inst.name}<span class="instructor-tooltip">${certHtml}</span></a>`
                : `<span class="instructor-with-tooltip" style="color:#4da6ff; text-decoration: underline; position: relative;">${inst.name}<span class="instructor-tooltip">${certHtml}</span></span>`;
              return nameEl;
            })
            .join(", ");
        } else {
          instructorsEl.textContent = "";
        }

        modal.querySelector(".classtime-notes").textContent = props.notes || "";
        modal.classList.add("open");
      }
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

  document.querySelectorAll(".classtime-modal-close").forEach((button) => {
    button.addEventListener("click", () => {
      document.querySelectorAll(".classtime-modal").forEach((modal) => {
        modal.classList.remove("open");
      });
    });
  });

  const instructorFilter = document.getElementById("instructor-filter");
  const typeFilter = document.getElementById("type-filter");
  const levelFilter = document.getElementById("level-filter");

  // Populate filters dynamically
  const instructorSet = new Set();
  const typeSet = new Set();
  const levelSet = new Set();

  allEvents.forEach((event) => {
    const props = event.extendedProps || {};
    if (props.instructors && Array.isArray(props.instructors)) {
      props.instructors.forEach((inst) => instructorSet.add(inst.name));
    }
    if (props.class_type) typeSet.add(props.class_type);
    if (props.class_level) levelSet.add(props.class_level);
  });

  function populateFilter(selectEl, values) {
    if (!selectEl) return;
    values.sort().forEach((val) => {
      const opt = document.createElement("option");
      opt.value = val;
      opt.textContent = val;
      selectEl.appendChild(opt);
    });
  }

  populateFilter(instructorFilter, Array.from(instructorSet));
  populateFilter(typeFilter, Array.from(typeSet));
  populateFilter(levelFilter, Array.from(levelSet));

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

  const clearButton = document.createElement("button");
  clearButton.textContent = "Clear All Filters";
  clearButton.style.marginLeft = "1rem";
  clearButton.style.backgroundColor = "#4b5563";
  clearButton.style.color = "white";
  clearButton.style.border = "none";
  clearButton.style.borderRadius = "4px";
  clearButton.style.padding = "4px 8px";
  clearButton.style.fontSize = "0.9rem";
  clearButton.style.cursor = "pointer";
  document.getElementById("classtime-filters").appendChild(clearButton);

  clearButton.addEventListener("click", () => {
    if (instructorFilter) instructorFilter.value = "";
    if (typeFilter) typeFilter.value = "";
    if (levelFilter) levelFilter.value = "";
    applyFilters();
  });

  [instructorFilter, typeFilter, levelFilter].forEach(function (filter) {
    if (filter) {
      filter.addEventListener("change", applyFilters);
    }
  });
});
