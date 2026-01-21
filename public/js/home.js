// Items per category
const items = {
    men: [
      '../images/1.jpg',
      '../images/2.jpg',
      '../images/3.jpg',
    ],
    women: [
        '../images/1.jpg',
        '../images/1.jpg',
        '../images/1.jpg',
    ],
    kids: [
        '../images/1.jpg',
        '../images/1.jpg',
        '../images/1.jpg',
    ]
  };
  
  // Switch active tab and update carousel
  function switchTab(category) {
    // Remove active from all tabs
    document.querySelectorAll('.tab').forEach(tab => {
      tab.classList.remove('active');
    });
  
    // Add active to clicked tab
    const activeTab = Array.from(document.querySelectorAll('.tab')).find(tab =>
      tab.textContent.trim().toLowerCase() === category
    );
    if (activeTab) activeTab.classList.add('active');
  
    // Get carousel inner & indicators
    const carouselInner = document.querySelector('#mostLovedCarousel .carousel-inner');
    const indicators = document.querySelector('#mostLovedCarousel .carousel-indicators');
  
    // Check if elements exist before manipulating
    if (!carouselInner || !indicators) {
      return; // Exit if carousel elements don't exist
    }
  
    // Clear current slides & indicators
    carouselInner.innerHTML = '';
    indicators.innerHTML = '';
  
    // Add new slides
    items[category].forEach((src, index) => {
      // Create indicator
      const li = document.createElement('li');
      li.setAttribute('data-target', '#mostLovedCarousel');
      li.setAttribute('data-slide-to', index);
      if (index === 0) li.classList.add('active');
      indicators.appendChild(li);
  
      // Create carousel item
      const div = document.createElement('div');
      div.classList.add('carousel-item');
      if (index === 0) div.classList.add('active');
  
      const img = document.createElement('img');
      img.src = src;
      img.className = 'd-block mx-auto';
      img.alt = `${category} outfit ${index+1}`;
      img.style.height = '400px';
  
      div.appendChild(img);
      carouselInner.appendChild(div);
    });
  }
  
  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', () => {
    switchTab('men');
  });


  //////////////////////////////
  document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.querySelector('.carousel-items');
    const nextBtn = document.querySelector('.next');
    const prevBtn = document.querySelector('.prev');
  
    // Check if elements exist before adding event listeners
    if (nextBtn && carousel) {
      nextBtn.addEventListener('click', () => {
        carousel.scrollBy({
          left: 200,
          behavior: 'smooth'
        });
      });
    }
  
    if (prevBtn && carousel) {
      prevBtn.addEventListener('click', () => {
        carousel.scrollBy({
          left: -200,
          behavior: 'smooth'
        });
      });
    }
  });
  