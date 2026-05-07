document.addEventListener('DOMContentLoaded', () => {
    checkAuthState();
    loadMovies();

    const reqForm = document.getElementById('requestMovieForm');
    if (reqForm) {
        reqForm.addEventListener('submit', handleRequestMovie);
    }
    document.getElementById('authForm').addEventListener('submit', handleAuthSubmit);
    
    document.getElementById('btnBook').addEventListener('click', () => handleReservationAction('book'));
    document.getElementById('btnCancel').addEventListener('click', () => handleReservationAction('cancel'));
});

// State
let currentSelectedMovie = null;
let currentSelectedSeat = null;
let authAction = 'login'; // 'login' or 'register'
let currentUser = null;

// --- Auth UI Logic ---
function checkAuthState() {
    currentUser = localStorage.getItem('currentUser');
    const guestBar = document.getElementById('authGuest');
    const userBar = document.getElementById('authUser');
    const adminLinkBtn = document.getElementById('adminLinkBtn');
    
    if (currentUser) {
        guestBar.style.display = 'none';
        userBar.style.display = 'flex';
        document.getElementById('loggedInUsername').textContent = currentUser;

        if (adminLinkBtn) {
            const isAdmin = localStorage.getItem('isAdmin');
            if (isAdmin === 'true') {
                adminLinkBtn.style.display = 'inline-block';
            } else {
                adminLinkBtn.style.display = 'none';
            }
        }
    } else {
        guestBar.style.display = 'flex';
        userBar.style.display = 'none';
        if (adminLinkBtn) adminLinkBtn.style.display = 'none';
    }
}

function openAuthModal(action) {
    authAction = action;
    document.getElementById('authModalTitle').textContent = action === 'login' ? 'Giriş Yap' : 'Kayıt Ol';
    document.getElementById('authSubmitBtn').textContent = action === 'login' ? 'Giriş' : 'Kayıt Ol';
    document.getElementById('authModal').classList.add('active');
    document.getElementById('authUsername').focus();
}

function closeAuthModal() {
    document.getElementById('authModal').classList.remove('active');
    document.getElementById('authUsername').value = '';
    document.getElementById('authPassword').value = '';
}

async function handleAuthSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('authUsername').value.trim();
    const password = document.getElementById('authPassword').value;

    const endpoint = authAction === 'login' ? '../services/api.php?action=login' : '../services/api.php?action=register';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ username, password })
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            if (authAction === 'login' || authAction === 'register') {
                localStorage.setItem('currentUser', username);
                if (result.is_admin) {
                    localStorage.setItem('isAdmin', 'true');
                } else {
                    localStorage.setItem('isAdmin', 'false');
                }
                checkAuthState();
            }
            closeAuthModal();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Bağlantı hatası.', 'error');
    }
}

async function handleLogout() {
    try {
        await fetch('../services/api.php?action=logout', { credentials: 'same-origin' });
    } catch (e) {
        console.error(e);
    }
    localStorage.removeItem('currentUser');
    localStorage.removeItem('isAdmin');
    checkAuthState();
    showToast('Çıkış yapıldı', 'success');
}


// --- Movie API Logic ---

async function loadMovies() {
    try {
        const response = await fetch('../services/api.php?action=movies');
        const result = await response.json();
        if (result.success) {
            renderShowcase(result.data);
            renderMovies(result.data);
        } else {
            showToast('Filmler yüklenemedi.', 'error');
        }
    } catch (error) {
        showToast('Sunucuya bağlanılamadı.', 'error');
    }
}

async function handleRequestMovie(e) {
    e.preventDefault();
    if (!currentUser) {
        showToast('Film talep etmek için giriş yapmalısınız.', 'error');
        return;
    }
    const movieName = document.getElementById('requestMovieName').value;

    try {
        const response = await fetch('../services/api.php?action=request_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ movie_name: movieName, user_name: currentUser })
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('requestMovieForm').reset();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Talep gönderilirken hata oluştu.', 'error');
    }
}

async function handleReservationAction(actionType) {
    if (!currentUser) {
        showToast('Lütfen önce giriş yapınız.', 'error');
        closeModal();
        return;
    }

    const endpoint = actionType === 'book' ? '../services/api.php?action=book' : '../services/api.php?action=cancel';

    const requestBody = {
        movie_name: currentSelectedMovie,
        seat_number: currentSelectedSeat,
        user_name: currentUser
    };

    if (actionType === 'book') {
        requestBody.ticket_type = document.getElementById('ticketType').value;
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestBody)
        });
        
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            closeModal();
            loadMovies();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('İşlem sırasında bir hata oluştu.', 'error');
    }
}


