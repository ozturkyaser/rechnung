/**
 * Main JavaScript for Rechnungsprogramm
 */

// Globale Konfiguration
const App = {
    baseUrl: window.location.origin,

    /**
     * Initialisierung
     */
    init: function() {
        this.initTooltips();
        this.initConfirmDelete();
        this.initAutoFormatting();
        this.initDatepicker();
    },

    /**
     * Bootstrap Tooltips initialisieren
     */
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    /**
     * Lösch-Bestätigung
     */
    initConfirmDelete: function() {
        document.querySelectorAll('.confirm-delete').forEach(function(element) {
            element.addEventListener('click', function(e) {
                if (!confirm('Möchten Sie diesen Eintrag wirklich löschen?')) {
                    e.preventDefault();
                }
            });
        });
    },

    /**
     * Auto-Formatierung für Währung
     */
    initAutoFormatting: function() {
        document.querySelectorAll('.format-currency').forEach(function(element) {
            element.addEventListener('blur', function() {
                let value = parseFloat(this.value.replace(',', '.'));
                if (!isNaN(value)) {
                    this.value = value.toFixed(2).replace('.', ',');
                }
            });
        });
    },

    /**
     * Datepicker initialisieren
     */
    initDatepicker: function() {
        // Wenn eine Datepicker-Library verwendet wird
        // Hier kann z.B. Flatpickr oder Bootstrap Datepicker initialisiert werden
    },

    /**
     * AJAX Request Helper
     */
    ajax: function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        };

        const config = Object.assign({}, defaults, options);

        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            });
    },

    /**
     * Loading Spinner anzeigen
     */
    showLoading: function() {
        const overlay = document.createElement('div');
        overlay.className = 'spinner-overlay';
        overlay.id = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Laden...</span></div>';
        document.body.appendChild(overlay);
    },

    /**
     * Loading Spinner ausblenden
     */
    hideLoading: function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    },

    /**
     * Toast Benachrichtigung
     */
    toast: function(message, type = 'success') {
        const colors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        // Einfache Toast-Implementation
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header" style="background-color: ${colors[type]}; color: white;">
                    <strong class="me-auto">Benachrichtigung</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    },

    /**
     * Formatiere Betrag
     */
    formatMoney: function(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    /**
     * Formatiere Datum
     */
    formatDate: function(date) {
        return new Intl.DateTimeFormat('de-DE').format(new Date(date));
    }
};

// Invoice/Offer Item Management
const InvoiceItems = {
    itemCount: 0,

    /**
     * Füge neue Position hinzu
     */
    addItem: function() {
        this.itemCount++;
        const template = document.getElementById('item-template');
        const clone = template.content.cloneNode(true);

        // Update IDs und Names
        clone.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace('[]', `[${this.itemCount}]`);
        });

        document.getElementById('invoice-items').appendChild(clone);
        this.updateTotals();
    },

    /**
     * Entferne Position
     */
    removeItem: function(button) {
        button.closest('.item-row').remove();
        this.updateTotals();
    },

    /**
     * Berechne Positionssumme
     */
    calculateItemTotal: function(row) {
        const quantity = parseFloat(row.querySelector('[name*="[quantity]"]').value) || 0;
        const price = parseFloat(row.querySelector('[name*="[price]"]').value.replace(',', '.')) || 0;
        const discount = parseFloat(row.querySelector('[name*="[discount]"]').value) || 0;
        const taxRate = parseFloat(row.querySelector('[name*="[tax_rate]"]').value) || 19;

        const subtotal = quantity * price;
        const discountAmount = subtotal * (discount / 100);
        const netTotal = subtotal - discountAmount;
        const taxAmount = netTotal * (taxRate / 100);
        const grossTotal = netTotal + taxAmount;

        row.querySelector('.item-total').textContent = App.formatMoney(grossTotal);

        return {
            net: netTotal,
            tax: taxAmount,
            gross: grossTotal
        };
    },

    /**
     * Aktualisiere Gesamtsummen
     */
    updateTotals: function() {
        let totalNet = 0;
        let totalTax = 0;
        let totalGross = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const totals = this.calculateItemTotal(row);
            totalNet += totals.net;
            totalTax += totals.tax;
            totalGross += totals.gross;
        });

        document.getElementById('total-net').textContent = App.formatMoney(totalNet);
        document.getElementById('total-tax').textContent = App.formatMoney(totalTax);
        document.getElementById('total-gross').textContent = App.formatMoney(totalGross);

        // Hidden Inputs für Form Submit
        document.getElementById('input-total-net').value = totalNet.toFixed(2);
        document.getElementById('input-total-tax').value = totalTax.toFixed(2);
        document.getElementById('input-total-gross').value = totalGross.toFixed(2);
    }
};

// Customer Search (Autocomplete)
const CustomerSearch = {
    init: function(inputId, resultsId) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);

        if (!input || !results) return;

        input.addEventListener('input', function() {
            const term = this.value;

            if (term.length < 2) {
                results.innerHTML = '';
                results.classList.add('d-none');
                return;
            }

            App.ajax(`/api/customers/search?q=${encodeURIComponent(term)}`)
                .then(data => {
                    results.innerHTML = '';

                    if (data.length === 0) {
                        results.innerHTML = '<div class="list-group-item">Keine Kunden gefunden</div>';
                    } else {
                        data.forEach(customer => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.innerHTML = `
                                <strong>${customer.customer_number}</strong> -
                                ${customer.company_name || (customer.first_name + ' ' + customer.last_name)}
                            `;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                CustomerSearch.selectCustomer(customer);
                                results.classList.add('d-none');
                            });
                            results.appendChild(item);
                        });
                    }

                    results.classList.remove('d-none');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    },

    selectCustomer: function(customer) {
        document.getElementById('customer_id').value = customer.id;
        document.getElementById('customer-search').value = customer.customer_number;
        // Weitere Felder können hier gefüllt werden
    }
};

// Initialisierung beim Laden
document.addEventListener('DOMContentLoaded', function() {
    App.init();
});
