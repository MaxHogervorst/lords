export default function searchableDropdown() {
    return {
        search: '',
        isOpen: false,
        selectedValue: null,
        selectedLabel: '',
        placeholder: 'Select an option',
        options: [], // Default empty array, will be overridden by getter in views

        init() {
            // Close dropdown when clicking outside
            this.$watch('isOpen', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                }
            });

            // Listen for form reset events
            const form = this.$el.closest('form');
            if (form) {
                form.addEventListener('reset', () => {
                    this.$nextTick(() => {
                        this.clearSelection();
                    });
                });
            }
        },

        get filteredOptions() {
            if (!this.options || !Array.isArray(this.options)) {
                return [];
            }

            if (!this.search) {
                return this.options;
            }

            const searchLower = this.search.toLowerCase().trim();

            // Score each option based on fuzzy match
            const scored = this.options.map(option => {
                const label = this.getOptionLabel(option);
                const score = this.fuzzyScore(label.toLowerCase(), searchLower);
                return { option, score };
            })
            .filter(item => item.score > 0) // Only keep matches
            .sort((a, b) => b.score - a.score); // Sort by best match first

            return scored.map(item => item.option);
        },

        fuzzyScore(text, search) {
            // If exact substring match, give high score
            if (text.includes(search)) {
                const index = text.indexOf(search);
                // Bonus if match is at start
                return 1000 + (index === 0 ? 500 : 0) + (1000 - index);
            }

            // Fuzzy match: characters must appear in order
            let searchIndex = 0;
            let textIndex = 0;
            let score = 0;
            let consecutiveMatches = 0;
            let lastMatchIndex = -1;

            while (textIndex < text.length && searchIndex < search.length) {
                if (text[textIndex] === search[searchIndex]) {
                    // Character matches
                    searchIndex++;

                    // Bonus for consecutive matches
                    if (textIndex === lastMatchIndex + 1) {
                        consecutiveMatches++;
                        score += 5 + consecutiveMatches * 2; // Increasing bonus
                    } else {
                        consecutiveMatches = 0;
                        score += 1;
                    }

                    // Bonus if match is at start of word
                    if (textIndex === 0 || text[textIndex - 1] === ' ') {
                        score += 10;
                    }

                    lastMatchIndex = textIndex;
                }
                textIndex++;
            }

            // Only return score if all search characters were found
            return searchIndex === search.length ? score : 0;
        },

        getOptionLabel(option) {
            if (typeof option === 'string') {
                return option;
            }
            // Support different label fields
            return option.label || option.name || option.text ||
                   (option.firstname && option.lastname ? `${option.firstname} ${option.lastname}` : '') ||
                   String(option.id || '');
        },

        getOptionValue(option) {
            if (typeof option === 'string') {
                return option;
            }
            return option.value !== undefined ? option.value : option.id;
        },

        selectOption(option) {
            this.selectedValue = this.getOptionValue(option);
            this.selectedLabel = this.getOptionLabel(option);
            this.isOpen = false;
            this.search = '';

            // Emit custom event for parent component
            this.$dispatch('option-selected', {
                value: this.selectedValue,
                label: this.selectedLabel,
                option: option
            });
        },

        clearSelection() {
            this.selectedValue = null;
            this.selectedLabel = '';
            this.search = '';

            this.$dispatch('option-selected', {
                value: null,
                label: '',
                option: null
            });
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (!this.isOpen) {
                this.search = '';
            }
        },

        closeDropdown() {
            this.isOpen = false;
            this.search = '';
        },

        handleKeydown(event) {
            // Handle keyboard navigation
            if (event.key === 'Escape') {
                this.closeDropdown();
            } else if (event.key === 'Enter' && this.filteredOptions.length === 1) {
                event.preventDefault();
                this.selectOption(this.filteredOptions[0]);
            }
        }
    };
}