// --- Rendering Logic ---

function renderShowcase(movies) {
    const list = document.getElementById('showcaseList');
    list.innerHTML = '';
    
    if (movies.length === 0) {
        list.innerHTML = '<p style="color:var(--text-secondary); text-align:center;">Vizyonda film yok.</p>';
        return;
    }

    movies.forEach(movie => {
        const card = document.createElement('div');
        card.className = 'showcase-card';

        let trailerHtml = '';
        if (movie.trailer_url) {
            trailerHtml = `
            <div class="video-container">
                <iframe src="${movie.trailer_url}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>`;
        } else {
            trailerHtml = `<div class="video-container" style="background:#111; display:flex; align-items:center; justify-content:center; color:#555;">Fragman Yok</div>`;
        }

        card.innerHTML = `
            ${trailerHtml}
            <div class="showcase-info">
                <h3>${movie.name}</h3>
                <p>${movie.description || "Bu film için özet girilmemiştir."}</p>
            </div>
        `;
        list.appendChild(card);
    });
}

function renderMovies(movies) {
    const moviesList = document.getElementById('moviesList');
    moviesList.innerHTML = '';

    if (movies.length === 0) {
        moviesList.innerHTML = '<p style="color:var(--text-secondary); grid-column: 1/-1; text-align:center;">Vizyonda film yok.</p>';
        return;
    }

    movies.forEach(movie => {
        const card = document.createElement('div');
        card.className = 'movie-card';

        const header = `
            <div class="movie-header">
                <div class="movie-title">${movie.name}</div>
                <div class="seat-stats">
                    <span class="stat-item">Boş: <strong>${movie.available_seats}</strong></span>
                    <span class="stat-item">Toplam: <strong>${movie.total_seats}</strong></span>
                </div>
            </div>
        `;

        const seatsContainer = document.createElement('div');
        seatsContainer.className = 'seats-container';

        for (let i = 1; i <= movie.total_seats; i++) {
            const seatWrapper = document.createElement('div');
            const reservation = movie.reservations[i];
            const isBooked = reservation !== undefined;
            
            seatWrapper.className = `seat ${isBooked ? 'booked' : ''}`;
            seatWrapper.textContent = i;
            
            if (isBooked) {
                seatWrapper.title = `Dolu: ${reservation.user_name} (${reservation.ticket_type})`;
            } else {
                seatWrapper.title = 'Boş - Seçim İçin Tıklayın';
            }

            seatWrapper.addEventListener('click', () => {
                openModal(movie, i, isBooked, reservation);
            });

            seatsContainer.appendChild(seatWrapper);
        }

        card.innerHTML = header;
        card.appendChild(seatsContainer);
        moviesList.appendChild(card);
    });
}

function openModal(movie, seatNumber, isBooked, reservation) {
    if(!currentUser) {
        showToast('Üye olmayan kişiler bilet satın alamaz. Lütfen giriş yapın!', 'error');
        return;
    }

    currentSelectedMovie = movie.name;
    currentSelectedSeat = seatNumber;

    document.getElementById('modalTitle').textContent = movie.name;
    document.getElementById('modalSeatNumber').textContent = seatNumber;
    
    const statusEl = document.getElementById('modalStatus');
    const btnBook = document.getElementById('btnBook');
    const btnCancel = document.getElementById('btnCancel');

    if (isBooked) {
        statusEl.className = 'status-badge status-booked';
        statusEl.textContent = `Dolu (${reservation.user_name} - ${reservation.ticket_type})`;
        
        // Sadece bileti alan kişi iptal butonunu görebilir (veya backend zaten engeller ama UI olarak da kapatalım)
        if (reservation.user_name === currentUser) {
            btnCancel.style.display = 'block';
        } else {
            btnCancel.style.display = 'none';
        }
        btnBook.style.display = 'none';
        document.getElementById('ticketTypeGroup').style.display = 'none';
    } else {
        statusEl.className = 'status-badge status-available';
        statusEl.textContent = 'Müsait';
        btnBook.style.display = 'block';
        btnCancel.style.display = 'none';
        document.getElementById('ticketTypeGroup').style.display = 'block';
    }

    document.getElementById('bookingModal').classList.add('active');
}

function closeModal() {
    document.getElementById('bookingModal').classList.remove('active');
    currentSelectedMovie = null;
    currentSelectedSeat = null;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    if (window.toastTimeout) {
        clearTimeout(window.toastTimeout);
    }
    window.toastTimeout = setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// --- Tabs Logic ---
function switchTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabId).classList.add('active');
    
    // Mark clicked button as active
    const btn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabId));
    if(btn) btn.classList.add('active');
}
