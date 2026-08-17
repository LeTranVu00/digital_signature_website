import QRCode from 'qrcode';

export function initQrCodes() {
    document.querySelectorAll('[data-qr-code]').forEach((element) => {
        const value = element.dataset.qrValue?.trim();

        if (! value || element.dataset.qrRendered === 'true') {
            return;
        }

        const size = Number(element.dataset.qrSize || 320);
        const canvas = document.createElement('canvas');
        canvas.className = 'h-full max-h-full w-full max-w-full object-contain';
        canvas.setAttribute('aria-label', element.dataset.qrLabel || 'QR code');

        QRCode.toCanvas(canvas, value, {
            width: Number.isFinite(size) ? size : 320,
            margin: 2,
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        })
            .then(() => {
                element.replaceChildren(canvas);
                element.dataset.qrRendered = 'true';
            })
            .catch(() => {
                element.textContent = 'Không tạo được QR từ link này.';
                element.classList.add('p-4', 'text-center', 'text-sm', 'font-semibold', 'text-red-600');
            });
    });
}
