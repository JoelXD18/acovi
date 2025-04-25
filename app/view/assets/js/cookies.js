document.addEventListener('DOMContentLoaded', function() {
    // Configuración
    const COOKIE_NAME = 'ayto_villacanada_cookies_v2'; // Nombre más específico
    const COOKIE_VALUE = 'aceptadas';
    const COOKIE_EXPIRE_DAYS = 365;
    
    // Elementos del DOM
    const cookieNotice = document.getElementById('aviso-cookies');
    const cookieOverlay = document.getElementById('fondo-aviso-cookies');
    const acceptBtn = document.getElementById('btn-aceptar-cookies');

    // Verificar si los elementos existen
    if (!cookieNotice || !cookieOverlay || !acceptBtn) {
        console.error('Error: Elementos del banner de cookies no encontrados');
        return;
    }

    // Función para verificar cookies
    function checkCookie() {
        const cookieData = localStorage.getItem(COOKIE_NAME);
        return cookieData === COOKIE_VALUE;
    }

    // Función para guardar la preferencia
    function setCookie() {
        localStorage.setItem(COOKIE_NAME, COOKIE_VALUE);
        const date = new Date();
        date.setTime(date.getTime() + (COOKIE_EXPIRE_DAYS * 24 * 60 * 60 * 1000));
        document.cookie = `${COOKIE_NAME}=${COOKIE_VALUE};expires=${date.toUTCString()};path=/`;
        
        // Notificar a Google Tag Manager
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({'event': 'cookies-aceptadas'});
    }

    // Mostrar el banner si no hay preferencia guardada
    if (!checkCookie()) {
        cookieNotice.classList.remove('hidden');
        cookieOverlay.classList.remove('hidden');
        console.log('Mostrando banner de cookies');
    } else {
        console.log('Cookies ya aceptadas');
    }

    // Evento para aceptar cookies
    acceptBtn.addEventListener('click', function() {
        setCookie();
        cookieNotice.classList.add('hidden');
        cookieOverlay.classList.add('hidden');
        console.log('Cookies aceptadas - preferencia guardada');
const botonAceptarCookies = document.getElementById('btn-aceptar-cookies');
const avisoCookies = document.getElementById('aviso-cookies');
const fondoAvisoCookies = document.getElementById('fondo-aviso-cookies');

// Inicializar dataLayer si no existe
window.dataLayer = window.dataLayer || [];

if(!localStorage.getItem('cookies-aceptadas')) {
    avisoCookies.classList.remove('hidden');
    fondoAvisoCookies.classList.remove('hidden');
} else {
    dataLayer.push({'event': 'cookies-aceptadas'});
}

botonAceptarCookies.addEventListener('click', () => {
    avisoCookies.classList.add('hidden');
    fondoAvisoCookies.classList.add('hidden');

    localStorage.setItem('cookies-aceptadas', true);

    dataLayer.push({'event': 'cookies-aceptadas'});
});
    });
});