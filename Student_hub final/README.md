# CHARUSAT Student Portal

## Project Summary
This is a modern, responsive student portal website for Charotar University of Science and Technology (CHARUSAT). The project implements coursework practicals progressively in a unified, multi-page frontend architecture using semantic HTML5, CSS3, and modern JavaScript (ES6+).

---

## Practical 6: Rendering External JSON Data using Fetch API, Search & Filter

### Problem Definition
Display event lists, student profiles, notices, and FAQs consumed dynamically from external JSON files using the **Fetch API**. Implement dynamic UI rendering, real-time search, category/department filtering, multi-field sorting, and client-side pagination.

### Key Datasets (`practicle_2/data/`)
- `events.json`: 16 comprehensive campus events with title, category, date, time, venue, organizer, capacity, and tags.
- `students.json`: 16 detailed student records with roll number, name, department, semester, CGPA, email, location, and skills.
- `faqs.json`: 16 university FAQs covering admissions, academics, examinations, placements, and campus facilities.
- `locations.json`: Hierarchical Country &rarr; State &rarr; City mappings used by the dependent dropdown filter.

---

## Key Pages in `practicle_2/`
- [index.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/index.html) &mdash; Home page featuring hero banner, content slider, highlights, and dynamic **Campus Events Spotlight** loaded via Fetch API.
- [events.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/events.html) &mdash; Dynamic **Events Directory** rendered from `events.json` with search, category filtering, date/title/capacity sorting, and pagination.
- [students.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/students.html) &mdash; Dynamic **Student Profiles Directory** from `students.json` with **Dependent Dropdowns (Country &rarr; State &rarr; City)**, department filtering, CGPA sorting, and pagination.
- [faq.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/faq.html) &mdash; Dynamic **University FAQs** from `faqs.json` with live search, category filtering, and collapsible accordion cards.
- [dashboard.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/dashboard.html) &mdash; Central dashboard displaying live aggregated stats, upcoming events preview, and scholar spotlights loaded via Fetch API.
- [profile.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/profile.html) &mdash; Individual student profile details with navigation to student directory.
- [about.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/about.html) &mdash; Institutional overview, mission, vision, and programs.
- [contact.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/contact.html) &mdash; Campus locations, admissions contact info, and inquiry form.
- [feedback.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/feedback.html) &mdash; Student suggestions and feedback submission.
- [login.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/login.html) &mdash; Account sign-in form.
- [register.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/register.html) &mdash; Registration form with real-time validation and dynamic password strength meter (Practical 5).
- [sitemap.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/sitemap.html) &mdash; Complete site map and module navigation flow.

---

## Key Questions & Viva Guide

### Q1: How is JSON fetched, parsed, and rendered?
1. **Fetching**: The browser `fetch()` API sends an asynchronous HTTP request to external `.json` files:
   ```javascript
   const response = await fetch('data/events.json');
   ```
2. **Parsing**: When the promise resolves, `.json()` parses the raw JSON text into native JavaScript objects:
   ```javascript
   const data = await response.json();
   ```
3. **Rendering**: Using `Array.prototype.map()` and ES6 template literals, HTML card strings are constructed and injected into the DOM:
   ```javascript
   container.innerHTML = data.map(item => `...card markup...`).join('');
   ```

### Q2: Which array methods are used for search, filter, sort, and pagination?
- **`Array.prototype.filter()`**: Evaluates items against query text and dropdown values:
  ```javascript
  const filtered = allEvents.filter(e => e.title.toLowerCase().includes(query));
  ```
- **`Array.prototype.sort()`**: Orders records numerically or alphabetically:
  ```javascript
  filtered.sort((a, b) => b.cgpa - a.cgpa); // Descending CGPA
  ```
- **`Array.prototype.slice()`**: Extracts the slice of items for the current page:
  ```javascript
  const paginated = filtered.slice((page - 1) * limit, page * limit);
  ```
- **`Array.prototype.map()`**: Transforms data objects into HTML card elements.

### Q3: How are loading and error states handled?
- **Loading State**: Displays a spinning CSS loader and text (`<div class="loading-state">...</div>`) before the fetch begins.
- **Error State**: The fetch call is wrapped in `try...catch`. If network fails or HTTP status is not OK, an error message and a **"Retry"** button are displayed.
- **Empty State**: When filter or search criteria match 0 records, a helpful empty state with a **"Clear Filters"** button appears.

### Q4: How is modularity maintained in JavaScript?
- Clean separation into single-responsibility functions:
  - `fetchData(url, cacheKey, forceRefresh)`: Handles data access and caching.
  - Page-specific controllers: `loadEvents()`, `loadStudentsData()`, `loadFaqs()`, `loadDashboardData()`.
  - Pure rendering functions: `renderEvents()`, `renderStudents()`, `renderFaqs()`.
  - DOM event listeners for search inputs, dropdown changes, and button clicks.

---

## Extensions Implemented

### 1. Intermediate Extension: Dependent Location Dropdowns
- Located on [students.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/practicle_2/students.html).
- **Cascading Dropdowns**:
  1. Selecting a **Country** dynamically loads its states and enables the State dropdown.
  2. Selecting a **State** dynamically loads its cities and enables the City dropdown.
  3. Selecting a **City** filters student profile cards to that exact locality.
  4. Includes a convenient **Reset Location** button.

### 2. Advanced Extension: LocalStorage Caching & Offline Support
- Whenever external JSON is fetched, the response is stored in `localStorage` under keys `charusat_events`, `charusat_students`, `charusat_faqs`.
- On subsequent visits, data loads instantly from `localStorage`, avoiding unnecessary network requests.
- If the browser is offline or encounters CORS restrictions, cached data is used automatically as a fallback.
- A **🔄 Refresh** button allows users to force a fresh fetch from the JSON files anytime.

---

## How to Run
Run a local static HTTP server in the `practicle_2` folder:
```bash
# Using Node.js npx serve:
npx -y serve "c:\Users\mrama\OneDrive\Desktop\WEB DEVELOPMENT\practicle_2" -l 5000

# Open in your web browser:
http://localhost:5000
```