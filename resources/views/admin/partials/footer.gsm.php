<footer class="footer">
  <div class="footer-text">
    GSMSDK v2.0.0 Admin Panel • <span id="currentYear"></span> • Build <span id="buildTime"></span> • <span id="onlineUsers">0</span> active users
  </div>
</footer>

<script>
document.getElementById('currentYear').textContent = new Date().getFullYear();
document.getElementById('buildTime').textContent = new Date().toLocaleString();
setInterval(() => {
  document.getElementById('onlineUsers').textContent = Math.floor(Math.random() * 50) + 1;
}, 5000);
</script>
