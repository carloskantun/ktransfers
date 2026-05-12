(() => {
    'use strict';

    const pageLang = (document.documentElement.lang || 'en').slice(0, 2).toLowerCase();
    const uiText = {
        placesEmpty: pageLang === 'es' ? 'No encontramos hoteles con ese nombre.' : 'We could not find hotels with that name.',
        placesError: pageLang === 'es' ? 'No se pudo consultar el catalogo de hoteles.' : 'Hotel catalog could not be loaded.',
    };

    const setupPlaceAutocomplete = ({
        queryId,
        hiddenId,
        listId,
        zoneId = null,
        selectedNameId = null,
        minChars = 1,
    }) => {
        const queryInput = document.getElementById(queryId);
        const hiddenInput = document.getElementById(hiddenId);
        const suggestions = document.getElementById(listId);
        const zoneInput = zoneId ? document.getElementById(zoneId) : null;
        const selectedNameInput = selectedNameId ? document.getElementById(selectedNameId) : null;

        if (!queryInput || !hiddenInput || !suggestions) {
            return;
        }

        const fieldBlock = suggestions.closest('.field-block');

        const setOpen = (open) => {
            suggestions.style.display = open ? 'block' : 'none';
            fieldBlock?.classList.toggle('is-open', open);
        };

        const closeSuggestions = () => {
            suggestions.innerHTML = '';
            setOpen(false);
        };

        const renderEmptyState = (message) => {
            suggestions.innerHTML = '';
            const li = document.createElement('li');
            li.className = 'places-list-empty';
            li.textContent = message;
            suggestions.appendChild(li);
            setOpen(true);
        };

        const fetchPlaces = async () => {
            const q = queryInput.value.trim();

            if (q.length < minChars) {
                closeSuggestions();
                return;
            }

            try {
                const response = await fetch(`/api/places?q=${encodeURIComponent(q)}`);
                const data = await response.json();
                const items = Array.isArray(data.items) ? data.items : [];

                suggestions.innerHTML = '';

                if (items.length === 0) {
                    renderEmptyState(uiText.placesEmpty);
                    return;
                }

                items.forEach((item) => {
                    const li = document.createElement('li');
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'places-list-button';
                    button.innerHTML = `<strong>${item.name}</strong><span>${item.zone_name}</span>`;
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        hiddenInput.value = String(item.id);
                        queryInput.value = item.name;

                        if (selectedNameInput) {
                            selectedNameInput.value = item.name;
                        }

                        if (zoneInput && item.zone_id) {
                            zoneInput.value = String(item.zone_id);
                            zoneInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        closeSuggestions();
                    });

                    li.appendChild(button);
                    suggestions.appendChild(li);
                });

                suggestions.style.display = 'block';
                fieldBlock?.classList.add('is-open');
            } catch (error) {
                console.error('Place fetch error:', error);
                renderEmptyState(uiText.placesError);
            }
        };

        let debounceTimer;

        queryInput.addEventListener('input', () => {
            hiddenInput.value = '';
            if (selectedNameInput) {
                selectedNameInput.value = '';
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchPlaces, 250);
        });

        queryInput.addEventListener('focus', () => {
            if (queryInput.value.trim().length >= minChars) {
                fetchPlaces();
            }
        });

        document.addEventListener('click', (event) => {
            if (!queryInput.contains(event.target) && !suggestions.contains(event.target)) {
                closeSuggestions();
            }
        });
    };

    // =========================================================================
    // FORM SYNC - Transfer mode logic
    // =========================================================================
    const transferMode = document.getElementById('transfer_mode');
    const tripType = document.getElementById('trip_type');
    const direction = document.getElementById('direction');
    const departureInput = document.getElementById('departure_datetime');
    const departureField = document.getElementById('departure_field');

    const syncTransferMode = () => {
        if (!transferMode || !tripType || !direction) return;

        const isRoundTrip = transferMode.value === 'ROUND_TRIP';

        if (tripType) tripType.value = isRoundTrip ? 'ROUND_TRIP' : 'ONE_WAY';
        if (direction) direction.value = isRoundTrip ? 'AIRPORT_TO_DESTINATION' : transferMode.value;

        if (departureInput) {
            departureInput.disabled = !isRoundTrip;
            if (!isRoundTrip) departureInput.value = '';
        }

        if (departureField) {
            departureField.classList.toggle('is-disabled', !isRoundTrip);
            departureField.setAttribute('aria-disabled', isRoundTrip ? 'false' : 'true');
        }
    };

    if (transferMode) {
        transferMode.addEventListener('change', syncTransferMode);
        syncTransferMode();
    }

    // =========================================================================
    // PLACE AUTOCOMPLETE - API integration
    // =========================================================================
    setupPlaceAutocomplete({
        queryId: 'place_query',
        hiddenId: 'place_id',
        listId: 'places_suggestions',
        minChars: 1,
    });

    setupPlaceAutocomplete({
        queryId: 'admin_place_query',
        hiddenId: 'place_id',
        listId: 'admin_places_suggestions',
        zoneId: 'zone_id',
        selectedNameId: 'place_name',
        minChars: 1,
    });

    // =========================================================================
    // FLOATING CONTACT BUTTON (FAB)
    // =========================================================================
    const fabToggleBtn = document.getElementById('fab-toggle');
    const fabChannels = document.getElementById('fab-channels');

    if (fabToggleBtn && fabChannels) {
        const fabRoot = fabToggleBtn.closest('.fab-contact');

        const openFab = () => {
            fabChannels.classList.add('is-open');
            fabToggleBtn.classList.add('is-open');
            fabToggleBtn.setAttribute('aria-expanded', 'true');
        };

        const closeFab = () => {
            fabChannels.classList.remove('is-open');
            fabToggleBtn.classList.remove('is-open');
            fabToggleBtn.setAttribute('aria-expanded', 'false');
        };

        const toggleFab = () => {
            const isOpen = fabChannels.classList.contains('is-open');
            if (isOpen) {
                closeFab();
            } else {
                openFab();
            }
        };

        fabToggleBtn.addEventListener('click', toggleFab);

        fabToggleBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleFab();
            }
        });

        document.addEventListener('click', (e) => {
            if (!fabToggleBtn.contains(e.target) && !fabChannels.contains(e.target)) {
                closeFab();
            }
        });

        document.querySelectorAll('a[href$="#contact"]').forEach((link) => {
            link.addEventListener('click', () => {
                window.setTimeout(openFab, 260);
            });
        });

        if (window.location.hash === '#contact') {
            window.setTimeout(openFab, 450);
        }

        const bookingForm = document.getElementById('booking-form');
        const mobileQuery = window.matchMedia('(max-width: 768px)');

        if (fabRoot && bookingForm && 'IntersectionObserver' in window) {
            const syncFabVisibility = (entry) => {
                fabRoot.classList.toggle('fab-contact--hidden-mobile', mobileQuery.matches && entry.isIntersecting);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(syncFabVisibility);
            }, { threshold: 0.12 });

            observer.observe(bookingForm);

            mobileQuery.addEventListener?.('change', () => {
                if (!mobileQuery.matches) {
                    fabRoot.classList.remove('fab-contact--hidden-mobile');
                }
            });
        }
    }

    // =========================================================================
    // THEME TOGGLE - Day/Night mode
    // =========================================================================
    const themeToggle = document.getElementById('theme-toggle');

    if (themeToggle) {
        const THEME_STORAGE_KEY = 'ktransfers-theme-preference';
        const THEME_DAY = 'day';
        const THEME_NIGHT = 'night';
        const brandLogo = document.getElementById('site-brand-logo');

        // Get stored preference or current theme from body class
        const getStoredTheme = () => {
            const stored = localStorage.getItem(THEME_STORAGE_KEY);
            if (stored === THEME_DAY || stored === THEME_NIGHT) {
                return stored;
            }

            // Get current theme from body class
            const bodyClass = document.body.className;
            if (bodyClass.includes('home-theme-night')) {
                return THEME_NIGHT;
            }
            if (bodyClass.includes('home-theme-day')) {
                return THEME_DAY;
            }

            // Default to day
            return THEME_DAY;
        };

        const applyTheme = (theme) => {
            const body = document.body;
            const isNight = theme === THEME_NIGHT;

            // Remove both theme classes
            body.classList.remove('home-theme-day', 'home-theme-night');

            // Add the appropriate theme class
            body.classList.add(isNight ? 'home-theme-night' : 'home-theme-day');

            // Store preference
            localStorage.setItem(THEME_STORAGE_KEY, theme);

            if (brandLogo) {
                const logoDay = (brandLogo.dataset.logoDay || '').trim();
                const logoNight = (brandLogo.dataset.logoNight || '').trim();
                const targetLogo = isNight ? (logoNight || logoDay) : (logoDay || logoNight);

                if (targetLogo !== '') {
                    brandLogo.src = targetLogo;
                }
            }

            // Update ARIA label
            themeToggle.setAttribute('aria-label', isNight ? 'Switch to light mode' : 'Switch to dark mode');
        };

        // Initialize theme
        const initialTheme = getStoredTheme();
        applyTheme(initialTheme);

        // Toggle theme on click
        themeToggle.addEventListener('click', () => {
            const currentTheme = getStoredTheme();
            const newTheme = currentTheme === THEME_DAY ? THEME_NIGHT : THEME_DAY;
            applyTheme(newTheme);
        });
    }

    // =========================================================================
    // HERO SLIDER - Minimal rotation
    // =========================================================================
    const heroSlides = Array.from(document.querySelectorAll('[data-hero-slide]'));

    if (heroSlides.length > 1) {
        let activeIndex = 0;
        const heroMedia = document.querySelector('.hero-media');
        const hasVideoBackground = Boolean(heroMedia && heroMedia.classList.contains('has-video'));
        const rotationInterval = hasVideoBackground ? 9000 : 6000;

        // Set initial state
        heroSlides[0]?.classList.add('is-active');

        // Rotate slower when video is present to avoid visual overload.
        setInterval(() => {
            heroSlides[activeIndex]?.classList.remove('is-active');
            activeIndex = (activeIndex + 1) % heroSlides.length;
            heroSlides[activeIndex]?.classList.add('is-active');
        }, rotationInterval);
    }

})();
