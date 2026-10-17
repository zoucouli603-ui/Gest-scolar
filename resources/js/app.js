import './bootstrap';

window.loadJsQR = () => import('jsqr').then((m) => m.default);
