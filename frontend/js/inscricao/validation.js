export class InscricaoValidator {
    constructor() {
        this.rules = {};
        this.errors = {};
        this.validators = {
            required: (value) => value !== null && value !== undefined && value !== '',
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            cpf: (value) => this.validarCPF(value),
            telefone: (value) => /^[\d\s\(\)\-]+$/.test(value.replace(/\s/g, '')) && value.replace(/\D/g, '').length >= 10,
            minLength: (value, min) => value.length >= min,
            maxLength: (value, max) => value.length <= max,
            numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
            date: (value) => !isNaN(Date.parse(value)),
            custom: null
        };
    }

    validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        
        if (cpf.length !== 11) return false;
        if (/^(\d)\1+$/.test(cpf)) return false;
        
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;
        
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;
        
        return true;
    }

    addRule(field, rule, message = null) {
        if (!this.rules[field]) {
            this.rules[field] = [];
        }
        this.rules[field].push({ rule, message });
    }

    validateField(field, value) {
        const fieldRules = this.rules[field] || [];
        const errors = [];

        for (const { rule, message } of fieldRules) {
            const [ruleName, ...params] = rule.split(':');
            const validator = this.validators[ruleName];
            
            if (!validator) continue;
            
            let isValid = false;
            if (ruleName === 'required') {
                isValid = validator(value);
            } else if (ruleName === 'minLength' || ruleName === 'maxLength') {
                isValid = validator(value, parseInt(params[0]));
            } else if (ruleName === 'custom') {
                isValid = params[0] ? eval(params[0])(value) : true;
            } else {
                isValid = validator(value);
            }

            if (!isValid) {
                errors.push(message || this.getDefaultMessage(ruleName, params));
            }
        }

        this.errors[field] = errors;
        return errors.length === 0;
    }

    validateForm(formData) {
        this.errors = {};
        let isValid = true;

        for (const [field, value] of Object.entries(formData)) {
            if (!this.validateField(field, value)) {
                isValid = false;
            }
        }

        return isValid;
    }

    getErrors(field = null) {
        if (field) {
            return this.errors[field] || [];
        }
        return this.errors;
    }

    hasErrors(field = null) {
        if (field) {
            return this.errors[field] && this.errors[field].length > 0;
        }
        return Object.keys(this.errors).length > 0;
    }

    getDefaultMessage(ruleName, params = []) {
        const messages = {
            required: 'Este campo é obrigatório',
            email: 'E-mail inválido',
            cpf: 'CPF inválido',
            telefone: 'Telefone inválido',
            minLength: `Mínimo de ${params[0]} caracteres`,
            maxLength: `Máximo de ${params[0]} caracteres`,
            numeric: 'Deve ser um número',
            date: 'Data inválida'
        };
        return messages[ruleName] || 'Valor inválido';
    }

    clearErrors(field = null) {
        if (field) {
            delete this.errors[field];
        } else {
            this.errors = {};
        }
    }

    setupRealTimeValidation(element, field, rules) {
        if (!element) return;

        rules.forEach(rule => {
            this.addRule(field, rule.rule, rule.message);
        });

        let debounceTimer;
        const validate = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const value = element.value || element.checked || element.selectedOptions?.[0]?.value;
                this.validateField(field, value);
                this.updateFieldUI(element, field);
            }, 300);
        };

        element.addEventListener('input', validate);
        element.addEventListener('change', validate);
        element.addEventListener('blur', () => {
            clearTimeout(debounceTimer);
            const value = element.value || element.checked || element.selectedOptions?.[0]?.value;
            this.validateField(field, value);
            this.updateFieldUI(element, field);
        });
    }

    updateFieldUI(element, field) {
        const hasError = this.hasErrors(field);
        const parent = element.closest('.form-group, .mb-4, .mb-3') || element.parentElement;
        
        if (parent) {
            if (hasError) {
                parent.classList.add('has-error');
                element.classList.add('border-red-500', 'border-2');
                element.classList.remove('border-green-500');
                
                let errorDiv = parent.querySelector('.error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                    parent.appendChild(errorDiv);
                }
                errorDiv.textContent = this.getErrors(field)[0];
            } else {
                parent.classList.remove('has-error');
                element.classList.remove('border-red-500', 'border-2');
                
                if (element.value && element.value.trim() !== '') {
                    element.classList.add('border-green-500', 'border-2');
                }
                
                const errorDiv = parent.querySelector('.error-message');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        }
    }

    setupFormValidation(formSelector) {
        const form = typeof formSelector === 'string' 
            ? document.querySelector(formSelector) 
            : formSelector;
        
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            if (!this.validateForm(data)) {
                e.preventDefault();
                this.showFormErrors();
                return false;
            }
            
            return true;
        });
    }

    showFormErrors() {
        const firstError = Object.keys(this.errors)[0];
        if (firstError) {
            const element = document.querySelector(`[name="${firstError}"]`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                element.focus();
            }
        }
    }
}

let globalValidator = null;

export function getValidator() {
    if (!globalValidator) {
        globalValidator = new InscricaoValidator();
    }
    return globalValidator;
}

export function initValidator() {
    globalValidator = new InscricaoValidator();
    window.inscricaoValidator = globalValidator;
    return globalValidator;
}
