(function() {
    'use strict';

    const header = document.getElementById('header');
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const scrollTopBtn = document.getElementById('scrollTop');
    const body = document.body;

    const userDropdown = document.getElementById('userDropdown');
    const userDropdownToggle = document.getElementById('userDropdownToggle');

    function initHeaderScroll() {
        if (!header) return;
        
        let ticking = false;
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    const scrollTop = window.pageYOffset;
                    
                    if (scrollTop > 10) {
                        header.classList.add('header--scrolled');
                    } else {
                        header.classList.remove('header--scrolled');
                    }
                    
                    if (scrollTopBtn) {
                        if (scrollTop > 500) {
                            scrollTopBtn.classList.add('scroll-top--visible');
                        } else {
                            scrollTopBtn.classList.remove('scroll-top--visible');
                        }
                    }
                    
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    function initScrollToTop() {
        if (!scrollTopBtn) return;
        
        scrollTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    function initMobileMenu() {
        if (!menuToggle || !navMenu) return;

        const mobileOverlayEl = document.getElementById('mobileOverlay') || createMobileOverlay();

        function createMobileOverlay() {
            const overlay = document.createElement('div');
            overlay.id = 'mobileOverlay';
            overlay.className = 'mobile-overlay';
            document.body.appendChild(overlay);
            return overlay;
        }

        function openMenu() {
            navMenu.classList.add('nav--open');
            mobileOverlayEl.classList.add('mobile-overlay--visible');
            menuToggle.classList.add('menu-toggle--active');
            menuToggle.setAttribute('aria-expanded', 'true');
            
            body.style.overflow = 'hidden';
            body.style.touchAction = 'none';
            
            setTimeout(function() {
                const firstLink = navMenu.querySelector('.nav__link');
                if (firstLink) firstLink.focus();
            }, 350);
        }

        function closeMenu() {
            navMenu.classList.remove('nav--open');
            mobileOverlayEl.classList.remove('mobile-overlay--visible');
            menuToggle.classList.remove('menu-toggle--active');
            menuToggle.setAttribute('aria-expanded', 'false');
            
            body.style.overflow = '';
            body.style.touchAction = '';
            
            menuToggle.focus();
        }

        function toggleMenu() {
            if (navMenu.classList.contains('nav--open')) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });

        mobileOverlayEl.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('nav--open')) {
                e.preventDefault();
                closeMenu();
            }
        });

        const navLinks = navMenu.querySelectorAll('.nav__link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    setTimeout(closeMenu, 150);
                }
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && navMenu.classList.contains('nav--open')) {
                closeMenu();
            }
        });
    }

    function initUserDropdown() {
        if (!userDropdown || !userDropdownToggle) return;
        
        const dropdownMenu = document.getElementById('userDropdownMenu');
        if (!dropdownMenu) return;

        function openDropdown() {
            userDropdown.classList.add('user-dropdown--open');
            userDropdownToggle.setAttribute('aria-expanded', 'true');
        }

        function closeDropdown() {
            userDropdown.classList.remove('user-dropdown--open');
            userDropdownToggle.setAttribute('aria-expanded', 'false');
        }

        function toggleDropdown(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (userDropdown.classList.contains('user-dropdown--open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        }

        userDropdownToggle.addEventListener('click', toggleDropdown);

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && userDropdown.classList.contains('user-dropdown--open')) {
                closeDropdown();
                userDropdownToggle.focus();
            }
        });

        const dropdownLinks = dropdownMenu.querySelectorAll('a');
        dropdownLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                setTimeout(closeDropdown, 100);
            });
        });
    }

    function initActiveLinks() {
        const currentPath = window.location.pathname;
        const currentFile = currentPath.split('/').pop();
        const navLinks = document.querySelectorAll('.nav__link');
        
        navLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (!href) return;
            const linkFile = href.split('/').pop();
            if (currentFile === linkFile) {
                navLinks.forEach(function(l) { l.classList.remove('nav__link--active'); });
                link.classList.add('nav__link--active');
            }
        });
    }

    function initSmoothScroll() {
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;
            
            const targetId = link.getAttribute('href');
            if (targetId === '#' || !targetId) return;
            
            const targetEl = document.querySelector(targetId);
            if (!targetEl) return;
            
            e.preventDefault();
            
            const headerHeight = header ? header.offsetHeight + 20 : 20;
            const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - headerHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        });
    }

    function initAlerts() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        
        alerts.forEach(function(alert, index) {
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-8px)';
                    alert.style.transition = 'all 0.3s ease-out';
                    setTimeout(function() {
                        if (alert.parentNode) alert.remove();
                    }, 300);
                }
            }, 5000 + (index * 800));
        });
    }

    function init() {
        initHeaderScroll();
        initScrollToTop();
        initMobileMenu();
        initUserDropdown();
        initActiveLinks();
        initSmoothScroll();
        initAlerts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();