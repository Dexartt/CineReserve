document.addEventListener('DOMContentLoaded', () => {
    loadData();
    document.getElementById('addMovieForm').addEventListener('submit', handleAddMovie);
});

async function loadData() {
    await Promise.all([
        loadAdminMovies(),
        loadAdminRequests(),
        loadAdminUsers()
    ]);
}

async function loadAdminMovies() {
    try {
        const response = await fetch('../services/api.php?action=movies');
        const result = await response.json();
        const list = document.getElementById('adminMovieList');
        list.innerHTML = '';
        
        if (result.success) {
            result.data.forEach(movie => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div>
                        <strong>${movie.name}</strong> <br>
                        <small>Koltuk: ${movie.available_seats} boş / ${movie.total_seats} toplam</small>
                    </div>
                    <button class="delete-btn" onclick="deleteMovie('${movie.name}')">Sil</button>
                `;
                list.appendChild(li);
            });
            if(result.data.length === 0) list.innerHTML = '<li>Sistemde film yok.</li>';
        }
    } catch (error) {
        showToast('Filmler yüklenemedi.', 'error');
    }
}

async function loadAdminRequests() {
    try {
        const response = await fetch('../services/api.php?action=request_movie');
        const result = await response.json();
        const list = document.getElementById('adminRequestsList');
        list.innerHTML = '';
        
        if (result.success) {
            const grouped = {};
            result.data.forEach(req => {
                const normName = req.movie_name.trim().toLowerCase();
                if (!grouped[normName]) {
                    grouped[normName] = {
                        originalName: req.movie_name.trim(),
                        count: 0,
                        users: []
                    };
                }
                grouped[normName].count++;
                if (!grouped[normName].users.includes(req.user_name)) {
                    grouped[normName].users.push(req.user_name);
                }
            });

            const sortedRequests = Object.values(grouped).sort((a, b) => b.count - a.count);

            sortedRequests.forEach(req => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                        <strong>Talep: ${req.originalName}</strong>
                        <span style="background: #f39c12; color: #111; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; font-weight: bold;">${req.count} Çift Oy</span>
                    </div>
                    <span class="req-date" style="margin-top: 5px;">İsteyenler: <strong>${req.users.join(', ')}</strong></span>
                `;
                // Adjust text based on count
                li.querySelector('span').textContent = req.count > 1 ? `${req.count} İstek` : `${req.count} İstek`;
                list.appendChild(li);
            });

            if(sortedRequests.length === 0) list.innerHTML = '<li>Henüz yeni bir talep yok.</li>';
        }
    } catch (error) {
        showToast('Talepler yüklenemedi.', 'error');
    }
}

async function loadAdminUsers() {
    try {
        const response = await fetch('../services/api.php?action=users');
        const result = await response.json();
        const list = document.getElementById('adminUsersList');
        list.innerHTML = '';
        
        if (result.success) {
            result.data.forEach(userObj => {
                const li = document.createElement('li');
                
                let adminBadge = userObj.is_admin ? '<span style="color: #f39c12; font-size: 0.8em; margin-left:10px;">(Yönetici)</span>' : '';
                let actionBtn = userObj.is_admin 
                    ? `<button class="delete-btn" style="background:#e67e22;" onclick="toggleAdminRole('${userObj.username}', false)">Yetkiyi Al</button>` 
                    : `<button class="btn primary-btn small" onclick="toggleAdminRole('${userObj.username}', true)">Yönetici Yap</button>`;
                
                // Do not allow current primary admin to remove themselves easily, but we'll leave it as is for now.
                if (userObj.username === 'enes12') {
                    actionBtn = `<span style="font-size:0.8em; opacity:0.5;">Kurucu</span>`;
                }

                li.innerHTML = `
                    <div><strong>👤 ${userObj.username}</strong> ${adminBadge}</div>
                    ${actionBtn}
                `;
                list.appendChild(li);
            });
            if(result.data.length === 0) list.innerHTML = '<li>Kayıtlı üye yok.</li>';
        }
    } catch (error) {
        showToast('Üyeler yüklenemedi.', 'error');
    }
}

async function toggleAdminRole(username, makeAdmin) {
    if (!confirm(`'${username}' adlı kullanıcıyı ${makeAdmin ? 'yönetici yapmak' : 'yöneticilikten çıkarmak'} istediğinize emin misiniz?`)) {
        return;
    }

    try {
        const response = await fetch('../services/api.php?action=toggle_admin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: username, make_admin: makeAdmin })
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            loadAdminUsers();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('İşlem başarısız.', 'error');
    }
}

async function handleAddMovie(e) {
    e.preventDefault();
    const name = document.getElementById('newMovieName').value;
    const seats = document.getElementById('newMovieSeats').value;
    const desc = document.getElementById('newMovieDesc').value;
    const trailer = document.getElementById('newMovieTrailer').value;

    try {
        const response = await fetch('../services/api.php?action=movies', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, total_seats: seats, description: desc, trailer_url: trailer })
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('addMovieForm').reset();
            loadAdminMovies();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Film eklenirken hata oluştu.', 'error');
    }
}

async function deleteMovie(movieName) {
    if (!confirm(`'${movieName}' filmini sistemden silmek istediğinize emin misiniz? Bütün bilet verileri silinecektir.`)) {
        return;
    }

    try {
        const response = await fetch('../services/api.php?action=delete_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: movieName })
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            loadAdminMovies();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Silme işlemi başarısız.', 'error');
    }
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
