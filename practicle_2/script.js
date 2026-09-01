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
});
