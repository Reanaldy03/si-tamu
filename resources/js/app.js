import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { Html5Qrcode } from 'html5-qrcode';
import jsQR from 'jsqr';
import Chart from 'chart.js/auto';

Alpine.plugin(focus);

window.Alpine = Alpine;
window.Html5Qrcode = Html5Qrcode;
window.jsQR = jsQR;
window.Chart = Chart;

// Synthesize pleasant chime using Web Audio API (zero external audio file dependency)
window.playBeep = function (freq = 880, durationMs = 150) {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + durationMs / 1000);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + durationMs / 1000);
    } catch (e) {
        // Silently ignore audio autoplay restrictions
    }
};

Alpine.start();
