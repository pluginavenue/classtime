<?php
if (!defined('ABSPATH')) exit;

function classtime_render_help_page() {
    ?>
    <div class="wrap">
        <h1>ClassTime Help & Instructions</h1>

        <h2>📋 Getting Started</h2>
        <ol>
            <li><strong>Create Class Types</strong><br>
                Go to <em>ClassTime → Class Types</em> and create categories like <code>Adult Judo</code>, <code>Youth Judo</code>, or <code>Open Mat</code>.<br>
                <em>Pro users can assign a custom badge color for each class type.</em>
            </li>

            <li><strong>Create Class Levels</strong><br>
                Go to <em>ClassTime → Class Levels</em> and create levels like <code>Beginner</code>, <code>Advanced</code>, or <code>All Levels</code>.<br>
                <em>Pro users can assign a badge color here too.</em>
            </li>

            <li><strong>Add Instructors</strong><br>
                Visit <em>ClassTime → Instructors</em> and add instructor names, certifications/ranks, bios, and profile images (Pro).<br>
                <em>Pro features include rich bios and profile image support.</em>
            </li>

            <li><strong>Schedule Classes</strong><br>
                Go to <em>ClassTime → Classes</em> and create a new class.<br>
                - Set a date, start/end time, and optionally make it recurring.<br>
                - Assign one or more instructors, a class type, and a class level.
            </li>
        </ol>

        <h2>🛠️ Class Overrides (Pro)</h2>
        <p>To modify a specific instance of a recurring class:</p>
        <ol>
            <li>Go to <em>ClassTime → Class Overrides</em></li>
            <li>Create a new override and choose:
                <ul>
                    <li>❌ Cancel a class on a specific date</li>
                    <li>👥 Add a guest instructor for that day</li>
                    <li>🌟 Mark a class as featured/special</li>
                    <li>📘 Add a technique focus or session note</li>
                </ul>
            </li>
        </ol>

        <h2>📅 Embedding the Calendar</h2>
        <p>Use the following shortcode to embed the calendar:</p>
        <pre><code>[classtime_calendar]</code></pre>
        <p>This will show a full calendar view with toggle options for month/week/day.</p>

        <p>Visitors can:</p>
        <ul>
            <li>🔍 Filter by instructor, class type, and level</li>
            <li>👆 Click events to open a class or clinic modal with full details</li>
            <li>🎨 See color-coded badges for type and level (Pro)</li>
        </ul>

        <h2>🧑‍🏫 Displaying Instructors</h2>
        <p>Use this shortcode to display a responsive grid of instructors:</p>
        <pre><code>[classtime_instructors]</code></pre>

        <p>This creates a user-friendly overview of all instructors.</p>
    </div>
    <?php
}
