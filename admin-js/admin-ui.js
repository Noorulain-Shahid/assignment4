// Sidebar toggle for mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    sidebar.classList.toggle('show');
    
    if (overlay) {
        if (sidebar.classList.contains('show')) {
            overlay.style.display = 'block';
            setTimeout(() => overlay.classList.add('show'), 10);
        } else {
            overlay.classList.remove('show');
            setTimeout(() => overlay.style.display = 'none', 300);
        }
    }
    
    // Prevent body scroll when sidebar is open on mobile
    if (window.innerWidth <= 768) {
        if (sidebar.classList.contains('show')) {
            body.style.overflow = 'hidden';
        } else {
            body.style.overflow = '';
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    sidebar.classList.remove('show');
    
    if (overlay) {
        overlay.classList.remove('show');
        setTimeout(() => overlay.style.display = 'none', 300);
    }
    
    body.style.overflow = '';
}
