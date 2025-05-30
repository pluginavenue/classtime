document.addEventListener("DOMContentLoaded", function () {
  const mobileDebug = document.createElement("div");
  mobileDebug.innerHTML = `
  <div style="position:fixed;top:0;left:0;right:0;z-index:99999;
              background:#000;color:#0f0;padding:10px;font-size:14px;
              font-family:monospace;border-bottom:2px solid lime;">
    ✅ JS LOADED | Events: ${
      window?.classtimeCalendarData?.events?.length ?? "❌"
    }
  </div>`;
  document.body.appendChild(mobileDebug);

  // ────────────────────────────────────────────────
  // 1️⃣ Read CSS variables in JS (with fallbacks)
  // ────────────────────────────────────────────────
  const rootStyles = getComputedStyle(document.documentElement);
  function cssVar(name, fallback) {
    const val = rootStyles.getPropertyValue(name).trim();
    return val || fallback;
  }

  const bgColor = cssVar("--classtime-bg-color", "#f9f9f9");
  const textColor = cssVar("--classtime-text-color", "#222");
  const mutedText = cssVar("--classtime-muted-text", "#666");
  const accentColor = cssVar("--classtime-accent", "#4da6ff");
  const eventSubtext = cssVar("--classtime-event-subtext", "#888");
  const eventHoverShadow = cssVar(
    "--classtime-event-hover-shadow",
    "0 0 5px #ffa500"
  );

  const calendarEl = document.getElementById("classtime-calendar");
  const calendarExists = !!calendarEl;
  const eventDataExists = !!window.classtimeCalendarData;
  const allEvents = eventDataExists ? window.classtimeCalendarData.events : [];

  document.body.insertAdjacentHTML(
    "beforeend",
    `<pre style="background:black;color:white;padding:1rem;font-size:1rem;z-index:9999;position:relative;">
      JS Loaded ✅<br>
      Calendar Found: ${calendarExists}<br>
      Event Data Found: ${eventDataExists}<br>
      Event Count: ${allEvents.length}
    </pre>`
  );

  if (!calendarExists || !eventDataExists) return;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    contentHeight: "auto",
    events: allEvents,
    displayEventTime: false,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    eventContent: function (info) {
      const props = info.event.extendedProps;
      const isClinic = !!props.clinic_note || !!props.clinic_id;
      const isCancelled = info.event.classNames.includes("classtime-cancelled");

      const inner = document.createElement("div");
      inner.style.padding = "6px";
      inner.style.fontSize = "0.85rem";
      inner.style.borderRadius = "8px";
      inner.style.backgroundColor = isClinic
        ? props.clinic_color || bgColor
        : "var(--wp--preset--color--background, rgba(255,255,255,0.05))";
      inner.style.color = textColor;

      if (isClinic) {
        const titleDiv = document.createElement("div");
        titleDiv.textContent = info.event.title || "Clinic";
        titleDiv.style.whiteSpace = "pre-line";
        titleDiv.style.fontWeight = "bold";
        titleDiv.style.fontSize = "0.9rem";
        titleDiv.style.color = textColor;
        inner.appendChild(titleDiv);
      }

      if (isCancelled && !isClinic) {
        const cancelDiv = document.createElement("div");
        cancelDiv.textContent = "❌ Class Cancelled";
        cancelDiv.style.color = "var(--classtime-accent)";
        cancelDiv.style.fontWeight = "bold";
        cancelDiv.style.marginBottom = "4px";
        inner.appendChild(cancelDiv);
      }

      if (!isClinic && props.guest_instructor) {
        const guestDiv = document.createElement("div");
        guestDiv.textContent = "👤 Guest Instructor ...";
        guestDiv.style.color = accentColor;
        guestDiv.style.fontSize = "0.8rem";
        guestDiv.style.fontWeight = "bold";
        guestDiv.style.marginBottom = "4px";
        inner.appendChild(guestDiv);
      }

      if (!isClinic && props.technique_focus) {
        const techDiv = document.createElement("div");
        techDiv.textContent = "📘 Technique Focus...";
        techDiv.style.color = accentColor;
        techDiv.style.fontSize = "0.8rem";
        techDiv.style.fontWeight = "bold";
        techDiv.style.marginBottom = "4px";
        inner.appendChild(techDiv);
      }

      if (props.class_type && !isClinic) {
        const badgeType = document.createElement("div");
        badgeType.textContent = props.class_type;
        badgeType.style.display = "inline-block";
        badgeType.style.padding = "2px 8px";
        badgeType.style.marginBottom = "4px";
        badgeType.style.marginRight = "4px";
        badgeType.style.fontSize = "0.75em";
        badgeType.style.borderRadius = "8px";
        badgeType.style.backgroundColor = props.badge_color || accentColor;
        badgeType.style.color = bgColor;
        badgeType.style.fontWeight = "bold";
        inner.appendChild(badgeType);
      }

      if (props.class_level && !isClinic) {
        const badgeLevel = document.createElement("div");
        badgeLevel.textContent = props.class_level;
        badgeLevel.style.display = "inline-block";
        badgeLevel.style.padding = "2px 8px";
        badgeLevel.style.marginBottom = "4px";
        badgeLevel.style.fontSize = "0.75em";
        badgeLevel.style.borderRadius = "8px";
        badgeLevel.style.backgroundColor =
          props.level_badge_color || eventSubtext;
        badgeLevel.style.color = bgColor;
        badgeLevel.style.fontWeight = "bold";
        inner.appendChild(badgeLevel);
      }

      if (props.instructors && Array.isArray(props.instructors)) {
        const names = props.instructors.map((inst) => inst.name).join(", ");
        const instDiv = document.createElement("div");
        instDiv.textContent = names;
        instDiv.style.color = "#fff";
        instDiv.style.fontSize = "0.7rem";
        instDiv.style.fontWeight = "500";
        instDiv.style.marginTop = "4px";
        inner.appendChild(instDiv);
      }

      const container = document.createElement("div");
      container.appendChild(inner);
      return { domNodes: [container] };
    },

    eventClick: function (info) {
      const props = info.event.extendedProps;
      const isClinic = Array.isArray(info.event.classNames)
        ? info.event.classNames.includes("classtime-clinic-session")
        : false;

      const clinicModal = document.getElementById("classtime-clinic-modal");
      const classModal = document.getElementById("classtime-modal");
      const sessionList = clinicModal.querySelector(".classtime-sessions");
      const instructorsEl = classModal.querySelector(".classtime-instructors");
      const overrideNoteEl = classModal.querySelector(
        ".classtime-override-note"
      );

      clinicModal.classList.remove("open");
      classModal.classList.remove("open");

      if (isClinic) {
        const title = info.event.title || "Clinic";
        clinicModal.querySelector(".classtime-title").textContent = title;
        if (sessionList) sessionList.innerHTML = "";

        if (Array.isArray(props.clinic_sessions)) {
          props.clinic_sessions.forEach((session) => {
            const li = document.createElement("li");
            li.textContent = `${session.start} – ${session.end}`;
            sessionList.appendChild(li);
            if (session.notes) {
              const note = document.createElement("div");
              note.textContent = `Note: ${session.notes}`;
              sessionList.appendChild(note);
            }
          });
        }

        clinicModal.querySelector(".classtime-info").innerHTML =
          props.clinic_info || "";
        clinicModal.classList.add("open");
      } else {
        classModal.querySelector(".classtime-title").textContent =
          props.class_type || "";
        classModal.querySelector(".classtime-level").textContent =
          props.class_level || "";
        classModal.querySelector(".classtime-time").textContent =
          props.time || "";
        classModal.querySelector(".classtime-notes").textContent =
          props.notes || "";

        if (instructorsEl && props.instructors) {
          instructorsEl.innerHTML = props.instructors
            .map((inst) => {
              const name = inst.name || inst.title || inst;
              const certs = inst.certification
                ? inst.certification
                    .split(",")
                    .map((c) => `<div>${c.trim()}</div>`)
                    .join("")
                : "";
              const style = `color: ${accentColor}; text-decoration: underline;`;
              return `<span class="instructor-with-tooltip" style="${style}">${name}<span class="instructor-tooltip">${certs}</span></span>`;
            })
            .join(", ");
        }

        if (overrideNoteEl) {
          overrideNoteEl.textContent = props.override_note || "";
          overrideNoteEl.style.display = props.override_note ? "block" : "none";
          overrideNoteEl.style.color = accentColor;
        }

        classModal.classList.add("open");
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

  document
    .getElementById("classtime-modal-close")
    ?.addEventListener("click", function () {
      document.getElementById("classtime-modal").classList.remove("open");
    });

  const instructorFilter = document.getElementById("instructor-filter");
  const typeFilter = document.getElementById("type-filter");
  const levelFilter = document.getElementById("level-filter");

  function applyFilters() {
    const selectedInstructor = instructorFilter?.value || "";
    const selectedType = typeFilter?.value || "";
    const selectedLevel = levelFilter?.value || "";

    const filtered = allEvents.filter((event) => {
      const props = event.extendedProps || {};
      if (
        selectedInstructor &&
        (!props.instructors ||
          !props.instructors.some((inst) => inst.name === selectedInstructor))
      )
        return false;
      if (
        selectedType &&
        !(
          props.class_type === selectedType ||
          props.clinic_type === selectedType
        )
      )
        return false;
      if (
        selectedLevel &&
        (!props.class_level || props.class_level !== selectedLevel)
      )
        return false;
      return true;
    });

    calendar.removeAllEvents();
    calendar.addEventSource(filtered);
  }

  [instructorFilter, typeFilter, levelFilter].forEach((filter) => {
    if (filter) filter.addEventListener("change", applyFilters);
  });
});
