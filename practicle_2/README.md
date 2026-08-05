# CHARUSAT Student Portal

## Project Summary
This is a simple student portal website for CHARUSAT. The site uses HTML and CSS to provide a navigation flow across 10+ pages, including home, information pages, student dashboard pages, and account forms.

## Problem Definition
Provide a manageable web portal that helps students, applicants, and campus visitors find information, events, contact details, FAQs, dashboard services, and account access.

## Key Pages
- `index.html` — Home page with introduction and highlights
- `about.html` — Institutional overview and programs
- `events.html` — Upcoming activities and campus notices
- `dashboard.html` — Student dashboard summary
- `profile.html` — Student profile details
- `contact.html` — Contact information and inquiry form
- `faq.html` — Frequently asked questions
- `feedback.html` — Feedback submission form
- `login.html` — Login form
- `register.html` — Registration form
- `sitemap.html` — Site map and page navigation overview

## Navigation Flow
The site uses relative links between pages. Navigation is handled through header links and context links inside each page, for example:
- `index.html` → `about.html`, `events.html`, `dashboard.html`, `contact.html`
- `dashboard.html` → `profile.html`, `feedback.html`, `events.html`
- `contact.html` → `login.html`, `register.html`

## Requirement Analysis
- URL parts: `protocol://host/path/file.html` with optional query strings or fragment identifiers.
- HTML processing: the browser parses the HTML, applies CSS styles, builds the DOM, and renders the page visually.
- Page navigation: uses anchor tags (`<a href="page.html">`) with relative paths so each page links directly to another page.
## Project Roles and Modules
- User roles: visitor, student, applicant, admin/support contact.
- Main modules: Home, About, Events, Dashboard, Contact, FAQ, Feedback, Account (Login/Register), Profile.

## Tools and Technology
- ANTIGRAVITY
- HTML
- CSS
- Git / GitHub

## Evaluation Strategy
- Requirement clarity
- Sitemap completeness
- Navigation flow
- Page structure consistency
- GitHub repository organization

## Advanced / Intermediate Extension
- Intermediate: Prepare responsive wireframes and keep layout simple.
- Advanced: Plan a backend route structure for future login, profile, and event modules.

## Wireframe Plan
A low-fidelity wireframe for the site is based on repeating layout blocks:
- Header with brand and navigation
- Main hero or title section
- Card grid for features and content
- Footer with contact or copyright

## Notes
Visit `sitemap.html` to see the site map and page connections for the existing website.