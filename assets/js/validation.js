/**
 * Form Validation Utilities
 */

const Validation = {
    /**
     * Validate CPF
     */
    cpf(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');

        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        let sum = 0;
        let remainder;

        for (let i = 1; i <= 9; i++) {
            sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }

        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(9, 10))) return false;

        sum = 0;
        for (let i = 1; i <= 10; i++) {
            sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }

        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(10, 11))) return false;

        return true;
    },

    /**
     * Validate email
     */
    email(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Validate required field
     */
    required(value) {
        return value !== null && value !== undefined && value.trim() !== '';
    },

    /**
     * Validate date
     */
    date(date) {
        const d = new Date(date);
        return d instanceof Date && !isNaN(d);
    },

    /**
     * Validate age
     */
    age(birthDate, minAge = 0, maxAge = 150) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        return age >= minAge && age <= maxAge;
    },

    /**
     * Format CPF
     */
    formatCPF(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    },

    /**
     * Format phone
     */
    formatPhone(phone) {
        phone = phone.replace(/[^\d]/g, '');
        if (phone.length === 11) {
            return phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (phone.length === 10) {
            return phone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return phone;
    },

    /**
     * Show error message
     */
    showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorElement = formGroup.querySelector('.form-error');

        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            formGroup.appendChild(errorElement);
        }

        errorElement.textContent = message;
        input.classList.add('error');
    },

    /**
     * Clear error message
     */
    clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.form-error');

        if (errorElement) {
            errorElement.remove();
        }

        input.classList.remove('error');
    }
};

/**
 * Auto-format inputs
 */
document.addEventListener('DOMContentLoaded', () => {
    // CPF formatting
    document.querySelectorAll('input[data-format="cpf"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = Validation.formatCPF(e.target.value);
        });
    });

    // Phone formatting
    document.querySelectorAll('input[data-format="phone"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = Validation.formatPhone(e.target.value);
        });
    });
});
