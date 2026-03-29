function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';

    bytes.forEach((b) => {
        binary += String.fromCharCode(b);
    });

    return btoa(binary)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');
}

function base64UrlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = '='.repeat((4 - (base64.length % 4)) % 4);
    const binary = atob(base64 + pad);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes.buffer;
}

async function registerPasskey() {
    if (!window.PublicKeyCredential || !navigator.credentials) {
        alert('WebAuthn non supporté sur ce navigateur.');
        return;
    }

    try {
        const optionsResponse = await fetch('/auth/passkey/register/options', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        const options = await optionsResponse.json();

        if (!optionsResponse.ok) {
            throw new Error(options.error || 'Impossible de récupérer les options d’enregistrement.');
        }

        const publicKey = {
            ...options,
            challenge: base64UrlToBuffer(options.challenge),
            user: {
                ...options.user,
                id: base64UrlToBuffer(options.user.id)
            }
        };

        const credential = await navigator.credentials.create({
            publicKey
        });

        if (!credential) {
            throw new Error('Création de la passkey annulée.');
        }

        const payload = {
            id: credential.id,
            rawId: bufferToBase64Url(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
                attestationObject: bufferToBase64Url(credential.response.attestationObject)
            }
        };

        const verifyResponse = await fetch('/auth/passkey/register/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        const data = await verifyResponse.json();

        if (!verifyResponse.ok) {
            throw new Error(data.error || 'Erreur lors de la vérification de la passkey.');
        }

        alert(data.message || 'Passkey enregistrée avec succès.');
    } catch (error) {
        console.error('registerPasskey error:', error);
        alert(error.message || 'Erreur lors de l’ajout de la passkey.');
    }
}

async function loginWithPasskey() {
    if (!window.PublicKeyCredential || !navigator.credentials) {
        alert('WebAuthn non supporté sur ce navigateur.');
        return;
    }

    try {
        const optionsResponse = await fetch('/api/auth/passkey/login/options', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        const options = await optionsResponse.json();

        if (!optionsResponse.ok) {
            throw new Error(options.error || 'Impossible de récupérer les options de connexion.');
        }

        const publicKey = {
            ...options,
            challenge: base64UrlToBuffer(options.challenge)
        };

        if (publicKey.allowCredentials && Array.isArray(publicKey.allowCredentials)) {
            publicKey.allowCredentials = publicKey.allowCredentials.map((cred) => ({
                ...cred,
                id: base64UrlToBuffer(cred.id)
            }));
        }

        const assertion = await navigator.credentials.get({
            publicKey
        });

        if (!assertion) {
            throw new Error('Connexion avec passkey annulée.');
        }

        const payload = {
            id: assertion.id,
            rawId: bufferToBase64Url(assertion.rawId),
            type: assertion.type,
            response: {
                clientDataJSON: bufferToBase64Url(assertion.response.clientDataJSON),
                authenticatorData: bufferToBase64Url(assertion.response.authenticatorData),
                signature: bufferToBase64Url(assertion.response.signature),
                userHandle: assertion.response.userHandle
                    ? bufferToBase64Url(assertion.response.userHandle)
                    : null
            }
        };

        const verifyResponse = await fetch('/api/auth/passkey/login/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        const data = await verifyResponse.json();

        if (!verifyResponse.ok) {
            throw new Error(data.error || 'Erreur de connexion avec passkey.');
        }

        if (data.token) {
            localStorage.setItem('token', data.token);
        }

        if (data.refresh_token) {
            localStorage.setItem('refresh_token', data.refresh_token);
        }

        alert(data.message || 'Connexion réussie avec passkey.');
       window.location.href = window.location.origin + '/';
    } catch (error) {
        console.error('loginWithPasskey error:', error);
        alert(error.message || 'Erreur de connexion.');
    }
}

async function refreshToken() {
    const refreshTokenValue = localStorage.getItem('refresh_token');

    if (!refreshTokenValue) {
        throw new Error('Refresh token introuvable.');
    }

    const response = await fetch('/api/token/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            refresh_token: refreshTokenValue
        })
    });

    const data = await response.json();

    if (data.token) {
        localStorage.setItem('token', data.token);

        if (data.refresh_token) {
            localStorage.setItem('refresh_token', data.refresh_token);
        }

        return data.token;
    }

    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');

    throw new Error(data.error || 'Impossible de renouveler le token.');
}

async function authFetch(url, options = {}) {
    let token = localStorage.getItem('token');

    options.headers = options.headers || {};

    if (token) {
        options.headers['Authorization'] = 'Bearer ' + token;
    }

    let response = await fetch(url, options);

    if (response.status === 401) {
        token = await refreshToken();
        options.headers['Authorization'] = 'Bearer ' + token;
        response = await fetch(url, options);
    }

    return response;
}

document.addEventListener('DOMContentLoaded', () => {
    const addPasskeyBtn = document.getElementById('add-passkey-btn');
    const loginPasskeyBtn = document.getElementById('login-passkey-btn');

    if (addPasskeyBtn) {
        addPasskeyBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await registerPasskey();
        });
    }

    if (loginPasskeyBtn) {
        loginPasskeyBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await loginWithPasskey();
        });
    }
});