(() => {
    'use strict';

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
            departureField.style.display = isRoundTrip ? '' : 'none';
        }
    };

    if (transferMode) {
        transferMode.addEventListener('change', syncTransferMode);
        syncTransferMode();
    }

    // =========================================================================
    // PLACE AUTOCOMPLETE - API integration
    // =========================================================================
    const placeHidden = document.getElementById('place_id');
    const placeQuery = document.getElementById('place_query');
    const suggestions = document.getElementById('places_suggestions');

    if (placeQuery && placeHidden && suggestions) {
        let debounceTimer;

        const fetchPlaces = async () => {
            const q = placeQuery.value.trim();

            if (q.length < 2) {
                suggestions.innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`/api/places?q=${encodeURIComponent(q)}`);
                const data = await response.json();
                const items = Array.isArray(data.items) ? data.items : [];

                suggestions.innerHTML = '';

                items.forEach((item) => {
                    const li = document.createElement('li');
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.textContent = `${item.name} · ${item.zone_name}`;
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        placeHidden.value = String(item.id);
                        placeQuery.value = item.name;
                        suggestions.innerHTML = '';
                    });

                    li.appendChild(button);
                    suggestions.appendChild(li);
                });
            } catch (error) {
                console.error('Place fetch error:', error);
            }
        };

        placeQuery.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchPlaces, 300);
        });

        document.addEventListener('click', (e) => {
            if (!placeQuery.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.innerHTML = '';
            }
        });
    }

    // =========================================================================
    // FLOATING CONTACT BUTTON (FAB)
    // =========================================================================
    const fabToggleBtn = document.getElementById('fab-toggle');
    const fabChannels = document.getElementById('fab-channels');

    if (fabToggleBtn && fabChannels) {
        const toggleFab = () => {
            const isOpen = fabChannels.classList.contains('is-open');
            fabChannels.classList.toggle('is-open', !isOpen);
            fabToggleBtn.classList.toggle('is-open', !isOpen);
            fabToggleBtn.setAttribute('aria-expanded', String(!isOpen));
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
                fabChannels.classList.remove('is-open');
                fabToggleBtn.classList.remove('is-open');
                fabToggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // =========================================================================
    // HERO SLIDER - Minimal rotation
    // =========================================================================
    const heroSlides = Array.from(document.querySelectorAll('[data-hero-slide]'));

    if (heroSlides.length > 1) {
        let activeIndex = 0;

        // Set initial state
        heroSlides[0]?.classList.add('is-active');

        // Rotate every 6 seconds
        setInterval(() => {
            heroSlides[activeIndex]?.classList.remove('is-active');
            activeIndex = (activeIndex + 1) % heroSlides.length;
            heroSlides[activeIndex]?.classList.add('is-active');
        }, 6000);
    }

})();