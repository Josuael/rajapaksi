import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('tr[data-row]').forEach(row => {
    row.addEventListener('click', () => {
      row.classList.toggle('row-selected');
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('profileBtn');
  const dd  = document.getElementById('profileDropdown');
  const wrap = document.getElementById('profileMenu');

  if (!btn || !dd || !wrap) return;

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dd.classList.toggle('d-none'); // bootstrap hide/show
  });

  document.addEventListener('click', () => {
    dd.classList.add('d-none');
  });

  wrap.addEventListener('click', (e) => e.stopPropagation());
});

// ==== Toast SweetAlert2 (from session) ====
document.addEventListener('DOMContentLoaded', () => {
  if (window.__toast && window.Swal) {
    const t = window.__toast;
    const icon = t.type || 'success';

    Swal.fire({
      icon,
      title: t.title || (icon === 'success' ? 'Success' : 'Error'),
      text: t.text || '',
      timer: 1800,
      showConfirmButton: false,
      timerProgressBar: true
    });

    // biar ga kepanggil 2x kalau ada reload partial
    window.__toast = null;
  }
});


document.addEventListener('DOMContentLoaded', () => {
  if (window.bootstrap && bootstrap.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new bootstrap.Tooltip(el);
    });
  }
});


document.getElementById('filterForm')?.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
                // allow submit
  }
});

// ==== Column Suggestion (datalist) ====
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[data-suggest-col]').forEach((inp) => {
    let t = null;

    inp.addEventListener('input', () => {
      const q = inp.value.trim();
      if (q.length < 2) return;

      clearTimeout(t);
      t = setTimeout(async () => {
        const col = inp.dataset.suggestCol;
        const baseUrl = inp.dataset.suggestUrl;

        try {
          const url = `${baseUrl}?col=${encodeURIComponent(col)}&q=${encodeURIComponent(q)}`;
          const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
          const data = await res.json();

          const dl = document.getElementById('dl-' + col);
          if (!dl) return;

          dl.innerHTML = '';
          (data || []).forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            dl.appendChild(opt);
          });
        } catch (e) {}
      }, 250);
    });
  });
});
