<!-- Font Awesome (icons for thumbs-down). Use the path that opens in your browser. -->
<link rel="stylesheet" href="/pace/Online_portal/font-awesome/css/font-awesome.min.css">

<!-- Small compatibility block (brings back yellow/red classes even if skins CSS isn’t loaded) -->
<style>
  .bg-yellow     { background-color:#f39c12 !important; color:#000 !important; }
  .bg-red-active { background-color:#dd4b39 !important; color:#fff !important; }
  .text-red      { color:#dd4b39 !important; }
</style>

_______

<script>
(function () {
  function normalizeAndColorizeCell(td) {
    if (!td || td.tagName === 'TH') return; // skip headers
    var raw = (td.textContent || '').trim();

    // Thumbs-down for NQKE or -1 (works with FA4/FA5/FA6 classes present)
    if (raw.toUpperCase() === 'NQKE' || raw === '-1') {
      td.innerHTML = "<i class='fa fa-thumbs-down fas fa-thumbs-down fa-solid fa-thumbs-down text-red' title='No Queue / Error'></i>";
      return;
    }

    // Numeric cells only (strict int, keeps minus sign)
    var numMatch = raw.match(/^-?\d+$/);
    if (!numMatch) return;

    var n = parseInt(numMatch[0], 10);

    // Column 0 is usually the name; don't color it
    var cellIndex = td.cellIndex;
    if (cellIndex === 0) return;

    // 1..98 => show 0 with tooltip
    if (n >= 1 && n < 99) {
      td.setAttribute('title', raw);
      td.textContent = '0';
      return;
    }
    // 99..499 => yellow
    if (n >= 99 && n < 500) {
      td.classList.add('bg-yellow', 'text-center');
      td.textContent = String(n);
      return;
    }
    // 500+ => red
    if (n >= 500) {
      td.classList.add('bg-red-active');
      td.textContent = String(n);
      return;
    }
  }

  function processTables() {
    document.querySelectorAll('table tbody td').forEach(normalizeAndColorizeCell);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', processTables);
  } else {
    processTables();
  }

  // If your tables auto-refresh, keep re-applying styles
  setInterval(processTables, 5000);
})();
</script>
