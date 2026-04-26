<header class="header">
  <div class="header-left">
    <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12h18M3 6h18M3 18h18"/>
      </svg>
    </button>
    <div class="search-box">
      <svg class="icon-sm" style="position:absolute;margin:10px" viewBox="0 0 24 24" fill="none" stroke="var(--text3)" stroke-width="2">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" placeholder="Search... (Ctrl+K)" style="background:none;border:none;outline:none;color:var(--text);width:100%;padding-left:2rem;font-size:0.85rem">
    </div>
  </div>
  <div class="header-right">
    <button class="icon-btn" aria-label="Notifications">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="badge badge-danger" style="position:absolute;margin-top:8px;margin-left:12px">3</span>
    </button>
    <button class="icon-btn" aria-label="Messages">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    </button>
    <div class="user-menu" onclick="toggleUserMenu()">
      <div class="avatar">A</div>
      <div style="display:flex;flex-direction:column;text-align:left">
        <span style="font-size:0.8rem;font-weight:600;color:var(--text)">Admin User</span>
        <span style="font-size:0.7rem;color:var(--text3)">Super Admin</span>
      </div>
      <svg class="icon-sm" style="color:var(--text3)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="m6 9 6 6 6-6"/>
      </svg>
    </div>
  </div>
</header>

<script>
function toggleSidebar() {
  document.querySelector('.sidebar').classList.toggle('collapsed');
}
function toggleUserMenu() {
  // Toggle user dropdown
}
// Keyboard shortcut
window.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    document.querySelector('.search-box input').focus();
  }
});
</script>
