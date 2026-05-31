
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('menuToggle');
        const body = document.body;

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            toggle?.classList.add('active');
            body.classList.add('sidebar-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            toggle?.classList.remove('active');
            body.classList.remove('sidebar-open');
        }

        function toggleSidebar() {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        document.querySelectorAll('.nav-item a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });
    