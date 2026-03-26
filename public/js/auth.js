function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    bytes.forEach(b => binary += String.fromCharCode(b));
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function base64UrlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = '='.repeat((4 - base64.length % 4) % 4);
    const binary = atob(base64 + pad);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes.buffer;
}

async function registerPasskey() {
    const token = localStorage.getItem('token');

    const optionsResponse = await fetch('/api/auth/passkey/register/options', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    });

    const options = await optionsResponse.json();

    options.challenge = base64UrlToBuffer(options.challenge);
    options.user.id = base64UrlToBuffer(btoa(options.user.id).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''));

    const credential = await navigator.credentials.create({
        publicKey: options
    });

    const payload = {
        id: credential.id,
        rawId: bufferToBase64Url(credential.rawId),
        type: credential.type,
        response: {
            clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
            attestationObject: bufferToBase64Url(credential.response.attestationObject),
        }
    };

    const verifyResponse = await fetch('/api/auth/passkey/register/verify', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    return await verifyResponse.json();
}

async function loginWithPasskey() {
    if (!navigator.credentials) {
        alert('WebAuthn non supporté sur ce navigateur.');
        return;
    }

    const optionsResponse = await fetch('/api/auth/passkey/login/options', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    });

    const options = await optionsResponse.json();
    options.challenge = base64UrlToBuffer(options.challenge);

    const assertion = await navigator.credentials.get({
        publicKey: options
    });

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
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    const data = await verifyResponse.json();

    if (data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('refresh_token', data.refresh_token);
        alert('Connexion réussie avec passkey.');
    } else {
        alert(data.error || 'Erreur de connexion.');
    }
}

async function refreshToken() {
    const refreshToken = localStorage.getItem('refresh_token');

    const response = await fetch('/api/token/refresh', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ refresh_token: refreshToken })
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
    throw new Error('Impossible de renouveler le token.');
}

async function authFetch(url, options = {}) {
    let token = localStorage.getItem('token');

    options.headers = options.headers || {};
    options.headers['Authorization'] = 'Bearer ' + token;

    let response = await fetch(url, options);

    if (response.status === 401) {
        token = await refreshToken();
        options.headers['Authorization'] = 'Bearer ' + token;
        response = await fetch(url, options);
    }

    return response;
}