<?php $pageTitle = "Connexion"; ?>

<div class="login-container">

    <h1>Connexion</h1>

    <!-- ÉCRAN 1 : formulaire email/password/id -->
    <form id="login-form" class="login-form">

        <div id="login-error" class="alert alert-error" style="display:none;"></div>

        <div class="form-group">
            <label for="user_type">Vous êtes :</label>
            <select id="user_type" name="user_type" required>
                <option value="client">Client</option>
                <option value="employee">Employé</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="id" id="id-label">Identifiant client</label>
            <input type="text" id="id" name="id" required>
        </div>

        <button type="submit" class="btn-primary">Se connecter</button>
    </form>

    <!-- ÉCRAN 2 : saisie code OTP (caché au départ) -->
    <form id="otp-form" class="otp-form" style="display:none;">

        <div id="otp-error" class="alert alert-error" style="display:none;"></div>

        <p>Un code a été envoyé à votre email. Saisissez-le ci-dessous :</p>

        <div class="form-group">
            <label for="otp_code">Code OTP</label>
            <input type="text" id="otp_code" name="otp_code" maxlength="6" required>
        </div>

        <button type="submit" class="btn-primary">Vérifier</button>
    </form>

</div>

<script>
// Change le label de l'identifiant selon le rôle choisi
document.getElementById('user_type').addEventListener('change', function () {
    const labels = {
        client: 'Identifiant client',
        employee: 'Identifiant employé',
        admin: 'Identifiant admin',
    };
    document.getElementById('id-label').textContent = labels[this.value];
});

let pendingUserId = null;
let pendingUserType = null;

// ÉTAPE 1 — Soumission email/password/id
document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const errorBox = document.getElementById('login-error');
    errorBox.style.display = 'none';

    const payload = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        id: document.getElementById('id').value,
        user_type: document.getElementById('user_type').value,
    };

    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            errorBox.textContent = data.message || 'Erreur de connexion.';
            errorBox.style.display = 'block';
            return;
        }

        // Succès : on passe à l'étape OTP
        pendingUserId = data.user_id;
        pendingUserType = data.user_type;

        document.getElementById('login-form').style.display = 'none';
        document.getElementById('otp-form').style.display = 'block';

    } catch (err) {
        errorBox.textContent = 'Erreur réseau. Réessayez.';
        errorBox.style.display = 'block';
    }
});

// ÉTAPE 2 — Soumission du code OTP
document.getElementById('otp-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const errorBox = document.getElementById('otp-error');
    errorBox.style.display = 'none';

    const payload = {
        user_id: pendingUserId,
        user_type: pendingUserType,
        code: document.getElementById('otp_code').value,
    };

    try {
        const response = await fetch('/api/verify-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            errorBox.textContent = data.message || 'Code invalide.';
            errorBox.style.display = 'block';
            return;
        }

        // Succès : on stocke le token et on redirige
        sessionStorage.setItem('jwt_token', data.token);
        sessionStorage.setItem('session_token', data.session_token);
        sessionStorage.setItem('user_type', data.user_type);

        window.location.href = '/dashboard';

    } catch (err) {
        errorBox.textContent = 'Erreur réseau. Réessayez.';
        errorBox.style.display = 'block';
    }
});
</script>