document.addEventListener('DOMContentLoaded', () => {
  // 1. Light/Dark Theme Switcher
  const themeToggle = document.createElement('button');
  themeToggle.textContent = '🌓';
  themeToggle.className = 'theme-toggle';
  themeToggle.style.background = 'none';
  themeToggle.style.border = 'none';
  themeToggle.style.cursor = 'pointer';
  themeToggle.style.fontSize = '1.2rem';
  themeToggle.title = 'Toggle Dark Mode';
  
  const headerContainer = document.querySelector('.site-header .container');
  if (headerContainer) {
    headerContainer.appendChild(themeToggle);
  }

  // Check localStorage for theme
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
  }

  themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    if (document.body.classList.contains('dark-theme')) {
      localStorage.setItem('theme', 'dark');
    } else {
      localStorage.setItem('theme', 'light');
    }
  });

  // 2. Hamburger Menu
  const nav = document.querySelector('nav');
  if (headerContainer && nav) {
    const hamburger = document.createElement('button');
    hamburger.className = 'hamburger';
    hamburger.innerHTML = '☰';
    // insert before nav
    headerContainer.insertBefore(hamburger, nav);

    hamburger.addEventListener('click', () => {
      nav.classList.toggle('nav-active');
    });
  }

  // 3. Notification Banner
  const banner = document.createElement('div');
  banner.className = 'notification-banner';
  banner.innerHTML = `
    <span>Welcome to CHARUSAT Student Portal! Stay tuned for new updates.</span>
    <button class="close-btn" aria-label="Close">&times;</button>
  `;
  document.body.insertBefore(banner, document.body.firstChild);

  banner.querySelector('.close-btn').addEventListener('click', () => {
    banner.remove();
  });

  // 4. Collapsible FAQ
  const faqCards = document.querySelectorAll('.faq-grid .card');
  faqCards.forEach(card => {
    const h4 = card.querySelector('h4');
    if (h4) {
      h4.style.cursor = 'pointer';
      h4.style.display = 'flex';
      h4.style.justifyContent = 'space-between';
      h4.style.alignItems = 'center';
      
      const icon = document.createElement('span');
      icon.textContent = '+';
      h4.appendChild(icon);

      card.classList.add('faq-collapsed');
      
      h4.addEventListener('click', () => {
        card.classList.toggle('faq-collapsed');
        icon.textContent = card.classList.contains('faq-collapsed') ? '+' : '-';
      });
    }
  });

  // 5. Modal Popup
  const dashboardMain = document.querySelector('.dashboard-grid');
  if (dashboardMain) {
    const modalTrigger = document.createElement('button');
    modalTrigger.className = 'btn mt-20';
    modalTrigger.textContent = 'View Important Update';
    dashboardMain.parentNode.insertBefore(modalTrigger, dashboardMain);

    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-overlay';
    modalOverlay.innerHTML = `
      <div class="modal-content">
        <h3 style="margin-top: 0;">Important Dashboard Update</h3>
        <p>This is a dynamic modal popup created using JavaScript as per the practical assignment requirements.</p>
        <button class="btn close-modal">Close</button>
      </div>
    `;
    document.body.appendChild(modalOverlay);

    modalTrigger.addEventListener('click', () => {
      modalOverlay.classList.add('active');
    });
    modalOverlay.querySelector('.close-modal').addEventListener('click', () => {
      modalOverlay.classList.remove('active');
    });
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) modalOverlay.classList.remove('active');
    });
  }

  // 6. Content Slider
  const hero = document.querySelector('.hero');
  if (hero && document.title.includes('Home')) {
    const sliderContainer = document.createElement('div');
    sliderContainer.className = 'slider-container mt-20';
    sliderContainer.innerHTML = `
      <div class="slider">
        <div class="slide" style="background:var(--accent); color:white; padding: 40px; text-align:center; border-radius: var(--radius);">
          <h3 style="color:white; margin-top:0;">Future Ready</h3>
          <p style="color:white;">Learn top skills with expert faculty.</p>
        </div>
        <div class="slide" style="background:var(--primary); color:white; padding: 40px; text-align:center; border-radius: var(--radius);">
          <h3 style="color:white; margin-top:0;">Campus Life</h3>
          <p style="color:white;">Experience state of the art facilities.</p>
        </div>
        <div class="slide" style="background:var(--muted); color:white; padding: 40px; text-align:center; border-radius: var(--radius);">
          <h3 style="color:white; margin-top:0;">Global Opportunities</h3>
          <p style="color:white;">Get placed in top multinational companies.</p>
        </div>
      </div>
      <button class="slider-btn prev">&#10094;</button>
      <button class="slider-btn next">&#10095;</button>
    `;
    hero.appendChild(sliderContainer);

    const slider = sliderContainer.querySelector('.slider');
    const slides = sliderContainer.querySelectorAll('.slide');
    let currentIndex = 0;

    const updateSlider = () => {
      slider.style.transform = `translateX(-${currentIndex * 100}%)`;
    };

    sliderContainer.querySelector('.prev').addEventListener('click', () => {
      currentIndex = (currentIndex > 0) ? currentIndex - 1 : slides.length - 1;
      updateSlider();
    });

    sliderContainer.querySelector('.next').addEventListener('click', () => {
      currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0;
      updateSlider();
    });
  }

  // =========================================
  // 7. Practical 5: Form Validation & Password Strength
  // =========================================
  const regForm = document.getElementById('regForm');
  if (regForm) {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const mobileInput = document.getElementById('mobile');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const courseInput = document.getElementById('course');
    const yearInput = document.getElementById('year');
    const termsInput = document.getElementById('terms');

    // Password strength check
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      const bar = document.getElementById('strengthBar');
      const text = document.getElementById('strengthText');
      
      let strength = 0;
      if (val.length >= 6) strength++;
      if (/[A-Z]/.test(val)) strength++;
      if (/[0-9]/.test(val)) strength++;
      if (/[^A-Za-z0-9]/.test(val)) strength++;

      if (val.length === 0) {
        bar.style.width = '0%';
        text.textContent = '';
      } else if (strength <= 1) {
        bar.style.width = '25%';
        bar.style.backgroundColor = '#dc2626'; // Red
        text.textContent = 'Weak';
        text.style.color = '#dc2626';
      } else if (strength === 2 || strength === 3) {
        bar.style.width = '65%';
        bar.style.backgroundColor = '#eab308'; // Yellow
        text.textContent = 'Medium';
        text.style.color = '#eab308';
      } else {
        bar.style.width = '100%';
        bar.style.backgroundColor = '#16a34a'; // Green
        text.textContent = 'Strong';
        text.style.color = '#16a34a';
      }
    });

    // Helper to set error message
    function setError(id, message) {
      document.getElementById(id).textContent = message;
    }

    // Clear all error messages
    function clearErrors() {
      const errors = document.querySelectorAll('.error-msg');
      errors.forEach(err => err.textContent = '');
    }

    // Real-time validation on input/change
    regForm.querySelectorAll('input, select').forEach(input => {
      input.addEventListener('input', validateForm);
      input.addEventListener('change', validateForm);
    });

    function validateForm() {
      clearErrors();
      let isValid = true;

      // 1. Name Validation (Letters and spaces only, min 3 chars)
      const namePattern = /^[A-Za-z\s]{3,}$/;
      if (!nameInput.value.trim()) {
        setError('nameError', 'Full name is required.');
        isValid = false;
      } else if (!namePattern.test(nameInput.value.trim())) {
        setError('nameError', 'Name must contain at least 3 letters.');
        isValid = false;
      }

      // 2. Email Validation
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailInput.value.trim()) {
        setError('emailError', 'Email address is required.');
        isValid = false;
      } else if (!emailPattern.test(emailInput.value.trim())) {
        setError('emailError', 'Please enter a valid email address.');
        isValid = false;
      }

      // 3. Mobile Validation (10 digits)
      const mobilePattern = /^[0-9]{10}$/;
      if (!mobileInput.value.trim()) {
        setError('mobileError', 'Mobile number is required.');
        isValid = false;
      } else if (!mobilePattern.test(mobileInput.value.trim())) {
        setError('mobileError', 'Mobile number must be exactly 10 digits.');
        isValid = false;
      }

      // 4. Password Validation (min 6 characters)
      if (!passwordInput.value) {
        setError('passwordError', 'Password is required.');
        isValid = false;
      } else if (passwordInput.value.length < 6) {
        setError('passwordError', 'Password must be at least 6 characters long.');
        isValid = false;
      }

      // 5. Confirm Password Validation
      if (!confirmPasswordInput.value) {
        setError('confirmPasswordError', 'Please confirm your password.');
        isValid = false;
      } else if (confirmPasswordInput.value !== passwordInput.value) {
        setError('confirmPasswordError', 'Passwords do not match.');
        isValid = false;
      }

      // 6. Course Select
      if (!courseInput.value) {
        setError('courseError', 'Please select a course.');
        isValid = false;
      }

      // 7. Year Select
      if (!yearInput.value) {
        setError('yearError', 'Please select your academic year.');
        isValid = false;
      }

      // 8. Gender Radio
      const selectedGender = document.querySelector('input[name="gender"]:checked');
      if (!selectedGender) {
        setError('genderError', 'Please select your gender.');
        isValid = false;
      }

      // 9. Terms Acceptance Checkbox
      if (!termsInput.checked) {
        setError('termsError', 'You must accept the terms and conditions.');
        isValid = false;
      }

      return isValid;
    }

    // Submit handler
    regForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (validateForm()) {
        alert('Registration successful!');
        regForm.reset();
        document.getElementById('strengthBar').style.width = '0%';
        document.getElementById('strengthText').textContent = '';
      }
    });
  }

  // =========================================================================
  // PRACTICAL 6: RENDERING EXTERNAL JSON DATA USING FETCH API
  // Features: Fetch API, Search, Filter, Sort, Pagination,
  // Intermediate Extension: Dependent Dropdowns (Country -> State -> City)
  // Advanced Extension: LocalStorage Caching for Offline-like display
  // =========================================================================

  /**
   * Helper function to fetch JSON data with LocalStorage caching
   * @param {string} url - Path to JSON file (e.g. 'data/events.json')
   * @param {string} cacheKey - LocalStorage key
   * @param {boolean} forceRefresh - If true, bypass cache and fetch freshly
   */
  async function fetchData(url, cacheKey, forceRefresh = false) {
    // 1. Check if cached data exists in LocalStorage (Advanced Extension)
    if (!forceRefresh) {
      const cached = localStorage.getItem(cacheKey);
      if (cached) {
        return {
          data: JSON.parse(cached),
          source: 'cache'
        };
      }
    }

    // 2. Fetch fresh JSON from external file via Fetch API
    try {
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();

      // Save into LocalStorage for offline display
      localStorage.setItem(cacheKey, JSON.stringify(data));

      return {
        data: data,
        source: 'live'
      };
    } catch (error) {
      // If network/CORS fails, try returning cached data as fallback
      const cached = localStorage.getItem(cacheKey);
      if (cached) {
        console.warn('Network fetch failed, using cached data for:', url);
        return {
          data: JSON.parse(cached),
          source: 'cache (offline)'
        };
      }
      throw error;
    }
  }

  // -------------------------------------------------------------------------
  // 1. EVENTS PAGE: events.html
  // -------------------------------------------------------------------------
  const eventsContainer = document.getElementById('eventsContainer');
  if (eventsContainer) {
    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 4;

    const searchInput = document.getElementById('eventSearch');
    const categorySelect = document.getElementById('eventCategory');
    const sortSelect = document.getElementById('eventSort');
    const refreshBtn = document.getElementById('refreshEventsBtn');
    const clearSearchBtn = document.getElementById('clearEventSearch');
    const dataSourceBadge = document.getElementById('eventsDataSource');
    const countText = document.getElementById('eventsCountText');
    const paginationContainer = document.getElementById('eventsPagination');

    // Load events from JSON
    async function loadEvents(forceRefresh = false) {
      eventsContainer.innerHTML = `
        <div class="loading-state">
          <div class="spinner"></div>
          <p>Fetching events from data/events.json...</p>
        </div>
      `;

      try {
        const result = await fetchData('data/events.json', 'charusat_events', forceRefresh);
        allEvents = result.data;

        // Update cache badge
        if (dataSourceBadge) {
          dataSourceBadge.textContent = result.source === 'live' ? '🌐 Live Fetch API' : '⚡ LocalStorage Cache';
          dataSourceBadge.className = 'source-badge ' + (result.source === 'live' ? '' : 'cached');
        }

        // Populate Category dropdown dynamically
        populateEventCategories();

        // Filter, sort, and render
        applyEventsFilterAndSort();
      } catch (err) {
        eventsContainer.innerHTML = `
          <div class="error-state">
            <h4>Failed to Load Events</h4>
            <p>${err.message}</p>
            <p><small>Tip: If opening locally via file://, run with a local server like Live Server or npx serve.</small></p>
            <button class="btn btn-sm mt-10" id="retryEventsBtn">Retry</button>
          </div>
        `;
        const retryBtn = document.getElementById('retryEventsBtn');
        if (retryBtn) retryBtn.addEventListener('click', () => loadEvents(true));
      }
    }

    // Populate category dropdown with unique values
    function populateEventCategories() {
      const categories = ['All Categories', ...new Set(allEvents.map(e => e.category))];
      categorySelect.innerHTML = categories.map(cat => {
        const val = cat === 'All Categories' ? '' : cat;
        return `<option value="${val}">${cat}</option>`;
      }).join('');
    }

    // Search, Filter and Sort events using Array methods
    function applyEventsFilterAndSort() {
      const query = searchInput.value.trim().toLowerCase();
      const selectedCat = categorySelect.value;
      const sortBy = sortSelect.value;

      // 1. Array.filter() for search query and category
      filteredEvents = allEvents.filter(event => {
        const matchesQuery = !query || 
          event.title.toLowerCase().includes(query) ||
          event.description.toLowerCase().includes(query) ||
          event.venue.toLowerCase().includes(query) ||
          (event.tags && event.tags.some(tag => tag.toLowerCase().includes(query)));

        const matchesCategory = !selectedCat || event.category === selectedCat;

        return matchesQuery && matchesCategory;
      });

      // 2. Array.sort()
      filteredEvents.sort((a, b) => {
        if (sortBy === 'date-asc') return new Date(a.date) - new Date(b.date);
        if (sortBy === 'date-desc') return new Date(b.date) - new Date(a.date);
        if (sortBy === 'title-asc') return a.title.localeCompare(b.title);
        if (sortBy === 'capacity-desc') return b.capacity - a.capacity;
        return 0;
      });

      // Reset to page 1 on new filter
      currentPage = 1;

      // Render
      renderEvents();
      renderEventsPagination();
    }

    // Render Event Cards into DOM using Array.slice() and Array.map()
    function renderEvents() {
      if (filteredEvents.length === 0) {
        eventsContainer.innerHTML = `
          <div class="empty-state">
            <h4>No Events Found</h4>
            <p>No campus events match your selected search or filter criteria.</p>
            <button class="btn btn-sm" id="resetEventsFilterBtn">Clear Filters</button>
          </div>
        `;
        const resetBtn = document.getElementById('resetEventsFilterBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            categorySelect.value = '';
            applyEventsFilterAndSort();
          });
        }
        if (countText) countText.textContent = 'Showing 0 events';
        paginationContainer.innerHTML = '';
        return;
      }

      // Pagination slice
      const startIndex = (currentPage - 1) * itemsPerPage;
      const paginatedItems = filteredEvents.slice(startIndex, startIndex + itemsPerPage);

      if (countText) {
        const end = Math.min(startIndex + itemsPerPage, filteredEvents.length);
        countText.textContent = `Showing ${startIndex + 1}–${end} of ${filteredEvents.length} events`;
      }

      // Array.map() to construct card HTML
      eventsContainer.innerHTML = paginatedItems.map(event => {
        const badgeClass = 'badge-' + (event.category ? event.category.toLowerCase() : 'general');
        const tagsHtml = (event.tags || []).map(t => `<span class="tag-pill">#${t}</span>`).join('');
        
        return `
          <article class="event-card">
            <div>
              <div class="event-card-top">
                <h3 class="event-title">${event.title}</h3>
                <span class="event-badge ${badgeClass}">${event.category}</span>
              </div>
              <ul class="event-meta-list">
                <li class="event-meta-item">📅 <strong>Date:</strong> ${event.date} (${event.time || 'Full Day'})</li>
                <li class="event-meta-item">📍 <strong>Venue:</strong> ${event.venue}</li>
                <li class="event-meta-item">👥 <strong>Capacity:</strong> ${event.registered || 0} / ${event.capacity} registered</li>
              </ul>
              <p class="event-desc">${event.description}</p>
            </div>
            <div class="event-tags">
              ${tagsHtml}
            </div>
          </article>
        `;
      }).join('');
    }

    // Render Pagination Buttons
    function renderEventsPagination() {
      const totalPages = Math.ceil(filteredEvents.length / itemsPerPage);
      if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
      }

      let buttonsHtml = `
        <button class="page-btn" id="prevEventPage" ${currentPage === 1 ? 'disabled' : ''}>&larr; Prev</button>
      `;

      for (let i = 1; i <= totalPages; i++) {
        buttonsHtml += `
          <button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
        `;
      }

      buttonsHtml += `
        <button class="page-btn" id="nextEventPage" ${currentPage === totalPages ? 'disabled' : ''}>Next &rarr;</button>
      `;

      paginationContainer.innerHTML = buttonsHtml;

      // Event listeners for pagination
      paginationContainer.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          currentPage = parseInt(e.target.dataset.page);
          renderEvents();
          renderEventsPagination();
          eventsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
      });

      const prevBtn = document.getElementById('prevEventPage');
      if (prevBtn) {
        prevBtn.addEventListener('click', () => {
          if (currentPage > 1) {
            currentPage--;
            renderEvents();
            renderEventsPagination();
          }
        });
      }

      const nextBtn = document.getElementById('nextEventPage');
      if (nextBtn) {
        nextBtn.addEventListener('click', () => {
          if (currentPage < totalPages) {
            currentPage++;
            renderEvents();
            renderEventsPagination();
          }
        });
      }
    }

    // Event Listeners for search & filters
    searchInput.addEventListener('input', applyEventsFilterAndSort);
    categorySelect.addEventListener('change', applyEventsFilterAndSort);
    sortSelect.addEventListener('change', applyEventsFilterAndSort);
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        applyEventsFilterAndSort();
      });
    }
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => loadEvents(true));
    }

    // Initial load
    loadEvents();
  }

  // -------------------------------------------------------------------------
  // 2. STUDENT PROFILES & DEPENDENT DROPDOWNS: students.html
  // -------------------------------------------------------------------------
  const studentsContainer = document.getElementById('studentsContainer');
  if (studentsContainer) {
    let allStudents = [];
    let filteredStudents = [];
    let locationsData = {};
    let currentPage = 1;
    const itemsPerPage = 6;

    const studentSearch = document.getElementById('studentSearch');
    const clearStudentSearch = document.getElementById('clearStudentSearch');
    const deptFilter = document.getElementById('deptFilter');
    const studentSort = document.getElementById('studentSort');
    const refreshStudentsBtn = document.getElementById('refreshStudentsBtn');
    const studentsDataSource = document.getElementById('studentsDataSource');
    const studentsCountText = document.getElementById('studentsCountText');
    const studentsPagination = document.getElementById('studentsPagination');

    // Dependent Location Dropdowns
    const countrySelect = document.getElementById('countrySelect');
    const stateSelect = document.getElementById('stateSelect');
    const citySelect = document.getElementById('citySelect');
    const resetLocationBtn = document.getElementById('resetLocationBtn');

    // Load students and locations
    async function loadStudentsData(forceRefresh = false) {
      studentsContainer.innerHTML = `
        <div class="loading-state">
          <div class="spinner"></div>
          <p>Fetching student records from data/students.json...</p>
        </div>
      `;

      try {
        // Fetch students and locations concurrently
        const [studentsResult, locationsResult] = await Promise.all([
          fetchData('data/students.json', 'charusat_students', forceRefresh),
          fetchData('data/locations.json', 'charusat_locations', forceRefresh)
        ]);

        allStudents = studentsResult.data;
        locationsData = locationsResult.data;

        // Update badge
        if (studentsDataSource) {
          studentsDataSource.textContent = studentsResult.source === 'live' ? '🌐 Live Fetch API' : '⚡ LocalStorage Cache';
          studentsDataSource.className = 'source-badge ' + (studentsResult.source === 'live' ? '' : 'cached');
        }

        // Setup dropdowns
        populateDepartments();
        setupDependentDropdowns();

        // Render
        applyStudentsFilterAndSort();
      } catch (err) {
        studentsContainer.innerHTML = `
          <div class="error-state">
            <h4>Failed to Load Student Records</h4>
            <p>${err.message}</p>
            <button class="btn btn-sm mt-10" id="retryStudentsBtn">Retry</button>
          </div>
        `;
        const retry = document.getElementById('retryStudentsBtn');
        if (retry) retry.addEventListener('click', () => loadStudentsData(true));
      }
    }

    // Populate department select
    function populateDepartments() {
      const depts = ['All Departments', ...new Set(allStudents.map(s => s.department))];
      deptFilter.innerHTML = depts.map(d => {
        const val = d === 'All Departments' ? '' : d;
        return `<option value="${val}">${d}</option>`;
      }).join('');
    }

    // Intermediate Extension: Dependent Dropdown Setup
    function setupDependentDropdowns() {
      // 1. Populate Country Dropdown
      const countries = Object.keys(locationsData);
      countrySelect.innerHTML = `<option value="">All Countries</option>` +
        countries.map(c => `<option value="${c}">${c}</option>`).join('');

      stateSelect.innerHTML = `<option value="">Select Country First</option>`;
      stateSelect.disabled = true;

      citySelect.innerHTML = `<option value="">Select State First</option>`;
      citySelect.disabled = true;

      // When Country Changes -> Update State Dropdown
      countrySelect.addEventListener('change', () => {
        const selectedCountry = countrySelect.value;

        if (selectedCountry && locationsData[selectedCountry]) {
          const states = Object.keys(locationsData[selectedCountry]);
          stateSelect.innerHTML = `<option value="">All States in ${selectedCountry}</option>` +
            states.map(s => `<option value="${s}">${s}</option>`).join('');
          stateSelect.disabled = false;
        } else {
          stateSelect.innerHTML = `<option value="">Select Country First</option>`;
          stateSelect.disabled = true;
        }

        // Reset City
        citySelect.innerHTML = `<option value="">Select State First</option>`;
        citySelect.disabled = true;

        applyStudentsFilterAndSort();
      });

      // When State Changes -> Update City Dropdown
      stateSelect.addEventListener('change', () => {
        const selectedCountry = countrySelect.value;
        const selectedState = stateSelect.value;

        if (selectedCountry && selectedState && locationsData[selectedCountry][selectedState]) {
          const cities = locationsData[selectedCountry][selectedState];
          citySelect.innerHTML = `<option value="">All Cities in ${selectedState}</option>` +
            cities.map(c => `<option value="${c}">${c}</option>`).join('');
          citySelect.disabled = false;
        } else {
          citySelect.innerHTML = `<option value="">Select State First</option>`;
          citySelect.disabled = true;
        }

        applyStudentsFilterAndSort();
      });

      // When City Changes -> Filter
      citySelect.addEventListener('change', applyStudentsFilterAndSort);

      // Reset Location Button
      if (resetLocationBtn) {
        resetLocationBtn.addEventListener('click', () => {
          countrySelect.value = '';
          stateSelect.innerHTML = `<option value="">Select Country First</option>`;
          stateSelect.disabled = true;
          citySelect.innerHTML = `<option value="">Select State First</option>`;
          citySelect.disabled = true;
          applyStudentsFilterAndSort();
        });
      }
    }

    // Filter and Sort students
    function applyStudentsFilterAndSort() {
      const query = studentSearch.value.trim().toLowerCase();
      const selectedDept = deptFilter.value;
      const selectedCountry = countrySelect.value;
      const selectedState = stateSelect.value;
      const selectedCity = citySelect.value;
      const sortBy = studentSort.value;

      // Array.filter()
      filteredStudents = allStudents.filter(student => {
        const matchesQuery = !query ||
          student.name.toLowerCase().includes(query) ||
          student.rollNo.toLowerCase().includes(query) ||
          (student.skills && student.skills.some(sk => sk.toLowerCase().includes(query))) ||
          student.city.toLowerCase().includes(query);

        const matchesDept = !selectedDept || student.department === selectedDept;
        const matchesCountry = !selectedCountry || student.country === selectedCountry;
        const matchesState = !selectedState || student.state === selectedState;
        const matchesCity = !selectedCity || student.city === selectedCity;

        return matchesQuery && matchesDept && matchesCountry && matchesState && matchesCity;
      });

      // Array.sort()
      filteredStudents.sort((a, b) => {
        if (sortBy === 'name-asc') return a.name.localeCompare(b.name);
        if (sortBy === 'name-desc') return b.name.localeCompare(a.name);
        if (sortBy === 'cgpa-desc') return b.cgpa - a.cgpa;
        if (sortBy === 'cgpa-asc') return a.cgpa - b.cgpa;
        if (sortBy === 'semester-desc') return b.semester - a.semester;
        return 0;
      });

      currentPage = 1;
      renderStudents();
      renderStudentsPagination();
    }

    // Render Student Cards
    function renderStudents() {
      if (filteredStudents.length === 0) {
        studentsContainer.innerHTML = `
          <div class="empty-state">
            <h4>No Students Found</h4>
            <p>No student profiles match your search and location criteria.</p>
            <button class="btn btn-sm" id="resetStudentFilterBtn">Clear Filters</button>
          </div>
        `;
        const resetBtn = document.getElementById('resetStudentFilterBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', () => {
            studentSearch.value = '';
            deptFilter.value = '';
            countrySelect.value = '';
            stateSelect.disabled = true;
            citySelect.disabled = true;
            applyStudentsFilterAndSort();
          });
        }
        if (studentsCountText) studentsCountText.textContent = 'Showing 0 students';
        studentsPagination.innerHTML = '';
        return;
      }

      // Slice items for pagination
      const startIndex = (currentPage - 1) * itemsPerPage;
      const paginated = filteredStudents.slice(startIndex, startIndex + itemsPerPage);

      if (studentsCountText) {
        const end = Math.min(startIndex + itemsPerPage, filteredStudents.length);
        studentsCountText.textContent = `Showing ${startIndex + 1}–${end} of ${filteredStudents.length} students`;
      }

      // Map to HTML
      studentsContainer.innerHTML = paginated.map(s => {
        const skillsHtml = (s.skills || []).map(skill => `<span class="tag-pill">${skill}</span>`).join('');
        const avatarColor = s.avatarColor || '#2563eb';
        const initials = s.avatarInitials || s.name.split(' ').map(n => n[0]).join('');

        return `
          <article class="student-card">
            <div>
              <div class="student-card-header">
                <div class="avatar-circle" style="background-color: ${avatarColor};">${initials}</div>
                <div>
                  <h3 class="student-name">${s.name}</h3>
                  <p class="student-roll">${s.rollNo}</p>
                </div>
              </div>

              <div class="student-details">
                <p><strong>Department:</strong> ${s.department}</p>
                <p><strong>Semester:</strong> Semester ${s.semester}</p>
                <div class="cgpa-badge">⭐ CGPA: ${s.cgpa}</div>
                <p class="student-location">📍 ${s.city}, ${s.state}, ${s.country}</p>
              </div>
            </div>

            <div class="skills-wrapper">
              ${skillsHtml}
            </div>
          </article>
        `;
      }).join('');
    }

    // Render Student Pagination
    function renderStudentsPagination() {
      const totalPages = Math.ceil(filteredStudents.length / itemsPerPage);
      if (totalPages <= 1) {
        studentsPagination.innerHTML = '';
        return;
      }

      let html = `<button class="page-btn" id="prevStudentPage" ${currentPage === 1 ? 'disabled' : ''}>&larr; Prev</button>`;
      for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-student-page="${i}">${i}</button>`;
      }
      html += `<button class="page-btn" id="nextStudentPage" ${currentPage === totalPages ? 'disabled' : ''}>Next &rarr;</button>`;

      studentsPagination.innerHTML = html;

      studentsPagination.querySelectorAll('[data-student-page]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          currentPage = parseInt(e.target.dataset.studentPage);
          renderStudents();
          renderStudentsPagination();
          studentsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
      });

      const prev = document.getElementById('prevStudentPage');
      if (prev) {
        prev.addEventListener('click', () => {
          if (currentPage > 1) {
            currentPage--;
            renderStudents();
            renderStudentsPagination();
          }
        });
      }

      const next = document.getElementById('nextStudentPage');
      if (next) {
        next.addEventListener('click', () => {
          if (currentPage < totalPages) {
            currentPage++;
            renderStudents();
            renderStudentsPagination();
          }
        });
      }
    }

    // Event listeners
    studentSearch.addEventListener('input', applyStudentsFilterAndSort);
    deptFilter.addEventListener('change', applyStudentsFilterAndSort);
    studentSort.addEventListener('change', applyStudentsFilterAndSort);
    if (clearStudentSearch) {
      clearStudentSearch.addEventListener('click', () => {
        studentSearch.value = '';
        applyStudentsFilterAndSort();
      });
    }
    if (refreshStudentsBtn) {
      refreshStudentsBtn.addEventListener('click', () => loadStudentsData(true));
    }

    loadStudentsData();
  }

  // -------------------------------------------------------------------------
  // 3. FAQ ACCORDION PAGE: faq.html
  // -------------------------------------------------------------------------
  const faqContainer = document.getElementById('faqContainer');
  if (faqContainer) {
    let allFaqs = [];
    const faqSearch = document.getElementById('faqSearch');
    const clearFaqSearch = document.getElementById('clearFaqSearch');
    const faqCategory = document.getElementById('faqCategory');
    const refreshFaqBtn = document.getElementById('refreshFaqBtn');
    const faqsDataSource = document.getElementById('faqsDataSource');
    const faqsCountText = document.getElementById('faqsCountText');

    async function loadFaqs(forceRefresh = false) {
      faqContainer.innerHTML = `
        <div class="loading-state">
          <div class="spinner"></div>
          <p>Fetching FAQs from data/faqs.json...</p>
        </div>
      `;

      try {
        const res = await fetchData('data/faqs.json', 'charusat_faqs', forceRefresh);
        allFaqs = res.data;

        if (faqsDataSource) {
          faqsDataSource.textContent = res.source === 'live' ? '🌐 Live Fetch API' : '⚡ LocalStorage Cache';
          faqsDataSource.className = 'source-badge ' + (res.source === 'live' ? '' : 'cached');
        }

        // Populate Category Dropdown
        const categories = ['All Categories', ...new Set(allFaqs.map(f => f.category))];
        faqCategory.innerHTML = categories.map(c => {
          const val = c === 'All Categories' ? '' : c;
          return `<option value="${val}">${c}</option>`;
        }).join('');

        renderFaqs();
      } catch (err) {
        faqContainer.innerHTML = `
          <div class="error-state">
            <h4>Failed to Load FAQs</h4>
            <p>${err.message}</p>
            <button class="btn btn-sm mt-10" id="retryFaqBtn">Retry</button>
          </div>
        `;
        const retry = document.getElementById('retryFaqBtn');
        if (retry) retry.addEventListener('click', () => loadFaqs(true));
      }
    }

    function renderFaqs() {
      const query = faqSearch.value.trim().toLowerCase();
      const selectedCat = faqCategory.value;

      const filtered = allFaqs.filter(faq => {
        const matchesQuery = !query ||
          faq.question.toLowerCase().includes(query) ||
          faq.answer.toLowerCase().includes(query) ||
          (faq.tags && faq.tags.some(t => t.toLowerCase().includes(query)));

        const matchesCat = !selectedCat || faq.category === selectedCat;

        return matchesQuery && matchesCat;
      });

      if (faqsCountText) {
        faqsCountText.textContent = `Showing ${filtered.length} FAQs`;
      }

      if (filtered.length === 0) {
        faqContainer.innerHTML = `
          <div class="empty-state">
            <h4>No FAQs Found</h4>
            <p>No questions matched your search criteria.</p>
          </div>
        `;
        return;
      }

      faqContainer.innerHTML = filtered.map(faq => `
        <div class="faq-item">
          <div class="faq-question">
            <div>
              <span class="faq-category-tag">${faq.category}</span>
              ${faq.question}
            </div>
            <span class="faq-icon">+</span>
          </div>
          <div class="faq-answer">
            <p>${faq.answer}</p>
          </div>
        </div>
      `).join('');

      // Attach accordion click events
      faqContainer.querySelectorAll('.faq-question').forEach(header => {
        header.addEventListener('click', () => {
          const item = header.parentElement;
          const wasActive = item.classList.contains('active');

          // Close other open accordions
          faqContainer.querySelectorAll('.faq-item.active').forEach(openItem => {
            if (openItem !== item) openItem.classList.remove('active');
          });

          // Toggle current
          item.classList.toggle('active', !wasActive);
        });
      });
    }

    faqSearch.addEventListener('input', renderFaqs);
    faqCategory.addEventListener('change', renderFaqs);
    if (clearFaqSearch) {
      clearFaqSearch.addEventListener('click', () => {
        faqSearch.value = '';
        renderFaqs();
      });
    }
    if (refreshFaqBtn) {
      refreshFaqBtn.addEventListener('click', () => loadFaqs(true));
    }

    loadFaqs();
  }

  // -------------------------------------------------------------------------
  // 4. DASHBOARD PAGE: dashboard.html (Dynamic Stats & Previews)
  // -------------------------------------------------------------------------
  const dashTotalStudents = document.getElementById('dashTotalStudents');
  if (dashTotalStudents) {
    async function loadDashboardData() {
      try {
        const [studentsRes, eventsRes, faqsRes] = await Promise.all([
          fetchData('data/students.json', 'charusat_students'),
          fetchData('data/events.json', 'charusat_events'),
          fetchData('data/faqs.json', 'charusat_faqs')
        ]);

        // 1. Update Counts
        dashTotalStudents.textContent = studentsRes.data.length + '+';
        const totalEventsEl = document.getElementById('dashTotalEvents');
        if (totalEventsEl) totalEventsEl.textContent = eventsRes.data.length;
        const totalFaqsEl = document.getElementById('dashTotalFaqs');
        if (totalFaqsEl) totalFaqsEl.textContent = faqsRes.data.length;

        // 2. Render Upcoming Events Preview (top 3)
        const eventsPreview = document.getElementById('dashEventsPreview');
        if (eventsPreview) {
          const topEvents = eventsRes.data.slice(0, 3);
          eventsPreview.innerHTML = topEvents.map(ev => `
            <div class="card" style="padding: 16px;">
              <span class="badge-${(ev.category || 'general').toLowerCase()} event-badge">${ev.category}</span>
              <h4 style="margin: 8px 0 4px;">${ev.title}</h4>
              <p style="font-size: 0.85rem; color: var(--muted); margin: 0;">📅 ${ev.date} • 📍 ${ev.venue}</p>
            </div>
          `).join('');
        }

        // 3. Render Top Scholars Preview (top 3 by CGPA)
        const studentsPreview = document.getElementById('dashStudentsPreview');
        if (studentsPreview) {
          const topScholars = [...studentsRes.data].sort((a, b) => b.cgpa - a.cgpa).slice(0, 3);
          studentsPreview.innerHTML = topScholars.map(st => `
            <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 12px;">
              <div class="avatar-circle" style="background-color: ${st.avatarColor || '#2563eb'}; width: 40px; height: 40px; font-size: 0.95rem;">
                ${st.avatarInitials || st.name.split(' ').map(n=>n[0]).join('')}
              </div>
              <div>
                <h4 style="margin: 0; font-size: 0.95rem;">${st.name}</h4>
                <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--muted);">${st.department} • ⭐ CGPA: ${st.cgpa}</p>
              </div>
            </div>
          `).join('');
        }
      } catch (err) {
        console.warn('Dashboard fetch issue:', err);
      }
    }
    loadDashboardData();
  }

  // -------------------------------------------------------------------------
  // 5. HOME PAGE: index.html (Dynamic Upcoming Events Spotlight)
  // -------------------------------------------------------------------------
  const homeEventsPreview = document.getElementById('homeEventsPreview');
  if (homeEventsPreview) {
    async function loadHomeEvents() {
      try {
        const res = await fetchData('data/events.json', 'charusat_events');
        const topEvents = res.data.slice(0, 3);
        homeEventsPreview.innerHTML = topEvents.map(ev => `
          <div class="card" style="padding: 16px;">
            <span class="badge-${(ev.category || 'general').toLowerCase()} event-badge">${ev.category}</span>
            <h4 style="margin: 8px 0 6px;">${ev.title}</h4>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 10px;">📅 ${ev.date} • 📍 ${ev.venue}</p>
            <p style="font-size: 0.88rem; margin: 0;">${ev.description.substring(0, 100)}...</p>
          </div>
        `).join('');
      } catch (err) {
        homeEventsPreview.innerHTML = `<p style="color: var(--muted); font-size: 0.9rem;">Check back soon for upcoming campus events.</p>`;
      }
    }
    loadHomeEvents();
  }
});


