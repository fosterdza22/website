// ============================================================
// Hostel Agency - front-end helpers
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- AJAX Hostel Filtering (student/hostels.php) ----
  const filterForm = document.getElementById('filterForm');
  const resultsBox = document.getElementById('hostelResults');

  if (filterForm && resultsBox) {
    const runFilter = () => {
      const params = new URLSearchParams(new FormData(filterForm)).toString();
      resultsBox.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
      fetch((window.BASE_URL || '') + '/student/filter_hostels.php?' + params)
        .then(r => r.text())
        .then(html => { resultsBox.innerHTML = html; })
        .catch(() => { resultsBox.innerHTML = '<p class="text-danger">Could not load hostels. Try again.</p>'; });
    };

    filterForm.addEventListener('input', debounce(runFilter, 350));
    filterForm.addEventListener('submit', function (e) { e.preventDefault(); runFilter(); });
    runFilter(); // initial load
  }

  function debounce(fn, delay) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // ---- Gallery lightbox (simple) ----
  document.querySelectorAll('.gallery-thumb').forEach(img => {
    img.addEventListener('click', () => {
      const modalImg = document.getElementById('lightboxImg');
      const modalEl = document.getElementById('lightboxModal');
      if (modalImg && modalEl) {
        modalImg.src = img.src;
        new bootstrap.Modal(modalEl).show();
      }
    });
  });

  // ---- Compare page: limit checkbox selection to 3 ----
  const compareBoxes = document.querySelectorAll('.compare-checkbox');
  if (compareBoxes.length) {
    compareBoxes.forEach(box => {
      box.addEventListener('change', () => {
        const checked = document.querySelectorAll('.compare-checkbox:checked');
        if (checked.length > 3) {
          box.checked = false;
          alert('You can compare a maximum of 3 hostels at a time.');
        }
      });
    });
  }

  // ---- Profile picture live preview (register / profile pages) ----
  const picInput = document.getElementById('profile_picture');
  const picPreview = document.getElementById('avatarPreview');
  if (picInput && picPreview) {
    picInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { picPreview.src = e.target.result; };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
});

// ---- Leaflet Map builder (called from pages with data attributes) ----
function buildMap(containerId, points, center) {
  const map = L.map(containerId).setView(center, 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const campusIcon = L.divIcon({ className: 'campus-marker', html: '🎓', iconSize: [30, 30] });
  L.marker(center, { icon: campusIcon }).addTo(map).bindPopup('<b>University Campus</b>');

  points.forEach(p => {
    L.marker([p.lat, p.lng]).addTo(map)
      .bindPopup(`<b>${p.name}</b><br>${p.distance} km from campus<br><a href="${(window.BASE_URL || '')}/student/hostel_detail.php?id=${p.id}">View details</a>`);
  });
  return map;
}
