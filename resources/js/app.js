import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    const initialCurrency = document.body.dataset.currency || 'IDR';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const currencyRoute = document.querySelector('meta[name="currency-route"]')?.content ?? '/profile/currency';

    Alpine.store('currency', {
        code: initialCurrency,
        symbol: initialCurrency === 'USD' ? '$' : initialCurrency === 'SGD' ? 'S$' : 'Rp',
        set(code) {
            this.code = code;
            this.symbol = code === 'USD' ? '$' : code === 'SGD' ? 'S$' : 'Rp';
            fetch(currencyRoute, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ currency: code }),
            });
        },
        format(value) {
            return new Intl.NumberFormat(this.code === 'IDR' ? 'id-ID' : this.code === 'SGD' ? 'en-SG' : 'en-US').format(value);
        }
    });
});

Alpine.start();
